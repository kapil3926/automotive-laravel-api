<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->all();
        unset($data['id']);
        if ($request->has('id') && $request->id !== null) {
            $status = Category::where('id', $request->id)->update($data);
            $message = 'Category has been updated successfully';
        } else {
            $data['user_id'] = $user->getAuthIdentifier();;
            $status = Category::create($data)->id;
            $message = 'Category has been added successfully';
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
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public function getCat(Request $request): JsonResponse
    {
        $data = Category::where('is_removed', 0)->with('subCat');
        $total = $data->count();
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }
        $data = $data->get()->toArray();
        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }

    public function getCatApp(Request $request): JsonResponse
    {
        $data = Category::where('is_removed', 0)->with('subCat');
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
    public function show($id): JsonResponse
    {
        $item = Category::with('subCat')->find($id);
        if ($item) {
            $response = array(
                'status' => true,
                'data' => $item
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Category not found'
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
        $delete = Category::find($id)->update(array('is_removed' => '1'));
        if ($delete) {
            SubCategory::where('cat_id', '=', $id)->update(array('is_removed' => '1'));
            $response = array(
                'status' => true,
                'message' => 'Category has been deleted successfully',
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
