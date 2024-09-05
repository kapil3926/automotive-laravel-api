<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
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
            $status = SubCategory::where('id', $request->id)->update($data);
            $message = 'Sub Category has been updated successfully';
        } else {
            $data['user_id'] = $user->getAuthIdentifier();;
            $status = SubCategory::create($data)->id;
            $message = 'Sub Category has been added successfully';
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
        $item = SubCategory::with(['Category'])->find($id);
        if ($item) {
            $response = array(
                'status' => true,
                'data' => $item
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Sub Category  not found'
            );

        }
        return response()->json($response);
    }

    /**
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public function getSubCat(Request $request): JsonResponse
    {
        $data = SubCategory::where('is_removed', 0)->with(['Category']);
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
    public function getSubCatApp (Request $request): JsonResponse
    {
        $data = SubCategory::where('is_removed', 0)->with(['Category']);
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
        $delete = SubCategory::find($id)->update(array('is_removed' => '1'));
        if ($delete) {
            $response = array(
                'status' => true,
                'message' => 'Sub Category has been deleted successfully',
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
