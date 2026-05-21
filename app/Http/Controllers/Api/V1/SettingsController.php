<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function public()
    {
        // Whitelist only the keys you want frontend to see
        $allowed = [
            'site_name',
            'site_description',
            'home_url',
            // add more safe ones as needed
        ];

        $data = Setting::query()
            ->whereIn('key', $allowed)
            ->pluck('value', 'key')
            ->toArray();

        return response()->json(['data' => $data]);
    }
}
