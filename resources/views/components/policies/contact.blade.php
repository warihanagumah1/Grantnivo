@if(config('legal.contact_email'))
    <p>For support, privacy questions, access/correction/deletion requests, waitlist withdrawal, complaints or concerns about these policies, email <a href="mailto:{{ config('legal.contact_email') }}">{{ config('legal.contact_email') }}</a>. Use “Grantnivo privacy” or “Leave waitlist” as the subject and contact us from the address you registered with where possible. Do not send identity documents or sensitive beneficiary information unless we explain why a limited verification step is necessary.</p>
@else
    <p><strong>Privacy contact pending:</strong> the operator must supply a monitored privacy and waitlist-withdrawal email address before public launch. This draft does not yet provide a working contact channel.</p>
@endif
