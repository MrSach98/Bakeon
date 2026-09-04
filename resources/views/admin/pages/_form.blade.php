@csrf

<div class="mb-3">
    <label class="form-label">Page Title <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $page->title ?? '') }}" data-required="true" required>
</div>

<div class="mb-3">
    <label class="form-label">Content</label>
    <textarea name="content" id="pageContent" class="form-control" rows="15">{{ old('content', $page->content ?? '') }}</textarea>
    <div class="form-text">Basic HTML allowed (headings, paragraphs, lists, links).</div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Meta Title</label>
        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $page->meta_title ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Meta Description</label>
        <input type="text" name="meta_description" class="form-control" value="{{ old('meta_description', $page->meta_description ?? '') }}">
    </div>
</div>

<div class="mb-3 form-check form-switch">
    <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label">Active</label>
</div>

<button type="submit" class="btn btn-dark">Save Page</button>
<a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">Cancel</a>