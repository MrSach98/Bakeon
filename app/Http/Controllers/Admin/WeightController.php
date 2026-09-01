<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Weight;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WeightController extends Controller
{
    public function index()
    {
        return view('admin.weights.index');
    }

    public function data()
    {
        $weights = Weight::select('weights.*');

        return DataTables::of($weights)
            ->addColumn('status', function (Weight $weight) {
                $checked = $weight->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $weight->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (Weight $weight) {
                return '
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(' . $weight->id . ')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $weight->id . ')">Delete</button>
                    <form id="deleteForm' . $weight->id . '" action="' . route('admin.weights.destroy', $weight) . '" method="POST" class="d-none">
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
        Weight::create($validated);

        return redirect()->route('admin.weights.index')->with('success', 'Weight created successfully.');
    }

    public function fetch(Weight $weight)
    {
        return response()->json($weight);
    }

    public function update(Request $request, Weight $weight)
    {
        $weight->update($this->validateData($request));

        return redirect()->route('admin.weights.index')->with('success', 'Weight updated successfully.');
    }

    public function destroy(Weight $weight)
    {
        if ($weight->products()->exists() ?? false) {
            return back()->with('error', 'This weight is used by products.');
        }

        $weight->delete();

        return back()->with('success', 'Weight deleted successfully.');
    }

    public function toggleStatus(Weight $weight)
    {
        $weight->update(['is_active' => ! $weight->is_active]);

        return response()->json(['is_active' => $weight->is_active]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'value_kg' => ['required', 'numeric', 'min:0.1'],
            'serves' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}