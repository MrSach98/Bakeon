<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class AddonController extends Controller
{
    public function index()
    {
        return view('admin.addons.index');
    }

    public function data()
    {
        $addons = Addon::select('addons.*');

        return DataTables::of($addons)
            ->addColumn('image', function (Addon $addon) {
                if ($addon->image) {
                    return '<img src="' . asset('storage/' . $addon->image) . '" width="42" height="42" class="rounded" style="object-fit:cover;">';
                }
                return '<span class="text-muted">—</span>';
            })
            ->editColumn('price', fn (Addon $addon) => '₹' . number_format($addon->price, 2))
            ->addColumn('status', function (Addon $addon) {
                $checked = $addon->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $addon->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (Addon $addon) {
                return '
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(' . $addon->id . ')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $addon->id . ')">Delete</button>
                    <form id="deleteForm' . $addon->id . '" action="' . route('admin.addons.destroy', $addon) . '" method="POST" class="d-none">
                        ' . csrf_field() . method_field('DELETE') . '
                    </form>
                ';
            })
            ->rawColumns(['image', 'status', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('addons', 'public');
        }

        Addon::create($validated);

        return redirect()->route('admin.addons.index')->with('success', 'Addon created successfully.');
    }

    public function fetch(Addon $addon)
    {
        return response()->json([
            'id' => $addon->id,
            'name' => $addon->name,
            'price' => $addon->price,
            'stock' => $addon->stock,
            'is_active' => $addon->is_active,
            'image_url' => $addon->image ? asset('storage/' . $addon->image) : null,
        ]);
    }

    public function update(Request $request, Addon $addon)
    {
        $validated = $this->validateData($request);

        if ($validated['name'] !== $addon->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $addon->id);
        }

        if ($request->hasFile('image')) {
            if ($addon->image) {
                Storage::disk('public')->delete($addon->image);
            }
            $validated['image'] = $request->file('image')->store('addons', 'public');
        }

        $addon->update($validated);

        return redirect()->route('admin.addons.index')->with('success', 'Addon updated successfully.');
    }

    public function destroy(Addon $addon)
    {
        if ($addon->products()->exists()) {
            return back()->with('error', 'This addon is attached to products, remove it from those products first.');
        }

        if ($addon->image) {
            Storage::disk('public')->delete($addon->image);
        }

        $addon->delete();

        return back()->with('success', 'Addon deleted successfully.');
    }

    public function toggleStatus(Addon $addon)
    {
        $addon->update(['is_active' => ! $addon->is_active]);

        return response()->json(['is_active' => $addon->is_active]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['stock'] = $data['stock'] ?? 0;

        return $data;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Addon::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}