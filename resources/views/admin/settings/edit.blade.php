@extends('admin.layouts.app')

@section('title', 'Site Settings')

@section('content')
<h4 class="mb-4">Site Settings</h4>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Store Information</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Store Name <span class="text-danger">*</span></label>
                        <input type="text" name="store_name" class="form-control" value="{{ old('store_name', $setting->store_name) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $setting->contact_phone) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $setting->contact_email) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $setting->address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Currency Symbol <span class="text-danger">*</span></label>
                        <input type="text" name="currency_symbol" class="form-control" style="max-width:100px;" value="{{ old('currency_symbol', $setting->currency_symbol) }}" required>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Social Media Links</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-brands fa-facebook"></i> Facebook URL</label>
                        <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $setting->facebook_url) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-brands fa-instagram"></i> Instagram URL</label>
                        <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $setting->instagram_url) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-brands fa-twitter"></i> Twitter/X URL</label>
                        <input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $setting->twitter_url) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-brands fa-youtube"></i> YouTube URL</label>
                        <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $setting->youtube_url) }}">
                    </div>
                </div>
            </div>
            

            <div class="card mb-3">
                <div class="card-header">Default SEO</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Default Meta Title</label>
                        <input type="text" name="default_meta_title" class="form-control" value="{{ old('default_meta_title', $setting->default_meta_title) }}">
                        <div class="form-text">Used on homepage / pages without their own meta title.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Default Meta Description</label>
                        <textarea name="default_meta_description" class="form-control" rows="2">{{ old('default_meta_description', $setting->default_meta_description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Logo</div>
                <div class="card-body">
                    @if ($setting->logo)
                        <img src="{{ asset($setting->logo) }}" class="img-fluid rounded mb-2" style="max-height:100px;">
                    @endif
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Favicon</div>
                <div class="card-body">
                    @if ($setting->favicon)
                        <img src="{{ asset($setting->favicon) }}" class="img-fluid rounded mb-2" style="max-height:60px;">
                    @endif
                    <input type="file" name="favicon" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Payment &amp; Delivery Settings</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" name="online_payment_enabled" class="form-check-input" value="1"
                            {{ old('online_payment_enabled', $setting->online_payment_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold">Enable Online Payment</label>
                        <div class="form-text">Jab off ho, customer sirf Cash on Delivery use kar payega.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Free Delivery Threshold (₹)</label>
                            <input type="number" step="0.01" name="free_delivery_threshold" class="form-control"
                                value="{{ old('free_delivery_threshold', $setting->free_delivery_threshold) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Delivery Charge (₹)</label>
                            <input type="number" step="0.01" name="default_delivery_charge" class="form-control"
                                value="{{ old('default_delivery_charge', $setting->default_delivery_charge) }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Ad Platform Product Feeds</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" name="feed_enabled" class="form-check-input" value="1"
                            {{ old('feed_enabled', $setting->feed_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold">Enable Product Feeds</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Feed Currency</label>
                        <input type="text" name="feed_currency" class="form-control" style="max-width:120px;" value="{{ old('feed_currency', $setting->feed_currency) }}">
                    </div>

                    <hr>

                    <label class="form-label small fw-bold">Google Shopping / Google Ads Feed URL</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" readonly value="{{ url('/feed/google-shopping.xml?token=' . $setting->feed_token) }}" id="googleFeedUrl">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyFeedUrl('googleFeedUrl')">Copy</button>
                    </div>

                    <label class="form-label small fw-bold">Facebook / Instagram Ads Catalog Feed URL</label>
                    <div class="input-group">
                        <input type="text" class="form-control" readonly value="{{ url('/feed/facebook-catalog.csv?token=' . $setting->feed_token) }}" id="fbFeedUrl">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyFeedUrl('fbFeedUrl')">Copy</button>
                    </div>

                    <div class="form-text mt-2">
                        Paste these URLs into Meta Commerce Manager (Facebook/Instagram) and Google Merchant Center as your data feed source. They auto-refresh with your live product catalog.
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-dark w-100">Save Settings</button>
        </div>
    </div>
</form>
@push('scripts')
<script>
    function copyFeedUrl(id) {
        const input = document.getElementById(id);
        input.select();
        navigator.clipboard.writeText(input.value);
        showToast('Feed URL copied!', 'success');
    }
</script>
@endpush
@endsection