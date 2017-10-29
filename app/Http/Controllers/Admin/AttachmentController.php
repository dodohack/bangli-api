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

    public function __construct(Request $request)
    {
        parent::__construct($request);

        // Default image storage for production is api_server/public/images.
        $this->disk = Storage::disk('public');

        // Get image root path
        $this->imagePath = config('filesystems.image-root') . date('Y/m/');
        $this->thumbPath = $this->imagePath . 'thumbs/';

        // Init list of support thumbnail dimensions
        $this->thumbConfig = config('filesystems.thumbs');
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
        $ret = $this->getEntities($request->all());

        return $this->response($ret, 'get attachments error');
    }

    /**
     * Update multiple attachments, optionally regenerate thumbnail when
     * 'gen_thumb' is given
     */
    public function putAttachments(Request $request)
    {
        $inputs = $request->all();

        // Regenerate thumbnails
        if ($inputs['gen-thumb'] && $inputs['gen-thumb'] == true) {
            $starts = isset($inputs['starts']) ? $inputs['starts'] : null;
            $ends   = isset($inputs['ends']) ? $inputs['ends'] : null;
            $ret = $this->genThumbnails($starts, $ends);
            return $this->success($ret);
        }

        return $this->error('API unimplemented');
    }

    /**
     * Upload multiple attachments
     */
    public function postAttachments(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Move multiple attachments into trash
     */
    public function deleteAttachments(Request $request)
    {
        return $this->error('API unimplemented');
    }

    /**
     * Return attachment status and occurrences
     */
    public function getStatus(Request $request)
    {
        $status = Attachment::select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')->get();

        return $this->response($status, 'get status error');
    }

    /**
     * Get a attachment with it's relations
     * @param Request $request
     * @param $id - attachment id
     * @return string
     */
    public function getAttachment(Request $request, $id)
    {
        $ret = $this->getEntity('id', $id, null);

        return $this->response($ret, 'get attachment error');
    }

    /**
     * Update attachment by given id(update attachment information only)
     * @param Request $request
     * @param $id - attachment id to be updated
     * @return object
     */
    public function putAttachment(Request $request, $id)
    {
        $ret = $this->putEntity($request->all(), 'id', $id);

        return $this->response($ret, 'put attachment error');
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
            return $this->error('No image');

        $file = $request->file('file');

        if (!$file->isValid())
            return $this->error('Image not valid');

        // Get filename extension for the image
        $imgExt = $this->getImageFileExtension($file);
        if (!$imgExt)
            return $this->error('Unsupported image format');

        // Get absolute name with path and extension
        $imgName = $this->generateFilename();
        $fullName = $this->imagePath . $imgName . '.' . $imgExt;

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
        $record->user_id = $this->guard()->user()->id;
        $record->catalog = 'cms';
        $record->path    = $this->imagePath;
        $record->thumb_path = $this->thumbPath;
        $record->mime_type  = $file->getMimeType();
        $record->tag_id     = 1;
        $record->title      = $file->getClientOriginalName();
        $record->desc       = $file->getClientOriginalName();
        $record->filename   = $imgName . '.' . $imgExt;
        $record->size       = $file->getSize();
        $record->width      = imagesx($image);
        $record->height     = imagesy($image);
        $record->thumbnail  = $this->genThumbRecord($imgName, $imgExt);

        if ($record->save()) {
            $ret = Attachment::find($record->id)->toArray();
            return $this->success($ret);
        }

        return $this->error("Update file fail");
    }

    /**
     * Move a attachment to trash by id
     * @param Request $request
     * @param $id
     * @return Attachment
     */
    public function deleteAttachment(Request $request, $id)
    {
        return $this->error("Unimplemented: need to delete both file and record");
    }

    /**
     * Determine the extension we are going to use when create the image file
     * on disk.
     * @param $image
     * @return bool|string
     */
    private function getImageFileExtension($image)
    {
        $mime = $image->getMimeType();
        switch ($mime) {
            case 'image/jpeg':
                return 'jpg';
            case 'image/png':
                return 'png';
            case 'image/gif':
                return 'gif';
            default:
                return false;
        }
    }

    /**
     * Return a filename based on current time and a random 4 bytes suffix
     */
    private function generateFilename()
    {
        return date('YmdHis') . rand(1000, 9999);
    }

    /**
     * Generate thumbnail record
     */
    private function genThumbRecord($name, $ext)
    {
        $records = [];
        foreach($this->thumbConfig as $t) {
            $records[$t[0]] = [
                'file' => $name . '-' . $t[1] . 'x' . $t[2] . '.' . $ext,
                'width' => $t[1],
                'height' => $t[2]
            ];
        }

        return json_encode($records);
    }

    /**
     * Generate thumbnails for images which are created within given range
     * @param $starts
     * @param $ends
     */
    private function genThumbnails($starts, $ends)
    {
        // Number of images
        $count = 0;
        // Number of images processed
        $countPass = 0;
        // Number of images failed to process
        $countFail = 0;

        $db = new Attachment;
        if ($starts) $db = $db->where('created_at', '>=', $starts);
        if ($ends)   $db = $db->where('created_at', '<=', $ends);

        // TODO: Support thumbnail gen for given images
        //$images = $db->limit(10)->get();
        $db->chunk(20, function ($images) use($count, $countPass, $countFail) {
            foreach ($images as $image) {
                // If thumbnails of current image all generated successfully
                $success = true;

                // Increase number of image
                $count++;

                $uri = $image->path . $image->filename;
                $pi  = pathinfo($image->filename);

                // Do not touch image which does not exists
                if (!$this->disk->exists($uri)) {
                    continue;
                }

                // Sanity check, ignore files which has image extension names
                // but actually are not images.
                // Such as html file in .jpg extension.
                $mimeType = mime_content_type($uri);
                if (substr($mimeType, 0, 5) != 'image')
                    continue;

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

                // 3. Generate thumbnails
                $imgObj = $this->createImage($uri);
                foreach ($this->thumbConfig as $tc) {
                    if (!$this->createThumbs($this->disk, $imgObj,
                        $image->thumb_path, $pi['filename'], $pi['extension'],
                        $tc[1], $tc[2])) {
                        $success = false;
                    }
                }

                if ($success) {
                    // Increase the succeed image counter
                    $countPass++;
                    // Update records
                    $image->thumbnail =
                        $this->genThumbRecord($pi['filename'], $pi['extension']);
                    $image->save();
                } else {
                    $countFail++;
                }
            }
        });

        return ['total' => $count, 'ok' => $countPass, 'fail' => $countFail];
    }

}
