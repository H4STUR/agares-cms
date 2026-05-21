<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'created_by', 'updated_by'];

    /**
     * Relationship to sites with ordering by menu_order in the pivot table.
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'menu_site')
                    ->withPivot('menu_order') // This ensures menu_order is available in the pivot
                    ->orderBy('menu_site.menu_order'); // Orders the sites by the menu_order
    }
    
}
