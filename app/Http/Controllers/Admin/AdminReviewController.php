<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminReviewController extends Controller
{
    public function index()
    {
        return view('admin.reviews.index');
    }

    public function data(Request $request)
    {
        $reviews = Review::with(['product', 'user', 'images'])->select('reviews.*');

        if ($request->filled('status')) {
            $reviews->where('status', $request->status);
        }

        return DataTables::of($reviews)
            ->editColumn('created_at', fn (Review $r) => $r->created_at->format('d M Y, h:i A'))
            ->addColumn('product_name', fn (Review $r) => $r->product->name ?? '—')
            ->addColumn('customer_name', fn (Review $r) => $r->user->name ?? '—')
            ->addColumn('rating_stars', fn (Review $r) => str_repeat('★', $r->rating) . str_repeat('☆', 5 - $r->rating))
            ->addColumn('has_images', fn (Review $r) => $r->images->count() ? $r->images->count() . ' photo(s)' : '—')
            ->addColumn('status_badge', function (Review $r) {
                $colors = ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger'];
                return '<span class="badge bg-' . $colors[$r->status] . '">' . ucfirst($r->status) . '</span>';
            })
            ->addColumn('actions', function (Review $r) {
                $btns = '<a href="' . route('admin.reviews.show', $r) . '" class="btn btn-sm btn-outline-primary">View</a> ';
                if ($r->status !== 'approved') {
                    $btns .= '<button class="btn btn-sm btn-outline-success" onclick="updateReviewStatus(' . $r->id . ', \'approved\')">Approve</button> ';
                }
                if ($r->status !== 'rejected') {
                    $btns .= '<button class="btn btn-sm btn-outline-danger" onclick="updateReviewStatus(' . $r->id . ', \'rejected\')">Reject</button>';
                }
                return $btns;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(Review $review)
    {
        $review->load(['product', 'user', 'images']);
        return view('admin.reviews.show', compact('review'));
    }

    public function updateStatus(Request $request, Review $review)
    {
        $validated = $request->validate(['status' => ['required', 'in:approved,rejected']]);
        $review->update(['status' => $validated['status']]);
        return response()->json(['success' => true, 'message' => 'Review status updated.']);
    }
}