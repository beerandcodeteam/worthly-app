<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateBrandAssetsCommand extends Command
{
    protected $signature = 'worthly:assets {--force : Overwrite existing files}';

    protected $description = 'Generate the Worthly app icon and iOS splash screens consumed by native:install';

    private const CREAM = [0xF2, 0xEF, 0xE6];

    private const INK = [0x14, 0x13, 0x0F];

    private const BUY = [0x1B, 0x7A, 0x3F];

    public function handle(): int
    {
        if (! extension_loaded('gd')) {
            $this->error('GD extension is required.');

            return self::FAILURE;
        }

        $targets = [
            ['public/icon.png', 1024, 1024, 'icon'],
            ['public/splash.png', 430, 932, 'splash'],
            ['public/splash@2x.png', 860, 1864, 'splash'],
            ['public/splash@3x.png', 1290, 2796, 'splash'],
        ];

        foreach ($targets as [$relative, $width, $height, $variant]) {
            $path = base_path($relative);

            if (File::exists($path) && ! $this->option('force')) {
                $this->components->twoColumnDetail($relative, '<fg=yellow>skipped (exists)</>');

                continue;
            }

            File::ensureDirectoryExists(dirname($path));

            $image = $this->renderCanvas($width, $height, $variant);

            imagepng($image, $path, 9);
            imagedestroy($image);

            $this->components->twoColumnDetail($relative, sprintf('<fg=green>%d×%d</>', $width, $height));
        }

        $this->newLine();
        $this->components->info('Brand assets written. Re-run `php artisan native:install` to copy them into the iOS/Android projects.');

        return self::SUCCESS;
    }

    /**
     * @return \GdImage
     */
    private function renderCanvas(int $width, int $height, string $variant)
    {
        $image = imagecreatetruecolor($width, $height);
        imagesavealpha($image, false);
        imagealphablending($image, true);

        $cream = imagecolorallocate($image, ...self::CREAM);
        imagefilledrectangle($image, 0, 0, $width, $height, $cream);

        $shorter = min($width, $height);
        $markSize = $variant === 'icon' ? (int) ($shorter * 0.48) : (int) ($shorter * 0.30);

        $this->drawMark(
            $image,
            centerX: (int) ($width / 2),
            centerY: (int) ($height / 2),
            size: $markSize,
        );

        return $image;
    }

    /**
     * Draws a stylized "W" with the signature Worthly accent dot.
     */
    private function drawMark(\GdImage $image, int $centerX, int $centerY, int $size): void
    {
        $ink = imagecolorallocate($image, ...self::INK);
        $buy = imagecolorallocate($image, ...self::BUY);

        $halfW = (int) ($size * 0.50);
        $halfH = (int) ($size * 0.42);
        $stroke = max(10, (int) ($size * 0.14));

        $dotRadius = max(12, (int) ($size * 0.115));
        $dotGap = (int) ($size * 0.06);
        $dotDiameter = $dotRadius * 2;

        // Shift the W left so the trailing dot stays inside the visual safe area.
        $shiftLeft = (int) (($dotDiameter + $dotGap) / 2);

        $apexTopLeft = [$centerX - $halfW - $shiftLeft, $centerY - $halfH];
        $valleyLeft = [$centerX - (int) ($halfW * 0.42) - $shiftLeft, $centerY + $halfH];
        $apexMiddle = [$centerX - $shiftLeft, $centerY - (int) ($halfH * 0.30)];
        $valleyRight = [$centerX + (int) ($halfW * 0.42) - $shiftLeft, $centerY + $halfH];
        $apexTopRight = [$centerX + $halfW - $shiftLeft, $centerY - $halfH];

        $segments = [
            [$apexTopLeft, $valleyLeft],
            [$valleyLeft, $apexMiddle],
            [$apexMiddle, $valleyRight],
            [$valleyRight, $apexTopRight],
        ];

        foreach ($segments as [$from, $to]) {
            $this->drawThickStrokePolygon($image, $from, $to, $stroke, $ink);
        }

        // Round caps so each apex meets the next stroke smoothly.
        foreach ([$apexTopLeft, $valleyLeft, $apexMiddle, $valleyRight, $apexTopRight] as $point) {
            imagefilledellipse($image, $point[0], $point[1], $stroke, $stroke, $ink);
        }

        $dotX = $apexTopRight[0] + $dotRadius + $dotGap;
        $dotY = $apexTopRight[1] + (int) ($halfH * 0.45);

        imagefilledellipse($image, $dotX, $dotY, $dotDiameter, $dotDiameter, $buy);
    }

    /**
     * @param  array{0:int,1:int}  $from
     * @param  array{0:int,1:int}  $to
     */
    private function drawThickStrokePolygon(\GdImage $image, array $from, array $to, int $thickness, int $color): void
    {
        $dx = $to[0] - $from[0];
        $dy = $to[1] - $from[1];
        $length = sqrt($dx * $dx + $dy * $dy);

        if ($length <= 0) {
            return;
        }

        $halfThickness = $thickness / 2;
        $normalX = -$dy / $length * $halfThickness;
        $normalY = $dx / $length * $halfThickness;

        $points = [
            (int) round($from[0] + $normalX), (int) round($from[1] + $normalY),
            (int) round($to[0] + $normalX), (int) round($to[1] + $normalY),
            (int) round($to[0] - $normalX), (int) round($to[1] - $normalY),
            (int) round($from[0] - $normalX), (int) round($from[1] - $normalY),
        ];

        imagefilledpolygon($image, $points, $color);
    }
}
