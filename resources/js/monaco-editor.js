// resources/js/monaco-editor.js

window.addEventListener('DOMContentLoaded', function () {
    // Check if any code editor exists on the page
    if (document.querySelectorAll('.code-editor').length === 0) {
        return; // If no code editors are on the page, don't load Monaco Editor
    }

    require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.20.0/min/vs' }});

    document.querySelectorAll('.code-editor').forEach((editorElement) => {
        const editorId = editorElement.id;
        const inputId = `code-editor-input-${editorId}`;
        const hiddenInput = document.getElementById(inputId);

        require(['vs/editor/editor.main'], function() {
            const editor = monaco.editor.create(editorElement, {
                value: hiddenInput.value,
                language: 'javascript',  // Choose language dynamically if needed
                theme: 'vs-dark',
                automaticLayout: true,
                fontSize: 14
            });

            // Sync Monaco editor content with the hidden input field
            editor.onDidChangeModelContent(() => {
                hiddenInput.value = editor.getValue();
            });
        });
    });
});
