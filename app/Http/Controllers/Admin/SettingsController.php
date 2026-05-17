<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon; 
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view settings', only: ['index']),
            new Middleware('can:manage settings', only: [
                'update', 'deleteSetting', 'storeCustom', 'clearCache',
                'saveRobots', 'generateSitemap', 'saveAiSeoSettings',
            ]),
        ];
    }

    public function index()
    {
        $settings = Setting::all();

        $robotsPath = public_path('robots.txt');
        $robotsContent = File::exists($robotsPath)
            ? File::get($robotsPath)
            : "User-agent: *\nDisallow:\n\nSitemap: " . url('/sitemap.xml') . "\n";

        $sitemapPath = public_path('sitemap.xml');
        $sitemapContent = File::exists($sitemapPath)
            ? File::get($sitemapPath)
            : '';

        return view('pages.admin.settings.index', compact('settings', 'robotsContent', 'sitemapContent'));
    }

    public function update(Request $request)
    {
        foreach ($request->input('settings', []) as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                if ($setting->type === 'boolean') {
                    $setting->value = filter_var($value, FILTER_VALIDATE_BOOL) ? 1 : 0;
                } else {
                    $setting->value = $value;
                }

                $setting->save();

                // Clear cache for this setting
                Cache::forget("setting.bool.$key");
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }


    public function storeCustom(Request $request)
    {
        $request->validate([
            'new_key' => 'required|string|max:100|unique:settings,key',
            'new_value' => 'nullable|string',
            'new_type' => 'required|in:string,integer,boolean,json',
            'new_description' => 'nullable|string|max:255',
        ]);

        Setting::create([
            'key' => $request->input('new_key'),
            'value' => $request->input('new_value'),
            'type' => $request->input('new_type'),
            'description' => $request->input('new_description'),
            'category' => 'custom',
        ]);

        return back()->with('success', 'Custom setting added successfully.');
    }

    public function deleteSetting($id)
    {
        $setting = Setting::findOrFail($id);

        if ($setting->category === 'custom') {
            $setting->delete();
            return back()->with('success', 'Custom setting deleted successfully.');
        }

        return back()->with('error', 'Cannot delete this setting.');
    }

    public function saveRobots(Request $request)
    {
        $request->validate([
            'robots' => 'nullable|string',
        ]);

        File::put(public_path('robots.txt'), $request->input('robots', ''));

        return back()->with('success', 'robots.txt saved successfully.');
    }

    public function generateSitemap()
    {
        $urls = [];

        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        // Load sites + categories in one query set
        $sites = \App\Models\Site::with([
            // IMPORTANT: do NOT select slug for categories (it doesn't exist)
            'categories:id,site_id,updated_at,name',
            // articles (optional)
            'articles:id,site_id,updated_at,title',
        ])->select('id', 'slug', 'updated_at')->get();

        foreach ($sites as $site) {
            $siteSlug = ltrim((string)$site->slug, '/');

            // Site page
            $urls[] = [
                'loc' => url('/' . $siteSlug),
                'lastmod' => optional($site->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];

            // Categories (use ID as URL segment to guarantee it exists)
            foreach ($site->categories as $cat) {
                $categorySegment = (string) $cat->id;

                $urls[] = [
                    'loc' => url('/' . $siteSlug . '/' . $categorySegment),
                    'lastmod' => optional($cat->updated_at)->toAtomString() ?? now()->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ];
            }

            /**
             * Articles:
             * Your route needs {category}/{articleId}/{articleName}.
             * Without knowing article->category mapping and what {category} should be,
             * we skip articles safely for now to avoid generating broken URLs.
             *
             * If you confirm how PageController finds category (by id or name)
             * AND how articles relate to categories, we’ll add them.
             */
        }

        $xml = $this->buildSitemapXml($urls);
        File::put(public_path('sitemap.xml'), $xml);

        return back()->with('success', 'sitemap.xml generated successfully.');
    }

    private function buildSitemapXml(array $urls): string
    {
        $escape = fn($v) => htmlspecialchars((string)$v, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $out = [];
        $out[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $out[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $u) {
            $out[] = '  <url>';
            $out[] = '    <loc>' . $escape($u['loc']) . '</loc>';
            if (!empty($u['lastmod'])) $out[] = '    <lastmod>' . $escape($u['lastmod']) . '</lastmod>';
            if (!empty($u['changefreq'])) $out[] = '    <changefreq>' . $escape($u['changefreq']) . '</changefreq>';
            if (!empty($u['priority'])) $out[] = '    <priority>' . $escape($u['priority']) . '</priority>';
            $out[] = '  </url>';
        }

        $out[] = '</urlset>';

        return implode("\n", $out) . "\n";
    }

    public function saveAiSeoSettings(Request $request)
    {
        $request->validate([
            'ai_seo_enabled'  => 'nullable',
            'ai_seo_industry' => 'nullable|string|max:255',
            'ai_seo_audience' => 'nullable|string|max:255',
            'ai_seo_tone'     => 'nullable|string|max:255',
        ]);

        Setting::updateOrCreate(['key' => 'ai_seo_enabled'], [
            'value'    => $request->boolean('ai_seo_enabled') ? '1' : '0',
            'category' => 'seo',
            'type'     => 'boolean',
        ]);

        foreach (['ai_seo_industry', 'ai_seo_audience', 'ai_seo_tone'] as $key) {
            Setting::updateOrCreate(['key' => $key], [
                'value'    => (string) $request->input($key, ''),
                'category' => 'seo',
                'type'     => 'string',
            ]);
        }

        return back()->with('success', __('AI SEO settings saved.'));
    }

    public function clearCache()
    {
        try {
            // 1) bootstrap/cache (config/routes/services/packages cache)
            $bootstrapCache = base_path('bootstrap/cache');
            if (is_dir($bootstrapCache)) {
                foreach (glob($bootstrapCache . '/*.php') as $file) {
                    // keep .gitignore if present
                    if (basename($file) !== '.gitignore') {
                        @unlink($file);
                    }
                }
            }

            // 2) compiled blade views
            $views = storage_path('framework/views');
            if (is_dir($views)) {
                foreach (glob($views . '/*') as $file) {
                    @unlink($file);
                }
            }

            // 3) framework cache (file cache store)
            $cache = storage_path('framework/cache');
            if (is_dir($cache)) {
                File::deleteDirectory($cache);
                File::makeDirectory($cache, 0755, true);
            }

            // 4) optional: clear sessions (comment out if you don’t want to logout users)
            // $sessions = storage_path('framework/sessions');
            // if (is_dir($sessions)) {
            //     foreach (glob($sessions . '/*') as $file) {
            //         @unlink($file);
            //     }
            // }

            return back()->with('success', 'Cache cleared successfully (filesystem).');
        } catch (\Throwable $e) {
            return back()->with('error', 'Cache clear failed: '.$e->getMessage());
        }
    }
}
