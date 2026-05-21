<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model 
{
  use HasFactory;
  
  protected $fillable = ['gallery_id','media_id','sort_order'];

  public function gallery() { return $this->belongsTo(Gallery::class); }
  public function media() { return $this->belongsTo(Media::class); }
}