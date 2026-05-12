<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view ecommerce', only: ['index']),
            new Middleware('can:manage ecommerce', only: ['store', 'update', 'destroy']),
        ];
    }

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
