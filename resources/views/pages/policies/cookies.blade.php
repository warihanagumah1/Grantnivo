<x-policies.page title="Cookie Policy & Preferences" intro="Necessary cookies keep the website and forms working. Optional analytics is off unless you enable it, and you can change that choice here." :sections="['overview'=>'How cookies are used', 'necessary'=>'Necessary cookies', 'optional'=>'Optional analytics', 'preferences'=>'Change your preferences', 'controls'=>'Other controls', 'contact'=>'Questions and changes']">
    <h2 id="overview">1. How cookies are used</h2>
    <p>Cookies are small values stored by your browser and sent back to the relevant website. Grantnivo uses first-party cookies for session security, recording your privacy choice and, only if you enable it, measuring visits and opportunity engagement. We do not use a third-party advertising tracker in the current application.</p>
    <p>The <a href="{{ route('policies.privacy') }}">Privacy Policy</a> explains the associated server records, purposes, retention and rights. Cookie duration describes browser storage, not how long every server log, backup or database record is retained.</p>

    <h2 id="necessary">2. Necessary cookies</h2>
    <ul>
        <li><strong>Session cookie (normally <code>grantnivo-session</code>):</strong> maintains the website session, staff sign-in and form feedback. The name depends on deployment configuration. The configured session lifetime is {{ (int) config('session.lifetime', 120) }} minutes and may refresh with activity.</li>
        <li><strong><code>XSRF-TOKEN</code>:</strong> supports protection against forged form requests. It is associated with the session; it is not an advertising identifier. Its visibility to JavaScript is intentional so protected requests can supply the token.</li>
        <li><strong><code>grantnivo_analytics</code>:</strong> remembers an accepted or declined analytics choice for up to one year. Both choices are stored so we can respect your selection without asking on every page. This cookie records the choice; it is not the visitor measurement identifier.</li>
    </ul>
    <p>Necessary cookies are used to deliver the requested functionality, rather than being an optional marketing choice. You can block them in your browser, but sign-in, preferences or form security may then stop working. Hosting infrastructure can have additional security cookies; these must be documented by the operator if introduced.</p>

    <h2 id="optional">3. Optional analytics</h2>
    <p>When you enable analytics, <code>grantnivo_visitor</code> stores a randomly generated visitor identifier for up to one year. The server uses keyed hashes of visitor and session identifiers to count visits and opportunity views/outbound clicks. It does not add your waitlist name or email to analytics events. This is pseudonymous measurement, not a guarantee of anonymity.</p>
    <p>Before you opt in, and after you decline, the application does not set a new visitor cookie or write optional visit/click events. Necessary logs and security processing can still occur. Do Not Track and Global Privacy Control request headers override an accepted choice for application analytics. Staff and recognized bots are excluded.</p>
    <p>Declining removes the optional visitor cookie through this browser and stops future optional events. It does not erase past database events or preferences on another browser. Use the Privacy Policy contact process for questions about historical data.</p>

    <h2 id="preferences">4. Change your preferences</h2>
    <div class="rounded-2xl border-2 border-purple/20 bg-lilac p-5 md:p-6">
        @if(session('cookie_status'))
            <div x-data="{ visible: true, timer: null, init() { this.timer = setTimeout(() => this.visible = false, 60000) }, destroy() { clearTimeout(this.timer) } }" x-show="visible" role="status" class="mb-4 flex items-start gap-3 text-purple">
                <p class="min-w-0 flex-1 font-semibold">{{ session('cookie_status') }}</p>
                <button type="button" @click="visible = false; clearTimeout(timer)" aria-label="Dismiss success message" class="flex size-10 shrink-0 items-center justify-center rounded-lg hover:bg-white focus-visible:outline-2 focus-visible:outline-purple"><flux:icon.x-mark class="size-5" aria-hidden="true" /></button>
            </div>
        @endif
        <p class="font-semibold text-charcoal">Current choice: {{ request()->cookie('grantnivo_analytics') === 'accepted' ? 'Optional analytics enabled' : 'Necessary cookies only' }}.</p>
        @if(request()->header('DNT') === '1' || request()->header('Sec-GPC') === '1')<p class="mt-3">Your browser is also sending a privacy signal, so optional analytics will remain off while that signal is present.</p>@endif
        <p class="mt-3">This choice is independent of joining the waitlist. Either option lets you browse opportunities and use the signup form.</p>
        <form method="POST" action="{{ route('policies.cookies.save') }}" class="mt-5 flex flex-wrap gap-3">
            @csrf
            <button type="submit" name="analytics" value="declined" class="admin-secondary">Use necessary cookies only</button>
            <button type="submit" name="analytics" value="accepted" class="admin-secondary">Enable optional analytics</button>
        </form>
        @error('analytics')<p role="alert" class="mt-3 text-red">{{ $message }}</p>@enderror
    </div>

    <h2 id="controls">5. Browser controls and external resources</h2>
    <p>You can inspect, delete or block cookies through your browser. Removing this site’s preference cookie resets the app to necessary cookies only until you choose again. Private-browsing sessions, another device or another browser can have different choices. Blocking all storage may prevent a saved preference from persisting.</p>
    <p>Optional analytics preferences do not prevent requests to servers needed to deliver the page, externally hosted legacy images/fonts or external funder pages you choose to open. Those providers may receive technical request information and operate under their own policies. The operator should review additional embedded tools before introducing them; this choice does not pre-authorise future tracking technologies.</p>

    <h2 id="contact">6. Questions and changes</h2>
    <p>This Cookie Policy was updated on 31 August 2026. We will update it when storage purposes or technologies materially change and seek new consent where required. For broader data questions, read the <a href="{{ route('policies.privacy') }}">Privacy Policy</a>.</p>
    <x-policies.contact />
</x-policies.page>
