@php
  $uid = $uid ?? 'code_' . md5($name);
@endphp

<div class="mb-4">
    <div id="{{ $uid }}_editor" style="height: 300px; border: 1px solid #ccc;"></div>
    <input type="hidden" name="{{ $name }}" id="{{ $uid }}_input" value="{{ $value ?? '' }}">
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.20.0/min/vs/loader.min.js"></script>

    <script>
        (function() {
            const editorEl = document.getElementById(@json($uid + '_editor'));
            const inputEl  = document.getElementById(@json($uid + '_input'));
            if (!editorEl || !inputEl) return;

            if (typeof require === 'undefined') {
                console.error("Monaco loader not found.");
                return;
            }

            require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.20.0/min/vs' } });
            require(['vs/editor/editor.main'], function () {
                const editor = monaco.editor.create(editorEl, {
                    value: inputEl.value || '',
                    language: 'javascript',
                    theme: 'vs-dark',
                    automaticLayout: true,
                    fontSize: 14
                });

                editor.onDidChangeModelContent(() => {
                    inputEl.value = editor.getValue();
                });
            });
        })();
    </script>
@endpush
