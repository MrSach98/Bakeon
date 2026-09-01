<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CouponController extends Controller
{
    public function index()
    {
        return view('admin.coupons.index');
    }

    public function data()
    {
        $coupons = Coupon::select('coupons.*');

        return DataTables::of($coupons)
            ->editColumn('value', function (Coupon $coupon) {
                return $coupon->type === 'flat'
                    ? '₹' . number_format($coupon->value, 2)
                    : number_format($coupon->value, 2) . '%';
            })
            ->addColumn('usage', function (Coupon $coupon) {
                $limit = $coupon->usage_limit ?? '∞';
                return "{$coupon->used_count} / {$limit}";
            })
            ->addColumn('validity', function (Coupon $coupon) {
                $from = $coupon->valid_from?->format('d M Y') ?? '—';
                $until = $coupon->valid_until?->format('d M Y') ?? '—';
                return "{$from} to {$until}";
            })
            ->addColumn('status', function (Coupon $coupon) {
                $checked = $coupon->is_active ? 'checked' : '';
                $expired = ! $coupon->isCurrentlyValid() && $coupon->is_active
                    ? '<div><small class="text-danger">Expired/Limit reached</small></div>'
                    : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $coupon->id . '" ' . $checked . '>
                        </div>' . $expired;
            })
            ->addColumn('actions', function (Coupon $coupon) {
                return '
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(' . $coupon->id . ')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $coupon->id . ')">Delete</button>
                    <form id="deleteForm' . $coupon->id . '" action="' . route('admin.coupons.destroy', $coupon) . '" method="POST" class="d-none">
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
        $validated['code'] = strtoupper($validated['code']);

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function fetch(Coupon $coupon)
    {
        return response()->json($coupon);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $this->validateData($request, $coupon->id);
        $validated['code'] = strtoupper($validated['code']);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return back()->with('success', 'Coupon deleted successfully.');
    }

    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return response()->json(['is_active' => $coupon->is_active]);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code' . ($ignoreId ? ",$ignoreId" : '')],
            'type' => ['required', 'in:flat,percentage'],
            'value' => ['required', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['min_order_value'] = $data['min_order_value'] ?? 0;

        // Percentage type shouldn't exceed 100
        if ($data['type'] === 'percentage' && $data['value'] > 100) {
            $data['value'] = 100;
        }

        return $data;
    }
}