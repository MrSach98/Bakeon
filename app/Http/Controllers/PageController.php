<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends StorefrontController
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('page', compact('page'));
    }
}