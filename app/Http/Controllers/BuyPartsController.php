<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\buyParts;
use App\Models\RequiredParts;
use App\Models\User;
use App\Models\BrandModel;
use App\Models\BrandVersion;
use App\Models\partsSelling;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BuyPartsController extends Controller
{


    /**
     *
     *Search the data acoording to input
     *
     */

    public function searchList(Request $request)
    {
        $brandId = $request->brandId;
        $modelId = $request->modelId;
        $versionId = $request->versionId;
        $cateId = $request->cateId;
        $subCateId = $request->subCateId;
        $data = RequiredParts::select('id', 'brand_id', 'approxRate', 'subCat_id', 'image', 'user_id', 'urgentSell', 'postDate')
            ->where('version_id', '=', $versionId)->where('brandModel_id', '=', $modelId)->where('is_removed', '0')
            ->where('brand_id', '=', $brandId)
            ->where('cat_id', '=', $cateId)->where('subCat_id', '=', $subCateId)
            ->orderBy('created_at', 'ASC')
            ->with('Brand', 'BrandVersion', 'Cat', 'SubCat', 'BrandModel');
        $total = $data->count();
        $data = $data->get()->toArray();
        if ($data) {
            foreach ($data as $i => $da) {
                $user_id = user::where('id', '=', $data[$i]['user_id'])->first();
                $data[$i]['brand_name'] = $da['brand']['name'];
                $data[$i]['subCategory'] = $da['sub_cat']['name'];
                $data[$i]['city'] = $user_id->city;

                unset(
                    $data[$i]['is_removed'],
                    $data[$i]['is_blocked'],
                    $data[$i]['user_id'],
                    $data[$i]['brand_id'],
                    $data[$i]['brandModel_id'],
                    $data[$i]['version_id'],
                    $data[$i]['cat_id'],
                    $data[$i]['subCat_id'],
                    $data[$i]['brand'],
                    $data[$i]['brand_version'],
                    $data[$i]['cat'],
                    $data[$i]['sub_cat'],
                    $data[$i]['brand_model']
                );
            }
            $response = [
                "status" => true,
                "data" => $data,
                'total' => $total
            ];
        } else {
            $response = [
                "status" => false,
                "message" => 'No Data Found'
            ];
        }

        return response()->json($response);
    }

    /**
     * Display selected item's detail
     */

    public function getSelectedData(Request $request): JsonResponse
    {
        $id = $request->id;
        $data = partsSelling::where('id', '=', $id)
            ->with('Brand', 'BrandVersion', 'Cat', 'SubCat', 'BrandModel')
            ->first();
        if ($data) {
           $data= $data->toArray();
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
     * Urgently required parts
     */
    public function urgentRequiredPart(Request $request): JsonResponse
    {
        $data = partsSelling::select('brand_id', 'approxRate', 'subCat_id', 'image', 'user_id', 'postDate')
            ->where('is_removed', 0)
            ->orderBy('created_at', 'ASC')
            ->where('urgentSell', 1)
            ->with('Brand', 'BrandVersion', 'Cat', 'SubCat', 'BrandModel');
        $total = $data->count();
        $data = $data->get()->toArray();
        if ($data) {
            foreach ($data as $i => $da) {
                $user_id = user::where('id', '=', $data[$i]['user_id'])->first();
                // dd($user_id);
                $data[$i]['brand_name'] = $da['brand']['name'];
                $data[$i]['subCategory'] = $da['sub_cat']['name'];
                $data[$i]['city'] = $user_id->city;

                unset(
                    $data[$i]['is_removed'],
                    $data[$i]['is_blocked'],
                    $data[$i]['user_id'],
                    $data[$i]['brand_id'],
                    $data[$i]['brandModel_id'],
                    $data[$i]['version_id'],
                    $data[$i]['cat_id'],
                    $data[$i]['subCat_id'],
                    $data[$i]['brand'],
                    $data[$i]['brand_version'],
                    $data[$i]['cat'],
                    $data[$i]['sub_cat'],
                    $data[$i]['brand_model']
                );
            }
            if ($request->start != '' && $request->pageSize != '') {
                $data->skip($request->start)->take($request->pageSize);
            }
            return response()->json(['data' => $data, 'status' => true, 'total' => $total]);
        } else {
            return response()->json(['message' => 'No Match Found', 'status' => false]);
        }

    }
}
