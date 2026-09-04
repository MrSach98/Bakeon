<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AdminPageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index');
    }

    public function data()
    {
        $pages = Page::select('pages.*');

        return DataTables::of($pages)
            ->addColumn('status', function (Page $p) {
                $checked = $p->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $p->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (Page $p) {
                return '<a href="' . route('admin.pages.edit', $p) . '" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="' . url('/page/' . $p->slug) . '" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['slug'] = $this->uniqueSlug($validated['title']);

        Page::create($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $this->validateData($request);

        if ($validated['title'] !== $page->title) {
            $validated['slug'] = $this->uniqueSlug($validated['title'], $page->id);
        }

        $page->update($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return back()->with('success', 'Page deleted successfully.');
    }

    public function toggleStatus(Page $page)
    {
        $page->update(['is_active' => ! $page->is_active]);
        return response()->json(['is_active' => $page->is_active]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $i = 1;

        while (Page::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}