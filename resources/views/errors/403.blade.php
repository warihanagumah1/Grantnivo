<x-layouts.marketing title="Staff access required">
    <main class="mx-auto flex w-full max-w-[1180px] flex-1 flex-col items-center justify-center px-6 py-28 text-center">
        <p class="font-mono text-[12px] uppercase tracking-[0.18em] text-teal">Staff access required</p>
        <h1 class="mt-4 text-[36px] font-bold tracking-tight">This area is for administrators.</h1>
        <p class="mt-4 max-w-md text-[16px] text-charcoal/60">Your account does not have access to the admin console. Contact your site administrator if you need staff access.</p>
        <a href="{{ route('home') }}" class="admin-primary mt-8">Return to Grantnivo</a>
    </main>
</x-layouts.marketing>
