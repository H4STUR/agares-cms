<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'file_name',
        'original_name',
        'file_path',
        'mime_type',
        'size',
        'type',
        'alternative',
        'description',
        'disk',
        'created_by',
        'updated_by',
    ];

    public function getUrlAttribute(): string
    {
        // because you want it in /public/uploads/... with no storage:link
        return asset($this->file_path);
    }

    public function galleries()
    {
        return $this->belongsToMany(\App\Models\Gallery::class, 'gallery_media')
            ->withPivot('sort_order');
    }

    public function inputInstances()
    {
        return $this->belongsToMany(\App\Models\InputInstance::class, 'input_instance_media')
            ->withPivot('sort_order');
    }

}
