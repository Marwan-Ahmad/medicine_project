<?php

namespace App\Http\Controllers;

use App\Models\cart;
use App\Models\cart_medicins;
use App\Models\category;
use App\Models\storehouse;
use App\Models\StoreHouseMedicine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StorehouseController extends Controller
{
    public function login1(Request $request)
    {
        $request->validate([
            "phone" => ["required", 'digits:10'],
            "password" => "required"
        ]);
        $user = new storehouse();

        $info = storehouse::where('phone', $request->phone)->first();
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

    public function logout1()
    {
        $userpharmacy = Auth::user();
        $userpharmacy->tokens()->delete();
        return response()->json([
            'status' => 1,
            'data' => [],
            'message' => 'Successfully logged out'
        ], 200);
    }


    public function storemedicines(Request $request)
    {
        $validate = $request->validate([
            'scientificname' => 'required',
            'commercialname' => ['required'],
            'category' => 'required',
            'company' => 'required',
            'quntity' => 'required',
            'expirationdate' => 'required',
            'price' => 'required'
        ]);

        $foundmedicin = StoreHouseMedicine::where('scientificname', $request->scientificname)->where('commercialname', $request->commercialname)->where('category', $request->category)->where('company', $request->company)->first();
        if ($foundmedicin) {
            $foundmedicin->quntity = $request->quntity + $foundmedicin->quntity;
            $foundmedicin->price = $request->price;
            $foundmedicin->save();
            return response()->json([
                'massage' => 'The Medicine You Try To Add IS Already Exists so The quantity will be change',
                'data' => [
                    'name' => $foundmedicin->commercialname,
                    'quantity' => $foundmedicin->quntity
                ]
            ]);
        } else {

            $store = StoreHouseMedicine::query()->create([
                'scientificname' => $request->scientificname,
                'commercialname' =>  $request->commercialname,
                'category' =>  $request->category,
                'company' =>  $request->company,
                'quntity' =>  $request->quntity,
                'expirationdate' =>  Carbon::parse($request->expirationdate)->format('Y-m-d'),
                'price' =>  $request->price,

            ]);
            return response()->json([
                'massage' => 'The Medicine has been added successfuly',
                'data' => $store
            ]);
        }
    }

    public function searchstorehouse(Request $request)
    {
        $searchTerm = $request->search_term;

        if (is_null($searchTerm)) {
            return response()->json([
                'massege' => "You Don't Insert any Thing In The Search",
                'data' => []
            ], 201);
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
            }

            return response()->json([
                'data' => $medicines
            ]);
        }
    }


    public function getmedicineinfostorehouse(Request $request)
    {
        $medicensinfo = StoreHouseMedicine::find($request->id);
        if (is_null($medicensinfo)) {
            return response()->json([
                'massage' => 'Medicine Not Have Information To Found It'
            ]);
        } else {
            return response()->json([
                'massage' => 'The Medicine Information Is Founded',
                'data' => $medicensinfo
            ]);
        }
    }
    public function UpdateOrderStatus(Request $request, $id)
    {


        DB::beginTransaction(); /// بدء العملية

        $record = cart::find($id);
        //return $record->medicins;

        if (!$record) {
            return response()->json([
                'error' => 'The Order You Try To Update Is Not Found'
            ], 404);
        }

        if (is_null($request->status) || is_null($request->paymentStatus)) {
            return response()->json([
                'massage' => 'Enter Update Order'
            ]);
        }

        $record->status = $request->input('status');
        $record->paymentStatus = $request->input('paymentStatus');
        $record->save();

        if ($request->input('status') == 'Reserved') {
            $order_medicines = $record->medicins;

            foreach ($order_medicines as $medicine) {
                $new_quantity = $medicine->pivot->quantity;

                $storehouse_medicine = StoreHouseMedicine::find($medicine->id);

                $available_quantity = $storehouse_medicine->quntity;

                if ($new_quantity > $available_quantity) {
                    DB::rollBack(); /// التراجع عن التعديلات
                    return response()->json([
                        'error' => 'One medication is not enough in the warehouse'
                    ], 500);
                }

                $storehouse_medicine->quntity -= $new_quantity;
                $storehouse_medicine->save();
            }
        }

        DB::commit(); /// حفظ التغييرات

        return response()->json([
            'message' => 'Update Order Done Successfully',
            'data' => $record
        ]);

        DB::rollBack(); /// التراجع عن التعديلات
        return response()->json([
            'message' => 'Failed To Update The Order. Please Try Again'
        ], 500);
    }


    // public function allorder()
    // {
    //     // $allorder = cart::all();

    //     $user_id = auth()->user()->id;
    //     $quantityorder = cart::find($user_id);
    //     $new_quantity = $quantityorder->medicins;
    //     foreach ($new_quantity as $quan) {
    //         $order = $quan->pivot->quantity;
    //     }


    //     return response()->json([
    //         'quantity' => $order
    //     ]);
    // }

    public function allorder()
    {
        $carts = cart::all();
        $orders = [];

        foreach ($carts as $cart) {
            $cartMedicins = cart_medicins::where('cart_id', $cart->id)->get();
            $medicin = [];

            foreach ($cartMedicins as $cartMedicin) {
                $medicine = StoreHouseMedicine::select('id', 'commercialname')->find($cartMedicin->medicin_id);

                if ($medicine) {
                    $medicin[] = [
                        'medicine' => $medicine,
                        'quantity' => $cartMedicin->quantity
                    ];
                }
            }

            $orders[] = [
                'cart_id' => $cart->id,
                'payedStatus' => $cart->paymentStatus,
                'status' => $cart->status,
                'medicines' => $medicin,

            ];
        }

        return response()->json([
            'message' => 'All Orders',
            'data' => $orders
        ]);
    }

    public function report(Request $request)
    {
        $startdate = $request->startdate;
        $enddate = $request->enddate;

        //one Report
        $sales_quantity = cart_medicins::whereBetween('created_at', [$startdate, $enddate])->sum('quantity');
        //second Report
        $num_order = cart::whereBetween('created_at', [$startdate, $enddate])->count();
        //Thired Report
        $reserved = cart::whereBetween('created_at', [$startdate, $enddate])->where('status', 'Reserved')->count();
        //Four Report
        $maxorder = cart_medicins::whereBetween('created_at', [$startdate, $enddate])->max('quantity');

        if (!$sales_quantity || !$num_order || !$reserved || !$maxorder) {
            return response()->json([
                'massage' => 'In This Time You Insert It is Empty Value'
            ]);
        }
        return response()->json([
            'Report One' => [
                'massage' => 'This Is Your Report About SalesQuantity ',
                'Totlal_quantity' => $sales_quantity,
            ],
            'Report Two' => [
                'massage' => 'This Is Your Report About Number Orders ',
                'Number (Orders)' => $num_order,
            ],
            'Report three' => [
                'massage' => 'This Is Your Report About Number Orders Reserved',
                'Number (Orders Reserved)' => $reserved,
            ],
            'Report Four' => [
                'massage' => 'This Is Your Report About Max Order Quantity',
                'Max (Order Quantity)' => $maxorder
            ]
        ]);
    }
}
