<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->orderBy('sort_order')->get();
        return view('pages.admin.ecommerce.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::orderBy('name')->get();
        return view('pages.admin.ecommerce.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id' => ['nullable','exists:ecommerce_categories,id'],
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'sort_order' => ['nullable','integer'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        Category::create($data);

        return redirect()->route('admin.ecommerce.categories.index')->with('success', __('Category created.'));
    }

    public function edit(Category $category)
    {
        $parents = Category::where('id', '!=', $category->id)->orderBy('name')->get();
        return view('pages.admin.ecommerce.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'parent_id' => ['nullable','exists:ecommerce_categories,id'],
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'sort_order' => ['nullable','integer'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $category->update($data);

        return back()->with('success', __('Category updated.'));
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', __('Category deleted.'));
    }
}
