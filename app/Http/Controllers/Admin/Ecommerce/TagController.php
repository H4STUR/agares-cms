<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('products')->orderBy('name')->paginate(50);

        return view('pages.admin.ecommerce.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('pages.admin.ecommerce.tags.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:ecommerce_tags,slug'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        Tag::create($validated);

        return redirect()->route('admin.ecommerce.tags.index')
            ->with('success', __('Tag created.'));
    }

    public function edit(Tag $tag)
    {
        return view('pages.admin.ecommerce.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:ecommerce_tags,slug,' . $tag->id],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        $tag->update($validated);

        return back()->with('success', __('Tag updated.'));
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('admin.ecommerce.tags.index')
            ->with('success', __('Tag deleted.'));
    }
}
