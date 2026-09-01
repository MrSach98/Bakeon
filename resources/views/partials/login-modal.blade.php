<div class="modal fade" id="loginModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">

                <!-- Step 1: Enter Email/Mobile -->
                <div id="loginStep1">
                    <h4 class="fw-bold mb-1">Login / Sign Up! 👋</h4>
                    <p class="text-muted mb-3" id="loginPromptText">Please enter your email address</p>

                    <div class="input-group mb-2">
                        <span class="input-group-text bg-white"><i class="fa-regular fa-envelope text-muted"></i></span>
                        <input type="email" id="loginEmailInput" class="form-control" placeholder="Your email address">
                    </div>

                    <div class="input-group mb-2 d-none">
                        <span class="input-group-text bg-white">+91</span>
                        <input type="text" id="loginPhoneInput" class="form-control" maxlength="10" placeholder="10 digit mobile number">
                    </div>

                    <div class="text-end mb-3">
                        <a href="javascript:void(0)" id="toggleMethodLink" class="small fw-semibold text-decoration-none" style="color:var(--brand-maroon, #d8232a);">With Mobile</a>
                    </div>

                    <div id="loginErrorBox" class="text-danger small mb-2"></div>

                    <button type="button" class="btn w-100 fw-bold" id="getOtpBtn" style="background:var(--brand-maroon, #d8232a); color:#fff;">
                        Get OTP <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>

                    <p class="small text-muted mt-3 mb-0">
                        By continuing I agree to Sweet Bakes's <a href="{{ url('/terms-and-conditions') }}">Terms &amp; Conditions</a> and <a href="{{ url('/privacy-policy') }}">Privacy Policy</a>.
                    </p>

                    <hr>
                    <p class="text-center small mb-0">
                        New here? <a href="javascript:void(0)" id="showSignupLink" class="fw-semibold text-decoration-none" style="color:var(--brand-maroon, #d8232a);">Create an Account</a>
                    </p>
                </div>

                <!-- Step 2: OTP Verification -->
                <div id="loginStep2" class="d-none">
                    <h4 class="fw-bold mb-2">OTP Verification</h4>
                    <p class="text-muted mb-3">
                        We have sent you a 6 digit OTP to <strong id="maskedContactDisplay"></strong>
                        (<a href="javascript:void(0)" id="editContactLink">Edit</a>)
                    </p>

                    <input type="text" id="otpInput" class="form-control text-center mb-2" maxlength="6" style="letter-spacing:6px; font-size:1.3rem;" placeholder="------">

                    <div class="text-center small text-muted mb-3">
                        <span id="resendTimerText">Resend OTP in <span id="resendCountdown">30</span>s</span>
                        <a href="javascript:void(0)" id="resendOtpLink" class="d-none fw-semibold text-decoration-none" style="color:var(--brand-maroon, #d8232a);">Resend OTP</a>
                    </div>

                    <div id="otpErrorBox" class="text-danger small mb-2"></div>

                    <button type="button" class="btn w-100 fw-bold" id="verifyOtpBtn" style="background:var(--brand-maroon, #d8232a); color:#fff;">
                        Verify OTP <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </div>
                

            </div>
        </div>
    </div>
</div>