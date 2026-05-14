<?php

namespace App\Http\Controllers\Admin\Newsletter;

use App\Http\Controllers\Controller;
use App\Models\Newsletter\NewsletterTemplate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TemplateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view newsletter templates', only: ['index', 'edit', 'preview']),
            new Middleware('can:manage newsletter templates', only: ['create', 'store', 'update', 'destroy']),
        ];
    }

    public function index(Request $request)
    {
        $templates = NewsletterTemplate::query()
            ->with('creator:id,name')
            ->orderByDesc('id')
            ->paginate(25);

        return view('pages.admin.newsletter.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('pages.admin.newsletter.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        NewsletterTemplate::create($validated);

        return redirect()
            ->route('admin.newsletter.templates.index')
            ->with('success', __('Template created.'));
    }

    public function edit(NewsletterTemplate $template)
    {
        return view('pages.admin.newsletter.templates.edit', compact('template'));
    }

    public function update(Request $request, NewsletterTemplate $template)
    {
        $validated = $this->validateData($request);
        $template->update($validated);

        return redirect()
            ->route('admin.newsletter.templates.index')
            ->with('success', __('Template updated.'));
    }

    public function destroy(NewsletterTemplate $template)
    {
        $template->delete();

        return redirect()
            ->route('admin.newsletter.templates.index')
            ->with('success', __('Template deleted.'));
    }

    public function preview(NewsletterTemplate $template)
    {
        return view('pages.admin.newsletter.templates.preview', compact('template'));
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'subject'     => ['nullable', 'string', 'max:255'],
            'body'        => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        return $validated;
    }
}
