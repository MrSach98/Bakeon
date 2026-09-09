<footer class="pt-5 pb-4">
    <div class="container-fluid px-4 px-lg-5 py-4">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <h6>Shop By Category</h6>
                @if(isset($footerCategories))
                    @foreach ($footerCategories as $category)
                        <a href="{{ url('/category/' . $category->slug) }}">{{ $category->name }}</a>
                    @endforeach
                @endif
            </div>

            <div class="col-6 col-md-3">
                <h6>By Occasion</h6>
                @if(isset($footerOccasions))
                    @foreach ($footerOccasions as $occasion)
                        <a href="{{ url('/occasion/' . $occasion->slug) }}">{{ $occasion->name }}</a>
                    @endforeach
                @endif
            </div>

            <div class="col-6 col-md-3">
                <h6>Company</h6>
                <a href="{{ url('/blog') }}">Blog</a>
                <a href="{{ url('/about-us') }}">About Us</a>
                <a href="{{ url('/contact-us') }}">Contact Us</a>
                <a href="{{ url('/terms-and-conditions') }}">Terms &amp; Conditions</a>
                <a href="{{ url('/privacy-policy') }}">Privacy Policy</a>
                <a href="{{ url('/refund-policy') }}">Refund Policy</a>
            </div>

            <div class="col-6 col-md-3">
                <h6>Get In Touch</h6>
                @if ($siteSettings->contact_phone ?? null)
                    <a href="tel:{{ $siteSettings->contact_phone }}"><i class="fa-solid fa-phone me-2"></i>{{ $siteSettings->contact_phone }}</a>
                @endif
                @if ($siteSettings->contact_email ?? null)
                    <a href="mailto:{{ $siteSettings->contact_email }}"><i class="fa-solid fa-envelope me-2"></i>{{ $siteSettings->contact_email }}</a>
                @endif
                @if ($siteSettings->address ?? null)
                    <p class="small mb-3"><i class="fa-solid fa-location-dot me-2"></i>{{ $siteSettings->address }}</p>
                @endif

                <div class="footer-social mt-3">
                    @if ($siteSettings->facebook_url ?? null)
                        <a href="{{ $siteSettings->facebook_url }}" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                    @endif
                    @if ($siteSettings->instagram_url ?? null)
                        <a href="{{ $siteSettings->instagram_url }}" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                    @endif
                    @if ($siteSettings->twitter_url ?? null)
                        <a href="{{ $siteSettings->twitter_url }}" target="_blank"><i class="fa-brands fa-twitter"></i></a>
                    @endif
                    @if ($siteSettings->youtube_url ?? null)
                        <a href="{{ $siteSettings->youtube_url }}" target="_blank"><i class="fa-brands fa-youtube"></i></a>
                    @endif
                </div>
            </div>
        </div>

        <hr class="mt-5" style="border-color:rgba(255,255,255,0.1);">

        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <small>100% FSSAI certified bakery. Freshly baked, on-time delivery, guaranteed.</small>
            </div>
            <div class="col-md-6 text-md-end">
                <i class="fa-brands fa-cc-visa fa-2x me-2"></i>
                <i class="fa-brands fa-cc-mastercard fa-2x me-2"></i>
                <i class="fa-brands fa-google-pay fa-2x me-2"></i>
                <i class="fa-solid fa-money-bill-wave fa-2x"></i>
            </div>
        </div>
    </div>
</footer>

<div class="footer-bottom text-center">
    &copy; {{ date('Y') }} {{ $siteSettings->store_name ?? 'Sweet Bakes' }}. All rights reserved.
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>