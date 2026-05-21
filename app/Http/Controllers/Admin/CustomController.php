<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomCode;
use Illuminate\Support\Facades\DB;


class CustomController extends Controller
{
    public function index()
    {
        // Fetch the custom script and style from the database
        $script = CustomCode::where('type', 'script')->first();  // Get first script
        $style = CustomCode::where('type', 'style')->first();    // Get first style

        return view('pages.admin.custom.index', compact('script', 'style'));
    }

    public function update(Request $request)
{
    // Validate the input
    $request->validate([
        'scripts' => 'nullable|array',
        'scripts.*' => 'nullable|string',
        'styles' => 'nullable|array',
        'styles.*' => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {
        // Handle scripts
        if ($request->has('scripts')) {
            foreach ($request->scripts as $scriptContent) {
                CustomCode::updateOrCreate(
                    ['type' => 'script'],
                    ['content' => $scriptContent]
                );
            }
        }

        // Handle styles
        if ($request->has('styles')) {
            foreach ($request->styles as $styleContent) {
                CustomCode::updateOrCreate(
                    ['type' => 'style'],
                    ['content' => $styleContent]
                );
            }
        }

        DB::commit();

        return redirect()->back()->with('success', 'Custom code updated successfully.');

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->back()->withErrors(['error' => 'An error occurred while saving the custom code. Please try again.']);
    }
}

}
