<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occasion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class OccasionController extends Controller
{
    public function index()
    {
        return view('admin.occasions.index');
    }

    public function data()
    {
        $occasions = Occasion::select('occasions.*');

        return DataTables::of($occasions)
            ->addColumn('products_count', fn (Occasion $o) => $o->products()->count())
            ->addColumn('status', function (Occasion $o) {
                $checked = $o->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $o->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (Occasion $o) {
                return '
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(' . $o->id . ')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $o->id . ')">Delete</button>
                    <form id="deleteForm' . $o->id . '" action="' . route('admin.occasions.destroy', $o) . '" method="POST" class="d-none">
                        ' . csrf_field() . method_field('DELETE') . '
                    </form>
                ';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        Occasion::create($validated);

        return redirect()->route('admin.occasions.index')->with('success', 'Occasion created successfully.');
    }

    public function fetch(Occasion $occasion)
    {
        return response()->json($occasion);
    }

    public function update(Request $request, Occasion $occasion)
    {
        $validated = $this->validateData($request);

        if ($validated['name'] !== $occasion->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $occasion->id);
        }

        $occasion->update($validated);

        return redirect()->route('admin.occasions.index')->with('success', 'Occasion updated successfully.');
    }

    public function destroy(Occasion $occasion)
    {
        if ($occasion->products()->exists()) {
            return back()->with('error', 'This occasion is attached to products, remove it from those products first.');
        }

        $occasion->delete();

        return back()->with('success', 'Occasion deleted successfully.');
    }

    public function toggleStatus(Occasion $occasion)
    {
        $occasion->update(['is_active' => ! $occasion->is_active]);

        return response()->json(['is_active' => $occasion->is_active]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
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

        while (Occasion::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}