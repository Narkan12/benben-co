<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotions';
    protected $primaryKey = 'id_promo';
    public $timestamps = false;

    protected $fillable = [
        'nama_promo',
        'kode_promo',
        'jenis_diskon',
        'nilai_diskon',
        'tanggal_mulai',
        'tanggal_berakhir',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'promo_id', 'id_promo');
    }
}
