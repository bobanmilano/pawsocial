<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Event\Event;

class ImageOptimizer
{
    private const MAX_WIDTH = 1080;
    private const MAX_HEIGHT = 1080;
    private const QUALITY = 80;

    public function onVichUploaderPreUpload(Event $event): void
    {
        $file = $event->getObject()->getImageFile();

        // Only process if it is a file and looks like an image
        if (!$file instanceof File || !str_contains($file->getMimeType(), 'image/')) {
            return;
        }

        $filePath = $file->getRealPath();

        // Basic optimization using GD
        $this->resizeAndCompress($filePath);
    }

    public function resizeAndCompress(string $filePath): void
    {
        list($originalWidth, $originalHeight, $type) = getimagesize($filePath);

        // Load image based on type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filePath);
                break;
            default:
                return; // Unsupported type
        }

        if (!$source) {
            return;
        }

        // Calculate new dimensions if resizing is needed
        $ratio = $originalWidth / $originalHeight;
        $newWidth = $originalWidth;
        $newHeight = $originalHeight;

        if ($originalWidth > self::MAX_WIDTH || $originalHeight > self::MAX_HEIGHT) {
            if ($ratio > 1) {
                $newWidth = self::MAX_WIDTH;
                $newHeight = self::MAX_WIDTH / $ratio;
            } else {
                $newHeight = self::MAX_HEIGHT;
                $newWidth = self::MAX_HEIGHT * $ratio;
            }
        }

        $newImage = imagecreatetruecolor((int) $newWidth, (int) $newHeight);

        // Preserve transparency for PNG/WEBP
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        imagecopyresampled($newImage, $source, 0, 0, 0, 0, (int) $newWidth, (int) $newHeight, $originalWidth, $originalHeight);

        // Save as same type but compressed (or JPEG if simple)
        // For simplicity and 150KB goal, we might convert everything to optimized JPEG or keep format with high compression
        // Let's keep format but enforce compression

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $filePath, self::QUALITY);
                break;
            case IMAGETYPE_PNG:
                // PNG compression 0-9
                imagepng($newImage, $filePath, 8);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($newImage, $filePath, self::QUALITY);
                break;
        }

        imagedestroy($source);
        imagedestroy($newImage);
    }
}
