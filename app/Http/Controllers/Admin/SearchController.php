<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Category;
use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Global search across sites, categories, articles, media, and users.
     * Returns JSON results grouped by type.
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Query too short',
                'results' => [],
            ]);
        }

        $user = Auth::user();
        $results = [];
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $query) . '%';
        $isNumeric = ctype_digit($query);
        $limit = 5; // max results per category

        // 1. Search Sites (Pages)
        $sites = Site::query()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($like, $isNumeric, $query) {
                if ($isNumeric) {
                    $q->orWhere('id', (int) $query);
                }
                $q->orWhere('name', 'like', $like)
                  ->orWhere('slug', 'like', $like)
                  ->orWhere('title', 'like', $like)
                  ->orWhere('description', 'like', $like);
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'status', 'updated_at']);

        if ($sites->isNotEmpty()) {
            $results['sites'] = [
                'label' => 'Pages',
                'icon' => 'web',
                'items' => $sites->map(fn($site) => [
                    'id' => $site->id,
                    'title' => $site->name,
                    'subtitle' => '/' . $site->slug,
                    'status' => $site->status,
                    'url' => route('admin.sites.edit', $site->id),
                ])->toArray(),
            ];
        }

        // 2. Search Categories
        $categories = Category::query()
            ->with('site:id,name,slug')
            ->where(function ($q) use ($like, $isNumeric, $query) {
                if ($isNumeric) {
                    $q->orWhere('id', (int) $query);
                }
                $q->orWhere('name', 'like', $like)
                  ->orWhere('meta_title', 'like', $like)
                  ->orWhere('meta_description', 'like', $like)
                  ->orWhere('description', 'like', $like);
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get(['id', 'site_id', 'name', 'updated_at']);

        if ($categories->isNotEmpty()) {
            $results['categories'] = [
                'label' => 'Categories',
                'icon' => 'folder',
                'items' => $categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'title' => $cat->name,
                    'subtitle' => $cat->site ? $cat->site->name : 'Unknown site',
                    'url' => $cat->site
                        ? route('admin.categories.edit', ['site' => $cat->site_id, 'category' => $cat->id])
                        : '#',
                ])->toArray(),
            ];
        }

        // 3. Search Articles
        $articles = Article::query()
            ->with('site:id,name,slug')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($like, $isNumeric, $query) {
                if ($isNumeric) {
                    $q->orWhere('id', (int) $query);
                }
                $q->orWhere('title', 'like', $like)
                  ->orWhere('meta_title', 'like', $like)
                  ->orWhere('meta_description', 'like', $like)
                  ->orWhere('description', 'like', $like)
                  ->orWhere('content', 'like', $like);
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get(['id', 'site_id', 'title', 'status', 'updated_at']);

        if ($articles->isNotEmpty()) {
            $results['articles'] = [
                'label' => 'Articles',
                'icon' => 'article',
                'items' => $articles->map(fn($article) => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'subtitle' => $article->site ? $article->site->name : 'Unknown site',
                    'status' => $article->status,
                    'url' => $article->site
                        ? route('admin.articles.edit', ['site' => $article->site_id, 'article' => $article->id])
                        : '#',
                ])->toArray(),
            ];
        }

        // 4. Search Media
        $media = Media::query()
            ->where(function ($q) use ($like, $isNumeric, $query) {
                if ($isNumeric) {
                    $q->orWhere('id', (int) $query);
                }
                $q->orWhere('file_name', 'like', $like)
                  ->orWhere('original_name', 'like', $like)
                  ->orWhere('alternative', 'like', $like)
                  ->orWhere('description', 'like', $like);
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'file_name', 'original_name', 'file_path', 'mime_type', 'type']);

        if ($media->isNotEmpty()) {
            $results['media'] = [
                'label' => 'Media',
                'icon' => 'perm_media',
                'items' => $media->map(fn($file) => [
                    'id' => $file->id,
                    'title' => $file->original_name ?: $file->file_name,
                    'subtitle' => $file->mime_type,
                    'thumbnail' => $this->isImage($file->mime_type) ? $file->url : null,
                    'type' => $file->type,
                    'url' => route('admin.media') . '?highlight=' . $file->id,
                ])->toArray(),
            ];
        }

        // 5. Search Users (only if user has permission)
        if ($user->can('manage users')) {
            $users = User::query()
                ->where(function ($q) use ($like, $isNumeric, $query) {
                    if ($isNumeric) {
                        $q->orWhere('id', (int) $query);
                    }
                    $q->orWhere('username', 'like', $like)
                      ->orWhere('name', 'like', $like)
                      ->orWhere('surname', 'like', $like)
                      ->orWhere('email', 'like', $like);
                })
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get(['id', 'username', 'name', 'surname', 'email', 'avatar']);

            if ($users->isNotEmpty()) {
                $results['users'] = [
                    'label' => 'Users',
                    'icon' => 'people',
                    'items' => $users->map(fn($u) => [
                        'id' => $u->id,
                        'title' => $u->name ? "{$u->name} {$u->surname}" : $u->username,
                        'subtitle' => $u->email,
                        'avatar' => $u->avatar_url,
                        'url' => route('admin.user.profile', $u->id),
                    ])->toArray(),
                ];
            }
        }

        // Count total results
        $totalCount = collect($results)->sum(fn($group) => count($group['items']));

        return response()->json([
            'success' => true,
            'query' => $query,
            'total_count' => $totalCount,
            'results' => $results,
        ]);
    }

    /**
     * Check if mime type is an image.
     */
    private function isImage(?string $mimeType): bool
    {
        if (!$mimeType) return false;
        return str_starts_with($mimeType, 'image/');
    }
}
