<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    private string $uploadFolder = 'userassets/categories';

    public function index()
    {
        $topLevelCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.index', compact('topLevelCategories'));
    }

    public function data(Request $request)
    {
        $categories = Category::with('parent')->select('categories.*');

        return DataTables::of($categories)
            ->addColumn('image', function (Category $category) {
                if ($category->image) {
                    return '<img src="' . asset($category->image) . '" width="42" height="42" class="rounded" style="object-fit:cover;">';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('display_name', function (Category $category) {
                $depth = $category->depth();
                $prefix = $depth === 1 ? '↳ ' : ($depth === 2 ? '↳↳ ' : '');
                return $prefix . '<strong>' . e($category->name) . '</strong>';
            })
            ->addColumn('level', function (Category $category) {
                return match ($category->depth()) {
                    0 => '<span class="badge bg-dark">Category</span>',
                    1 => '<span class="badge bg-secondary">Subcategory</span>',
                    default => '<span class="badge bg-light text-dark border">Child Category</span>',
                };
            })
            ->addColumn('products_count', fn (Category $category) => $category->products()->count())
            ->addColumn('status', function (Category $category) {
                $checked = $category->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $category->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (Category $category) {
                return '
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(' . $category->id . ')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $category->id . ')">Delete</button>
                    <form id="deleteForm' . $category->id . '" action="' . route('admin.categories.destroy', $category) . '" method="POST" class="d-none">
                        ' . csrf_field() . method_field('DELETE') . '
                    </form>
                ';
            })
            ->rawColumns(['image', 'display_name', 'level', 'status', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['parent_id'] = $this->resolveParentId($request, $validated);
        unset($validated['level'], $validated['category_id'], $validated['subcategory_id']);

        $validated['slug'] = $this->uniqueSlug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request->file('image'));
        }

        Category::create($validated);

        $label = match ($request->input('level')) {
            'subcategory' => 'Subcategory',
            'child' => 'Child Category',
            default => 'Category',
        };

        return redirect()->route('admin.categories.index')->with('success', "{$label} created successfully.");
    }

    public function fetch(Category $category)
    {
        $currentCategoryId = null;
        $currentSubcategoryId = null;

        if ($category->depth() === 1) {
            $currentCategoryId = $category->parent_id;
        } elseif ($category->depth() === 2) {
            $currentSubcategoryId = $category->parent_id;
            $currentCategoryId = $category->parent->parent_id;
        }

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
            'image_url' => $category->image ? asset($category->image) : null,
            'depth' => $category->depth(),
            'category_id' => $currentCategoryId,
            'subcategory_id' => $currentSubcategoryId,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $this->validateData($request);
        $validated['parent_id'] = $this->resolveParentId($request, $validated);
        unset($validated['level'], $validated['category_id'], $validated['subcategory_id']);

        if ($validated['name'] !== $category->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $category->id);
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($category->image);
            $validated['image'] = $this->uploadImage($request->file('image'));
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Delete its subcategories/child categories first.');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'This category has products attached, move or delete them first.');
        }

        $this->deleteImage($category->image);

        $category->delete();

        return back()->with('success', 'Deleted successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        return response()->json(['is_active' => $category->is_active]);
    }

    public function children(Category $category)
    {
        return response()->json(
            $category->children()->orderBy('name')->get(['id', 'name'])
        );
    }

    private function validateData(Request $request): array
    {
        $rules = [
            'level' => ['required', 'in:category,subcategory,child'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($request->input('level') === 'subcategory') {
            $rules['category_id'] = ['required', 'exists:categories,id'];
        }

        if ($request->input('level') === 'child') {
            $rules['category_id'] = ['required', 'exists:categories,id'];
            $rules['subcategory_id'] = ['required', 'exists:categories,id'];
        }

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /**
     * level=category   -> no parent (top level)
     * level=subcategory -> parent is the selected Category
     * level=child       -> parent is the selected Subcategory
     */
    private function resolveParentId(Request $request, array $validated): ?int
    {
        return match ($request->input('level')) {
            'subcategory' => $validated['category_id'],
            'child' => $validated['subcategory_id'],
            default => null,
        };
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function uploadImage($file): string
    {
        $destination = public_path($this->uploadFolder);

        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);

        return $this->uploadFolder . '/' . $filename;
    }

    private function deleteImage(?string $relativePath): void
    {
        if ($relativePath) {
            $fullPath = public_path($relativePath);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }
    }
}