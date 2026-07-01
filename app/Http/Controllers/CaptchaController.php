<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CaptchaController extends Controller
{
    /**
     * Generate a captcha image.
     */
    public function generate(Request $request): \Illuminate\Http\Response
    {
        $code = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        Session::put('captcha_code', $code);

        // Create image
        $width = 100;
        $height = 40;
        $image = imagecreatetruecolor($width, $height);

        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 200, 200, 200);

        // Background
        imagefill($image, 0, 0, $white);

        // Add some noise
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel($image, random_int(0, $width), random_int(0, $height), $gray);
        }

        for ($i = 0; $i < 3; $i++) {
            imageline($image, 0, random_int(0, $height), $width, random_int(0, $height), $gray);
        }

        // Draw the code
        $font = 5; // Built-in font
        $x = ($width - (imagefontwidth($font) * strlen($code))) / 2;
        $y = ($height - imagefontheight($font)) / 2;
        imagestring($image, $font, (int) $x, (int) $y, $code, $black);

        // Output image
        ob_start();
        imagepng($image);
        $content = ob_get_clean();
        imagedestroy($image);

        return response($content)->header('Content-Type', 'image/png');
    }

    /**
     * Get captcha status (for frontend).
     */
    public function getStatus(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'enabled' => true,
        ]);
    }

    /**
     * Get captcha code (only for demo mode).
     */
    public function getCode(): \Illuminate\Http\JsonResponse
    {
        if (config('app.mode') !== 'demo') {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        return response()->json([
            'code' => session('captcha_code'),
        ]);
    }
}
