<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Passport\HasApiTokens;

class BrandVersion extends Model
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
    public function BrandModel(): HasMany
    {
        return $this->hasMany(BrandModel::class, 'id', 'brand_model_id');
    }

    public function BrandModelSingle(): HasOne
    {
        return $this->hasOne(BrandModel::class, 'id', 'brand_model_id');
    }

    /**
     * Brand
     * @return HasMany
     */
    public function Brand(): HasMany
    {
        return $this->hasMany(Brand::class, 'id', 'brand_id');
    }
}
