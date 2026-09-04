<?php

namespace App\Http\Controllers\Account;

use App\Models\Review;

class MyReviewsController extends AccountBaseController
{
    public function index()
    {
        $reviews = Review::with(['product', 'images'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('account.reviews.index', compact('reviews'));
    }
}