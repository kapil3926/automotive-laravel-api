<?php

namespace App\Http\Controllers;

use App\Models\BrandModel;
use App\Models\BrandVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandVersionController extends Controller
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
//            $check = BrandVersion::where('id', '=', $request->id)->first();
//            if ($request->brand_id !== $check->brand_id) {
////                dd("please Select Brand Model");
//                if ($request->brand_model_id === $check->brand_model_id) {
//                    return response()->json(
//                        ['message' => 'You Change Brand Please Select Brand Version',
//                            'status' => false]);
//                } else {
//                    $status = BrandVersion::where('id', $request->id)->update($data);
//                    $message = 'Brand ver has been updated successfully';
//                }
//            } else {
            $status = BrandVersion::where('id', $request->id)->update($data);
            $message = 'Version has been updated successfully';
//            }

        } else {
            $data['user_id'] = $user->getAuthIdentifier();;
            $status = BrandVersion::create($data)->id;
            $message = 'Version has been added successfully';
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
     * Display the specified resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $item = BrandVersion::with(['BrandModel', 'BrandModelSingle', 'Brand'])->find($id);
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
    public function getBrandVersion(Request $request): JsonResponse
    {
//        ->with(['Brand', 'BrandModel']
        $data = BrandVersion::where('is_removed', 0);
        $total = $data->count();
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }
        $data = $data->get()->toArray();
        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }

    /**
     * get Version Name
     * @param Request $request
     * @return JsonResponse
     */
    public function getBrandVersionApp(Request $request): JsonResponse
    {
//        ->with(['Brand', 'BrandModel'])
        $data = BrandVersion::where('is_removed', 0)->select('id','name');
        $total = $data->count();
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }
        $data = $data->get()->toArray();
        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $delete = BrandVersion::find($id)->update(array('is_removed' => '1'));
        if ($delete) {
            $response = array(
                'status' => true,
                'message' => 'Brand Version has been deleted successfully',
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
