@php
    $size = $size ?? 38;
    $variant = $variant ?? 'color';
    $uid = $uid ?? 'slogo';
    $class = $class ?? '';
@endphp

<svg viewBox="0 0 64 64"
     width="{{ $size }}"
     height="{{ $size }}"
     xmlns="http://www.w3.org/2000/svg"
     class="simonter-s-logo simonter-shield-s {{ $class }}"
     role="img"
     aria-label="Logo SIMONTER Shield S">
    <defs>
        {{-- Unique Cosmic Aurora Shield Border: Electric Cyan -> Emerald Mint -> Royal Violet --}}
        <linearGradient id="shieldRim_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#22d3ee" />
            <stop offset="50%" stop-color="#10b981" />
            <stop offset="100%" stop-color="#8b5cf6" />
        </linearGradient>

        {{-- Deep Shield Interior: Midnight Indigo -> Cosmic Violet -> Deep Teal --}}
        <linearGradient id="shieldField_{{ $uid }}" x1="15%" y1="0%" x2="85%" y2="100%">
            <stop offset="0%" stop-color="#0f172a" />
            <stop offset="52%" stop-color="#1e1b4b" />
            <stop offset="100%" stop-color="#0f766e" />
        </linearGradient>

        {{-- Heroic "S" Crest Gradient: Luminous White -> Neon Aqua -> Aurora Mint & Gold Glint --}}
        <linearGradient id="shieldS_{{ $uid }}" x1="10%" y1="0%" x2="90%" y2="100%">
            <stop offset="0%" stop-color="#ffffff" />
            <stop offset="45%" stop-color="#67e8f9" />
            <stop offset="78%" stop-color="#34d399" />
            <stop offset="100%" stop-color="#fde047" />
        </linearGradient>

        <linearGradient id="shieldBevel_{{ $uid }}" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.32" />
            <stop offset="100%" stop-color="#ffffff" stop-opacity="0.0" />
        </linearGradient>
    </defs>

    {{-- Outer Pentagonal Superman-Style Diamond Crest --}}
    <path d="M 14 7 L 50 7 L 60 21.5 L 32 58.5 L 4 21.5 Z"
          fill="url(#shieldRim_{{ $uid }})"
          stroke="{{ $variant === 'light' ? 'rgba(255,255,255,0.65)' : 'rgba(15,23,42,0.18)' }}"
          stroke-width="1.2"
          stroke-linejoin="round" />

    {{-- Inner Shield Field --}}
    <path d="M 16.5 11.2 L 47.5 11.2 L 55.2 22 L 32 52.4 L 8.8 22 Z"
          fill="url(#shieldField_{{ $uid }})"
          stroke="rgba(103, 232, 249, 0.35)"
          stroke-width="0.9"
          stroke-linejoin="round" />

    {{-- Top Crystal Facet Sheen --}}
    <path d="M 16.5 11.2 L 47.5 11.2 L 55.2 22 L 32 24.5 L 8.8 22 Z"
          fill="url(#shieldBevel_{{ $uid }})" />

    {{-- Sculpted Heroic "S" Monogram Framed Inside the Shield --}}
    <path d="M 46.2 15.6 C 42 13.8 36.2 13.4 30.2 13.6 C 21.6 13.8 16.8 17.5 16.4 22.8 C 16 27.6 19.6 30.8 27.6 32.5 L 34.4 33.9 C 38.4 34.8 40 36.2 39.8 38.4 C 39.5 41 36.2 42.4 31.2 42.4 C 26.2 42.4 22.4 40.8 20.6 38.8 L 16.8 42.2 C 20.8 46.2 26.4 47.8 32 47.8 C 40.4 47.8 46.8 44 47.2 38 C 47.6 32.8 44 29.4 35.6 27.6 L 28.8 26.2 C 24.8 25.3 23.4 23.8 23.6 21.8 C 23.8 19.6 26.8 18.4 31.6 18.4 C 35.8 18.4 39.2 19.4 42.4 21.2 L 46.2 15.6 Z"
          fill="url(#shieldS_{{ $uid }})" />

    {{-- Apex Aurora Spark Diamond --}}
    <polygon points="32,8 34,9.8 32,11.6 30,9.8" fill="#fde047" />
</svg>
