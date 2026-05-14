<?php

namespace App\Http\Controllers\Admin\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\Newsletter\NewsletterList;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view newsletter lists', only: ['index', 'edit']),
            new Middleware('can:manage newsletter lists', only: ['create', 'store', 'update', 'destroy']),
        ];
    }

    public function index()
    {
        $lists = NewsletterList::query()
            ->withCount('subscribers')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(50);

        return view('pages.admin.newsletter.lists.index', compact('lists'));
    }

    public function create()
    {
        return view('pages.admin.newsletter.lists.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        DB::transaction(function () use ($validated) {
            $list = NewsletterList::create([
                'name'        => $validated['name'],
                'slug'        => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'is_default'  => $validated['is_default'] ?? false,
            ]);

            if ($list->is_default) {
                NewsletterList::where('id', '!=', $list->id)->update(['is_default' => false]);
            }
        });

        return redirect()
            ->route('admin.newsletter.lists.index')
            ->with('success', __('List created.'));
    }

    public function edit(NewsletterList $list)
    {
        return view('pages.admin.newsletter.lists.edit', compact('list'));
    }

    public function update(Request $request, NewsletterList $list)
    {
        $validated = $this->validateData($request, $list->id);

        DB::transaction(function () use ($validated, $list) {
            $list->update([
                'name'        => $validated['name'],
                'slug'        => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'is_default'  => $validated['is_default'] ?? false,
            ]);

            if ($list->is_default) {
                NewsletterList::where('id', '!=', $list->id)->update(['is_default' => false]);
            }
        });

        return redirect()
            ->route('admin.newsletter.lists.index')
            ->with('success', __('List updated.'));
    }

    public function destroy(NewsletterList $list)
    {
        $list->delete();

        return redirect()
            ->route('admin.newsletter.lists.index')
            ->with('success', __('List deleted.'));
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = ['nullable', 'string', 'max:255'];
        $slugRule[] = $ignoreId
            ? 'unique:newsletter_lists,slug,' . $ignoreId
            : 'unique:newsletter_lists,slug';

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => $slugRule,
            'description' => ['nullable', 'string', 'max:5000'],
            'is_default'  => ['nullable', 'boolean'],
        ]);

        $validated['slug']       = $validated['slug'] ?: Str::slug($validated['name']);
        $validated['is_default'] = (bool) ($validated['is_default'] ?? false);

        return $validated;
    }
}
