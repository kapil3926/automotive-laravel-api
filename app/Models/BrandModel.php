<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Passport\HasApiTokens;

class BrandModel extends Model
{
    use HasFactory, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Brand Version
     * @return HasMany
     */
    public function BrandVersion(): HasMany
    {
        return $this->hasMany(BrandVersion::class, 'brand_model_id', 'id');
    }

    /**
     * Brand
     * @return HasMany
     */
    public function Brand(): HasMany
    {
        return $this->hasMany(Brand::class, 'id', 'brand_id');
    }

    public function BrandName(): HasOne
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }
}
