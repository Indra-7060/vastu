<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.site', [
            'groups' => SiteSettings::groups(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (SiteSettings::fields() as $key => $field) {
            $rules[$key] = match ($field['type']) {
                'email' => ['nullable', 'email', 'max:255'],
                'url' => ['nullable', 'url', 'max:500'],
                'textarea' => ['nullable', 'string', 'max:5000'],
                default => ['nullable', 'string', 'max:255'],
            };
        }
        $data = $request->validate($rules, [
            '*.url' => 'Enter a full link starting with https://',
            '*.email' => 'Enter a valid email address.',
        ]);

        SiteSettings::save($data);

        return redirect()
            ->route('admin.settings.site.edit')
            ->with('success', 'Site details saved.');
    }
}
