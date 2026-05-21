<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Media;

class MediaController extends Controller
{
    public function show(Media $media)
    {
        return response()->json([
            'data' => [
                'id' => $media->id,
                'original_name' => $media->original_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'type' => $media->type,
                'alt' => $media->alternative,
                'description' => $media->description,
                'url' => $media->url,
            ]
        ]);
    }
}
