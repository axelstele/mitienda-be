<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Image;
use App\Models\Category;

class Article extends Model
{
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'category_id'
    ];

    public function category()
    {
        return $this->hasOne(Category::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
