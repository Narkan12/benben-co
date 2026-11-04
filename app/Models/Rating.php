<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';
    protected $primaryKey = 'id_rating';
    public $timestamps = false;

    protected $fillable = ['product_id', 'nama_customer', 'rating', 'komentar'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }
}
