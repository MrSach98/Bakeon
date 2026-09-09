<?php

namespace App\Http\Controllers;

use App\Models\Blog;

class BlogController extends StorefrontController
{
    public function index()
    {
        $blogs = Blog::where('is_active', true)->latest()->paginate(9);
        return view('blog-list', compact('blogs'));
    }

    public function show(string $slug)
    {
        $blog = Blog::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $relatedBlogs = Blog::where('is_active', true)->where('id', '!=', $blog->id)->latest()->take(4)->get();

        return view('blog-detail', compact('blog', 'relatedBlogs'));
    }
}