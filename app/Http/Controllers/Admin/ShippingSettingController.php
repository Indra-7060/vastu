<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.shipping', [
            'settings' => ShippingSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'free_shipping_threshold' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'flat_shipping_rate' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
        ]);

        ShippingSetting::current()->update($data);

        return redirect()
            ->route('admin.settings.shipping.edit')
            ->with('success', 'Shipping settings updated successfully.');
    }
}
