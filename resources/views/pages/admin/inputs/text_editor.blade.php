@php($id = 'input_' . preg_replace('/[^a-zA-Z0-9\-_]/', '_', $name))

<div class="mb-3">
    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="5"
        class="form-control wysiwyg-editor"
    >{{ $value ?? '' }}</textarea>
</div>

@once
    @push('scripts')
        <script src="{{ asset('assets/admin/js/tinymce/tinymce.min.js') }}"></script>
        <script>
                tinymce.init({
    license_key: 'gpl',
    selector: '.wysiwyg-editor',
    plugins: 'link image table lists code fullscreen preview',
    toolbar: 'code undo redo | styles fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor removeformat | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | link image table | fullscreen preview',
    toolbar_mode: 'wrap',
    menubar: false,
    branding: false,
    skin: 'oxide-dark',
    // content_css: 'light',
    height: 300,
    toolbar_sticky: false,
    font_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,palatino,serif; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif; Verdana=verdana,geneva,sans-serif',
    fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
    style_formats: [
        { title: 'Headers', items: [
            { title: 'Header 1', format: 'h1' },
            { title: 'Header 2', format: 'h2' },
            { title: 'Header 3', format: 'h3' },
            { title: 'Header 4', format: 'h4' },
            { title: 'Header 5', format: 'h5' },
            { title: 'Header 6', format: 'h6' }
        ]},
        { title: 'Inline', items: [
            { title: 'Bold', icon: 'bold', format: 'bold' },
            { title: 'Italic', icon: 'italic', format: 'italic' },
            { title: 'Underline', icon: 'underline', format: 'underline' },
            { title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough' }
        ]},
        { title: 'Blocks', items: [
            { title: 'Paragraph', format: 'p' },
            { title: 'Div', format: 'div' },
            { title: 'Preformatted', format: 'pre' },
            { title: 'Blockquote', format: 'blockquote' }
        ]}
    ],
    setup: function(editor) {
            editor.on('change', function() {
                editor.save(); // Ensure TinyMCE content is saved to the corresponding textarea
            });
        }
});
        </script>
    @endpush
@endonce
