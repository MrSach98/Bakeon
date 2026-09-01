<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Category;
use App\Models\DeliveryOption;
use App\Models\Flavor;
use App\Models\Occasion;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductWeight;
use App\Models\Weight;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    private string $uploadFolder = 'userassets/products';

    public function index()
    {
        return view('admin.products.index');
    }

    public function data()
    {
        $products = Product::with(['category', 'primaryImage'])->select('products.*');

        return DataTables::of($products)
            ->addColumn('image', function (Product $product) {
                $img = $product->primaryImage ?: $product->images->first();
                if ($img) {
                    return '<img src="' . asset($img->image_path) . '" width="45" height="45" class="rounded" style="object-fit:cover;">';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('category_name', fn (Product $product) => $product->category->name ?? '—')
            ->editColumn('base_price', fn (Product $product) => '₹' . number_format($product->base_price, 2))
            ->addColumn('status_badge', function (Product $product) {
                return match ($product->status) {
                    'active' => '<span class="badge bg-success">Active</span>',
                    'inactive' => '<span class="badge bg-secondary">Inactive</span>',
                    default => '<span class="badge bg-warning text-dark">Draft</span>',
                };
            })
            ->addColumn('actions', function (Product $product) {
                return '
                    <a href="' . route('admin.products.edit', $product) . '" class="btn btn-sm btn-outline-primary">Edit</a>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $product->id . ')">Delete</button>
                    <form id="deleteForm' . $product->id . '" action="' . route('admin.products.destroy', $product) . '" method="POST" class="d-none">
                        ' . csrf_field() . method_field('DELETE') . '
                    </form>
                ';
            })
            ->rawColumns(['image', 'status_badge', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $product = new Product(); 
        return view('admin.products.create', array_merge(
            $this->formData(),
            ['product' => $product] 
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        DB::transaction(function () use ($request, $validated) {
            $product = Product::create($validated);

            $this->syncRelations($request, $product);
            $this->saveWeightVariants($request, $product);
            $this->saveImages($request, $product);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'weightVariants', 'flavors', 'addons', 'deliveryOptions', 'occasions']);

        return view('admin.products.edit', array_merge(
            $this->formData(),
            ['product' => $product]
        ));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateData($request);

        if ($validated['name'] !== $product->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $product->id);
        }

        DB::transaction(function () use ($request, $product, $validated) {
            $product->update($validated);

            $this->syncRelations($request, $product);

            // Remove old variants and re-save (simplest correct approach for admin CRUD)
            $product->weightVariants()->delete();
            $this->saveWeightVariants($request, $product);

            $this->saveImages($request, $product);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            $this->deleteImageFile($image->image_path);
        }

        $product->delete();

        return back()->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update([
            'status' => $product->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json(['status' => $product->status]);
    }

    public function deleteImage(ProductImage $productImage)
    {
        $this->deleteImageFile($productImage->image_path);
        $productImage->delete();

        return response()->json(['success' => true]);
    }

    // ---------------- Helpers ----------------

    private function formData(): array
    {
        return [
            'topLevelCategories' => Category::whereNull('parent_id')->orderBy('name')->get(),
            'weights' => Weight::where('is_active', true)->orderBy('sort_order')->get(),
            'flavors' => Flavor::where('is_active', true)->orderBy('name')->get(),
            'addons' => Addon::where('is_active', true)->orderBy('name')->get(),
            'deliveryOptions' => DeliveryOption::where('is_active', true)->orderBy('name')->get(),
            'occasions' => Occasion::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:categories,id'],
            'child_category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'egg_type' => ['required', 'in:egg,eggless,both'],
            'is_photo_cake' => ['nullable', 'boolean'],
            'is_message_enabled' => ['nullable', 'boolean'],
            'message_char_limit' => ['nullable', 'integer', 'min:1'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['nullable', 'boolean'],
            'is_bestseller' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive,draft'],
        ]);

        $data['is_photo_cake'] = $request->boolean('is_photo_cake');
        $data['is_message_enabled'] = $request->boolean('is_message_enabled');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_bestseller'] = $request->boolean('is_bestseller');
        $data['message_char_limit'] = $data['message_char_limit'] ?? 30;

        return $data;
    }

    private function syncRelations(Request $request, Product $product): void
    {
        // Flavors: array of flavor_id[] with matching price_modifier[]
        $flavorSync = [];
        if ($request->filled('flavor_ids')) {
            foreach ($request->input('flavor_ids') as $i => $flavorId) {
                $flavorSync[$flavorId] = [
                    'price_modifier' => $request->input("flavor_price_modifier.$i", 0),
                    'is_default' => $request->input('flavor_default') == $flavorId,
                ];
            }
        }
        $product->flavors()->sync($flavorSync);

        // Addons: array of addon_id[]
        $product->addons()->sync($request->input('addon_ids', []));

        // Delivery options
        $product->deliveryOptions()->sync($request->input('delivery_option_ids', []));

        // Occasions
        $product->occasions()->sync($request->input('occasion_ids', []));
    }

    private function saveWeightVariants(Request $request, Product $product): void
    {
        $weightIds = $request->input('variant_weight_id', []);
        $eggTypes = $request->input('variant_egg_type', []);
        $prices = $request->input('variant_price', []);
        $discountPrices = $request->input('variant_discount_price', []);
        $stocks = $request->input('variant_stock', []);
        $defaultIndex = $request->input('variant_default', 0);

        foreach ($weightIds as $i => $weightId) {
            if (! $weightId || ! isset($prices[$i])) {
                continue;
            }

            ProductWeight::create([
                'product_id' => $product->id,
                'weight_id' => $weightId,
                'egg_type' => $eggTypes[$i] ?? 'eggless',
                'price' => $prices[$i],
                'discount_price' => $discountPrices[$i] ?: null,
                'stock' => $stocks[$i] ?? 0,
                'is_default' => (int) $defaultIndex === $i,
                'is_active' => true,
            ]);
        }
    }

    private function saveImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $existingCount = $product->images()->count();
        $destination = public_path($this->uploadFolder);

        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        foreach ($request->file('images') as $i => $file) {
            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($destination, $filename);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $this->uploadFolder . '/' . $filename,
                'is_primary' => $existingCount === 0 && $i === 0,
                'sort_order' => $existingCount + $i,
            ]);
        }
    }

    private function deleteImageFile(string $relativePath): void
    {
        $fullPath = public_path($relativePath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}