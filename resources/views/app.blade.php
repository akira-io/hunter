<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	{{-- Inline script to detect system dark mode preference and apply it immediately --}}
	<script>
      (function() {
          const appearance = '{{ $appearance ?? "system" }}';

          if (appearance === 'system') {
              const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

              if (prefersDark) {
                  document.documentElement.classList.add('dark');
              }
          }
      })();
	</script>
	{{-- Inline style to set the HTML background color based on our theme in app.css --}}
	<style>
      html {
          background-color: oklch(1 0 0);
      }

      html.dark {
          background-color: oklch(0.145 0 0);
      }
	</style>
	<title inertia>{{ config('app.name', 'Laravel') }}</title>

	<!-- PWA Meta Tags -->
	<meta name="application-name" content="{{ config('app.name', 'Hunter') }}">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Hunter') }}">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="theme-color" content="#a855f7" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#7c3aed" media="(prefers-color-scheme: dark)">

	<!-- PWA Icons -->
	<link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-72x72.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-72x72.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-192x192.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152x152.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/icons/icon-144x144.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/icons/icon-128x128.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/icons/icon-72x72.png">

	<!-- Manifest -->
	<link rel="manifest" href="/manifest.json">

	<!-- Splash Screens for iOS -->
	<link rel="apple-touch-startup-image" href="/icons/icon-512x512.png">

	<link rel="preconnect" href="https://fonts.bunny.net">
	<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
	@viteReactRefresh
	@vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
	@inertiaHead
</head>
<body class="font-sans antialiased">
@inertia
</body>
</html>
