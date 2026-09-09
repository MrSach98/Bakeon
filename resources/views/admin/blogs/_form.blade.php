@csrf

<div class="mb-3">
    <label class="form-label">Blog Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $blog->name ?? '') }}" required>
    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Short Description</label>
    <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $blog->short_description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Full Description</label>
    <textarea name="description" id="blogDescriptionEditor" class="form-control" rows="12">{{ old('description', $blog->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Featured Image</label>
    <input type="file" name="image" class="form-control" accept="image/*">
    @if (! empty($blog) && $blog->image)
        <img src="{{ asset($blog->image) }}" width="100" class="rounded mt-2">
    @endif
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Meta Title</label>
        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $blog->meta_title ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Meta Description</label>
        <input type="text" name="meta_description" class="form-control" value="{{ old('meta_description', $blog->meta_description ?? '') }}">
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $blog->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label">Active</label>
</div>

<button type="submit" class="btn btn-dark">Save Blog Post</button>
<a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary">Cancel</a>