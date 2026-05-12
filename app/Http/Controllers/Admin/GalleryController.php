<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\InputInstance;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class GalleryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage sites', only: [
                'ensureForInputInstance', 'uploadToInputInstance', 'reorder', 'removeFromGallery',
            ]),
        ];
    }

    /**
     * Ensure the input instance has a gallery_id assigned.
     * POST /admin/input-instances/{inputInstance}/gallery/ensure
     */
    public function ensureForInputInstance(InputInstance $inputInstance)
    {
        try {
            if (!$inputInstance->gallery_id) {
                $gallery = Gallery::create([
                    'name' => $inputInstance->label
                        ? ('Gallery: ' . $inputInstance->label)
                        : ('Gallery #' . $inputInstance->id),
                ]);

                $inputInstance->gallery_id = $gallery->id;
                $inputInstance->save();
            }

            return response()->json(['success' => true, 'gallery_id' => $inputInstance->gallery_id]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload images into an input instance gallery.
     * POST /admin/input-instances/{inputInstance}/gallery/upload
     */

    public function uploadToInputInstance(Request $request, InputInstance $inputInstance)
{
    // accept both images and images[]
    $files = $request->file('images');
    if (!$files) {
        $files = $request->file('images[]');
    }

    // normalize single file to array
    if ($files instanceof \Illuminate\Http\UploadedFile) {
        $files = [$files];
    }

    if (!is_array($files) || count($files) === 0) {
        return response()->json([
            'success' => false,
            'message' => 'No files received (images[]).'
        ], 422);
    }

    // validate AFTER we know which key we got
    $request->validate([
        'keep_original_name' => ['nullable', 'in:0,1'],
    ]);

    foreach ($files as $f) {
        if (!$f || !$f->isValid()) {
            $msg = $f ? $f->getErrorMessage() : 'No file received.';
            return response()->json(['success' => false, 'message' => 'Upload error: '.$msg], 422);
        }

        // allow gif too
        $mime = $f->getClientMimeType();
        if (!in_array($mime, ['image/jpeg','image/png','image/webp','image/gif'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type: ' . ($mime ?: 'unknown')
            ], 422);
        }
    }

    $keepOriginal = $request->input('keep_original_name') === '1';

    DB::beginTransaction();
    try {
        if (!$inputInstance->gallery_id) {
            $gallery = Gallery::create([
                'name' => $inputInstance->label
                    ? ('Gallery: ' . $inputInstance->label)
                    : ('Gallery #' . $inputInstance->id),
            ]);

            $inputInstance->gallery_id = $gallery->id;
            $inputInstance->save();
        }

        $gallery = Gallery::findOrFail($inputInstance->gallery_id);

        $maxOrder = DB::table('gallery_media')
            ->where('gallery_id', $gallery->id)
            ->max('sort_order');

        $order = (int)($maxOrder ?? 0) + 1;

        $relativeDir = "uploads/galleries/{$gallery->id}";
        $absoluteDir = public_path($relativeDir);

        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0755, true);
        }

        $cols = Schema::getColumnListing((new Media)->getTable());
        $created = [];

        foreach ($files as $file) {
            $mime = $file->getClientMimeType();
            $size = $file->getSize();
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension() ?: 'gif');

            $baseName = $keepOriginal
                ? pathinfo($originalName, PATHINFO_FILENAME)
                : Str::random(16);

            $safeBase = Str::slug($baseName) ?: Str::random(8);

            $filename = $safeBase . '-' . Str::random(6) . '-' . time() . '.' . $ext;

            $file->move($absoluteDir, $filename);

            $filePath = $relativeDir . '/' . $filename;
            $url = asset($filePath);

            $mediaData = [];
            if (in_array('file_name', $cols, true))   $mediaData['file_name'] = $filename;
            if (in_array('file_path', $cols, true))   $mediaData['file_path'] = $filePath;
            if (in_array('mime_type', $cols, true))   $mediaData['mime_type'] = $mime;
            if (in_array('size', $cols, true))        $mediaData['size'] = $size;
            if (in_array('type', $cols, true))        $mediaData['type'] = 'image';
            if (in_array('created_by', $cols, true))  $mediaData['created_by'] = auth()->id();

            $media = Media::create($mediaData);

            DB::table('gallery_media')->insert([
                'gallery_id' => $gallery->id,
                'media_id'   => $media->id,
                'sort_order' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created[] = ['id' => $media->id, 'url' => $url, 'name' => $originalName];
        }

        if (count($created) === 0) {
            throw new \RuntimeException('No files were processed.');
        }

        DB::commit();

        return response()->json([
            'success'    => true,
            'created'    => $created,
            'gallery_id' => $gallery->id,
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}




    /**
     * Reorder gallery items.
     * POST /admin/galleries/{gallery}/reorder  { ids: [mediaId1, mediaId2, ...] }
     */
    public function reorder(Request $request, Gallery $gallery)
    {
        $ids = $request->input('ids');
        if (!is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'Invalid ids'], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($ids as $i => $mediaId) {
                DB::table('gallery_media')
                    ->where('gallery_id', $gallery->id)
                    ->where('media_id', (int)$mediaId)
                    ->update(['sort_order' => $i]);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove media from a gallery (does NOT delete file/media row).
     * DELETE /admin/galleries/{gallery}/media/{media}
     */
    public function removeFromGallery(Gallery $gallery, Media $media)
    {
        try {
            DB::table('gallery_media')
                ->where('gallery_id', $gallery->id)
                ->where('media_id', $media->id)
                ->delete();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Permanently delete media and file (and detach from all galleries).
     * DELETE /admin/media/{media}
     */
    public function deleteMedia(Media $media)
    {
        try {
            DB::beginTransaction();

            DB::table('gallery_media')->where('media_id', $media->id)->delete();

            if (!empty($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

            $media->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
