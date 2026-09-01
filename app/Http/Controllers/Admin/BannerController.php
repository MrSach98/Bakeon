<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BannerController extends Controller
{
    private string $uploadFolder = 'userassets/banners';

    public function index()
    {
        return view('admin.banners.index');
    }

    public function data()
    {
        $banners = Banner::select('banners.*')->orderBy('sort_order');

        return DataTables::of($banners)
            ->addColumn('image', function (Banner $banner) {
                return '<img src="' . asset($banner->image) . '" width="80" height="45" class="rounded" style="object-fit:cover;">';
            })
            ->addColumn('status', function (Banner $banner) {
                $checked = $banner->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $banner->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (Banner $banner) {
                return '
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(' . $banner->id . ')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $banner->id . ')">Delete</button>
                    <form id="deleteForm' . $banner->id . '" action="' . route('admin.banners.destroy', $banner) . '" method="POST" class="d-none">
                        ' . csrf_field() . method_field('DELETE') . '
                    </form>
                ';
            })
            ->addColumn('type_label', fn (Banner $banner) => Banner::TYPES[$banner->type] ?? $banner->type)
            ->rawColumns(['type_label', 'image', 'status', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['image'] = $this->uploadImage($request->file('image'));

        Banner::create($validated);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    public function fetch(Banner $banner)
    {
        return response()->json([
            'id' => $banner->id,
            'type' => $banner->type,
            'title' => $banner->title,
            'subtitle' => $banner->subtitle,
            'button_text' => $banner->button_text,
            'link_url' => $banner->link_url,
            'sort_order' => $banner->sort_order,
            'is_active' => $banner->is_active,
            'image_url' => asset($banner->image),
        ]);
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $this->validateData($request, isEdit: true);

        if ($request->hasFile('image')) {
            $this->deleteImage($banner->image);
            $validated['image'] = $this->uploadImage($request->file('image'));
        }

        $banner->update($validated);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        $this->deleteImage($banner->image);
        $banner->delete();

        return back()->with('success', 'Banner deleted successfully.');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return response()->json(['is_active' => $banner->is_active]);
    }

    private function validateData(Request $request, bool $isEdit = false): array
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(Banner::TYPES))],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => [$isEdit ? 'nullable' : 'required', 'image', 'max:3072'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($isEdit) {
            unset($data['image']);
        }

        return $data;
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

    private function deleteImage(string $relativePath): void
    {
        if (File::exists(public_path($relativePath))) {
            File::delete(public_path($relativePath));
        }
    }
}