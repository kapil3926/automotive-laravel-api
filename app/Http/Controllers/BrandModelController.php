<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\BrandVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandModelController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->all();
        unset($data['id']);
        if ($request->has('id') && $request->id !== null) {
            $status = BrandModel::where('id', $request->id)->update($data);
            $message = 'Brand Model has been updated successfully';
        } else {
            $data['user_id'] = $user->getAuthIdentifier();;
            $status = BrandModel::create($data)->id;
            $message = 'Brand Model has been added successfully';
        }
        if ($status) {
            $response = array(
                'status' => true,
                'message' => $message,
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Something went wrong'
            );
        }
        return response()->json($response);
    }

    /**
     * @param $id
     * @return void
     */
    public function show($id): JsonResponse
    {
        $item = BrandModel::with(['BrandName'])->find($id);
        if ($item) {
            $response = array(
                'status' => true,
                'data' => $item
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Brand Model not found'
            );

        }
        return response()->json($response);
    }

    /**
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public function getBrandModel(Request $request): JsonResponse
    {
        $data = BrandModel::where('is_removed', 0)->with(['BrandName']);
        $total = $data->count();
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }
        $data = $data->get()->toArray();
        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }

    /**
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public function getBrandModelApp(Request $request): JsonResponse
    {
        $data = BrandModel::where('is_removed', 0)->with(['BrandName']);
        $total = $data->count();
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }
        $data = $data->get()->toArray();
        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }


    /**
     * @param $id
     * @return void
     */
    public function destroy($id): JsonResponse
    {
        $delete = BrandModel::find($id)->update(array('is_removed' => '1'));
        if ($delete) {
//            BrandVersion::where('brand_model_id', '=', $id)->update(array('is_removed' => '1'));
            $response = array(
                'status' => true,
                'message' => 'Brand Model has been deleted successfully',
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Not affected',
            );
        }
        return response()->json($response);
    }
}
