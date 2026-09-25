@props(['title' => null, 'description' => null])
@php
    use App\Support\Locales;
    use App\Support\Seo;

    $page = Seo::page();
    $title = Seo::title($title ?? null);
    $description = Seo::description($description ?? null);
    $canonical = Seo::canonical();
    $ogImage = Seo::ogImage();
    $localeMeta = Locales::meta();
    $jsonLd = Seo::jsonLd();
@endphp
<!DOCTYPE html>
<html lang="{{ $localeMeta['hreflang'] }}" dir="{{ $localeMeta['dir'] }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $title }}</title>
  <meta name="description" content="{{ $description }}">
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
  <meta name="author" content="Honowa Technologies">
  <meta name="geo.region" content="CM">
  <meta name="geo.placename" content="Douala">
  <link rel="canonical" href="{{ $canonical }}">
  @foreach (Locales::hreflang($page) as $tag)
  <link rel="alternate" hreflang="{{ $tag['hreflang'] }}" href="{{ $tag['href'] }}">
  @endforeach
  <link rel="alternate" type="text/plain" title="llms.txt" href="{{ locale_route('llms') }}">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Focus Rent">
  <meta property="og:title" content="{{ $title }}">
  <meta property="og:description" content="{{ $description }}">
  <meta property="og:url" content="{{ $canonical }}">
  <meta property="og:image" content="{{ $ogImage }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:locale" content="{{ $localeMeta['regional'] }}">
  @foreach (Locales::codes() as $code)
    @if ($code !== app()->getLocale())
  <meta property="og:locale:alternate" content="{{ Locales::available()[$code]['regional'] }}">
    @endif
  @endforeach
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="{{ '@honowa6' }}">
  <meta name="twitter:title" content="{{ $title }}">
  <meta name="twitter:description" content="{{ $description }}">
  <meta name="twitter:image" content="{{ $ogImage }}">
  <meta name="theme-color" content="#0A0413">
  <link rel="icon" href="{{ asset('images/logo.svg') }}" type="image/svg+xml" sizes="32x32">
  <link rel="preload" href="{{ asset('fonts/montserrat-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
  @if(locale_route_is('index'))
  <link rel="preload" href="{{ asset('images/hero-bg.webp') }}" as="image" type="image/webp" fetchpriority="high">
  @endif
  <style>
    @font-face {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 400 800;
      font-display: swap;
      src: url('{{ asset('fonts/montserrat-latin.woff2') }}') format('woff2');
    }
  </style>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
  @stack('head')
</head>
<body class="min-h-screen antialiased">
    <a href="#main-content" class="skip-link">{{ __('site.skip') }}</a>
    {{ $slot }}
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-ENXE8RCY9R', {transport_type: 'beacon'});
      function loadGtag() {
        var s = document.createElement('script');
        s.src = 'https://www.googletagmanager.com/gtag/js?id=G-ENXE8RCY9R';
        s.async = true;
        document.head.appendChild(s);
      }
      if ('requestIdleCallback' in window) requestIdleCallback(loadGtag, {timeout: 3500});
      else window.addEventListener('load', function () { setTimeout(loadGtag, 1); });
    </script>
    @stack('scripts')
</body>
</html>
