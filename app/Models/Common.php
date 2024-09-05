<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Common extends Model
{
    use HasFactory;

    /**
     * Create Pagination
     * @param $request
     * @param $query
     * @return null
     */
    public function paginateData($query, $request)
    {
        if (isset($request['start'])) {
            return $query->skip($request['start'])->take($request['pageSize'])->get()->toArray();
        } else {
            return $query->get()->toArray();
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function searchCommon($data)
    {
        if ($data->get('search')) {
            $data->where('name', 'LIKE', "%{$data->get('search')}%")->orWhere('price', 'LIKE', "%{$data->get('search')}%");
        }
        return $data->where('is_removed', 0);
    }

    /**
     * Order Data By Default input or from request
     * @param $query
     * @param $request
     * @param string $default
     * @return mixed
     */
    public function orderData($query, $request, string $default = '')
    {
        if (isset($request['name']) && $request['name'] != '' && $request['dir']) {
            return $query->orderBy($request['name'], $request['dir']);
        } else {
            if ($default) {
                return $query->orderBy($default);
            } else {
                return $query;
            }
        }
    }

    /**
     * @param $query
     * @param $request
     * @param string $default
     * @return mixed
     */
    public function filter($query, $request, string $default = '')
    {
        if ($request['asc'] == "") {
            return $query->orderBy('id','DESC' , $request['dir']);
        } else {
            return $query->orderBy($default);
        }
    }
}
