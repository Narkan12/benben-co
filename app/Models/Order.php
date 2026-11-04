<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $primaryKey = 'id_order';

    public $timestamps = false;

    protected $fillable = [
        'kode_order',
        'customer_id',
        'tanggal',
        'subtotal',
        'delivery',
        'diskon',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'cash_received',
        'cash_change',
        'payment_proof',
        'delivery_method',
        'catatan',
        'source',
        'promo_id',
        'kode_promo_used',
        'admin_viewed_at',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'cash_received' => 'float',
        'cash_change' => 'float',
        'admin_viewed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id_customer');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id_order');
    }

    public function promo()
    {
        return $this->belongsTo(Promotion::class, 'promo_id', 'id_promo');
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id', 'id_order')
            ->orderBy('changed_at')
            ->orderBy('id_history');
    }
}
