<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartsRequest;
use App\Models\partsSelling;
use App\Models\RequiredParts;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class PartsSellingController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getData(Request $request): JsonResponse
    {
        $id = $request->id;
        $data = partsSelling::where('id', '=', $id)
            ->with('Brand', 'BrandVersion', 'Cat', 'SubCat', 'BrandModel')
            ->first();
        if ($data) {
            $data = $data->toArray();
            $user_id = user::where('id', '=', $data['user_id'])->first();
            $data['title'] = $data['brand']['name'];
            $data['brandModel_name'] = $data['brand_model']['name'];
            $data['version'] = $data['brand_version']['name'];
            $data['category'] = $data['cat']['name'];
            $data['subCategory'] = $data['sub_cat']['name'];
            $data['City'] = $user_id->city;
            $data['SellerName'] = $user_id->name;
            $data['MobileNumber'] = $user_id->mobile;
            unset(
                $data['is_removed'],
                $data['is_blocked'],
                // $data['id'],
                $data['user_id'],
                $data['brand_id'],
                $data['brandModel_id'],
                $data['version_id'],
                $data['cat_id'],
                $data['subCat_id'],
                $data['brand'],
                $data['brand_version'],
                $data['cat'],
                $data['sub_cat'],
                $data['brand_model']
            );
            $response = [
                "status" => true,
                "data" => $data
            ];
        } else {
            $response = [
                "status" => false,
                "message" => 'Data not found'
            ];
        }


        return response()->json($response);
    }

    /**
     *
     * list for urgent and
     * @param Request $request
     * @return JsonResponse
     */
    public function getListUrgent(Request $request): JsonResponse
    {

        $data = partsSelling::
        where('is_removed', 0)
            ->with('Brand', 'BrandVersion', 'Cat', 'SubCat', 'BrandModel', 'User')
            ->orderBy('created_at', 'desc');

        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }
        $data = $data->get()->toArray();
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
     * Display the specified resource.
     *
     * @param \App\Models\RequiredParts $requiredParts
     * @return \Illuminate\Http\Response
     */
    public
    function showProduct($id): JsonResponse
    {
        $item = partsSelling::with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat', 'User')->find($id);
        if ($item) {
            $item = $item->toArray();
            $item['brand'] = $item['brand']['name'];
            $item['brand_model'] = $item['brand_model']['name'];
            $item['brand_version'] = $item['brand_version']['name'];
            $item['cat'] = $item['cat']['name'];
            $item['sub_cat'] = $item['sub_cat']['name'];
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

    // public function upload(Request $request): JsonResponse
    // {
    //     if ($request->hasFile('image')) {
    //         $file = $request->file('image');
    //         $original_name = strtotime(date('d-m-Y H:i:s')) . $file->getClientOriginalName();
    //         $destinationPath = "partsSelling/";
    //         $file->move($destinationPath, $original_name);
    //         $result = array("status" => true, "data" => $original_name);
    //     } else {
    //         $result = array("status" => false, "message" => 'Image not given', 'data' => $_FILES);
    //     }
    //     return response()->json($result);
    // }
    public
    function upload(Request $request): JsonResponse
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
     * @param PartsRequest $request
     * @return JsonResponse
     */
    public
    function store(PartsRequest $request): JsonResponse
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
        if ($request->has('id') && $request->id !== null) {
            $data['image'] = implode(',', $data['image']);
            $data['postDate'] = date('d-m-Y');
            $status = partsSelling::where('id', $request->id)->update($data);
            $message = 'Parts Selling  has been updated successfully';
        } else {
            $data['image'] = implode(',', $data['image']);
            $data['user_id'] = $user->getAuthIdentifier();
            $data['postDate'] = date('d-m-Y');
            $status = partsSelling::create($data)->id;
            $message = 'Parts Selling  has been added successfully';
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
     * Parts Selling Search data from multiple table
     * @param Request $request
     * @return JsonResponse
     */
    public
    function search(Request $request): JsonResponse
    {
        $data = partsSelling::where('parts_sellings.is_removed', '=', '0')
            ->join('brands', 'parts_sellings.brand_id', '=', 'brands.id')
            ->join('brand_models', 'parts_sellings.brandModel_id', '=', 'brand_models.id')
            ->join('brand_versions', 'parts_sellings.version_id', '=', 'brand_versions.id')
            ->join('categories', 'parts_sellings.cat_id', '=', 'categories.id')
            ->join('sub_categories', 'parts_sellings.subCat_id', '=', 'sub_categories.id')
            ->select('parts_sellings.*', 'brands.name as brandName', 'brand_models.name as brandModelName',
                'brand_versions.name as brandVersionName', 'categories.name as categoryName'
                , 'sub_categories.name as subCatName')
            ->where('parts_sellings.modelYear', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('parts_sellings.quantity', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('parts_sellings.approxRate', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('parts_sellings.conditionPart', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('parts_sellings.postDate', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('parts_sellings.urgentSell', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('brands.name', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('brand_models.name', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('brand_versions.name', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('categories.name', 'LIKE', "%{$request->get('search')}%")
            ->orWhere('sub_categories.name', 'LIKE', "%{$request->get('search')}%")
            ->with('User')->get()->toArray();
        $data1 = [];
        foreach ($data as $d => $da) {
            if ($da['is_removed'] === '0') {
                $data1[] = $da;
            }
        }
        return response()->json(['status' => true, 'data' => $data1]);

    }

    /**
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public
    function getBrand(Request $request): JsonResponse
    {
        $data = partsSelling::where('is_removed', '0')->with('BrandModel');
        $total = $data->count();
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }
        $data = $data->get()->toArray();
        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }


    /**
     *
     * Brand For Application
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public
    function getPartsSelling(Request $request): JsonResponse
    {
        $data = partsSelling::where('is_removed', 0)->with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat', 'User');
        $total = $data->count();
        $data = $data->get()->toArray();
        foreach ($data as $i => $da) {
            $data[$i]['brandName'] = $da['brand']['name'];
            $data[$i]['brandModelName'] = $da['brand_model']['name'];
            $data[$i]['brandVersion'] = $da['brand_version']['name'];
            $data[$i]['categoryName'] = $da['cat']['name'];
            $data[$i]['subCategoryName'] = $da['sub_cat']['name'];
//            $data[$i]['image'] = unserialize($da['image']);
//            unset($data[$i]['brand'], $data[$i]['brand_version'], $data[$i]['cat'], $data[$i]['sub_cat'], $data[$i]['brand_model']);
            unset($data[$i]['brand'], $data[$i]['brand_version'], $data[$i]['cat'], $data[$i]['sub_cat'], $data[$i]['brand_model']
                , $data[$i]['brand_id'], $data[$i]['brandModel_id'], $data[$i]['version_id'], $data[$i]['cat_id'], $data[$i]['subCat_id'], $data[$i]['is_removed'], $data[$i]['is_blocked']);

        }
        if ($request->start != '' && $request->pageSize != '') {
            $data->skip($request->start)->take($request->pageSize);
        }


        return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
    }

    /**
     *
     * get user profile urgent list
     * get category
     * @param Request $request
     * @return JsonResponse
     */
    public
    function userUrgentPartsList(Request $request): JsonResponse
    {
        $user = auth()->user();
        $data = partsSelling::where('user_id', $user->getAuthIdentifier())->where('is_removed', 0)->with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat', 'User');
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

    /**
     * @param $id
     * @return void
     */
    public
    function show($id): JsonResponse
    {
        $item = partsSelling::with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat')->find($id);

        if ($item) {
            $item = $item->toArray();

            unset($item['brand'], $item['brand_version'], $item['cat'], $item['sub_cat'], $item['brand_model']
                , $item['brand_id'], $item['brandModel_id'], $item['version_id'], $item['cat_id'], $item['subCat_id'], $item['is_removed'], $item['is_blocked']);
//            $item['image'] = unserialize($item['image']);
            $response = array(
                'status' => true,
                'data' => $item
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Part Selling  not found'
            );

        }
        return response()->json($response);
    }

    /**
     * @param $id
     * @return void
     */
    public
    function destroy($id): JsonResponse
    {
        $delete = partsSelling::where('id', $id)->update(array('is_removed' => '1'));
        if ($delete) {
            $response = array(
                'status' => true,
                'message' => 'Parts Selling  has been deleted successfully',
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
     * searchUserProfile
     * @param Request $request
     * @return JsonResponse
     */
    public
    function searchUserProfile(Request $request): JsonResponse
    {
        $user = auth()->user();
        $userId = (string)$user->getAuthIdentifier();
        if ($request->urgentSell === 1 || $request->urgentSell === '1') {
            $data = partsSelling::join('brands', 'parts_sellings.brand_id', '=', 'brands.id')
                ->join('brand_models', 'parts_sellings.brandModel_id', '=', 'brand_models.id')
                ->join('brand_versions', 'parts_sellings.version_id', '=', 'brand_versions.id')
                ->join('categories', 'parts_sellings.cat_id', '=', 'categories.id')
                ->join('sub_categories', 'parts_sellings.subCat_id', '=', 'sub_categories.id')
                ->select('parts_sellings.*', 'brands.name as brandName', 'brand_models.name as brandModelName',
                    'brand_versions.name as brandVersionName', 'categories.name as categoryName'
                    , 'sub_categories.name as subCatName')
                ->where('parts_sellings.modelYear', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('parts_sellings.quantity', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('parts_sellings.approxRate', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('parts_sellings.conditionPart', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('parts_sellings.postDate', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('parts_sellings.urgentSell', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('brands.name', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('brand_models.name', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('brand_versions.name', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('categories.name', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('sub_categories.name', 'LIKE', "%{$request->get('search')}%")
                ->with('User')->get()->toArray();
//            dd($data);
            $data1 = [];
            foreach ($data as $d => $da) {
                if ($da['is_removed'] === '0' && $da['user_id'] === $userId) {
                    $data1[] = $da;
                }
            }
            return response()->json(['status' => true, 'data' => $data1]);
        } elseif ($request->urgentSell === 0 || $request->urgentSell === '0') {
            $data = RequiredParts::join('brands', 'required_parts.brand_id', '=', 'brands.id')
                ->join('brand_models', 'required_parts.brandModel_id', '=', 'brand_models.id')
                ->join('brand_versions', 'required_parts.version_id', '=', 'brand_versions.id')
                ->join('categories', 'required_parts.cat_id', '=', 'categories.id')
                ->join('sub_categories', 'required_parts.subCat_id', '=', 'sub_categories.id')
                ->select('required_parts.*', 'brands.name as brandName', 'brand_models.name as brandModelName',
                    'brand_versions.name as brandVersionName', 'categories.name as categoryName'
                    , 'sub_categories.name as subCatName')
                ->where('required_parts.modelYear', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('required_parts.quantity', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('required_parts.approxRate', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('required_parts.conditionPart', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('required_parts.postDate', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('required_parts.urgentSell', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('brands.name', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('brand_models.name', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('brand_versions.name', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('categories.name', 'LIKE', "%{$request->get('search')}%")
                ->orWhere('sub_categories.name', 'LIKE', "%{$request->get('search')}%")
                ->with('User')->get()->toArray();
            $data1 = [];
            foreach ($data as $d => $da) {
                if ($da['is_removed'] === '0' && $da['user_id'] === $userId) {
                    $data1[] = $da;
                }
            }
            return response()->json(['status' => true, 'data' => $data1]);
        } else {
            return response()->json(['status' => false, 'data' => [],]);
        }

    }
}
