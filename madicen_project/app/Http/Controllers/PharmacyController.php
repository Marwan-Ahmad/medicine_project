<?php

namespace App\Http\Controllers;

use App\Models\cart;
use App\Models\cart_medicins;
use App\Models\favorite;
use App\Models\pharmacy;
use App\Models\StoreHouseMedicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class PharmacyController extends Controller
{
    public function register(Request $request)
    {

        $request->validate([
            'firstname' => ['required'],
            'lastname' => ['required'],
            'phone' => ['required', 'unique:pharmacies'],
            'address' => ['required'],
            'password' => ['required']
        ]);

        $user = pharmacy::query()->create([
            'firstname' => $request['firstname'],
            'lastname' => $request['lastname'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'password' => $request['password']
        ]);
        $token = $user->createToken('Api Token')->plainTextToken;
        $data = [];
        $data['user'] = $user;
        $data['token'] = $token;
        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => 'You Created a New Account'
        ]);
    }


    public function login(Request $request)
    {
        $request->validate([
            "phone" => ["required", 'digits:10', 'exists:pharmacies,phone'],
            "password" => "required"
        ]);


        $info = pharmacy::where('phone', $request->phone)->first();
        if (isset($info->id)) {
            if (Hash::check($request->password, $info->password)) {
                $token = $info->createToken('Api Token')->plainTextToken;
                $data = [];
                $data['info'] = $info;
                $data['token'] = $token;
                return response()->json([
                    'status' => 1,
                    'data' => $data,
                    'message' => 'You Logging in Successfuly'
                ]);
            } else {
                return response()->json(['message' => 'the Password is incorrect'], 200);
            }
        } else {
            $massege = 'The Number of the phone Is Not Registerd';
            return response()->json([
                'data' => [],
                'status' => 0,
                'massage' => $massege
            ], 500);
        }
    }



    public function logout()
    {
        $userpharmacy = Auth::user();
        $userpharmacy->tokens()->delete();
        return response()->json([
            'status' => 1,
            'data' => [],
            'message' => 'Successfully logged out'
        ], 200);
    }
    public function showMedicineViaCategory(Request $request)
    {
        $medicine = DB::table('store_house_medicines')->where('category', $request->category)->select('commercialname', 'scientificname', 'company', 'category', 'price', 'expirationdate')->get();

        return response()->json([
            'massage' => $medicine
        ]);
    }

    public function showcategory()
    {
        $categories = DB::table('store_house_medicines')->select('category')->distinct()->get();
        return response()->json([
            'message' => $categories
        ]);
    }



    public function search(Request $request)
    {
        $searchTerm = $request->search_term;

        if (is_null($searchTerm)) {
            return response()->json([
                'massege' => "You didn't enter anything in the search",
                'data' => []
            ]);
        } else {
            $medicines = StoreHouseMedicine::where(function ($query) use ($searchTerm) {
                $query->where('commercialname', 'like', "%$searchTerm%")
                    ->orWhere(function ($query) use ($searchTerm) {
                        $query->where('category', 'like', "%$searchTerm%");
                    });
            })
                ->select('commercialname', 'category')
                ->get();

            if ($medicines->isEmpty()) {
                return response()->json([
                    'message' => 'No matching medicine found in the warehouse',
                    'data' => [],

                ]);
            } else {
                return response()->json([
                    'data' => $medicines
                ]);
            }
        }
    }


    public function getmedicineinfo(Request $request)
    {
        $medicininfo = StoreHouseMedicine::select('scientificname', 'commercialname', 'category', 'company', 'expirationdate', 'price')->find($request->id);
        if (is_null($medicininfo)) {
            return response()->json([
                'massege' => 'Medicine Not Have Information To Found It',
                'data' => []
            ]);
        } else {
            return response()->json([
                'massege' => 'The Medicine Information Is Founded',
                'data' => $medicininfo
            ]);
        }
    }

    public function order(Request $request)
    {
        $user_id = auth()->user()->id;
        if (!$user_id) {
            return response()->json([
                'massage' => 'You Are Not Logging In'
            ]);
        }
        $medicin = [];
        $cart = cart::query()->create([
            'status' => 'prepering',
            'paymentStatus' => 'notPayed',
            'pharmesist_id' => $user_id
        ]);

        foreach ($request->cart_medicins as $items) {
            $medicine = StoreHouseMedicine::select('id', 'scientificname', 'commercialname', 'category', 'company', 'expirationdate', 'price')->find($items['medicin_id']);
            if ($medicine) {
                $cart->medicins()->attach($medicine->id, ['quantity' => $items['quantity']]);

                $medicin[] = [
                    'Number'  =>  $medicine->id,
                    'scientificname' => $medicine->scientificname,
                    'commercialname' => $medicine->commercialname,
                    'category' => $medicine->category,
                    'company' => $medicine->company,
                    'Quantity' => $items['quantity'],
                    'expirationdate' => $medicine->expirationdate,
                    'price' => $medicine->price,

                ];
            }
        }
        return response()->json([
            'massage' => 'The Order Add Successfuly',
            'data' => [
                'Info' =>  $medicin,

            ]
        ]);
    }

    public function showorder()
    {
        $user_id = auth()->user()->id;
        $user_name = Auth::user()->firstname;
        $pharmaciest = pharmacy::find($user_id);
        $orders = $pharmaciest->orders()->orderby('created_at', 'DESC')->get()->all();
        $ifempty = cart::where('pharmesist_id', $user_id)->first();
        if (empty($ifempty)) {
            return response()->json([
                'massage' => "You Don't Have Any Orders To Show It",
                'massages' => 'Go Add Order If You Want To Show It Here'

            ]);
        } else {
            return response()->json([
                'massage' => 'This Is Your Orders',
                'data' => $orders
            ]);
        }
    }

    public function favorite(Request $request)
    {

        $user_id = auth()->user()->id;
        $medicine_id = $request->med_id;

        $storemed = StoreHouseMedicine::find($medicine_id);
        $found = favorite::where('pharmesist_id', $user_id)->where('medicin_id', $medicine_id)->first();

        if ($found) {
            return response()->json([
                'Error' => 'This Medicins Is In Fav Already'
            ]);
        }

        if ($medicine_id < 0 || $medicine_id == 0 || empty($medicine_id) || !$storemed) {
            return response()->json([
                'Error' => 'Please Try Again'
            ]);
        }


        $fav = favorite::create([
            'name' => $storemed->commercialname,
            'pharmesist_id' => $user_id,
            'medicin_id' => $medicine_id
        ]);

        return response()->json([
            'massage' => 'This Medicins is Fav',
            'data' => $fav
        ]);
    }

    public function getfav()
    {
        $user_id = auth()->user()->id;
        $returnall = favorite::where('pharmesist_id', $user_id)->get()->all();

        if (empty($returnall)) {
            return response()->json([
                'massage' => 'The FavList Is Empty',
            ]);
        } else {

            return response()->json([
                'massage' => 'This Is Your FavList',
                'data' => $returnall
            ]);
        }
    }
}
