<?php

namespace App\Services;

use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    /**
     * Generate Base64 Data URI for QR Code.
     */
    public static function generateDataUri(string $content, int $scale = 8): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'imageBase64' => true,
            'scale' => $scale,
            'addQuietzone' => true,
        ]);

        return (new QRCode($options))->render($content);
    }

    /**
     * Generate raw SVG string.
     */
    public static function generateSvg(string $content, int $scale = 8): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'imageBase64' => false,
            'scale' => $scale,
            'addQuietzone' => true,
        ]);

        return (new QRCode($options))->render($content);
    }
}
