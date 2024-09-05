<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\BrandVersion;
use Illuminate\Support\Facades\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Upload image to temp storage and return name
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $original_name = strtotime(date('d-m-Y H:i:s')) . $file->getClientOriginalName();
            $destinationPath = "temp/";
            $file->move($destinationPath, $original_name);
            $result = array("status" => true, "data" => $original_name);
        } else {
            $result = array("status" => false, "message" => 'Image not given', 'data' => $_FILES);
        }
        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     * @param BrandRequest $request
     * @return JsonResponse
     */
    public function store(BrandRequest $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->all();
        unset($data['id']);
        if ($data['image'] != "") {
            if (File::exists(public_path("temp/" . $data['image']))) {
                File::move(public_path("temp/" . $data['image']), public_path("brand/" . $data['image']));
                File::delete("temp/" . $data['image']);
            }
        }
        if ($request->has('id') && $request->id !== null) {
            $status = Brand::where('id', $request->id)->update($data);
            $message = 'Brand has been updated successfully';
        } else {
            $data['user_id'] = $user->getAuthIdentifier();;
            $status = Brand::create($data)->id;
            $message = 'Brand has been added successfully';
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
     * Frequent True False
     * @param Request $request
     * @return JsonResponse
     */
    public function changeBrandStatus(Request $request): JsonResponse
    {
        $act = ['frequent' => $request->status ? 'true' : 'false'];
        $updateArr = Brand::where('id', $request->get('id'))->update($act);
        if ($updateArr) {
            $response = array(
                'status' => true,
                'message' => 'Brand has been ' . ($request->status ? 'Frequent' : 'Not Frequent')
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'not affected',
            );
        }

        return response()->json($response);
    }

    /**
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public function getBrand(Request $request): JsonResponse
    {
        $data = Brand::where('is_removed', 0)->with('BrandModel');
        $total = $data->count();
        // if ($request->start != '' && $request->pageSize != '') {
        //     $data->skip($request->start)->take($request->pageSize);
        // }
        if ($request->get('name') != '' && $request->get('dir')) {
            $data->orderBy($request->get('name'), $request->get('dir'));
        } else {
            $data->orderBy('created_at', 'desc');
        }

        $total = $data->count();

        if ($request->pageSize) {
            $data->skip($request->start)->take($request->pageSize);
        }

        $data = $data->get()->toArray();
        //
        // $data = $data->get()->toArray();
        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }


    /**
     *
     * Brand For Application
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public function getBrandForApp(Request $request): JsonResponse
    {
        $data = Brand::select('id', 'name', 'image')->where('is_removed', 0)->where('frequent', '=', 'false');
        $frequent = Brand::select('id', 'name', 'image')->where('is_removed', 0)->where('frequent', '=', 'true');
        if ($request->search) {
            $data->where('name', 'LIKE', "%{$request->get('search')}%");
            $frequent->where('name', 'LIKE', "%{$request->get('search')}%");
        }
        $totalOthers = $data->count();
        $totalFrequent = $frequent->count();
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
            $frequent->skip($request->start)->take($request->pageSize);
        }

        $data = $data->get()->toArray();
        $frequent = $frequent->get()->toArray();
        return response()->json(['status' => true, 'frequent' => $frequent, 'others' => $data, 'totalOthers' => $totalOthers, 'totalFrequent' => $totalFrequent]);
    }

    /**
     * @param $id
     * @return void
     */
    public function show($id): JsonResponse
    {
        $item = Brand::with('BrandModel')->find($id);
        if ($item) {
            $response = array(
                'status' => true,
                'data' => $item
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Brand not found'
            );

        }
        return response()->json($response);
    }

    /**
     * @param $id
     * @return void
     */
    public function destroy($id): JsonResponse
    {
        $delete = Brand::find($id)->update(array('is_removed' => '1'));
        if ($delete) {
            BrandModel::where('brand_id', '=', $id)->update(array('is_removed' => '1'));
//            BrandVersion::where('brand_id', '=', $id)->update(array('is_removed' => '1'));
            $response = array(
                'status' => true,
                'message' => 'Brand has been deleted successfully',
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
