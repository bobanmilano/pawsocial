<?php

namespace App\Tests\Service;

use App\Service\ImageOptimizer;
use PHPUnit\Framework\TestCase;

class ImageOptimizerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pawsocial_tests';
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        }
    }

    protected function tearDown(): void
    {
        // Cleanup
        array_map('unlink', glob("$this->tempDir/*.*"));
        rmdir($this->tempDir);
    }

    public function testResize(): void
    {
        // Create a dummy image
        $filename = $this->tempDir . '/test_image.jpg';
        $image = imagecreatetruecolor(1000, 1000);
        imagejpeg($image, $filename);
        imagedestroy($image);

        $optimizer = new ImageOptimizer();
        $optimizer->resizeAndCompress($filename);

        [$width, $height] = getimagesize($filename);

        $this->assertLessThanOrEqual(1080, $width);
        $this->assertLessThanOrEqual(1080, $height);
    }
}
