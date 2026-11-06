@php $email = config('nextkeepers.contact_email'); @endphp
<x-legal-page title="Privacy Policy" updated="November 6, 2026">
    <p>Next Keepers ("we", "us") is building an acquisition intelligence platform. This page explains what we collect on nextkeepers.com today, why, and the choices you have. The site currently offers one thing that collects information: the early-access form.</p>

    <h2>What we collect</h2>
    <ul>
        <li><strong>What you enter:</strong> your email address, your name if you give one, and whether you are interested as a buyer, business owner or investor.</li>
        <li><strong>Technical details:</strong> your browser's user-agent string and a one-way, salted hash of your IP address, used to spot abuse and duplicate sign-ups. We do not store your raw IP address in the sign-up record.</li>
        <li><strong>Campaign source:</strong> the <code>utm_source</code> value, if the link you used contained one.</li>
        <li><strong>Consent record:</strong> the date and time you agreed to hear from us.</li>
    </ul>

    <h2>How we use it</h2>
    <p>We use this information to email you about early access to Next Keepers and to understand which audiences the product should serve first. We do not sell your information and we do not share it for advertising.</p>

    <h2>Who handles it</h2>
    <p>Sign-ups are stored in our own database. We may use service providers to host the website and to send email. They may process your information only to provide those services to us.</p>

    <h2>Cookies</h2>
    <p>The site sets the essential cookies Laravel needs to protect the form from cross-site request forgery. We do not use advertising cookies. If we add analytics later, we will update this page first.</p>

    <h2>How long we keep it</h2>
    <p>We keep early-access records until you ask us to delete them or until we no longer need them for the early-access program.</p>

    <h2>Your choices</h2>
    <p>You can ask us to see, correct or delete your information, or to stop emailing you, at any time.
        @if ($email) Email us at <a href="mailto:{{ $email }}">{{ $email }}</a>. @else Reply to any email we send you and say what you would like. @endif
    </p>

    <h2>Changes</h2>
    <p>If we change this policy we will update the date at the top of the page.</p>
</x-legal-page>
