<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class MediaController extends Controller
{
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'csv', 'zip', 'mp4', 'mp3', 'ogg', 'wav', 'weba', 'webm',
    ];

    private const ALLOWED_MIMES =
        'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,mp4,mp3,ogg,wav,weba,webm';

    private function isAllowedExtension(string $filename): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, self::ALLOWED_EXTENSIONS, true);
    }

    public function index()
    {
        $media = Media::latest()->get();
        return view('pages.admin.media.index', compact('media'));
    }

    public function upload(Request $request)
{
    DB::beginTransaction();

    try {
        $mimes = self::ALLOWED_MIMES;

        $validated = $request->validate([
            // accept either "file" or "files" or "files[]"
            'file'    => "nullable|file|max:10240|mimes:{$mimes}",
            'files'   => 'nullable',
            'files.*' => "nullable|file|max:10240|mimes:{$mimes}",

            'context' => 'nullable|string|in:media,input_file,gallery',
            'owner_id' => 'nullable|integer',
            'keep_original_name' => 'nullable|in:0,1',
        ]);

        $file = $request->file('file')
            ?? $request->file('files')
            ?? ($request->file('files')[0] ?? null);

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'No file received. Expected field: file or files[]'
            ], 422);
        }


        $context      = $validated['context'] ?? 'media';
        $ownerId      = $validated['owner_id'] ?? null;
        $keepOriginal = ($validated['keep_original_name'] ?? '1') === '1';

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $request->file('file');

        // IMPORTANT: grab these BEFORE move()
        $mime = $file->getMimeType() ?? 'application/octet-stream';
        $size = $file->getSize() ?? 0;

        $isImage = str_starts_with(strtolower($mime), 'image/');
        $type = $isImage ? 'image' : 'file';

        $folder = match ($context) {
            'input_file' => $type === 'file'
                ? ('uploads/files/' . (int) $ownerId)
                : ('uploads/images/' . date('Y/m')),
            'gallery' => 'uploads/galleries/' . (int) $ownerId,
            default => $type === 'image'
                ? 'uploads/images/' . date('Y/m')
                : 'uploads/files/' . date('Y/m'),
        };

        // Build filename
        $originalName = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();

        if ($keepOriginal) {
            $base = \Illuminate\Support\Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
            $base = $base !== '' ? $base : 'file';
            $fileName = $ext ? ($base . '.' . $ext) : $base;
        } else {
            $fileName = (string) \Illuminate\Support\Str::uuid() . ($ext ? '.' . $ext : '');
        }

        // Store in /public
        $absDir = public_path($folder);
        if (!is_dir($absDir)) {
            if (!@mkdir($absDir, 0755, true) && !is_dir($absDir)) {
                throw new \RuntimeException("Cannot create directory: {$absDir}");
            }
        }

        // Avoid collisions if keeping name
        $finalName = $fileName;
        if ($keepOriginal) {
            $i = 1;
            while (file_exists($absDir . DIRECTORY_SEPARATOR . $finalName)) {
                $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);
                $extOnly  = pathinfo($fileName, PATHINFO_EXTENSION);
                $finalName = $extOnly ? ($nameOnly . '_' . $i . '.' . $extOnly) : ($nameOnly . '_' . $i);
                $i++;
            }
        }

        if (!is_writable($absDir)) {
            throw new \RuntimeException("Upload directory not writable: {$absDir}");
        }

        // Move file
        $file->move($absDir, $finalName);

        $filePath = trim($folder, '/') . '/' . $finalName;

        $media = Media::create([
            'file_name'  => $finalName,
            'file_path'  => $filePath,
            'mime_type'  => $mime,
            'size'       => $size,
            'type'       => $type,
            'created_by' => auth()->id() ?? null,
        ]);

        DB::commit();

        // FilePond-friendly response
        return response()->json([
            'success' => true,
            'id' => $media->id,
            'url' => asset($media->file_path),
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => app()->environment('local') ? $e->getTraceAsString() : null,
        ], 500);
    }

}




    public function update(Request $request, \App\Models\Media $media)
    {
        $validated = $request->validate([
            'file_name'   => ['nullable', 'string', 'max:255'],
            'alternative' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'name'        => ['nullable', 'string', 'max:255'], // only if you actually have "name" column
        ]);

        try {
            $newFileName = isset($validated['file_name']) ? trim($validated['file_name']) : null;

            // If you DON'T have `name` column in DB (you earlier got "unknown column name"),
            // remove it safely:
            if (!Schema::hasColumn('media', 'name')) {
                unset($validated['name']);
            }

            // Rename file on disk if file_name changes
            if ($newFileName && $media->file_name && $newFileName !== $media->file_name) {

                if (! $this->isAllowedExtension($newFileName)) {
                    $msg = 'File extension not allowed.';
                    return $request->expectsJson()
                        ? response()->json(['success' => false, 'message' => $msg], 422)
                        : back()->withErrors(['file_name' => $msg]);
                }

                $oldRel = $media->file_path;          // e.g. uploads/galleries/1/a.jpg
                $oldAbs = public_path($oldRel);

                $dirRel = trim(dirname($oldRel), '.\/');
                $newRel = $dirRel . '/' . $newFileName;
                $newAbs = public_path($newRel);

                if ($oldRel && File::exists($oldAbs)) {
                    if (File::exists($newAbs)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'File with this name already exists.'
                        ], 422);
                    }

                    File::move($oldAbs, $newAbs);
                }

                $media->file_name = $newFileName;
                $media->file_path = $newRel;
            }

            // Update meta
            if (array_key_exists('alternative', $validated)) $media->alternative = $validated['alternative'];
            if (array_key_exists('description', $validated)) $media->description = $validated['description'];

            // Only if column exists
            if (array_key_exists('name', $validated) && Schema::hasColumn('media', 'name')) {
                $media->name = $validated['name'];
            }

            $media->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success'   => true,
                    'file_name' => $media->file_name,
                    'file_path' => $media->file_path,
                    'url'       => asset($media->file_path),
                ]);
            }

            return back()->with('success', 'Media updated.');
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }



    public function rename(Request $request, $id)
    {
        $validated = $request->validate([
            'file_name' => ['required', 'string', 'max:255'],
        ]);

        $fileName = $validated['file_name'];

        if (! $this->isAllowedExtension($fileName)) {
            return back()->withErrors(['file_name' => 'File extension not allowed.']);
        }

        $media = Media::findOrFail($id);
        $media->update(['file_name' => $fileName]);

        return back()->with('success', 'File name updated successfully.');
    }

    public function delete($id)
    {
        $media = Media::findOrFail($id);

        $abs = public_path($media->file_path);
        if (File::exists($abs)) {
            File::delete($abs);
        }

        $media->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'File deleted successfully.');
    }




}
