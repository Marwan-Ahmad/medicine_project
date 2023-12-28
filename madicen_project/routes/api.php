<?php

use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\StorehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


/*
there is the api of the pharmacy auth
*/

Route::post('/register', [PharmacyController::class, 'register']);
Route::post('/login', [PharmacyController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [PharmacyController::class, 'logout']);
    // route medicines for category
    Route::post('medicines', [PharmacyController::class, 'showMedicineViaCategory']);

    //route for return just category
    Route::get('category', [PharmacyController::class, 'showcategory']);

    //route of search
    Route::post('search', [PharmacyController::class, 'search']);

    //reoute for find the medicin info
    Route::post('medicininfo', [PharmacyController::class, 'getmedicineinfo']);
    //route for create order
    Route::post('order', [PharmacyController::class, 'order']);
    //This route for show orders you are create it
    Route::get('showorder', [PharmacyController::class, 'showorder']);
    Route::post('favorite', [PharmacyController::class, 'favorite']);
    Route::get('getfav', [PharmacyController::class, 'getfav']);
});


//****************************************************************************************************************************************** */

// this is storehouse api
Route::post('/login1', [StorehouseController::class, 'login1']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout1', [StorehouseController::class, 'logout1']);
    // this route for add medicine
    Route::post('store', [StorehouseController::class, 'storemedicines']);

    //this route is search on store house
    Route::post('searchstorehouse', [StorehouseController::class, 'searchstorehouse']);
    //reoute for find the medicin info
    Route::post('medicininfostorehouse', [StorehouseController::class, 'getmedicineinfostorehouse']);
    Route::post('UpdateOrderStatus/{id}', [StorehouseController::class, 'UpdateOrderStatus']);
    Route::post('report', [StorehouseController::class, 'report']);
    Route::get('allorder', [StorehouseController::class, 'allorder']);
});
