<?php
/**
 * Dashboard attachment controller
 * Shared by all domains.
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\JWTAuth;

use App\Http\Controllers\EntityController;
use App\Models\Attachment;

use App\Http\Controllers\Traits\ThumbnailTrait;


class AttachmentController extends EntityController
{
    use ThumbnailTrait;

    // Local or remote disk storage
    protected $disk;

    protected $imagePath;
    protected $thumbPath;
    protected $thumbConfig;

    public function __construct(JWTAuth $jwt)
    {
        parent::__construct($jwt);

        // We can change 'local' to 's3' or other cloud system
        $this->disk = Storage::disk('s3');

        // Get image root path
        $this->imagePath = config('filesystems.image-root') . date('Y/m/');
        $this->thumbPath = $this->imagePath . 'thumbs/';

        // Init list of support thumbnail dimensions
        if ($this->domain == 'huluwa.uk') {
            $this->thumbConfig = config('filesystems.thumb-huluwa');
        } else {
            $this->thumbConfig = config('filesystems.thumb-bangli');
        }
    }

    /**
     * Return a list of posts
     * @param Request $request
     * @return object
     */
    public function getAttachments(Request $request)
    {
        // We need to add extra columns to the returned array, which is the
        // image server address.
        $inputs = $request->all();
        $images = $this->getArrayEntities($inputs['etype'], $inputs);
        return $this->getEntitiesReq($request);
    }

    /**
     * Update multiple attachments, optionally regenerate thumbnail when
     * 'gen_thumb' is given
     */
    public function putAttachments(Request $request)
    {
        // TODO: Need extra authentication for editing others' files

        $inputs = $request->all();

        // TODO: Retrieve images from request body

        // Regenerate thumbnails
        if (array_key_exists('gen-thumb', $inputs)) {

            // TODO: Support thumbnail gen for given images
            $images = Attachment::limit(10)->get();
            //Attachment::chunk(100, function ($images) {
                foreach ($images as $image) {
                    $uri = $image->path . $image->filename;
                    $pi  = pathinfo($image->filename);

                    // Do not touch image which does not exists
                    if (!$this->disk->exists($uri)) {
                        echo "Image '", $uri, "' does not exist<br>";
                        continue;
                    }

                    // 1. Get name and path info of old thumbnails
                    $oldThumbs = json_decode($image->thumbnail, true);

                    if (count($oldThumbs)) {
                        // Form the full path for each old thumbnails
                        $thumbNames = array_column($oldThumbs, 'file');
                        foreach ($thumbNames as $idx => $tn) {
                            $thumbNames[$idx] = $image->thumb_path . $tn;
                        }

                        // 2. Remove old thumbnails
                        $this->disk->delete($thumbNames);
                    }

                    // 3. Remove old records, [skip this for faster speed]
                    // $this->thumbnail = null;
                    // $this->save();

                    // 4. Generate thumbnails
                    $imgObj = $this->createImage($uri);
                    foreach ($this->thumbConfig as $tc) {
                        $this->createThumbs($this->disk, $imgObj,
                            $image->thumb_path, $pi['basename'], $pi['extension'],
                            $tc[1], $tc[2]);
                    }

                    // 5. Update records
                    $image->thumbnail =
                        $this->genThumbRecord($pi['basename'], $pi['extension']);
                    $image->save();
                }
            //});
        }

        return response('Posts batch editing API unimplemented', 401);
    }

    /**
     * Upload multiple attachments
     */
    public function postAttachments(Request $request)
    {
        return response('Posts batch editing API unimplemented', 401);
    }

    /**
     * Move multiple attachments into trash
     */
    public function deleteAttachments(Request $request)
    {
        return response('API unimplemented', 401);
    }

    /**
     * Return attachment states and occurrences
     */
    public function getStates(Request $request)
    {
        return $this->getEntityStates($request, 'attachments');
    }

    /**
     * Get a attachment with it's relations
     * @param Request $request
     * @param $id - attachment id
     * @return string
     */
    public function getAttachment(Request $request, $id)
    {
        return $this->getEntityReq($request, 'id', $id, null);
    }

    /**
     * Update attachment by given id(update attachment information only)
     * @param Request $request
     * @param $id - attachment id to be updated
     * @return object
     */
    public function putAttachment(Request $request, $id)
    {
        // TODO: Need extra authentication for editing others' files
        return $this->putEntityReq($request, 'id', $id);
    }

    /**
     * Create a new attachment(upload a new file), the file name is converted
     * to a user specified one or default file name
     * @param Request $request
     * @return object
     */
    public function postAttachment(Request $request)
    {
        if (!$request->hasFile('file'))
            return response('No image', 400);

        $file = $request->file('file');

        if (!$file->isValid())
            return response('Image is not valid', 400);

        // Get filename extension for the image
        $imgExt = $this->getImageFileExtension($file);
        if (!$imgExt)
            return response('Unsupported image format', 415);

        // Get absolute name with path and extension
        $imgName = $this->generateFilename();
        $fullName = $this->imagePath . $imgName . $imgExt;

        // Store the image to disk or cloud
        $this->disk->put("{$fullName}", File::get($file));
        //$this->disk->put($image->getClientOriginalName(), File::get($image));

        // Create the image which will be used to generated thumbnail
        $image = imagecreatefromstring(File::get($file));

        // Generate thumbnails
        foreach ($this->thumbConfig as $thumb) {
            $this->createThumbs($this->disk, $image,
                $this->thumbPath, $imgName, $imgExt, $thumb[1], $thumb[2]);
        }

        // Update database record
        $record = new Attachment;
        $record->user_id = $this->jwt->authenticate()->id;
        $record->catalog = 'cms';
        $record->path    = $this->imagePath;
        $record->thumb_path = $this->thumbPath;
        $record->mime_type  = $file->getMimeType();
        $record->tag_id     = 1;
        $record->title      = $file->getClientOriginalName();
        $record->desc       = $file->getClientOriginalName();
        $record->filename   = $imgName . $imgExt;
        $record->size       = $file->getSize();
        $record->width      = imagesx($image);
        $record->height     = imagesy($image);
        $record->thumbnail  = $this->genThumbRecord($imgName, $imgExt);

        if ($record->save()) {
            $ret = Attachment::find($record->id)->toArray();
            return parent::success($request, $ret);
        } else {
            return response("Failed to update file", 401);
        }
    }

    /**
     * Move a attachment to trash by id
     * @param Request $request
     * @param $id
     * @return Attachment
     */
    public function deleteAttachment(Request $request, $id)
    {
        // TODO: Need extra authentication for deleting others' files
        return $this->deleteEntityReq($request, 'id', $id);
    }

    /**
     * Determine the extension we are going to use when create the image file
     * on disk.
     * @param $image
     * @return bool|string
     */
    private function getImageFileExtension($image) {
        $mime = $image->getMimeType();
        switch ($mime) {
            case 'image/jpeg':
                return '.jpg';
            case 'image/png':
                return '.png';
            case 'image/gif':
                return '.gif';
            default:
                return false;
        }
    }

    /**
     * Return a filename based on current time and a random 4 bytes suffix
     */
    private function generateFilename() {
        return date('YmdHis') . rand(1000, 9999);
    }

    /**
     * Generate thumbnail record
     */
    private function genThumbRecord($name, $ext) {
        $records = [];
        foreach($this->thumbConfig as $t) {
            $records[$t[0]] = [
                'file' => $name . '-' . $t[1] . 'x' . $t[2] . $ext,
                'width' => $t[1],
                'height' => $t[2]
            ];
        }

        return json_encode($records);
    }
}