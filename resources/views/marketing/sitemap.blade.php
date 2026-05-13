<?php
$jurisdictions = ['eg', 'sa', 'ae', 'kw', 'qa', 'bh', 'om', 'jo', 'lb', 'tn', 'ly'];
$glossaryIsos = ['eg', 'sa', 'ae', 'kw', 'qa', 'bh', 'om', 'jo', 'lb'];
$contractTypes = ['spa', 'mou', 'employment', 'services', 'nda', 'lease', 'distribution', 'shareholders'];
$now = now()->toAtomString();
?>
<?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
        <xhtml:link rel="alternate" hreflang="ar" href="{{ url('/?lang=ar') }}" />
        <xhtml:link rel="alternate" hreflang="en" href="{{ url('/?lang=en') }}" />
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ url('/') }}" />
    </url>
    <url>
        <loc>{{ url('/contracts') }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.95</priority>
    </url>
    <url>
        <loc>{{ url('/glossary') }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ url('/faq') }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc>{{ url('/pricing') }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>

    {{-- Legal pages --}}
    <url>
        <loc>{{ url('/legal/terms') }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ url('/legal/privacy') }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ url('/legal/dpa') }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.4</priority>
    </url>
    <url>
        <loc>{{ url('/legal/aup') }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.4</priority>
    </url>

    {{-- Jurisdictions --}}
    @foreach ($jurisdictions as $iso)
    <url>
        <loc>{{ url('/jurisdictions/'.strtoupper($iso)) }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
        <xhtml:link rel="alternate" hreflang="ar" href="{{ url('/jurisdictions/'.strtoupper($iso).'?lang=ar') }}" />
        <xhtml:link rel="alternate" hreflang="en" href="{{ url('/jurisdictions/'.strtoupper($iso).'?lang=en') }}" />
    </url>
    @endforeach

    {{-- Glossaries --}}
    @foreach ($glossaryIsos as $iso)
    <url>
        <loc>{{ url('/glossary/'.$iso) }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Contract type hubs --}}
    @foreach ($contractTypes as $type)
    <url>
        <loc>{{ url('/contracts/'.$type) }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.85</priority>
    </url>
    @endforeach

    {{-- Contract type × jurisdiction (88 pages) --}}
    @foreach ($contractTypes as $type)
        @foreach ($jurisdictions as $iso)
    <url>
        <loc>{{ url('/contracts/'.$type.'/'.strtoupper($iso)) }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.75</priority>
        <xhtml:link rel="alternate" hreflang="ar" href="{{ url('/contracts/'.$type.'/'.strtoupper($iso).'?lang=ar') }}" />
        <xhtml:link rel="alternate" hreflang="en" href="{{ url('/contracts/'.$type.'/'.strtoupper($iso).'?lang=en') }}" />
    </url>
        @endforeach
    @endforeach
</urlset>
