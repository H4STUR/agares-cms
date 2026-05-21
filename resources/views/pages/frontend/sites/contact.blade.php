@extends('pages.frontend.base')

@section('content')
    <section class="section-sm" style="background: var(--color-bg-secondary);">
        <div class="container text-center">
            <h1>Get in Touch</h1>
            <p style="font-size: var(--text-lg);">Have questions? We'd love to hear from you.</p>
        </div>
    </section>

    <section>





        <div class="container">
            <div class="grid md:grid-2" style="gap: var(--space-4xl);">
                <div>
                    <h2>Send us a Message</h2>
                    
                    @php $cf = $data['contact'] ?? null; @endphp

                    @if($cf && $cf->field?->field_type === 'contact_form')
                        @include('pages.frontend.partials.contact_form', ['instance' => $cf])
                    @endif

                    {{-- <h2>Send us a Message</h2>
                    <p style="color: var(--color-text-secondary); margin-bottom: var(--space-2xl);">Fill out the form and our
                        team will get back to you within 24 hours.</p>
                    <form id="contact-form">
                        <div class="form-group"><label class="form-label" for="name">Name *</label><input type="text"
                                id="name" name="name" class="form-input" required></div>
                        <div class="form-group"><label class="form-label" for="email">Email *</label><input
                                type="email" id="email" name="email" class="form-input" required></div>
                        <div class="form-group"><label class="form-label" for="subject">Subject *</label><input
                                type="text" id="subject" name="subject" class="form-input" required></div>
                        <div class="form-group"><label class="form-label" for="message">Message *</label>
                            <textarea id="message" name="message" class="form-textarea" required></textarea>
                        </div><button type="submit" class="btn btn-primary">Send Message</button>
                    </form> --}}
                </div>

                <div>
                    <h2>Other Ways to Reach Us</h2>
                    <div class="grid" style="margin-top: var(--space-2xl);">
                        <div class="card"><svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2"
                                style="margin-bottom: var(--space-lg); color: var(--color-accent-primary);">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                            <h3>Email</h3>
                            <p style="color: var(--color-text-secondary); margin-bottom: var(--space-md);">For general
                                inquiries</p><a href="mailto:office@agares.co.uk"
                                style="font-weight: var(--font-weight-semibold);">office@agares.co.uk</a>
                        </div>
                    </div>
                    {{-- <div class="alert alert-info" style="margin-top: var(--space-2xl);"><svg width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4M12 8h.01" />
                        </svg>
                        <div><strong>Enterprise Support:</strong> For dedicated support and SLA guarantees, contact our
                            sales team at <a href="mailto:office@agares.co.uk">office@agares.co.uk</a></div>
                    </div> --}}
                </div>
            </div>
        </div>
    </section>
@endsection
