<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
class Otp extends Model
{
    use HasFactory;

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
     * Save OTP
     * @param $data
     * @return Otp|Model
     */
    public function createOTP($data)
    {
        return $this->create($data);
    }

    /**
     * Delete OTP
     * @param $id
     * @return bool|null
     */
    public function deleteOTP($id): ?bool
    {
        return $this->find($id)->delete();
    }

    /**
     * Get OTP Entry
     * @param $mobile
     * @param $otp
     * @return Otp|Model|object|null
     */
    public function getOTPData($mobile, $otp)
    {
        return $this->where('mobile', $mobile)->where('otp', $otp)->first();
    }
}
