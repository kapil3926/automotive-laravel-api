<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Passport\HasApiTokens;

class partsSelling extends Model
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

    public function User(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function Brand(): HasOne
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    public function BrandVersion(): HasOne
    {
        return $this->hasOne(BrandVersion::class, 'id', 'version_id');
    }

    public function Cat(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'cat_id');
    }

    public function SubCat(): HasOne
    {
        return $this->hasOne(SubCategory::class, 'id', 'subCat_id');
    }

    public function BrandModel(): HasOne
    {
        return $this->hasOne(BrandModel::class, 'id', 'brandModel_id');
    }
//      public function Brand(): HasOne
//    {
//        return $this->hasOne(Brand::class, 'id', 'brand_id')->select('id','name','image');
//    }
//
//    public function BrandVersion(): HasOne
//    {
//        return $this->hasOne(BrandVersion::class, 'id', 'version_id')->select('id','name','brand_id','brand_model_id');
//    }
//
//    public function Cat(): HasOne
//    {
//        return $this->hasOne(Category::class, 'id', 'cat_id')->select('id','name');
//    }
//
//    public function SubCat(): HasOne
//    {
//        return $this->hasOne(SubCategory::class, 'id', 'subCat_id')->select('id','name','cat_id');
//    }
//
//    public function BrandModel(): HasOne
//    {
//        return $this->hasOne(BrandModel::class, 'id', 'brandModel_id')->select('id','name','brand_id');
//    }
}