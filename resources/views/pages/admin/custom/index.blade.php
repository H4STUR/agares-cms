<x-app-layout>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">{{ __('Custom') }}</div>
    </div>

    <div class="container-fluid">
        {{-- <x-notification /> --}}

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.custom.update') }}" method="POST" id="custom-code-form">
                    @csrf
                    @method('PATCH')

                    <!-- Custom Script Editor -->
                    <div class="mb-4">
                        <label class="form-label">{{ __("Custom Script") }}</label>
                        <div id="code-editor-scripts" style="height: 300px; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
                        <input type="hidden" name="scripts[]" id="code-editor-input-scripts" value="{{ $script->content ?? '' }}">
                    </div>

                    <!-- Custom Style Editor -->
                    <div class="mb-4">
                        <label class="form-label">{{ __("Custom Styles") }}</label>
                        <div id="code-editor-styles" style="height: 300px; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
                        <input type="hidden" name="styles[]" id="code-editor-input-styles" value="{{ $style->content ?? '' }}">
                    </div>

                </form>
            </div>
        </div>
    </div>

{{-- STICKY BOTTOM SAVE BAR --}}
<div class="sticky-bottom-bar bg-body border-top shadow-sm p-2">
    <div class="container-fluid">
        <div class="d-flex justify-content-end align-items-center gap-3">
            <button type="submit" form="custom-code-form" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> {{ __('Save') }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <!-- Load Monaco Loader only once -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.20.0/min/vs/loader.min.js"></script>
    <script>
        require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.20.0/min/vs' }});

        require(['vs/editor/editor.main'], function () {
            const scriptEditor = monaco.editor.create(document.getElementById('code-editor-scripts'), {
                value: document.getElementById('code-editor-input-scripts').value,
                language: 'html',
                theme: 'vs-dark',
                automaticLayout: true,
                fontSize: 14
            });

            scriptEditor.onDidChangeModelContent(() => {
                document.getElementById('code-editor-input-scripts').value = scriptEditor.getValue();
            });

            const styleEditor = monaco.editor.create(document.getElementById('code-editor-styles'), {
                value: document.getElementById('code-editor-input-styles').value,
                language: 'css',
                theme: 'vs-dark',
                automaticLayout: true,
                fontSize: 14
            });

            styleEditor.onDidChangeModelContent(() => {
                document.getElementById('code-editor-input-styles').value = styleEditor.getValue();
            });
        });
    </script>
@endpush
</x-app-layout>
