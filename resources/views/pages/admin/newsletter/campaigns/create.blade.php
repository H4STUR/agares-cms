<x-app-layout>
  <h4 class="mb-3">{{ __('Create campaign') }}</h4>

  @include('pages.admin.newsletter.campaigns._safety_notice')

  <form method="POST" action="{{ route('admin.newsletter.campaigns.store') }}">
    @csrf
    @include('pages.admin.newsletter.campaigns._form', ['campaign' => $template ? new \App\Models\Newsletter\NewsletterCampaign([
      'subject' => $template->subject,
      'body'    => $template->body,
      'template_id' => $template->id,
      'status'  => 'draft',
    ]) : null])

    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-primary">{{ __('Save campaign') }}</button>
      <a class="btn btn-outline-secondary" href="{{ route('admin.newsletter.campaigns.index') }}">{{ __('Cancel') }}</a>
    </div>
  </form>
</x-app-layout>
