<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $primaryKey = 'id_customer';

    public $timestamps = false;

    protected $fillable = ['nama', 'no_whatsapp', 'alamat'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id_customer');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'customer_id', 'id_customer');
    }
}
