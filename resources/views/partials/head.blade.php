<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{-- Privacy-respecting font CDN (no Google tracking). Inter for UI, Frank
     Ruhl Libre for editorial headings (first-class Arabic), JetBrains Mono
     for code/IDs/cost figures. --}}
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700|frank-ruhl-libre:400,500,700|jetbrains-mono:400,500&display=swap">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
