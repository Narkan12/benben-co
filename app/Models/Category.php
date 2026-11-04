<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'id_category';
    public $timestamps = false;

    protected $fillable = ['nama_kategori', 'slug'];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id_category');
    }
}
