<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    public const DEFINITIONS = [
        'home' => [
            'label' => 'Beranda',
            'eyebrow' => 'Freshly baked · Made with care',
            'title' => 'A Little Sweetness for Every Moment',
            'description' => 'Berawal dari dapur keluarga, BENBEN CO. menghadirkan cheese roll, brownies, bolen pisang, cookies, dan minuman dengan bahan berkualitas serta harga yang bersahabat.',
        ],
        'about' => [
            'label' => 'Tentang Kami',
            'eyebrow' => 'A small family-run bakery',
            'title' => 'Resep rumahan. Cerita yang dibagikan.',
            'description' => 'BENBEN CO. adalah usaha toko kue rumahan di Antapani, Bandung. Ceritanya tumbuh dari rasa penasaran owner, dukungan keluarga, dan keinginan untuk berbagi produk bakery berkualitas dengan masyarakat sekitar.',
        ],
        'favorites' => [
            'label' => 'Menu Favorit',
            'eyebrow' => 'Made for sharing',
            'title' => 'Kenalan dengan produk kami.',
            'description' => 'Beberapa pilihan bakery dan minuman yang tersedia di BENBEN CO.',
        ],
        'promotions' => [
            'label' => 'Promo',
            'eyebrow' => 'Promo',
            'title' => 'Penawaran Spesial untuk Anda',
            'description' => 'Temukan promo dan penawaran terbaru dari BENBEN CO.',
        ],
        'contact' => [
            'label' => 'Kontak',
            'eyebrow' => 'Kontak',
            'title' => 'Hubungi Kami',
            'description' => 'Kami siap membantu menjawab pertanyaan Anda tentang produk dan pemesanan.',
        ],
    ];

    protected $fillable = [
        'page_key',
        'eyebrow',
        'title',
        'description',
        'image_path',
        'status',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public static function publishedFor(string $pageKey): ?self
    {
        return static::query()->published()->where('page_key', $pageKey)->first();
    }
}
