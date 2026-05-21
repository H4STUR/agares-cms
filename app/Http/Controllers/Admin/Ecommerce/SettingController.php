<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $ecommerceSettings = Setting::orderBy('category')->orderBy('key')->get();

        return view('pages.admin.ecommerce.settings.index', [
            'ecommerceSettings' => $ecommerceSettings,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required','string','max:100'],
            'value' => ['nullable','string'],
            'category' => ['required','string','max:100'],
            'type' => ['required','in:string,integer,boolean,json'],
            'description' => ['nullable','string','max:255'],
        ]);

        Setting::updateOrCreate(
            ['site_id' => null, 'key' => $validated['key']],
            [
                ...$validated,
                'site_id' => null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );

        return back()->with('success', __('Ecommerce setting saved.'));
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'value' => ['nullable','string'],
            'category' => ['required','string','max:100'],
            'type' => ['required','in:string,integer,boolean,json'],
            'description' => ['nullable','string','max:255'],
        ]);

        $setting->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', __('Ecommerce setting updated.'));
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();

        return back()->with('success', __('Ecommerce setting deleted.'));
    }
}
