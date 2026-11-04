<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;

    protected $fillable = ['order_id', 'product_id', 'harga', 'qty', 'subtotal'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id_order');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }
}
