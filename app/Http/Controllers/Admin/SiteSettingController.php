<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class SiteSettingController extends Controller
{
    private string $uploadFolder = 'userassets/settings';

    public function edit()
    {
        $setting = SiteSetting::current();

        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = SiteSetting::current();

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'default_meta_title' => ['nullable', 'string', 'max:255'],
            'default_meta_description' => ['nullable', 'string', 'max:300'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:1024'],
            'online_payment_enabled' => ['nullable', 'boolean'],
            'free_delivery_threshold' => ['nullable', 'numeric', 'min:0'],
            'default_delivery_charge' => ['nullable', 'numeric', 'min:0'],
        ]);
        $validated['online_payment_enabled'] = $request->boolean('online_payment_enabled');

        if ($request->hasFile('logo')) {
            $this->deleteFile($setting->logo);
            $validated['logo'] = $this->uploadFile($request->file('logo'));
        }

        if ($request->hasFile('favicon')) {
            $this->deleteFile($setting->favicon);
            $validated['favicon'] = $this->uploadFile($request->file('favicon'));
        }

        $setting->update($validated);

        return redirect()->route('admin.settings.edit')->with('success', 'Settings updated successfully.');
    }

    private function uploadFile($file): string
    {
        $destination = public_path($this->uploadFolder);

        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);

        return $this->uploadFolder . '/' . $filename;
    }

    private function deleteFile(?string $relativePath): void
    {
        if ($relativePath && File::exists(public_path($relativePath))) {
            File::delete(public_path($relativePath));
        }
    }
}