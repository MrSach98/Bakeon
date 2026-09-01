<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class CustomerAuthController extends Controller
{
    private const OTP_VALID_MINUTES = 10;

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'method' => ['required', 'in:email,mobile'],
            'email' => ['required_if:method,email', 'nullable', 'email'],
            // Mobile abhi disabled hai — SMS API aane ke baad ye validation wapas enable karna
            // 'phone' => ['required_if:method,mobile', 'nullable', 'digits:10'],
        ]);

        if ($validated['method'] !== 'email') {
            return response()->json([
                'success' => false,
                'message' => 'Mobile OTP is not available right now. Please use email.',
            ], 422);
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(self::OTP_VALID_MINUTES);

        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            ['name' => null, 'role' => 'customer', 'password' => Hash::make(str()->random(16)), 'is_active' => false]
        );

        $user->update(['otp_code' => $otp, 'otp_expires_at' => $expiresAt]);

        // Real email bhej rahe hain (SMTP already .env me configured hai)
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
            'masked_contact' => $this->maskEmail($validated['email']),
        ]);

        // ---------------------------------------------------------
        // MOBILE OTP — abhi disabled hai kyunki koi SMS API connect
        // nahi hui hai. Jab customer real SMS API (Twilio/MSG91/etc.)
        // de, tab neeche wala pura block uncomment karna hai aur
        // upar wali validation rule bhi wapas enable karni hai,
        // aur upar wala "method !== email" early-return hataana hai.
        // ---------------------------------------------------------
        /*
        $user = User::firstOrCreate(
            ['phone' => $validated['phone']],
            ['name' => null, 'email' => null, 'role' => 'customer', 'password' => Hash::make(str()->random(16)), 'is_active' => false]
        );

        $user->update(['otp_code' => $otp, 'otp_expires_at' => $expiresAt]);

        // Sms::send($user->phone, "Your OTP is {$otp}");

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
            'masked_contact' => $this->maskPhone($validated['phone']),
        ]);
        */
    }

    /**
     * Verify OTP. Agar user ka signup already complete hai (name+password set hai)
     * to seedha login kar do. Agar naya user hai (signup incomplete), to
     * "needs_signup" flag bhejo taaki frontend Step 3 (remaining details) dikhaye.
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'method' => ['required', 'in:email,mobile'],
            'email' => ['required_if:method,email', 'nullable', 'email'],
            // 'phone' => ['required_if:method,mobile', 'nullable', 'digits:10'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = $validated['method'] === 'email'
            ? User::where('email', $validated['email'])->first()
            // : User::where('phone', $validated['phone'])->first();
            : null;

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        if (! $user->otp_code || $user->otp_code !== $validated['otp']) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 422);
        }

        if (! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'OTP has expired. Please request a new one.'], 422);
        }

        if ($validated['method'] === 'email') {
            $user->update(['email_verified_at' => now(), 'otp_code' => null, 'otp_expires_at' => null]);
        }
        // else {
        //     $user->update(['phone_verified_at' => now(), 'otp_code' => null, 'otp_expires_at' => null]);
        // }

        if (! $user->name || ! $user->is_active) {
            return response()->json([
                'success' => true,
                'needs_signup' => true,
                'user_id' => $user->id,
                'verified_method' => $validated['method'],
                'message' => 'OTP verified. Please complete your profile.',
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        // Guest cart ko is account me merge karo taaki login hote hi cart khaali na dikhe
        $this->mergeGuestCartIntoUser($request, $user);

        return response()->json([
            'success' => true,
            'needs_signup' => false,
            'message' => 'Logged in successfully.',
        ]);
    }

    /**
     * Step 3: Naam + baaki verify na hua contact (email ya phone) + password lo,
     * account complete karo, aur login kar do.
     */
    public function completeSignup(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'digits:10'],
            'password' => ['required', 'string', 'min:6'],
            'password_confirmation' => ['required', 'same:password'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->is_active && $user->name) {
            return response()->json(['success' => false, 'message' => 'Account already completed. Please login.'], 422);
        }

        if (! $user->email && ! empty($validated['email'])) {
            if (User::where('email', $validated['email'])->where('id', '!=', $user->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'This email is already registered.'], 422);
            }
            $user->email = $validated['email'];
        }

        if (! $user->phone && ! empty($validated['phone'])) {
            if (User::where('phone', $validated['phone'])->where('id', '!=', $user->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'This mobile number is already registered.'], 422);
            }
            $user->phone = $validated['phone'];
        }

        $user->name = $validated['name'];
        $user->password = Hash::make($validated['password']);
        $user->is_active = true;
        $user->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        // Guest cart ko is naye account me merge karo taaki cart khaali na dikhe
        $this->mergeGuestCartIntoUser($request, $user);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully!',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Guest checkout ke waqt jo items guest_token se cart me add hue the,
     * unhe login/signup hote hi is naye logged-in user ke account me
     * merge kar do — taki cart kabhi khaali na dikhe.
     */
    private function mergeGuestCartIntoUser(Request $request, User $user): void
    {
        $guestToken = $request->cookie('guest_token');

        if (! $guestToken) {
            return;
        }

        $guestItems = CartItem::where('guest_token', $guestToken)
            ->where('status', 'active')
            ->get();

        foreach ($guestItems as $guestItem) {
            // Agar user ke account me pehle se wahi product/variant/addon hai,
            // to quantity jod do — duplicate row mat banao
            $existing = CartItem::where('user_id', $user->id)
                ->where('status', 'active')
                ->where(function ($q) use ($guestItem) {
                    if ($guestItem->addon_id) {
                        $q->where('addon_id', $guestItem->addon_id);
                    } else {
                        $q->where('product_weight_id', $guestItem->product_weight_id);
                    }
                })
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
                $guestItem->delete();
            } else {
                $guestItem->update([
                    'user_id' => $user->id,
                    'guest_token' => null,
                ]);
            }
        }
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email);
        $visible = substr($name, 0, 2);
        return $visible . str_repeat('*', max(strlen($name) - 2, 0)) . '@' . $domain;
    }

    // Mobile disabled hone tak ye method use nahi hoga, lekin rakh rahe hain future ke liye
    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 1) . '*' . substr($phone, 2, 1) . '*' . substr($phone, 4, 1) . '***' . substr($phone, 8, 2);
    }
}