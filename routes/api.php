<?php

use Illuminate\Support\Facades\Route;


// TODO Urgent Part Sell now in part selling
// TODO Normal Part Sell in Required Part

// Buy Car Parts , List Of urgently required part list and search,

Route::group(['middleware' => ['auth:api'], 'namespace' => 'App\Http\Controllers'], function () {
    Route::get('logout', 'UserController@logout');
    Route::get('adminDetail', 'UserController@adminDetail');
    Route::get('user/chart', 'UserController@chart');
    Route::get('user/dashboard', 'UserController@dashboard');
    Route::get('user/get_profile', 'UserController@get_profile');
    Route::resource('user', 'UserController');
    Route::prefix('user')->group(function () {
        Route::post('update_profile', 'UserController@update_profile');
        Route::post('changePassword', 'UserController@change_password');
        Route::get('get_users/{id}', 'UserController@show');
        Route::post('getUsers', 'UserController@getUsers');
        Route::post('changeUserStatus', 'UserController@changeUserStatus');
        Route::post('updateUserProduct', 'UserController@updateUserProduct');
    });
    //     For Brand
    Route::post('upload', 'BrandController@upload');
    Route::resource('brand', 'BrandController');
    Route::prefix('brand')->group(function () {
        Route::post('getBrand', 'BrandController@getBrand');
        Route::post('changeBrandStatus', 'BrandController@changeBrandStatus');
    });
    //    For Brand Model
    Route::resource('brandModel', 'BrandModelController');
    Route::prefix('brandModel')->group(function () {
        Route::post('getBrandModel', 'BrandModelController@getBrandModel');
    });
    //    For Brand Version
    Route::resource('brandVersion', 'BrandVersionController');
    Route::prefix('brandVersion')->group(function () {
        Route::post('getBrandVersion', 'BrandVersionController@getBrandVersion');
    });
    //     For Category
    Route::resource('cat', 'CategoryController');
    Route::prefix('cat')->group(function () {
        Route::post('getCat', 'CategoryController@getCat');
    });

    //     For Sub Category
    Route::resource('subCat', 'SubCategoryController');
    Route::prefix('subCat')->group(function () {
        Route::post('getSubCat', 'SubCategoryController@getSubCat');
    });

    //     For Parts Selling
    Route::resource('partsSelling', 'PartsSellingController');
    Route::prefix('partsSelling')->group(function () {
        Route::post('getPartsSelling', 'PartsSellingController@getPartsSelling');
        Route::post('upload', 'PartsSellingController@upload');
        Route::post('search', 'PartsSellingController@search');
        Route::post('userUrgentPartsList', 'PartsSellingController@userUrgentPartsList');
    });
    Route::post('searchUserProfile', 'PartsSellingController@searchUserProfile');
/// For Required Parts
    Route::resource('requiredParts', 'RequiredPartsController');
    Route::prefix('requiredParts')->group(function () {
        Route::post('userNormalPartsList', 'RequiredPartsController@userNormalPartsList');
    });

    Route::post('deleteAccount', 'UserController@deleteAccount');
});

Route::group(['middleware' => ['api'], 'namespace' => 'App\Http\Controllers'], function () {

    Route::post('updateProfile', 'UserController@updateProfile');
    Route::post('loginOTP', 'UserController@loginOTP');
    Route::post('verifyOTP', 'UserController@verifyOTP');
    Route::post('resendOTP', 'UserController@resendOTP');
    Route::post('resendUserOTP', 'UserController@resendUserOTP');
    Route::post('forgot', 'UserController@forgot');
    Route::post('tokenVerification', 'UserController@tokenVerification');
    Route::post('reset', 'UserController@reset');

    //  Get Brand List , Brand Model , Brand Version For Application
    Route::post('login', 'UserController@login');
    Route::post('verifyOtpUser', 'UserController@verifyOtpUser');
    Route::post('getBrandForApp', 'BrandController@getBrandForApp');
    Route::post('getBrandModelApp', 'BrandModelController@getBrandModelApp');
    Route::post('getBrandVersionApp', 'BrandVersionController@getBrandVersionApp');
    Route::post('getCatApp', 'CategoryController@getCatApp');
    Route::post('getSubCatApp', 'SubCategoryController@getSubCatApp');

    // buy parts
    Route::resource('buy', 'BuyPartsController');
    Route::prefix('buy')->group(function () {
        Route::post('searchList', 'BuyPartsController@searchList');
        Route::post('getSelectedData', 'BuyPartsController@getSelectedData');
        Route::post('urgentRequiredPart', 'BuyPartsController@urgentRequiredPart');


    });
    Route::prefix('requiredParts')->group(function () {
        Route::get('showProduct/{id}', 'PartsSellingController@showProduct');
        Route::post('getListUrgent', 'PartsSellingController@getListUrgent');
        Route::post('getList', 'RequiredPartsController@getList');
        Route::post('getListSearch', 'RequiredPartsController@getListSearch');
    });
    Route::prefix('partsSelling')->group(function () {
        Route::post('getData', 'PartsSellingController@getData');
    });



});
