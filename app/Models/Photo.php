<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'title',
        'caption',
        'alt_text',
        'image_path',
        'order',
        'featured',
    ];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
