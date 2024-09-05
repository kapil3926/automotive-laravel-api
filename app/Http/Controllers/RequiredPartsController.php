<?php

namespace App\Http\Controllers;

use App\Models\RequiredParts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class RequiredPartsController extends Controller
{


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        $data = $request->all();
        unset($data['id']);

        if ($data['image'] != "") {
            $data['image'] = explode(',', $data['image']);
            foreach ($data['image'] as $d => $da) {
                if (File::exists(public_path("temp/" . $da))) {
                    File::move(public_path("temp/" . $da), public_path("partsSelling/" . $da));
                    File::delete("temp/" . $da);
                }
            }
        }
        $data['image'] = implode(',', $data['image']);
        if ($request->has('id') && $request->id !== null) {
            // $data['image'] = implode(',', $data['image']);
            $data['postDate'] = date('d-m-Y');
            $status = RequiredParts::where('id', $request->id)->update($data);
            $message = 'Required parts has been updated successfully';
        } else {
            $data['user_id'] = $user->getAuthIdentifier();
            $data['postDate'] = date('d-m-Y');
            $status = RequiredParts::create($data)->id;
            $message = 'Required parts has been added successfully';
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
    public function showProduct($id): JsonResponse
    {
        $item = RequiredParts::with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat', 'User')->find($id);

        if ($item) {
            $item = $item->toArray();
            $item['brand'] = $item['brand']['name'];
            $item['brand_model'] = $item['brand_model']['name'];
            $item['brand_version'] = $item['brand_version']['name'];
            $item['cat'] = $item['cat']['name'];
            $item['sub_cat'] = $item['sub_cat']['name'];
            // unset($item['brand'], $item['brand_version'], $item['cat'], $item['sub_cat'], $item['brand_model']
            //     , $item['brand_id'], $item['brandModel_id'], $item['version_id'], $item['cat_id'], $item['subCat_id'], $item['is_removed'], $item['is_blocked']);
            unset($item['brand_id'], $item['brandModel_id'], $item['version_id'], $item['cat_id'], $item['subCat_id'], $item['is_removed'], $item['is_blocked']);

            $response = array(
                'status' => true,
                'data' => $item
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Data not found'
            );

        }
        return response()->json($response);
    }

    /**
     * List of not urgent list
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function getList(Request $request): JsonResponse
    {
        //  after bug changes year wise data not required
        //
        if ($request->modelYear) {
            $data = RequiredParts::
            where('brand_id', $request->brandId)
                ->where('brandModel_id', $request->modelId)
                ->where('version_id', $request->versionId)
                ->where('cat_id', $request->cateId)
                ->where('subCat_id', $request->subCateId)
                ->where('modelYear', $request->modelYear)
                ->where('is_removed', 0)
                ->with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat', 'User')
                ->orderBy('created_at', 'desc');
        } else {
            $data = RequiredParts::
            where('brand_id', $request->brandId)
                ->where('brandModel_id', $request->modelId)
                ->where('version_id', $request->versionId)
                ->where('cat_id', $request->cateId)
                ->where('subCat_id', $request->subCateId)
                ->where('is_removed', 0)
                ->with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat', 'User')
                ->orderBy('created_at', 'desc');
        }


//        $total = $data->count();
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }

        $data = $data->get()->toArray();
//        foreach ($data as $i => $da) {
//            $data[$i]['brandName'] = $da['brand']['name'];
//            $data[$i]['brandModelName'] = $da['brand_model']['name'];
//            $data[$i]['brandVersion'] = $da['brand_version']['name'];
//            $data[$i]['categoryName'] = $da['cat']['name'];
//            $data[$i]['subCategoryName'] = $da['sub_cat']['name'];
//            $data[$i]['city'] = $da['user']['city'];
//            unset($data[$i]['brand'], $data[$i]['brand_version'], $data[$i]['cat'], $data[$i]['sub_cat'], $data[$i]['brand_model']
//                , $data[$i]['brand_id'], $data[$i]['brandModel_id'], $data[$i]['version_id'], $data[$i]['cat_id'], $data[$i]['subCat_id'], $data[$i]['is_removed'], $data[$i]['is_blocked'], $data[$i]['user']);
//        }

        $new_arr = [];

        foreach ($data as $i => $da1) {
            if ($data[$i]['user']['activate'] === '0') {
                $new_arr[] = $data[$i];
            }
        }
        foreach ($new_arr as $n => $da) {
            $new_arr[$n]['brandName'] = $da['brand']['name'];
            $new_arr[$n]['brandModelName'] = $da['brand_model']['name'];
            $new_arr[$n]['brandVersion'] = $da['brand_version']['name'];
            $new_arr[$n]['categoryName'] = $da['cat']['name'];
            $new_arr[$n]['subCategoryName'] = $da['sub_cat']['name'];
            $new_arr[$n]['city'] = $da['user']['city'];
            unset($new_arr[$n]['brand'], $new_arr[$n]['brand_version'], $new_arr[$n]['cat'], $new_arr[$n]['sub_cat'], $new_arr[$n]['brand_model']
                , $new_arr[$n]['brand_id'], $new_arr[$n]['brandModel_id'], $new_arr[$n]['version_id'], $new_arr[$n]['cat_id'], $new_arr[$n]['subCat_id'], $new_arr[$n]['is_removed'], $new_arr[$n]['is_blocked'], $new_arr[$n]['user']);
        }
        $total = count($new_arr);
        return response()->json(['data' => $new_arr, 'status' => true, 'total' => $total]);
    }

    /**
     * Display the List  resource.
     *
     * @param RequiredParts $requiredParts
     * @return Response
     */

    public function getListSearch(Request $request): JsonResponse
    {
        $data = RequiredParts::where('is_removed', 0)->with('Brand', 'BrandVersion', 'Cat', 'SubCat', 'BrandModel', 'User');
        //   if ($request->get('search')) {
        //       $brand=Brand::where('name',$request->search);
        //     $data->where('brand_id', 'LIKE', "%{$request->get('search')}%")
        //     // ->orWhere('total', 'LIKE', "%{$request->get('search')}%")
        //     // ->orWhere('paymentMethod', 'LIKE', "%{$request->get('search')}%")
        //     ;
        // }
        $total = $data->count();
        $data = $data->get()->toArray();
        foreach ($data as $i => $da) {
            $data[$i]['brandName'] = $da['brand']['name'];
            $data[$i]['brandModelName'] = $da['brand_model']['name'];
            $data[$i]['brandVersion'] = $da['brand_version']['name'];
            $data[$i]['categoryName'] = $da['cat']['name'];
            $data[$i]['subCategoryName'] = $da['sub_cat']['name'];
            $data[$i]['city'] = $da['user']['city'];
            unset($data[$i]['brand'], $data[$i]['brand_version'], $data[$i]['cat'], $data[$i]['sub_cat'], $data[$i]['brand_model']
                , $data[$i]['brand_id'], $data[$i]['brandModel_id'], $data[$i]['version_id'], $data[$i]['cat_id'], $data[$i]['subCat_id'], $data[$i]['is_removed'], $data[$i]['is_blocked'], $data[$i]['user']);

        }
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }
        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }

    /**
     * @param $id
     * @return void
     */
    public function destroy($id): JsonResponse
    {
        $delete = RequiredParts::where('id', $id)->update(array('is_removed' => '1'));
        if ($delete) {
            $response = array(
                'status' => true,
                'message' => 'Parts has been deleted successfully',
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Not affected',
            );
        }
        return response()->json($response);
    }

    /**
     *
     * get user profile NormalParts list
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public function userNormalPartsList(Request $request): JsonResponse
    {
        $user = auth()->user();
        $data = RequiredParts::where('user_id', $user->getAuthIdentifier())->where('is_removed', 0)->with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat', 'User');
        $total = $data->count();
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }
        $data = $data->get()->toArray();
        // foreach ($data as $i => $da) {
        //     $data[$i]['brandName'] = $da['brand']['name'];
        //     $data[$i]['brandModelName'] = $da['brand_model']['name'];
        //     $data[$i]['brandVersion'] = $da['brand_version']['name'];
        //     $data[$i]['categoryName'] = $da['cat']['name'];
        //     $data[$i]['subCategoryName'] = $da['sub_cat']['name'];
        //     unset($data[$i]['brand'], $data[$i]['brand_version'], $data[$i]['cat'], $data[$i]['sub_cat'], $data[$i]['brand_model']
        //         , $data[$i]['brand_id'], $data[$i]['brandModel_id'], $data[$i]['version_id'], $data[$i]['cat_id'], $data[$i]['subCat_id'], $data[$i]['is_removed'], $data[$i]['is_blocked']);

        // }
        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }

}
