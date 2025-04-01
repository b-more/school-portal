<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    use HasFactory;

    protected $table = 'news'; // Explicitly set table name since 'news' is both singular and plural

    protected $fillable = [
        'title',
        'content',
        'slug',
        'date',
        'image',
        'category',
        'status',
        'author_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'author_id');
    }
}
