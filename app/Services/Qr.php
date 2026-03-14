<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use InvalidArgumentException;

class Qr
{
    public const EC_LOW      = 'L';
    public const EC_MEDIUM   = 'M';
    public const EC_QUARTILE = 'Q';
    public const EC_HIGH     = 'H';

    // ─── SVG ─────────────────────────────────────────────────────────────────

    public static function svg(
        string $content,
        int    $size            = 200,
        int    $margin          = 4,
        string $errorCorrection = self::EC_MEDIUM,
        array  $foreground      = [0, 0, 0],
        array  $background      = [255, 255, 255],
    ): string {
        self::validate($content, $size, $margin);

        // SVG es texto puro → seguro cachearlo directamente
        return static::remember(
            'svg', $content, $size, $margin, $errorCorrection, $foreground, $background,
            function () use ($content, $size, $margin, $errorCorrection, $foreground, $background) {
                $fill = Fill::withForegroundColor(
                    new Rgb(...$background),
                    new Rgb(...$foreground),
                    new EyeFill(null, null),
                    new EyeFill(null, null),
                    new EyeFill(null, null),
                );

                $renderer = new ImageRenderer(
                    new RendererStyle($size, $margin, null, null, $fill),
                    new SvgImageBackEnd()
                );

                return (new Writer($renderer))->writeString(
                    $content,
                    Encoder::DEFAULT_BYTE_MODE_ECODING,
                    self::ecLevel($errorCorrection)
                );
            }
        );
    }

    /**
     * SVG como data URI — cachea el URI completo (texto seguro)
     */
    public static function svgDataUri(
        string $content,
        int    $size            = 200,
        int    $margin          = 4,
        string $ec              = self::EC_MEDIUM,
        array  $fg              = [0, 0, 0],
        array  $bg              = [255, 255, 255],
    ): string {
        return 'data:image/svg+xml;base64,' . base64_encode(
            static::svg($content, $size, $margin, $ec, $fg, $bg)
        );
    }

    // ─── PNG ─────────────────────────────────────────────────────────────────

    /**
     * PNG como bytes binarios crudos — SIN caché (binario no es seguro en BD)
     * Usar solo cuando necesites los bytes directamente (respuesta HTTP, etc.)
     */
    public static function png(
        string $content,
        int    $size            = 200,
        int    $marginModules   = 4,
        string $errorCorrection = self::EC_MEDIUM,
        array  $foreground      = [0, 0, 0],
        array  $background      = [255, 255, 255],
    ): string {
        self::validate($content, $size, $marginModules);

        return self::renderPng($content, $size, $marginModules, $errorCorrection, $foreground, $background);
    }

    /**
     * PNG como data URI — cachea el base64 (texto puro, seguro en cualquier driver)
     * Usar en Blade, Livewire, APIs, etc.
     */
    public static function pngDataUri(
        string $content,
        int    $size            = 200,
        int    $margin          = 4,
        string $ec              = self::EC_MEDIUM,
        array  $fg              = [0, 0, 0],
        array  $bg              = [255, 255, 255],
    ): string {
        self::validate($content, $size, $margin);

        // ✅ Cacheamos el data URI completo → es texto base64, seguro en MariaDB/MySQL
        return static::remember(
            'png_uri', $content, $size, $margin, $ec, $fg, $bg,
            function () use ($content, $size, $margin, $ec, $fg, $bg) {
                return 'data:image/png;base64,' . base64_encode(
                    self::renderPng($content, $size, $margin, $ec, $fg, $bg)
                );
            }
        );
    }

    // ─── Internals ───────────────────────────────────────────────────────────

    /**
     * Lógica GD pura, sin caché ni validación. Llamado internamente.
     */
    private static function renderPng(
        string $content,
        int    $size,
        int    $marginModules,
        string $errorCorrection,
        array  $foreground,
        array  $background,
    ): string {
        $qrCode     = Encoder::encode($content, self::ecLevel($errorCorrection));
        $matrix     = $qrCode->getMatrix();
        $matrixW    = $matrix->getWidth();
        $total      = $matrixW + ($marginModules * 2);
        $moduleSize = max(1, (int) ($size / $total));
        $canvas     = $moduleSize * $total;

        $image = imagecreatetruecolor($canvas, $canvas)
            ?: throw new \RuntimeException('No se pudo crear el recurso GD.');

        imagesavealpha($image, true);

        $bgColor = imagecolorallocate($image, ...$background);
        $fgColor = imagecolorallocate($image, ...$foreground);
        imagefill($image, 0, 0, $bgColor);

        for ($y = 0; $y < $matrixW; $y++) {
            for ($x = 0; $x < $matrixW; $x++) {
                if ($matrix->get($x, $y)) {
                    $px = ($x + $marginModules) * $moduleSize;
                    $py = ($y + $marginModules) * $moduleSize;
                    imagefilledrectangle(
                        $image, $px, $py,
                        $px + $moduleSize - 1, $py + $moduleSize - 1,
                        $fgColor
                    );
                }
            }
        }

        $stream = fopen('php://memory', 'r+');
        imagepng($image, $stream);
        imagedestroy($image);
        rewind($stream);
        $bytes = stream_get_contents($stream);
        fclose($stream);

        return $bytes;
    }

    private static function remember(
        string   $type,
        string   $content,
        int      $size,
        int      $margin,
        string   $ec,
        array    $fg,
        array    $bg,
        callable $generate,
    ): string {
        $key = 'qr.' . $type . '.' . md5(
            "{$content}|{$size}|{$margin}|{$ec}|" . implode(',', $fg) . '|' . implode(',', $bg)
        );

        return cache()->remember($key, now()->addHours(24), $generate);
    }

    private static function ecLevel(string $level): ErrorCorrectionLevel
    {
        return match (strtoupper($level)) {
            'L'     => ErrorCorrectionLevel::L(),
            'M'     => ErrorCorrectionLevel::M(),
            'Q'     => ErrorCorrectionLevel::Q(),
            'H'     => ErrorCorrectionLevel::H(),
            default => throw new InvalidArgumentException(
                "Nivel de corrección inválido: '{$level}'. Use L, M, Q o H."
            ),
        };
    }

    private static function validate(string $content, int $size, int $margin): void
    {
        if (trim($content) === '') throw new InvalidArgumentException('El contenido no puede estar vacío.');
        if ($size < 10)            throw new InvalidArgumentException("Tamaño mínimo 10px, recibido: {$size}.");
        if ($margin < 0)           throw new InvalidArgumentException("El margen no puede ser negativo: {$margin}.");
    }
}