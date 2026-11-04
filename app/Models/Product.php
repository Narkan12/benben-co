<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'id_product';

    protected $fillable = [
        'category_id',
        'nama',
        'deskripsi',
        'harga',
        'gambar',
        'stok',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id_category');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'product_id', 'id_product');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'product_id', 'id_product');
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class, 'product_id', 'id_product');
    }

    public function getSlugAttribute(): string
    {
        return Str::slug($this->nama ?? 'product');
    }
}
