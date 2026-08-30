<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head')</head>
<body class="marketing-v1 bg-offwhite! min-h-screen">
    <main class="flex min-h-svh flex-col items-center justify-center px-6 py-12">
        <div class="w-full max-w-[420px]">
            <x-app-logo class="mb-10 [&_img]:h-8" />
            {{ $slot }}
        </div>
    </main>
    @fluxScripts
</body>
</html>
