<x-app-layout>

<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <h4 class="mb-0">Add Article</h4>
        <div class="text-muted small">Site: {{ $site->name }}</div>
      </div>

      <a href="{{ route('admin.sites.show', $site->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to site
      </a>
    </div>

    <form action="{{ route('admin.articles.store', $site->id) }}" method="POST">
      @csrf

      <div class="mb-3">
        <label class="form-label">Article Title</label>
        <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
      </div>

      {{-- Categories picker --}}
        <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="fw-semibold mb-2">Available Categories</div>

            <div class="category-picker-box">
            <ul class="list-group" id="availableCategories">
                @foreach($categories as $category)
                <li class="list-group-item d-flex align-items-center justify-content-between gap-2"
                    data-id="{{ $category->id }}">
                    <span class="me-auto">{{ $category->name }}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add" title="Add">
                    <i class="bi bi-arrow-right"></i>
                    </button>
                </li>
                @endforeach
            </ul>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="fw-semibold mb-2">Selected Categories</div>

            <div class="category-picker-box">
            <ul class="list-group" id="selectedCategories"></ul>
            </div>

            <div class="form-text text-muted mt-2">You must select at least 1 category.</div>
        </div>
        </div>

        <input type="hidden" name="selectedCategoryIds" id="selectedCategoryIds" value="{{ old('selectedCategoryIds','') }}">


      <button class="btn btn-primary">
        <i class="bi bi-save me-1"></i> Save Article
      </button>
    </form>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
  const available = document.getElementById("availableCategories");
  const selected  = document.getElementById("selectedCategories");
  const hidden    = document.getElementById("selectedCategoryIds");

  function updateHidden() {
    const ids = Array.from(selected.querySelectorAll("li")).map(li => li.dataset.id);
    hidden.value = ids.join(",");
  }

  function renderAvailable(li, name) {
    li.className = "list-group-item d-flex align-items-center justify-content-between gap-2";
    li.innerHTML = `
      <span class="me-auto">${name}</span>
      <button type="button" class="btn btn-sm btn-outline-primary btn-add" title="Add">
        <i class="bi bi-arrow-right"></i>
      </button>
    `;
  }

  function renderSelected(li, name) {
    li.className = "list-group-item d-flex align-items-center justify-content-between gap-2";
    li.innerHTML = `
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove" title="Remove">
        <i class="bi bi-arrow-left"></i>
      </button>
      <span class="me-auto">${name}</span>
    `;
  }

  function moveToSelected(li) {
    const name = li.querySelector("span")?.textContent?.trim() ?? "";
    li.classList.add('fade-out');
    setTimeout(() => {
      renderSelected(li, name);
      selected.appendChild(li);
      li.classList.add('fade-in');
      requestAnimationFrame(() => {
        li.classList.remove('fade-out', 'fade-in');
      });
      updateHidden();
    }, 150);
  }

  function moveToAvailable(li) {
    const name = li.querySelector("span")?.textContent?.trim() ?? "";
    li.classList.add('fade-out');
    setTimeout(() => {
      renderAvailable(li, name);
      available.appendChild(li);
      li.classList.add('fade-in');
      requestAnimationFrame(() => {
        li.classList.remove('fade-out', 'fade-in');
      });
      updateHidden();
    }, 150);
  }

  available.addEventListener("click", (e) => {
    if (e.target.closest(".btn-add")) moveToSelected(e.target.closest("li"));
  });

  selected.addEventListener("click", (e) => {
    if (e.target.closest(".btn-remove")) moveToAvailable(e.target.closest("li"));
  });

  // Restore old values (optional)
  const old = hidden.value ? hidden.value.split(",").filter(Boolean) : [];
  if (old.length) {
    old.forEach(id => {
      const li = available.querySelector(`li[data-id="${id}"]`);
      if (li) moveToSelected(li);
    });
  }

  updateHidden();
});
</script>
@endpush


</x-app-layout>
