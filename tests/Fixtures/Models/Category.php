<?php

namespace Elegant\DataTables\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function parent()
    {
        return $this->belongsTo(self::class);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class)
                    ->withPivot(['position']);
    }
}
