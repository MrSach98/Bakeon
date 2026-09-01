<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flavor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class FlavorController extends Controller
{
    public function index()
    {
        return view('admin.flavors.index');
    }

    public function data()
    {
        $flavors = Flavor::select('flavors.*');

        return DataTables::of($flavors)
            ->addColumn('swatch', function (Flavor $flavor) {
                if ($flavor->swatch_color) {
                    return '<span style="display:inline-block;width:20px;height:20px;border-radius:50%;background:' . e($flavor->swatch_color) . ';border:1px solid #ddd;"></span>';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('products_count', fn (Flavor $flavor) => $flavor->products()->count())
            ->addColumn('status', function (Flavor $flavor) {
                $checked = $flavor->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $flavor->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (Flavor $flavor) {
                return '
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(' . $flavor->id . ')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $flavor->id . ')">Delete</button>
                    <form id="deleteForm' . $flavor->id . '" action="' . route('admin.flavors.destroy', $flavor) . '" method="POST" class="d-none">
                        ' . csrf_field() . method_field('DELETE') . '
                    </form>
                ';
            })
            ->rawColumns(['swatch', 'status', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        Flavor::create($validated);

        return redirect()->route('admin.flavors.index')->with('success', 'Flavor created successfully.');
    }

    public function fetch(Flavor $flavor)
    {
        return response()->json($flavor);
    }

    public function update(Request $request, Flavor $flavor)
    {
        $validated = $this->validateData($request);

        if ($validated['name'] !== $flavor->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $flavor->id);
        }

        $flavor->update($validated);

        return redirect()->route('admin.flavors.index')->with('success', 'Flavor updated successfully.');
    }

    public function destroy(Flavor $flavor)
    {
        if ($flavor->products()->exists()) {
            return back()->with('error', 'This flavor is used by products, remove it from those products first.');
        }

        $flavor->delete();

        return back()->with('success', 'Flavor deleted successfully.');
    }

    public function toggleStatus(Flavor $flavor)
    {
        $flavor->update(['is_active' => ! $flavor->is_active]);

        return response()->json(['is_active' => $flavor->is_active]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'swatch_color' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Flavor::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}