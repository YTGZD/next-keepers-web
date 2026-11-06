@php $email = config('nextkeepers.contact_email'); @endphp
<x-legal-page title="Terms of Service" updated="November 6, 2026">
    <p>These terms apply to your use of nextkeepers.com. By using the site you agree to them.</p>

    <h2>What the site is</h2>
    <p>Next Keepers is an early-stage acquisition intelligence platform. The public website describes the product and collects early-access sign-ups. The product itself is in development, and features described on the site may change or may not yet be available.</p>

    <h2>Illustrative content</h2>
    <p>Example businesses, figures, scores and map points shown on the site are illustrative samples. They are not real listings, and they are not offers or recommendations.</p>

    <h2>Not financial, legal or investment advice</h2>
    <p>Information and scores we provide are decision-support tools. They are not investment, financial, tax or legal advice, and they do not guarantee any outcome. Talk to qualified advisors before buying or selling a business.</p>

    <h2>Early access</h2>
    <p>Joining the early-access list does not guarantee access to the product, a particular launch date or any pricing. You can ask to be removed at any time.</p>

    <h2>Acceptable use</h2>
    <p>Do not misuse the site: no attempts to disrupt it, scrape it at scale, or submit other people's information without their permission.</p>

    <h2>Intellectual property</h2>
    <p>The Next Keepers name, logo and site content belong to us or our licensors. Do not reuse them without permission.</p>

    <h2>Liability</h2>
    <p>The site is provided "as is". To the extent the law allows, we are not liable for losses arising from your use of the site or from relying on its content.</p>

    <h2>Contact</h2>
    <p>
        @if ($email) Questions about these terms: <a href="mailto:{{ $email }}">{{ $email }}</a>. @else Questions about these terms: reply to any email we send you. @endif
        See also our <a href="{{ route('privacy') }}">Privacy Policy</a>.
    </p>
</x-legal-page>
