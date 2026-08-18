<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgricultureKnowledge extends Model
{
    protected $table = 'agriculture_knowledges';

    protected $fillable = [
        'category',
        'title',
        'slug',
        'summary',
        'content_markdown',
        'tags',
        'is_featured',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
    ];
}
