<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfileImage extends Model
{
    protected $table = 'company_profile_images';

    protected $fillable = [
        'image_key',
        'label',
        'default_path',
        'image_path',
    ];

    public function currentPath(): string
    {
        return $this->image_path ?: $this->default_path;
    }
}
