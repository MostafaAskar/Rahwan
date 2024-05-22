<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use PayMob\Facades\PayMob;

class PayMobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // public static function pay(float $total_amount , int $Booking_id)
    // {

    //     $auth = PayMob::AuthenticationRequest();
    //     $Booking = PayMob::OrderRegistrationAPI([
    //         'auth_token' => $auth->token,
    //         'amount_cents' => $total_amount * 100, //put your price
    //         'currency' => 'EGP',
    //         'delivery_needed' => false, // another option true
    //         'merchant_Booking_id' => $Booking_id, //put Booking id from your database must be unique id
    //         'items' => [] // all items information or leave it empty
    //     ]);
    //     // dd($auth);
    //     // dd($Booking);

    //     $PaymentKey = PayMob::PaymentKeyRequest([
    //         'auth_token' => $auth->token,
    //         'amount_cents' => $total_amount * 100, //put your price
    //         'currency' => 'EGP',
    //         'Booking_id' => $Booking_id,
    //         "billing_data" => [ // put your client information
    //             "apartment" => "803",
    //             "email" => "claudette09@exa.com",
    //             "floor" => "42",
    //             "first_name" => "Clifford",
    //             "street" => "Ethan Land",
    //             "building" => "8028",
    //             "phone_number" => "+86(8)9135210487",
    //             "shipping_method" => "PKG",
    //             "postal_code" => "01898",
    //             "city" => "Jaskolskiburgh",
    //             "country" => "CR",
    //             "last_name" => "Nicolas",
    //             "state" => "Utah"
    //         ]
    //     ]);

    //     return $PaymentKey->token;


    // }

    // public function checkout_processed(Request $request){
    //     $request_hmac = $request->hmac;
    //     $calc_hmac = PayMob::calcHMAC($request);

    // if ($request_hmac == $calc_hmac) {
    //     $Booking_id = $request->obj['Booking']['merchant_order_id'];
    //     $amount_cents = $request->obj['amount_cents'];
    //     $transaction_id = $request->obj['id'];

    //     $Booking = Booking::find($Booking_id);

    //     if ($request->obj['success'] == true && ($Booking->total_amount * 100) == $amount_cents) {
    //         $Booking->update([
    //             'transaction_status' => 'finished',
    //             'transaction_id' => $transaction_id
    //         ]);
    //     } else {
    //         $Booking->update([
    //             'transaction_status' => "failed",
    //             'transaction_id' => $transaction_id
    //         ]);
    //     }
    // }
    // }


    public function credit(Request $request) {
        //this fucntion that send all below function data to paymob and use it for routes;


        // dd($request);
        $tokens = $this->getToken();
        // $order = $this->createOrder($tokens ,$request->id);
        $order = $this->createOrder($tokens);

        $paymentToken = $this->getPaymentToken($order, $tokens);
        // return view('PayMob.paymob.ifram', ['paymentToken' => $paymentToken]);
        
        return Redirect::away('https://accept.paymob.com/api/acceptance/iframes/'.env('PAYMOB_IFRAME_ID').'?payment_token='.$paymentToken);
    }

    public function getToken() {
        //this function takes api key from env.file and get token from paymob accept


        $response = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => env('PAYMOB_API_KEY')
        ]);
        return $response->object()->token;
        
    }

    public function createOrder($tokens ) {
        //this function takes last step token and send new order to paymob dashboard

        // $bookingDetails = Booking::findOrFail($id);
        // dd($bookingDetails->total_amount );
       
        // $amount = new Checkoutshow; here you add your checkout controller
        // $total = $amount->totalProductAmount(); total amount function from checkout controller
        //here we add example for test only
        
        
        // $total = $bookingDetails->total_amount ;
        $total = 100 ;
        $items = [
            [ "name"=> "ASC1515",
                "amount_cents"=> "500000",
                "description"=> "Smart Watch",
                "quantity"=> "1"
            ],
            [
                "name"=> "ERT6565",
                "amount_cents"=> "200000",
                "description"=> "Power Bank",
                "quantity"=> "1"
            ]
        ];

        $data = [
            "auth_token" =>   $tokens,
            "delivery_needed" =>"false",
            "amount_cents"=> $total*100,
            "currency"=> "EGP",
            "items"=> $items,

        ];
        $response = Http::post('https://accept.paymob.com/api/ecommerce/orders', $data);
        return $response->object();
    }

    public function getPaymentToken($order, $token)
    {
        //this function to add details to paymob order dashboard and you can fill this data from your Model Class as below


        // $amountt = new Checkoutshow;
        // $totall = $amountt->totalProductAmount();
        // $todayDate = Carbon::now();
        // $dataa = Order::where('user_id',Auth::user()->id)->whereDate('created_at',$todayDate)->orderBy('created_at','desc')->first();

        //we just added dummy data for test
        //all data we fill is required for paymob
        $billingData = [
            "apartment" => '45', //example $dataa->appartment
            "email" => "Mostafa.askar.dev@gmai.com", //example $dataa->email
            "floor" => '5',
            "first_name" => 'Mostafa',
            "street" => "NA",
            "building" => "NA",
            "phone_number" => '0123456789',
            "shipping_method" => "NA",
            "postal_code" => "NA",
            "city" => "cairo",
            "country" => "NA",
            "last_name" => "Askar",
            "state" => "NA"
        ];
        $data = [
            "auth_token" => $token,
            "amount_cents" => 100*100,
            "expiration" => 3600,
            "order_id" => $order->id, // this order id created by paymob
            "billing_data" => $billingData,
            "currency" => "EGP",
            "integration_id" => env('PAYMOB_INTEGRATION_ID'),
            "lock_order_when_paid" => "false",
        ];
        $response = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', $data);
        // dd( $response->object()->token);
        return $response->object()->token;
    }


    public function callback(Request $request)
    {
        //this call back function its return the data from paymob and we show the full response and we checked if hmac is correct means successfull payment

        $data = $request->all();
        ksort($data);
        $hmac = $data['hmac'];
        // dd($data);
        $array = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];
        $connectedString = '';
        foreach ($data as $key => $element) {
            if(in_array($key, $array)) {
                $connectedString .= $element;
            }
        }
        $secret = env('PAYMOB_HMAC');
        // dd( $secret );
        $hased = hash_hmac('sha512', $connectedString, $secret);
        // dd($hased == $hmac);
        if ( $hased == $hmac) {
            //this below data used to get the last order created by the customer and check if its exists to 
            // $todayDate = Carbon::now();
            // $datas = Order::where('user_id',Auth::user()->id)->whereDate('created_at',$todayDate)->orderBy('created_at','desc')->first();
            $status = $data['success'];
            // $pending = $data['pending'];

            if ( $status == "true" ) {

                //here we checked that the success payment is true and we updated the data base and empty the cart and redirct the customer to thankyou page

                // Cart::where('user_id',auth()->user()->id)->delete();
                // $datas->update([
                //     'payment_id' => $data['id'],
                //     'payment_status' => "Compeleted"
                // ]);
                // try {
                //     $order = Order::find($datas->id);
                //     Mail::to('maherfared@gmail.com')->send(new PlaceOrderMailable($order));
                // }catch(\Exception $e){
        
                // }
                // dd("hellllllollllooooooo");
                return redirect('/');
                
            }
            else {
                // $datas->update([
                //     'payment_id' => $data['id'],
                //     'payment_status' => "Failed"
                // ]);

                return response("error from check" );

                // return redirect('/checkout')->with('message', 'Something Went Wrong Please Try Again');

            }
            
        }else {
            return redirect('/checkout')->with('message', 'Something Went Wrong Please Try Again');
        }
        
    }
    
    public function index()
    {
    
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
