<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AdminBlogController extends Controller
{
    private string $uploadFolder = 'userassets/blogs';

    public function index()
    {
        return view('admin.blogs.index');
    }

    public function data()
    {
        $blogs = Blog::select('blogs.*');

        return DataTables::of($blogs)
            ->addColumn('image', function (Blog $b) {
                if ($b->image) {
                    return '<img src="' . asset($b->image) . '" width="60" height="60" class="rounded" style="object-fit:cover;">';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('status', function (Blog $b) {
                $checked = $b->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $b->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (Blog $b) {
                return '<a href="' . route('admin.blogs.edit', $b) . '" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="' . url('/blog/' . $b->slug) . '" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>';
            })
            ->rawColumns(['image', 'status', 'actions'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.blogs.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request->file('image'));
        }

        Blog::create($validated);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post created successfully.');
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        $validated = $this->validateData($request);

        if ($validated['name'] !== $blog->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $blog->id);
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($blog->image);
            $validated['image'] = $this->uploadImage($request->file('image'));
        }

        $blog->update($validated);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post updated successfully.');
    }

    public function destroy(Blog $blog)
    {
        $this->deleteImage($blog->image);
        $blog->delete();

        return back()->with('success', 'Blog post deleted successfully.');
    }

    public function toggleStatus(Blog $blog)
    {
        $blog->update(['is_active' => ! $blog->is_active]);
        return response()->json(['is_active' => $blog->is_active]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:300'],
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

        while (Blog::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function uploadImage($file): string
    {
        $destination = public_path($this->uploadFolder);
        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);

        return $this->uploadFolder . '/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}