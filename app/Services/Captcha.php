<?php

namespace App\Services;

class Captcha
{
    public function generate(string $text) {
        
        $width = 150;
        $height = 50;

        $image = imagecreate($width, $height);

        $bg = imagecolorallocate($image, 245, 245, 245);
        $textColor = imagecolorallocate($image, 60, 60, 60);
        $lineColor = imagecolorallocate($image, 180, 180, 180);

        // líneas de ruido
        for ($i = 0; $i < 10; $i++) {
            imageline($image, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $lineColor);
        }

        imagestring($image, 5, 35, 15, $text, $textColor);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();

        return response($imageData)->header('Content-Type', 'image/png');
    }
}