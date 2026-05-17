<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Ecommerce\Product;
use App\Models\Setting;
use App\Models\Site;
use App\Services\AiSeo\AgaresSeoClient;
use App\Services\AiSeo\SeoPayloadBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

class AiSeoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view admin panel'),
        ];
    }

    public function generate(
        Request $request,
        SeoPayloadBuilder $builder,
        AgaresSeoClient $client,
    ): JsonResponse {
        if (! Setting::bool('ai_seo_enabled')) {
            return response()->json(['error' => 'AI SEO is disabled in settings.'], 403);
        }

        $validated = $request->validate([
            'content_type'              => 'required|in:site,article,product,category',
            'content_id'                => 'required|integer',
            'mode'                      => 'required|in:generate,improve',
            'focus_keyword'             => 'nullable|string|max:120',
            'current_meta_title'        => 'nullable|string|max:255',
            'current_meta_description'  => 'nullable|string|max:1000',
            'current_slug'              => 'nullable|string|max:255',
        ]);

        try {
            [$model, $abilityFailure] = $this->loadAndAuthorize($validated['content_type'], (int) $validated['content_id']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        if ($abilityFailure !== null) {
            return response()->json(['error' => $abilityFailure], 403);
        }

        $options = [
            'mode'                     => $validated['mode'],
            'focus_keyword'            => $validated['focus_keyword']            ?? null,
            'current_meta_title'       => $validated['current_meta_title']       ?? null,
            'current_meta_description' => $validated['current_meta_description'] ?? null,
            'current_slug'             => $validated['current_slug']             ?? null,
        ];

        $payload = match ($validated['content_type']) {
            'site'     => $builder->forSite($model, $options),
            'article'  => $builder->forArticle($model, $options),
            'product'  => $builder->forProduct($model, $options),
            'category' => $builder->forCategory($model, $options),
        };

        try {
            $result = $client->generate($payload);
        } catch (\Throwable $e) {
            $status = $e->getCode();
            $status = (is_int($status) && $status >= 400 && $status < 600) ? $status : 502;

            Log::warning('AiSeoController: SaaS call failed', [
                'status'  => $status,
                'message' => $e->getMessage(),
                'type'    => $validated['content_type'],
                'id'      => $validated['content_id'],
            ]);

            return response()->json(['error' => $e->getMessage()], $status);
        }

        return response()->json($result);
    }

    /**
     * Load the target model and check the user's permission to edit it.
     * Returns [model, errorMessage|null].
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when the model does not exist
     */
    private function loadAndAuthorize(string $type, int $id): array
    {
        $user = auth()->user();

        switch ($type) {
            case 'site':
                $model = Site::findOrFail($id);
                $can = $user && $user->can('manage sites');
                return [$model, $can ? null : 'You do not have permission to edit sites.'];

            case 'article':
                $model = Article::with(['categories', 'site'])->findOrFail($id);
                $can = $user && $user->can('manage articles');
                return [$model, $can ? null : 'You do not have permission to edit articles.'];

            case 'product':
                $model = Product::with(['categories'])->findOrFail($id);
                $can = $user && ($user->can('manage products') || $user->can('manage ecommerce'));
                return [$model, $can ? null : 'You do not have permission to edit products.'];

            case 'category':
                $model = Category::with(['site'])->findOrFail($id);
                $can = $user && $user->can('manage categories');
                return [$model, $can ? null : 'You do not have permission to edit categories.'];
        }

        return [null, 'Unknown content type.'];
    }
}
