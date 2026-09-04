<?php

namespace App\Http\Controllers\Account;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends AccountBaseController
{
    public function index()
    {
        $addresses = Address::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return view('account.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        if ($request->boolean('is_default') || ! Address::where('user_id', auth()->id())->exists()) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $validated['user_id'] = auth()->id();
        Address::create($validated);

        return redirect()->route('account.addresses.index')->with('success', 'Address saved successfully.');
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $this->validateData($request);

        if ($request->boolean('is_default')) {
            Address::where('user_id', auth()->id())->where('id', '!=', $address->id)->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $address->update($validated);

        return redirect()->route('account.addresses.index')->with('success', 'Address updated successfully.');
    }

    public function destroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // Agar default address delete hua, to kisi aur ko default bana do
        if ($wasDefault) {
            $next = Address::where('user_id', auth()->id())->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return back()->with('success', 'Address deleted successfully.');
    }

    public function setDefault(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        Address::where('user_id', auth()->id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(['success' => true, 'message' => 'Default address updated.']);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:500'],
            'area_locality' => ['required', 'string', 'max:255'],
            'pincode' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
            'address_type' => ['required', 'in:home,office,others'],
        ]);
    }
}