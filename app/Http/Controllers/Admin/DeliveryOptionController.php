<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class DeliveryOptionController extends Controller
{
    public function index()
    {
        return view('admin.delivery-options.index');
    }

    public function data()
    {
        $options = DeliveryOption::select('delivery_options.*');

        return DataTables::of($options)
            ->editColumn('extra_charge', fn (DeliveryOption $o) => '₹' . number_format($o->extra_charge, 2))
            ->editColumn('order_cutoff_time', fn (DeliveryOption $o) => $o->order_cutoff_time ? \Carbon\Carbon::parse($o->order_cutoff_time)->format('h:i A') : '—')
            ->addColumn('window', function (DeliveryOption $o) {
                if ($o->delivery_window_start && $o->delivery_window_end) {
                    return \Carbon\Carbon::parse($o->delivery_window_start)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($o->delivery_window_end)->format('h:i A');
                }
                return '—';
            })
            ->addColumn('status', function (DeliveryOption $o) {
                $checked = $o->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $o->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (DeliveryOption $o) {
                return '
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(' . $o->id . ')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $o->id . ')">Delete</button>
                    <form id="deleteForm' . $o->id . '" action="' . route('admin.delivery-options.destroy', $o) . '" method="POST" class="d-none">
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

        DeliveryOption::create($validated);

        return redirect()->route('admin.delivery-options.index')->with('success', 'Delivery option created successfully.');
    }

    public function fetch(DeliveryOption $deliveryOption)
    {
        return response()->json($deliveryOption);
    }

    public function update(Request $request, DeliveryOption $deliveryOption)
    {
        $validated = $this->validateData($request);

        if ($validated['name'] !== $deliveryOption->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $deliveryOption->id);
        }

        $deliveryOption->update($validated);

        return redirect()->route('admin.delivery-options.index')->with('success', 'Delivery option updated successfully.');
    }

    public function destroy(DeliveryOption $deliveryOption)
    {
        if ($deliveryOption->products()->exists()) {
            return back()->with('error', 'This delivery option is attached to products.');
        }

        $deliveryOption->delete();

        return back()->with('success', 'Delivery option deleted successfully.');
    }

    public function toggleStatus(DeliveryOption $deliveryOption)
    {
        $deliveryOption->update(['is_active' => ! $deliveryOption->is_active]);

        return response()->json(['is_active' => $deliveryOption->is_active]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'order_cutoff_time' => ['nullable', 'date_format:H:i'],
            'delivery_window_start' => ['nullable', 'date_format:H:i'],
            'delivery_window_end' => ['nullable', 'date_format:H:i'],
            'extra_charge' => ['required', 'numeric', 'min:0'],
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

        while (DeliveryOption::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}