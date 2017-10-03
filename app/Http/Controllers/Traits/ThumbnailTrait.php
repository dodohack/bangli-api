<?php
/**
 * Helper trait to create thumbnails from given images, folders etc
 * PHP libgd is required to function
 */

namespace App\Http\Controllers\Traits;

trait ThumbnailTrait
{
    /**
     * Create thumbnails for single image, the data for the thumbnail is
     * generate from the whole source image or centered rect of source image.
     *
     * @param $disk         - the disk object to store the thumbnail
     * @param $image        - image object
     * @param $thumbPath    - path to thumbnail storage
     * @param $imgName      - Image file base name
     * @param $imgExt       - Image file extension
     * @param $thumbWidth   - thumbnail width
     * @param $thumbHeight  - thumbnail height, optional
     * @return boolean      - true on success
     */
    public function createThumbs($disk, $image, $thumbPath, $imgName, $imgExt,
                                 $thumbWidth, $thumbHeight = null)
    {
        // Init default source dimension and offsets
        $width  = imagesx($image);
        $height = imagesy($image);
        $srcX   = 0;
        $srcY   = 0;

        // Calculate thumbnail size if thumbHeight is null
        if ($thumbHeight === null)
            $thumbHeight = floor($height * ($thumbWidth / $width));

        // Get max src width and height with the same ratio of thumb width:height
        $thumbRatio = $thumbWidth / $thumbHeight;
        $srcRatio   = $width / $height;

        if ($thumbRatio > $srcRatio) {
            // We are generate a thumbnail with width:height > src's
            // width:height, it normally means we are generate a landscape thumb
            // from a portrait image,
            // We will keep width unchanged and reduce src height and y properly
            $tmpHeight = floor($width / $thumbRatio);
            $srcY      = floor(($height - $tmpHeight) / 2);
            $height    = $tmpHeight;
        } else if ($thumbRatio < $srcRatio) {
            // We are generate a thumbnail with width:height < src's
            // width:height, it normally means we are generate a portrait thumb
            // from a landscape image
            // We will keep height unchanged and reduce src width and x properly
            $tmpWidth = floor($height / $thumbRatio);
            $srcX     = floor(($width - $tmpWidth) / 2);
            $width    = $tmpWidth;
        }

        // Create a new temporary image
        $tmpImg = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // Copy and resize old image into new image
        $success = imagecopyresized($tmpImg, $image,
            0/* dst x*/, 0/* dst y*/, $srcX, $srcY,
            $thumbWidth, $thumbHeight, $width, $height);

        if ($success) {
            // save thumbnail into a file
            $fullName = $thumbPath . $imgName . '-' .
                $thumbWidth . 'x' . $thumbHeight . $imgExt;

            // Storing thumbnail image in a variable
            ob_start(); // start a new output buffer
            // Create different type of thumbnail
            switch($imgExt) {
                case '.jpg':
                    imagejpeg($tmpImg, NULL);
                    break;
                case '.png':
                    imagepng($tmpImg, NULL);
                    break;
                case '.gif':
                    imagegif($tmpImg, NULL);
                    break;
                default:
                    return false;
            }
            $data = ob_get_contents();
            ob_end_clean(); // stop this output buffer

            // Save to local or cloud disk.
            $disk->put("{$fullName}", $data);

            return true;
        }

        return false;
    }

    /**
     * Create image from file or URL, support jpg, png and gif.
     * @param $uri    - file or URL
     * @return object - image object
     */
    public function createImage($uri)
    {
        $pi = pathinfo($uri);
        switch($pi['extension'])
        {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($uri);
            case 'png':
                return imagecreatefrompng($uri);
            case 'gif':
                return imagecreatefromgif($uri);
            default:
                return null;
        }
    }
}
