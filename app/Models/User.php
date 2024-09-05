<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Laravel\Passport\HasApiTokens;

/**
 * @mixin Builder
 */
class User extends Authenticatable
{
    use HasFactory, HasApiTokens;


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'is_removed',
        'created_at',
        'updated_at'
    ];

    /**
     * Join User with Distributor
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Create User
     * @param $data
     * @return Model|User
     */
    public function createUser($data)
    {
        return $this->create($data);
    }

    /**
     * Get User Data
     * @param $mobile
     * @param string $role
     * @param string $password
     * @return Model|object|User|null
     */
    public function getUser($mobile, string $role = '', string $password = '')
    {
        $data = $this->where('mobile', $mobile);
        if ($role) {
            $data->where('role', $role);
        }
        if ($password) {
            $data->where('password', hash('sha512', $password));
        }
        return $data->first();
    }

    /**
     * Get User Data
     * @param $id
     * @return Model|object|User|null
     */
    public function getUserById($id)
    {
        return $this->find($id);
    }


    /**
     * Update User Token for Push Notification
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateData($id, $data): bool
    {
        return $this->where('id', $id)->update($data);
    }

    /**
     * Get User List
     * @param Request $request
     * @return array
     */
    public function userList(Request $request): array
    {
        $common = new Common();
        $data = $this->where('role', 'user');
        if ($request->get('search')) {
            $data->where('name', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('email', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('mobile', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('address', 'LIKE', "%{$request->get('search')}%");
        }
        $data->having('role', '=', 'user');
        $data = $common->orderData($data, $request, 'name');
        $total = $data->count();
        $data = $common->paginateData($data, $request);
        return ['total' => $total, 'data' => $data, 'status' => true];
    }

    public function changeStatus($request): bool
    {
        $updateArr = ['is_blocked' => $request->status ? 'true' : 'false'];
        if ($request->status === true) {
            OauthAccessToken::where('user_id', $request->id)->delete();
        }
        return $this->where('id', $request->id)->update($updateArr);
    }
}
