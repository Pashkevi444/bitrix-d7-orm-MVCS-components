<?php

class WebpConverter
{
    /**
     * Converts an image to WebP format.
     * If the conversion already exists and overwrite is false, the original image path is returned.
     * @param string $src The path to the source image.
     * @param bool $overwrite If true, overwrites an existing WebP file.
     * @return string The path to the WebP image or the original path if conversion fails.
     */
    public static function makeWebp(string $src, bool $overwrite = false): string
    {
        if (!function_exists('imagewebp')) {
            return $src;
        }

        $newImgPath = preg_replace('/\.(jpg|jpeg|gif|png)$/i', '.webp', $src);
        $srcFullPath = $_SERVER['DOCUMENT_ROOT'] . $src;
        $newImgFullPath = $_SERVER['DOCUMENT_ROOT'] . $newImgPath;

        // Skip conversion if file already exists and overwrite is false
        if (!$overwrite && file_exists($newImgFullPath)) {
            return $newImgPath;
        }

        $info = @getimagesize($srcFullPath);
        if ($info === false) {
            return $src;
        }

        [$width, $height, $type] = $info;

        // Create image resource based on type
        $newImg = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($srcFullPath),
            IMAGETYPE_GIF => imagecreatefromgif($srcFullPath),
            IMAGETYPE_PNG => self::preparePng(imagecreatefrompng($srcFullPath)),
            default => null,
        };

        // Return original if image type is not supported
        if ($newImg === null) {
            return $src;
        }

        $res = imagewebp($newImg, $newImgFullPath, 100); // Convert to WebP format
        imagedestroy($newImg); // Free up memory

        return $res ? $newImgPath : $src; // Return the new WebP path or original path if conversion failed
    }

    /**
     * Prepares a PNG image by ensuring true color and transparency support.
     * @param resource $image The PNG image resource.
     * @return resource The prepared PNG image resource.
     */
    private static function preparePng($image)
    {
        if ($image !== false) {
            imagepalettetotruecolor($image); // Convert palette-based image to true color
            imagealphablending($image, true); // Enable alpha blending
            imagesavealpha($image, true); // Save alpha channel
        }
        return $image;
    }
}
