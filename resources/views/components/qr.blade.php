{{-- resources/views/components/qr.blade.php --}}

@props([
    'data',
    'size'            => 200,
    'filename'        => 'qrcode',
    'download'        => false,
    'format'          => 'svg',          {{-- 'svg' | 'png' --}}
    'errorCorrection' => 'M',            {{-- L | M | Q | H --}}
    'foreground'      => [0, 0, 0],
    'background'      => [255, 255, 255],
    'alt'             => 'Código QR',
])

@php
    $isSvg  = strtolower($format) === 'svg';
    $src    = $isSvg
        ? \App\Services\Qr::svgDataUri($data, $size, 4, $errorCorrection, $foreground, $background)
        : \App\Services\Qr::pngDataUri($data, $size, 4, $errorCorrection, $foreground, $background);

    $mime   = $isSvg ? 'svg+xml' : 'png';
    $ext    = $isSvg ? 'svg' : 'png';
    $uid    = 'qr-' . substr(md5($data . $size . $format), 0, 8);
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-4']) }}>
    <img
        id="{{ $uid }}"
        src="{{ $src }}"
        alt="{{ $alt }}"
        width="{{ $size }}"
        height="{{ $size }}"
        loading="lazy"
    >

    @if($download)
        <flux:button
            variant="primary"
            icon="document-arrow-down"
            x-data
            @click="
                const a = document.createElement('a');
                a.href = '{{ $src }}';
                a.download = '{{ $filename }}.{{ $ext }}';
                a.click();
            "
        >
            Descargar QR
        </flux:button>
    @endif
</div>