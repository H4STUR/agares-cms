<x-app-layout>
<div class="card">
    <div class="card-body">
        <h5 class="mb-4 text-gray-800 dark:text-gray-100 fw-semibold">{{ __('Create New Site') }}</h5>

        <form action="{{ route('admin.sites.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">{{ __('Site Name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                

                <div class="col-md-6">
                    <label for="slug" class="form-label">{{ __('Slug') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="slug" name="slug" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary" id="slugifyButton" title="{{ __('Generate from name') }}">
                            <i class="bi bi-magic"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-12">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea id="description" name="description" rows="4" class="form-control"></textarea>
                </div>

                <div class="col-md-6">
                    <label for="meta_title" class="form-label">{{ __('Meta Title') }}</label>
                    <input type="text" id="meta_title" name="meta_title" class="form-control">
                </div>

                <div class="col-md-6">
                    <label for="meta_keywords" class="form-label">{{ __('Meta Keywords') }}</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" class="form-control">
                </div>

                <div class="col-md-6">
                    <label for="menu_id" class="form-label">{{ __('Select Menu') }} <span class="text-danger">*</span></label>
                    <select name="menu_id" id="menu_id" class="form-select" required>
                        @foreach($menus as $menu)
                            <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="parent_id" class="form-label">{{ __('Parent Site') }}</label>
                    <select name="parent_id" id="parent_id" class="form-select">
                        <option value="">{{ __('No Parent') }}</option>
                    </select>
                </div>

                @isset($siteTemplates)
                <div class="col-md-12">
                    <label for="site_template_id" class="form-label">{{ __('Default Inputs (Site)') }}</label>
                    <select name="site_template_id" id="site_template_id" class="form-select">
                        @foreach($siteTemplates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                        @endforeach
                        <option value="">{{ __('No default inputs') }}</option>
                    </select>
                    <small class="text-muted">
                        Applies a default set of inputs to this site during creation.
                    </small>
                </div>
                @endisset

            </div>

            <div class="col-md-6 my-4">
                <label class="form-label d-block">{{ __('Publish immediately') }}</label>

                <x-toggle-switch
                    name="publish_immediately"
                    :checked="false"
                    value="1"
                />

                <small class="text-muted d-block mt-1">
                    {{ __('If enabled, the site will be published immediately. Otherwise it will be saved as draft.') }}
                </small>
            </div>




            <div class="text-end mt-4">
                <div class="flex justify-end gap-2 mt-6">
                    <x-success-button type="submit" name="action" value="create">{{ __('Create') }}</x-success-button>
                    <x-primary-button type="submit" name="action" value="create_and_edit">{{ __('Create & Edit') }}</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('menu_id').addEventListener('change', function () {
        const menuId = this.value;
        const parentDropdown = document.getElementById('parent_id');

        parentDropdown.innerHTML = '<option value="">{{ __('Loading...') }}</option>';

        if (menuId) {
            fetch(`/admin/menus/${menuId}/sites`)
                .then(response => response.json())
                .then(sites => {
                    parentDropdown.innerHTML = '<option value="">{{ __('No Parent') }}</option>';
                    sites.forEach(site => {
                        const option = document.createElement('option');
                        option.value = site.id;
                        option.textContent = site.name;
                        parentDropdown.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching sites:', error);
                    parentDropdown.innerHTML = '<option value="">{{ __('No Parent') }}</option>';
                    alert('{{ __('Could not load parent sites. Please try again.') }}');
                });
        } else {
            parentDropdown.innerHTML = '<option value="">{{ __('No Parent') }}</option>';
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('menu_id').dispatchEvent(new Event('change'));
    });
</script>

<script>
    function slugify(text) {
        return text
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-');
    }

    document.getElementById('slugifyButton').addEventListener('click', function () {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (slugInput.value.trim() !== '') {
            slugInput.value = slugify(slugInput.value);
        } else if (nameInput.value.trim() !== '') {
            slugInput.value = slugify(nameInput.value);
        }
    });
</script>

@endpush
</x-app-layout>
