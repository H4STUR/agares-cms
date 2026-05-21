<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomCode extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'custom_codes';

    // Define the fillable fields
    protected $fillable = ['type', 'content', 'description'];

    // If you want to use the timestamps automatically, make sure they are enabled
    public $timestamps = true;
}
