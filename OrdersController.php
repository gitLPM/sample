<?php

namespace App\Http\Controllers\Web;

//use Mail;

//validator is builtin class in laravel

//for password encryption or hash protected

//for authenitcate login data

use App\Http\Traits\GlobalTrait;
use App\Models\Core\Footerlink;

//for requesting a value

use App\Models\Core\Order as CoreOrder;
use App\Models\Web\Cart;
use App\Models\Web\Currency;
use App\Models\Web\Index;
use App\Models\Web\Languages;
use App\Models\Web\Order;

//for Carbon a value

use App\Models\Web\Products;
use App\Models\Web\Shipping;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lang;

//email
use Omnipay\Omnipay;
use Session;
use Srmklive\PayPal\Services\ExpressCheckout;

class OrdersController extends Controller
{
    use GlobalTrait;

    public $gateway;

    public function __construct() {}

    //test stripe

    public function stripeForm(Request $request)
    {
            $title = array('pageTitle' => Lang::get('website.Checkout'));

            $result = array();

            $result['fetch_footer_link'] = $this->footerlink->footer_link();

            $result['commonContent'] = $this->index->commonContent();

            return view("stripeForm", $title)->with('result', $result);
    }

    public function guest_checkout()
    {
        session(['guest_checkout' => 1]);

        return redirect('/checkout');
    }

    //checkout

    public function checkout(Request $request)
    {
        $currency_symbol = session('symbol_left') ? session('symbol_left') : session('symbol_right');

        $currency = DB::table('currencies')->where('symbol_left', $currency_symbol)->orwhere('symbol_right', $currency_symbol)->first();

        $title = array('pageTitle' => Lang::get('website.Checkout'));

        // $final_theme = $this->theme->theme();

        $result = array();

        $applied_coupons = array();

        //cart data

        $result['cart'] = $this->cart->myCart($result);
        $result['currency_value'] = $currency ? $currency->value : 1;
        session(['banktransfer' => '']);

        if (count($result['cart']) == 0) {

            return redirect("/");

        } else {

            //apply coupon

            if (!empty(session('coupon')) and count(session('coupon')) > 0) {

                if (!empty(session('coupon'))) {

                    foreach (session('coupon') as $key => $session_coupon) {

                        $applied_coupons[] = $session_coupon->code;
                    }

                }

                if (Session::get('guest_checkout') == 1 && session('shipping_address')) {

                    $email = session('shipping_address')->email;

                    $check = DB::table('users')->where('role_id', 2)->where('email', $email)->first();

                    if ($check == null) {

                        $customers_id = DB::table('users')

                            ->insertGetId([
                                'role_id' => 2,
                                'email' => $email = session('shipping_address')->email,
                                'password' => Hash::make('123456dfdfdf'),
                                'first_name' => session('shipping_address')->firstname,
                                'last_name' => session('shipping_address')->lastname,
                                'phone' => session('billing_address')->billing_phone,
                            ]);

                        session(['customers_id' => $customers_id]);

                    } else {

                        $customers_id = $check->id;
                        session(['customers_id' => $customers_id]);
                    }

                } else {
                    $customers_id = auth()->guard('customer')->user() ? auth()->guard('customer')->user()->id : "";
                }

                if (!empty($customers_id)) {

                    $usedcouponcount = 0;

                    $count_of_coupon_used = DB::table('orders')->select('coupon_code')->where('coupon_code', '!=', "")->where('customers_id', $customers_id)->get();

                    foreach ($count_of_coupon_used as $couponcount) {

                        $coupon_array = json_decode($couponcount->coupon_code);

                        foreach ($coupon_array as $coupon_array_item) {
                            if (in_array($coupon_array_item->code, $applied_coupons)) {
                                $usedcouponcount++;
                            }
                        }

                    }

                    $coupons = DB::table('coupons')->whereIn('code', $applied_coupons)->first();

                    // dd($coupons,$applied_coupons);

                    if ($coupons) {

                        if ($coupons->usage_limit_per_user == $usedcouponcount) {

                            session(['coupon_usage_per_user_limit' => 1]);
                            session(['coupon' => array()]);
                            session(['coupon_discount' => '']);
                            return redirect('viewcart');
                        }
                    }
                }

                $session_coupon_data = session('coupon');

                session(['coupon' => array()]);

                $response = array();

                if (!empty($session_coupon_data)) {

                    foreach ($session_coupon_data as $key => $session_coupon) {

                        $response = $this->cart->common_apply_coupon($session_coupon->code);
                        $applied_coupons[] = $session_coupon->code;

                    }
                }
            }

            $result['fetch_footer_link'] = $this->footerlink->footer_link();
            $result['commonContent'] = $this->index->commonContent();
            $InventoryStatus = isset($result['commonContent']) ? $result['commonContent']['settings']['Inventory'] : '0';
            $address = array();

            if (empty(session('step'))) {
                session(['step' => '0']);
                session(['get_billingaddress' => '0']);
            }

            if (auth()->guard('customer')->check()) {

                $all_addresses = $this->shipping->getShippingAddress(array());

                if (!empty($all_addresses) and count($all_addresses) > 0) {

                    foreach ($all_addresses as $default_address) {

                        if ($default_address->default_address == 1) {

                            $default_address->delivery_phone = auth()->guard('customer')->user()->phone;

                            $address = $default_address;

                        }
                    }
                }
            }

            $Billingaddress = array();
            $billing_data = array();
            $billing_details = array();
            
        if(session('get_billingaddress')==0){
            if (auth()->guard('customer')->check()) {

                $all_addressess = $this->shipping->getBillingAddress(array());

                if (!empty($all_addressess) and count($all_addressess) > 0) {

                    foreach ($all_addressess as $default_addressddd) {

                        if ($default_addressddd->default_address == 1) {

                            // dd( $default_addressddd);

                            $default_addressddd->delivery_phone = auth()->guard('customer')->user()->phone;

                            $Billingaddress = $default_addressddd;

                            $billing_data['billing_firstname'] = $Billingaddress->firstname;
                            $billing_data['billing_lastname'] = $Billingaddress->lastname;
                            $billing_data['billing_company'] = $Billingaddress->company;
                            $billing_data['billing_phone'] = $Billingaddress->delivery_phone;
                            $billing_data['billing_street'] = $Billingaddress->street;
                            $billing_data['billing_countries_id'] = $Billingaddress->countries_id;
                            $billing_data['billing_zone_id'] = $Billingaddress->zone_id;
                            $billing_data['billing_city'] = $Billingaddress->city;
                            $billing_data['billing_zip'] = $Billingaddress->postcode;

                            
                            $billing_details['billing_firstname'] = $Billingaddress->firstname;                             
                            $billing_details['billing_lastname']  = $Billingaddress->lastname;
                            $billing_details['billing_company']   = $Billingaddress->company;
                            $billing_details['billing_phone']     = $Billingaddress->delivery_phone;
                            $billing_details['billing_street']    =  $Billingaddress->street;
                            $billing_details['billing_countries_id'] = $Billingaddress->countries_id;
                            $billing_details['billing_zone_id']   = $Billingaddress->zone_id;
                            $billing_details['billing_city']      = $Billingaddress->city;
                            $billing_details['billing_zip']       = $Billingaddress->postcode;
                            $billing_details['same_billing_address'] = '1';
                            session(['billing_details' => $billing_details]);   

                        }
                    }

                }
            }
        }

            // if($Billingaddress){
            // dd( $billing_data);
            // }

            if (empty(session('billing_address'))) {

                session(['billing_address' => $billing_data]);
            }

            // dd( session('billing_address'));

            if (empty(session('shipping_address'))) {

                session(['shipping_address' => $address]);
            }

            // dd( session('shipping_address'));

            //shipping counties

            if (!empty(session('shipping_address')->countries_id)) {

                $countries_id = session('shipping_address')->countries_id;

            } else {
                $countries_id = '';
            }

            $result['countries'] = $this->shipping->countries();
            $result['zones'] = $this->shipping->zones($countries_id);

            //get tax

            if (!empty(session('shipping_address')->zone_id)) {

                $tax_zone_id = session('shipping_address')->zone_id;
                $tax = $this->calculateTax($tax_zone_id);
                session(['tax_rate' => $tax]);

            } else {

                session(['tax_rate' => '0']);

            }

            //ZST shipping methods

            $result['shipping_methods'] = $this->shipping_methods();

            //payment methods

            $result['payment_methods'] = $this->getPaymentMethods();

            //dd($result['cart']);
            //price

            $price = 0;

            if (count($result['cart']) > 0) {

                foreach ($result['cart'] as $products) {

                    $req = array();

                    $attr = array();

                    $req['products_id'] = $products->products_id;

                    if (isset($products->attributes)) {

                        foreach ($products->attributes as $key => $value) {

                            $attr[$key] = $value->products_attributes_id;

                        }

                        $req['attributes'] = $attr;

                    }

                    $check = $this->products->getquantity($req);

                    // dd($products->customers_basket_quantity." ".$products->max_order);

                    if ($InventoryStatus === '1') {

                        if ($products->customers_basket_quantity > $check['stock']) {

                            session(['out_of_stock' => 1]);
                            session(['out_of_stock_product' => $products->products_id]);
                            return redirect('viewcart');

                        } elseif ($products->customers_basket_quantity < $products->min_order) {

                            session(['min_order' => 1]);
                            session(['min_order_value' => $products->min_order]);
                            session(['min_order_product' => $products->products_id]);
                            return redirect('viewcart');

                        } elseif ($products->customers_basket_quantity > $products->max_order) {

                            session(['max_order' => 1]);
                            session(['max_order_value' => $products->max_order]);
                            session(['max_order_product' => $products->products_id]);
                            return redirect('viewcart');
                        } else {
                            session(['out_of_stock' => 0]);
                            session(['out_of_stock_product' => 0]);
                            session(['min_order' => 0]);
                            session(['min_order_value' => 0]);
                            session(['min_order_product' => 0]);
                            session(['max_order' => 0]);
                            session(['max_order_value' => 0]);
                            session(['max_order_product' => 0]);
                        }

                    } else {

                        if ($products->customers_basket_quantity < $products->min_order) {
                            session(['min_order' => 1]);
                            session(['min_order_value' => $products->min_order]);
                            session(['min_order_product' => $products->products_id]);
                            return redirect('viewcart');
                        } elseif ($products->customers_basket_quantity > $products->max_order) {

                            session(['max_order' => 1]);
                            session(['max_order_value' => $products->max_order]);
                            session(['max_order_product' => $products->products_id]);
                            return redirect('viewcart');

                        } else {
                            session(['out_of_stock' => 0]);
                            session(['out_of_stock_product' => 0]);
                            session(['min_order' => 0]);
                            session(['min_order_value' => 0]);
                            session(['min_order_product' => 0]);
                            session(['max_order' => 0]);
                            session(['max_order_value' => 0]);
                            session(['max_order_product' => 0]);
                            session(['min_order_price' => 0]);
                            session(['min_order_price_value' => '']);
                            session(['coupon_usage_per_user_limit' => 0]);
                        }

                    }

                    $price += $products->final_price * $products->customers_basket_quantity;

                }

                if ($price < $result['commonContent']['settings']['min_order_price']) {

                    session(['min_order_price' => 1]);
                    session(['min_order_price_value' => $result['commonContent']['settings']['min_order_price']]);
                    return redirect('viewcart');
                }

                session(['products_price' => $price]);
            }

            //breaintree token

            $token = $this->generateBraintreeTokenWeb();

            session(['braintree_token' => $token]);

            // $UserMember = DB::table('user_membership')->where('user_id',  $customer->id)->where('payment_status', 'Success')->where('end_date', '>=', $todayDate)->where('plan_status', '1')->first();

            // echo $UserMember;
            // exit;
            //      echo "sdsdsdf";

            //      echo "<pre>";
            //         // print_r($shippings);
            //      echo "</pre>";

            //         echo "===========";

            //     // echo "<pre>";
            //     // print_r($result['shipping_methods'][0]['services']);
            //     // echo "</pre>";
            //     // echo "<pre>";
            //     // print_r($result['shipping_methods']);
            //     // echo "</pre>";

            $total = 0;
            //echo count($result['cart']);
            //echo min($result['cart']->final_price);
            //echo $min = min(array_column($result['cart'], 'final_price'));
            //echo "<pre>";
            // print_r($result['cart'] );
            //echo "</pre>";

            $i = 0;
            foreach ($result['cart'] as $cart_product) {
                $total += $cart_product->final_price * $cart_product->customers_basket_quantity;

                $result['cart'][$i]->f_price = $cart_product->final_price * $cart_product->customers_basket_quantity;
                $i++;
                // echo $cart_product->products_name;
                // echo $cart_product->final_price;
                // echo "<br>";
            }
            echo "<pre>";
            //print_r($result['cart'] );
            echo "</pre>";

            //For Fetch Min f_price

            $min_price = min(array_column($result['cart'], 'f_price'));
            Session::put('min_price', 0);

            $c = count($result['cart']);
            $fetch_index = 0;

            for ($i = $c; $i >= 0; $i--) {
                if ($result['cart'][$i]->f_price == $min_price) {
                    $fetch_index = $i;
                    break;
                }
            }

            //  echo "<pre>";
            //    print_r($result['cart'] );
            //  echo "</pre>";
            // echo $key = array_search('14.00',array_column($result['cart'],'final_price'));
            $mytime = \Carbon\Carbon::now();
            $todayDate = $mytime->format("Y-m-d");
            $customer = auth()->guard('customer')->user();
            $customerid = $result['cart'][0]->customers_id;
            $UserMember = DB::table('user_membership')->where('user_id', $customer->id)->where('payment_status', 'Success')->where('end_date', '>=', $todayDate)->where('plan_status', '1')->first();

            //    echo count($UserMember);
            // echo "<pre>";
            //     print_r($UserMember);
            // echo "</pre>";
            // echo count($UserMember);
            Session::put('clubmember', 0);
            if (count($UserMember) == 0) {
                Session::put('clubmember', 0);
            } else {
                Session::put('clubmember', 1);
            }

            if (count($UserMember) == 0 && (count($result['cart']) >= 7)) {
                $result['cart'][$fetch_index]->f_price = 0;
                Session::put('min_price', $min_price);
                //$result['cart'][$fetch_index]->final_price=0;f_price

            }
            // exit;

            $particular_product = 0;

            foreach ($result['cart'] as $products) {
                $proudct_name = $products->products_name;
                // echo "<br>";

                if ($proudct_name == "Naja's Shea with Vitamin E Body Lotion") {
                    $particular_product = 1;
                } else if ($proudct_name == "Naja famous After Shower Mist") {
                    $particular_product = 1;
                } else if ($proudct_name == "Imported and private reserve Body Lotion") {
                    $particular_product = 1;
                }
            }

            // if( ($products->products_name == "Naja's Shea With Vitamin E Body Lotion")  ||
            //     ($products->products_name == "Imported And Private Reserve Body Lotion") ||
            //     ($products->products_name == "Naja Famous After Shower Mist") ||
            //     ($products->products_name == "Naja's Shea with Vitamin E Body Lotion")||
            //     ($products->products_name == "Naja famous After Shower Mist")
            //     )
            //  {
            //   $particular_product =1;
            //  }

            if (($total >= 75 || (count($UserMember) > 0 && $total >= 50)) && $particular_product == 0) {
                unset($result['shipping_methods'][1]);
                // Session::put('shipping_price', 0);
                Session::put('particular_products', 0);
            } else {
                unset($result['shipping_methods'][0]);
                Session::put('particular_products', 1);
            }

            //     echo $total;

            // echo "<pre>";
            // print_r($result);
            // echo "</pre>";
            //    print_r($result);

            //    echo $total;

            //    echo "<br>";
            //    echo $particular_product;
            //         exit;

            return view("web.checkout", ['title' => $title])->with('result', $result);
        }

    }

    //checkout

    public function checkout_shipping_address(Request $request)
    {
        if (session('step') == '1') {

            session(['step' => '2']);
        }

        if ($request->all()) {

            $shipping_data['firstname'] = $request->firstname;

            $shipping_data['lastname'] = $request->lastname;

            $shipping_data['billing_company'] = $request->billing_company;

            $shipping_data['company'] = $request->company;

            $shipping_data['street'] = $request->street;

            $shipping_data['countries_id'] = $request->countries_id;

            $shipping_data['zone_id'] = $request->zone_id;

            $shipping_data['city'] = $request->city;

            $shipping_data['postcode'] = $request->postcode;

            $shipping_data['delivery_phone'] = $request->delivery_phone;

        }

        $shipping_data = (object) $shipping_data;

        // $shipping_data->same_billing_address = 1;

        session(['shipping_address' => $shipping_data]);

        // dd($request->all());

        // if (empty($request->same_billing_address)) {

        //     // dd($request->all());

        //     foreach ($request->all() as $key => $value) {

        //         $shipping_data[$key] = $value;

        //     }

        //     $billing_address = (object) $shipping_data;

        //     $billing_address->same_billing_address = 0;

        //     session(['billing_address' => $shipping_data]);

        // } else {

        //     $shipping_data = (object) $shipping_data;;

        //     $shipping_data->same_billing_address = 1;

        //     session(['shipping_address' => $shipping_data]);

        // }

        // dd(session('shipping_address'));

        // dd(session('shipping_address'));

        return redirect()->back();
    }

    //checkout_billing_address

    public function checkout_billing_address(Request $request)
    {
            
        $title = array('pageTitle' => Lang::get('website.Checkout'));

        $result = array();
        $billing_details = array();
        $result['commonContent'] = $this->index->commonContent();

        if (session('step') == '0') {
            session(['step' => '1']);
        }

        if($request->all()){
            $billing_data['billing_firstname'] = $request->billing_firstname;
            $billing_data['billing_lastname'] = $request->billing_lastname;
            $billing_data['billing_company'] = $request->billing_company;
            $billing_data['billing_phone'] = $request->billing_phone;
            $billing_data['billing_street'] = $request->billing_street;
            $billing_data['billing_countries_id'] = $request->billing_countries_id;
            $billing_data['billing_zone_id'] = $request->billing_zone_id;
            $billing_data['billing_city'] = $request->billing_city;
            $billing_data['billing_zip'] = $request->billing_zip;
            
                       
            $billing_details['billing_firstname'] = $request->billing_firstname;
            $billing_details['billing_lastname']  = $request->billing_lastname;
            $billing_details['billing_company']   = $request->billing_company;
            $billing_details['billing_phone']     = $request->billing_phone;
            $billing_details['billing_street']    = $request->billing_street;
            $billing_details['billing_countries_id'] = $request->billing_countries_id;
            $billing_details['billing_zone_id']   = $request->billing_zone_id;
            $billing_details['billing_city']      = $request->billing_city;
            $billing_details['billing_zip']       = $request->billing_zip;
            $billing_details['same_billing_address'] = $request->same_billing_address;

            session(['billing_details' => $billing_details]);
            session(['get_billingaddress' => '1']);
            // echo session('billing_details')['billing_firstname'];
            // echo "<pre>";
            //     echo "Billing Details";
            //     print_r(session('billing_details'));
            // echo "</pre>";
            // exit;
        }

        if (!empty($request->same_billing_address)) {  

                $shipping_data['firstname'] = $request->billing_firstname;
                $shipping_data['lastname'] = $request->billing_lastname;
                $shipping_data['billing_company'] = $request->billing_company;
                $shipping_data['company'] = $request->billing_company;
                $shipping_data['street'] = $request->billing_street;
                $shipping_data['countries_id'] = $request->billing_countries_id;
                $shipping_data['zone_id'] = $request->billing_zone_id;
                $shipping_data['city'] = $request->billing_city;
                $shipping_data['postcode'] = $request->billing_zip;
                $shipping_data['delivery_phone'] = $request->billing_phone;
                $shipping_data = (object) $shipping_data;
                $shipping_data->same_billing_address = 1;
                session(['shipping_address' => $shipping_data]);
        }

        

        if (empty(session('billing_address'))) {

            $billing_address = (object) $billing_data;
            session(['billing_address' => $billing_address]);
        }
       
      
        return redirect()->back();
    }

    //checkout_payment_method

    public function checkout_payment_method(Request $request)
    {
        if (session('step') == '2') {

            session(['step' => '3']);
            // session(['step' => '2']);
        }

        $result['commonContent'] = $this->index->commonContent();

        $shipping_detail = array();

        foreach ($request->all() as $key => $value) {

            if ($key == 'shipping_price') {

                if (!empty($result['commonContent']['setting'][82]->value) and $result['commonContent']['setting'][82]->value <= session('total_price')) {

                    $shipping_detail['shipping_price'] = 0;

                } else {

                    $shipping_detail['shipping_price'] = $value;
                }

            } else {

                $shipping_detail[$key] = $value;

            }
        }

        session(['shipping_detail' => (object) $shipping_detail]);
        return redirect()->back();
    }

    //order_detail

    public function paymentComponent(Request $request)
    {
        session(['payment_method' => $request->payment_method]);
    }

    //generate token

    public function generateBraintreeTokenWeb()
    {
        $payments_setting = $this->order->payments_setting_for_brain_tree();

        if ($payments_setting['merchant_id']->status == 1) {

            //braintree transaction get nonce

            $is_transaction = '0'; # For payment through braintree

            if ($payments_setting['merchant_id']->environment == '0') {

                $braintree_environment = 'sandbox';
            } else {

                $environment = 'production';
            }

            //for token please check braintree.php file
            require_once app_path('braintree/index.php');

            $braintree_merchant_id = $payments_setting['merchant_id']->value;

            $braintree_public_key = $payments_setting['public_key']->value;

            $braintree_private_key = $payments_setting['private_key']->value;

        } else {
            $clientToken = '';
        }
        return $clientToken;
    }

    public function cancel()
    {
        return redirect(url('checkout'))->with('message', 'Your payment is cancelled');
        //dd('Your payment is canceled. You can create cancel page here.');
    }

    public function success(Request $request)
    {

            $paypalModule = new ExpressCheckout;
            $response = $paypalModule->getExpressCheckoutDetails($request->token);
            // $response = $provider->getExpressCheckoutDetails($request->token);

            // dd($response);

            //$response = $paypalModule->doExpressCheckoutPayment($response['PAYERID']);
            //dd($response);

            if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {

                $order_information = array(
                    'paid' => 'true',
                    'transaction_id' => $response['PAYERID'],
                    'CORRELATIONID' => $response['CORRELATIONID'],
                    'status' => $response['ACK'],
                    'currency' => $response['CURRENCYCODE'],
                    'amount' => $response['AMT'],
                    'created' => $response['TIMESTAMP'],
                );

                DB::table('orders')->where('orders_id', $response['INVNUM'])->update(['order_information' => json_encode($order_information)]);
				
			 /**************************************************************
             *
             *  order status: 13 - pending payment, 1 - received, 2 - completed, 3 - cancelled
             *
             *************************************************************/
				
				DB::table('orders_status_history')->where('orders_id', $response['INVNUM'])->update(['orders_status_id' => 1]);
				
                $message = Lang::get("website.Payment has been processed successfully");
                return redirect('/thankyou');
                //dd('Your payment was successfully. You can create success page here.');
            }
    }

    //place_order
    public function place_order(Request $request)
    {

        switch ($request->payment_method):

    case 'paypal':
        session(['payment_method' => ($request->payment_method)]);

        break;

    default:

        if (strlen($request['cvc']) != 3):
            return redirect(url('checkout'))->with('message', 'CVC no. length should be equal to 3');
        endif;

        $validator = $request->validate([ // <---
            'number' => 'required',
            'expiry' => 'required',
            'cvc' => 'required',
        ]);

        endswitch;

        //dd($request->all());

        $payment_status = $this->order->place_order($request);
        $checkHttp = mb_substr($payment_status, 0, 4);

        //dd($payment_status);
        //dd(mb_substr($payment_status,0,4));

        if ($payment_status == 'success') {

                $message = Lang::get("website.Payment has been processed successfully");

                return redirect('/thankyou');

        } elseif ($checkHttp == 'http') {
            return redirect($payment_status);

        } else {

            return redirect()->back()->with('error', Lang::get("website.Error while placing order"));

        }

    }

    //thankyou

    public function thankyou(Request $request)
    {

        $title = array('pageTitle' => Lang::get('website.Thank You'));

        $bankdetail = array();

        if (!empty(session('banktransfer')) and session('banktransfer') == 'yes') {

            $payments_setting = $this->order->payments_setting_for_directbank();

            $bankdetail = array(

                'account_name' => $payments_setting['account_name']->value,

                'account_number' => $payments_setting['account_number']->value,

                'payment_method' => $payments_setting['account_name']->payment_method,

                'bank_name' => $payments_setting['bank_name']->value,

                'short_code' => $payments_setting['short_code']->value,

                'iban' => $payments_setting['iban']->value,

                'swift' => $payments_setting['swift']->value,

            );

        }

        $final_theme = $this->theme->theme();

        $result = $this->order->orders($request);

        return view("web.thankyou", ['title' => $title, 'final_theme' => $final_theme, 'bankdetail' => $bankdetail])->with('result', $result);

    }

    //orders

    public function orders(Request $request)
    {

        $title = array('pageTitle' => Lang::get("website.My Orders"));

        // $final_theme = $this->theme->theme();

        $result = array();

        $result['fetch_footer_link'] = $this->footerlink->footer_link();

        $result = $this->order->orders($request);

        // echo "<pre>";
        //     print_r($result);
        // echo "</pre>";
        // exit;

        return view("web.orders", ['title' => $title])->with('result', $result);

    }

    //viewMyOrder

    public function viewOrder(Request $request, $id)
    {
        $title = array('pageTitle' => Lang::get("website.View Order"));

        // $final_theme = $this->theme->theme();
        // $result = $this->order->viewOrder($request, $id);
        //orders data

        $ordersData = $this->Coreorder->detail($request);

        // current order status

        $orders_status_history = $this->Coreorder->currentOrderStatus($request);

        //all statuses

        $orders_status = $this->Coreorder->orderStatuses();

        $ordersData['orders_status'] = $orders_status;

        $ordersData['orders_status_history'] = $orders_status_history;
        $result['fetch_footer_link'] = $this->footerlink->footer_link();
        $result['commonContent'] = $this->index->commonContent();

        //dd($request);
        //dd($ordersData);
        if ($result['res'] = "view-order") {
            return view("web.view-order", $title)->with(['data' => $ordersData, 'result' => $result]);

        } else {

            return redirect('orders');

        }

    }

    //calculate tax
    public function calculateTax($tax_zone_id)
    {
        $tax = $this->order->calculateTax($tax_zone_id);

        return $tax;
    }

    //shipping methods

    public function shipping_methods()
    {

        $result = array();

        if (!empty(session('shipping_address'))) {

            $countries_id = session('shipping_address')->countries_id;
            $toPostalCode = session('shipping_address')->postcode;
            $toCity = session('shipping_address')->city;
            $toAddress = 'gh';
            $countries = $this->order->getCountries($countries_id);
            $toCountry = $countries[0]->countries_iso_code_2;
            $zone_id = session('shipping_address')->zone_id;

            if ($zone_id != -1 and !empty($zone_id)) {

                $zones = $this->order->getZones($zone_id);
                $toState = $zones[0]->zone_code;
            }

        } else {

            $countries_id = '';
            $toPostalCode = '34669';
            $toCity = '';
            $toAddress = '';
            $toCountry = '';
            $zone_id = '';
        }

        //product weight

        $cart = $this->cart->myCart($result);

        $index = '0';
        $total_weight = '0';

        foreach ($cart as $products_data) {

            foreach ($products_data->attributes as $GetW) {

                $ChechWeight = DB::table('products_attributes')->where('products_attributes_id', $GetW->products_attributes_id)->first();

                if (isset($ChechWeight) && !empty($ChechWeight)) {

                    $productsWeight = $ChechWeight->weight;
                }

                $total_weight += $productsWeight;

            }

            // if ($products_data->unit == 'Gram') {

            //     $productsWeight = $products_data->weight / 453.59237;
            // } else if ($products_data->unit == 'Kilogram') {
            //     $productsWeight = $products_data->weight / 0.45359237;
            // } else {
            //     $productsWeight = $products_data->weight;
            // }

        }

        // dd($total_weight);

        $products_weight = $total_weight;

        //website path

        //$websiteURL =  "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $websiteURL = "https://" . $_SERVER['SERVER_NAME'] . '/';
        $replaceURL = str_replace('getRate', '', $websiteURL);
        $requiredURL = $replaceURL . 'app/ups/ups.php';

        //default shipping method

        $shippings = $this->order->getShippingMethods();
        $result = array();
        $mainIndex = 0;

        foreach ($shippings as $shipping_methods) {

            $shippings_detail = $this->order->getShippingDetail($shipping_methods);

            //ups shipping rate

            if ($shipping_methods->methods_type_link == 'upsShipping' and $shipping_methods->status == '1') {

                $result2 = array();

                $is_transaction = '0';

                if ($total_weight == '0') {

                    $total_weight = '1';
                }

                $ups_shipping = $this->order->getUpsShipping();

                // dd($ups_shipping);
                //shipp from and all credentials

                $accessKey = $ups_shipping[0]->access_key;
                $userId = $ups_shipping[0]->user_name;
                $password = $ups_shipping[0]->password;

                //ship from address

                $fromAddress = $ups_shipping[0]->address_line_1;
                $fromPostalCode = $ups_shipping[0]->post_code;
                $fromCity = $ups_shipping[0]->city;
                $fromState = $ups_shipping[0]->state;
                $fromCountry = $ups_shipping[0]->country;

                // Add UPS Mahavir

                // $rate = new \Ups\RateTimeInTransit(

                $rate = new \Ups\Rate(

                    $accessKey,
                    $userId,
                    $password
                );

                try {

                    $shipment = new \Ups\Entity\Shipment();

                    $shipperAddress = $shipment->getShipper()->getAddress();
                    $shipperAddress->setPostalCode('34669');
                    $address = new \Ups\Entity\Address();
                    $address->setPostalCode('34669');
                    $shipFrom = new \Ups\Entity\ShipFrom();
                    $shipFrom->setAddress($address);
                    $shipment->setShipFrom($shipFrom);
                    $shipTo = $shipment->getShipTo();

                    $shipTo->setCompanyName('Test Ship To');
                    $shipToAddress = $shipTo->getAddress();
                    $shipToAddress->setPostalCode($toPostalCode);
                    $shipToAddress->setPostalCode('34669');

                    $package = new \Ups\Entity\Package();

                    $package->getPackagingType()->setCode(\Ups\Entity\PackagingType::PT_PACKAGE);
                    $package->getPackageWeight()->setWeight($total_weight);

                    // if you need this (depends of the shipper country)

                    $weightUnit = new \Ups\Entity\UnitOfMeasurement;

                    $weightUnit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_LBS);

                    // dd($weightUnit);

                    $package->getPackageWeight()->setUnitOfMeasurement($weightUnit);

                    $dimensions = new \Ups\Entity\Dimensions();

                    $dimensions->setHeight(10);
                    $dimensions->setWidth(10);
                    $dimensions->setLength(10);

                    $unit = new \Ups\Entity\UnitOfMeasurement;
                    $unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_IN);

                    $dimensions->setUnitOfMeasurement($unit);
                    $package->setDimensions($dimensions);

                    $shipment->addPackage($package);

                    $GetShip = $rate->getRate($shipment);

                    $RateNew = 0;
                    $Description = '';

                    if (!empty($GetShip)) {

                        if ($GetShip->RatedShipment[0]->RatedPackage) {

                            $GetRate = $GetShip->RatedShipment[0]->RatedPackage;
                            $RateNew = $GetRate[0]->TotalCharges->MonetaryValue;
                            $Description = $GetRate[0]->TotalCharges->Description;

                            // dd($GetRate[0]->TotalCharges);

                            $data = DB::table('user_membership')->where('user_id', Auth::user()->id)->first();
                            $membeship = false;
                            $cartTotal = Session::get('total_price');
                            if ($data) {
                                if (\Carbon\Carbon::now()->gt($data->end_date)) {
                                    $membeship = true;
                                }
                            }
                            // dd($membeship);
                            /* ZST Hide From
                            $pa_proudct = Session::get('particular_products');
                            if($cartTotal > 49 && $membeship && $pa_proudct==0) {
                            Session::put('shipping_price', $pa_proudct);
                            }else {
                            Session::put('shipping_price', $RateNew);
                            }
                            ZST Hide End */

                            // ZST Add Code From
                            //Session::get('particular_products');
                            if (Session::get('particular_products') == 0) {
                                Session::put('shipping_price', 0);
                            } else {
                                Session::put('shipping_price', $RateNew);
                            }

                            // ZST Add Code End

                            Session::put('shipping_detail', $Description);

                        }

                    }

                    if (!empty($GetShip)) {

                        /*
                        ZST Hide Start
                        $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => 'UPS Shipping', 'is_default' => $shipping_methods->isDefault);
                        ZST Hide End
                         */
                        $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => 'UPS Shipping ', 'is_default' => $shipping_methods->isDefault);

                        $result2[$index] = array('name' => $Description, 'rate' => $RateNew, 'currencyCode' => 'USD', 'shipping_method' => 'upsShipping');

                        $index++;

                    } else {

                        $success = array('success' => '0', 'message' => "Selected regions are not supported for UPS shipping", 'name' => $shippings_detail[0]->name);

                    }

                    $success['services'] = $result2;

                } catch (Exception $e) {

                    var_dump($e);
                }

                $result[$mainIndex] = $success;
                $mainIndex++;
            } else if ($shipping_methods->methods_type_link == 'flateRate' and $shipping_methods->status == '1') {

                $ups_shipping = $this->order->getUpsShippingRate();

                $data2 = array('name' => $shippings_detail[0]->name, 'rate' => $ups_shipping[0]->flate_rate, 'currencyCode' => $ups_shipping[0]->currency, 'shipping_method' => 'flateRate');

                if (count($ups_shipping) > 0) {

                    $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => $shippings_detail[0]->name, 'is_default' => $shipping_methods->isDefault);
                    $success['services'][0] = $data2;
                    $result[$mainIndex] = $success;
                    $mainIndex++;
                }

            } else if ($shipping_methods->methods_type_link == 'localPickup' and $shipping_methods->status == '1') {

                $data2 = array('name' => $shippings_detail[0]->name, 'rate' => '0', 'currencyCode' => 'USD', 'shipping_method' => 'localPickup');

                $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => $shippings_detail[0]->name, 'is_default' => $shipping_methods->isDefault);

                $success['services'][0] = $data2;
                $result[$mainIndex] = $success;
                $mainIndex++;
            } else if ($shipping_methods->methods_type_link == 'freeShipping' and $shipping_methods->status == '1') {

                $data2 = array('name' => $shippings_detail[0]->name, 'rate' => '0', 'currencyCode' => 'USD', 'shipping_method' => 'freeShipping');

                /* ZST Hide from
                $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => $shippings_detail[0]->name, 'is_default' => $shipping_methods->isDefault);
                ZST Hide End
                 */

                $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => "Free Shipping", 'is_default' => $shipping_methods->isDefault);

                $success['services'][0] = $data2;
                $result[$mainIndex] = $success;

                $mainIndex++;

            } else if ($shipping_methods->methods_type_link == 'shippingByWeight' and $shipping_methods->status == '1') {

                //cart data
                $carts = $this->cart->myCart('');
                $weight = 0;
                foreach ($carts as $cart) {

                    $weight += $cart->weight * $cart->customers_basket_quantity;

                }
                //check price by weight
                $priceByWeight = $this->order->priceByWeight($weight);

                if (!empty($priceByWeight) and count($priceByWeight) > 0) {

                    $price = $priceByWeight[0]->weight_price;

                } else {

                    $price = 0;

                }

                $data2 = array('name' => $shippings_detail[0]->name, 'rate' => $price, 'currencyCode' => 'USD', 'shipping_method' => 'Shipping By Weight');

                $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => $shippings_detail[0]->name, 'is_default' => $shipping_methods->isDefault);

                $success['services'][0] = $data2;
                $result[$mainIndex] = $success;

                $mainIndex++;
            }

        }
        return $result;

    }

    //get default payment method

    public function getPaymentMethods()
    {

        /**   BRAIN TREE **/

        //////////////////////

        $result = array();

        $payments_setting = $this->order->payments_setting_for_brain_tree();

        if ($payments_setting['merchant_id']->environment == '0') {

            $braintree_enviroment = 'Test';

        } else {

            $braintree_enviroment = 'Live';

        }

        $braintree = array(

            'environment' => $braintree_enviroment,

            'name' => $payments_setting['merchant_id']->name,

            'public_key' => $payments_setting['public_key']->value,

            'active' => $payments_setting['merchant_id']->status,

            'payment_method' => $payments_setting['merchant_id']->payment_method,

            'payment_currency' => Session::get('currency_code'),

        );

        /**  END BRAIN TREE **/

        //////////////////////

        /**   STRIPE**/

        //////////////////////

        $payments_setting = $this->order->payments_setting_for_stripe();

        if ($payments_setting['publishable_key']->environment == '0') {

            $stripe_enviroment = 'Test';

        } else {

            $stripe_enviroment = 'Live';

        }

        $stripe = array(

            'environment' => $stripe_enviroment,

            'name' => $payments_setting['publishable_key']->name,

            'public_key' => $payments_setting['publishable_key']->value,

            'active' => $payments_setting['publishable_key']->status,

            'payment_currency' => Session::get('currency_code'),

            'payment_method' => $payments_setting['publishable_key']->payment_method,

        );

        /**   END STRIPE**/

        //////////////////////

        /**   CASH ON DELIVERY**/

        //////////////////////

        $payments_setting = $this->order->payments_setting_for_cod();

        $cod = array(

            'environment' => 'Live',

            'name' => $payments_setting->name,

            'public_key' => '',

            'active' => $payments_setting->status,

            'payment_currency' => Session::get('currency_code'),

            'payment_method' => $payments_setting->payment_method,

        );

        /**   END CASH ON DELIVERY**/

        /*************************/

        /*************************/

        /**   INSTAMOJO**/

        /*************************/

        $payments_setting = $this->order->payments_setting_for_instamojo();

        if ($payments_setting['auth_token']->environment == '0') {

            $instamojo_enviroment = 'Test';

        } else {

            $instamojo_enviroment = 'Live';

        }

        $instamojo = array(

            'environment' => $instamojo_enviroment,

            'name' => $payments_setting['auth_token']->name,

            'public_key' => $payments_setting['api_key']->value,

            'active' => $payments_setting['api_key']->status,

            'payment_currency' => Session::get('currency_code'),

            'payment_method' => $payments_setting['api_key']->payment_method,

        );

        /**   END INSTAMOJO**/

        /*************************/

        /**   END HYPERPAY**/

        /*************************/

        $payments_setting = $this->order->payments_setting_for_hyperpay();

        //dd($payments_setting);

        if ($payments_setting['userid']->environment == '0') {

            $hyperpay_enviroment = 'Test';

        } else {

            $hyperpay_enviroment = 'Live';

        }

        $hyperpay = array(

            'environment' => $hyperpay_enviroment,

            'name' => $payments_setting['userid']->name,

            'public_key' => $payments_setting['userid']->value,

            'active' => $payments_setting['userid']->status,

            'payment_currency' => Session::get('currency_code'),

            'payment_method' => $payments_setting['userid']->payment_method,

        );

        /**   END HYPERPAY**/

        /*************************/

        $payments_setting = $this->order->payments_setting_for_razorpay();

        if ($payments_setting['RAZORPAY_SECRET']->environment == '0') {

            $razorpay_enviroment = 'Test';

        } else {

            $razorpay_enviroment = 'Live';

        }

        $razorpay = array(

            'environment' => $razorpay_enviroment,

            'public_key' => $payments_setting['RAZORPAY_KEY']->value,

            'name' => $payments_setting['RAZORPAY_KEY']->name,

            'RAZORPAY_KEY' => $payments_setting['RAZORPAY_KEY']->value,

            'RAZORPAY_SECRET' => $payments_setting['RAZORPAY_SECRET']->value,

            'active' => $payments_setting['RAZORPAY_SECRET']->status,

            'payment_currency' => Session::get('currency_code'),

            'payment_method' => $payments_setting['RAZORPAY_SECRET']->payment_method,

        );

        $payments_setting = $this->order->payments_setting_for_paytm();

        if ($payments_setting['paytm_mid']->environment == '0') {

            $paytm_enviroment = 'Test';

        } else {

            $paytm_enviroment = 'Live';

        }

        $paytm = array(

            'environment' => $paytm_enviroment,

            'payment_currency' => Session::get('currency_code'),

            'public_key' => '',

            'name' => $payments_setting['paytm_mid']->name,

            'active' => $payments_setting['paytm_mid']->status,

            'payment_method' => $payments_setting['paytm_mid']->payment_method,

        );

        $payments_setting = $this->order->payments_setting_for_directbank();

        if ($payments_setting['account_name']->environment == '0') {

            $enviroment = 'Live';

        } else {

            $enviroment = 'Live';

        }

        $banktransfer = array(

            'environment' => $enviroment,

            'payment_currency' => Session::get('currency_code'),

            'public_key' => '',

            'name' => $payments_setting['account_name']->name,

            'descriptions' => $payments_setting['account_name']->sub_name_1,

            'active' => $payments_setting['account_name']->status,

            'payment_method' => $payments_setting['account_name']->payment_method,

        );

        $payments_setting = $this->order->payments_setting_for_paystack();

        if ($payments_setting['secret_key']->environment == '0') {

            $enviroment = 'Test';

        } else {

            $enviroment = 'Live';

        }

        $paystack = array(

            'environment' => $enviroment,

            'payment_currency' => Session::get('currency_code'),

            'public_key' => $payments_setting['secret_key']->value,

            'name' => $payments_setting['secret_key']->name,

            'active' => $payments_setting['secret_key']->status,

            'payment_method' => $payments_setting['secret_key']->payment_method,

        );

        $payments_setting = $this->order->payments_setting_for_midtrans();

        if ($payments_setting['merchant_id']->environment == '0') {

            $enviroment = 'Test';

        } else {

            $enviroment = 'Live';

        }

        $midtrans = array(

            'environment' => $enviroment,

            'payment_currency' => Session::get('currency_code'),

            'public_key' => $payments_setting['server_key']->value,

            'name' => $payments_setting['merchant_id']->name,

            'active' => $payments_setting['merchant_id']->status,

            'payment_method' => $payments_setting['merchant_id']->payment_method,

        );

        $result[0] = $braintree;

        $result[1] = $stripe;

        $result[2] = $cod;

        $result[4] = $instamojo;

        $result[5] = $hyperpay;

        $result[6] = $razorpay;

        $result[7] = $paytm;

        $result[8] = $banktransfer;

        $result[9] = $paystack;

        $result[10] = $midtrans;

        return $result;

    }

    public function commentsOrder(Request $request)
    {

        session(['order_comments' => $request->comments]);

    }

    public function payIinstamojo(Request $request)
    {

        $commonContent = $this->index->commonContent();

        if (empty($commonContent['setting'][18]->value)) {

            $siteName = Lang::get('website.Empty Site Name');

        } else {

            $siteName = $commonContent['setting'][18]->value;

        }

        //payment methods

        $payments_setting = $this->order->payments_setting_for_instamojo();

        $instamojo_api_key = $payments_setting['api_key']->value;

        $instamojo_auth_token = $payments_setting['auth_token']->value;

        $websiteURL = "http://" . $_SERVER['SERVER_NAME'] . '/';

        $fullname = $request->fullname;

        $email_id = $request->email_id;

        $phone_number = $request->phone_number;

        $amount = $request->amount;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.instamojo.com/api/1.1/payment-requests/');

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER,

            array("X-Api-Key:" . $instamojo_api_key,

                "X-Auth-Token:" . $instamojo_auth_token));

        $payload = array(

            'purpose' => $siteName . ' Payment',

            'amount' => $amount,

            'phone' => $phone_number,

            'buyer_name' => $fullname,

            'send_email' => true,

            'send_sms' => true,

            'email' => $email_id,

            'allow_repeated_payments' => false,

        );

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));

        $response = curl_exec($ch);

        curl_close($ch);

        session(['instamojo_info' => $response]);

        print_r($response);

    }

    //hyperpaytoken

    public function hyperpay(Request $request)
    {

        $title = array('pageTitle' => Lang::get('website.Checkout'));

        $result = array();

        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $replaceURL = str_replace('/hyperpay', '/hyperpay/checkpayment', $actual_link);

        $amount = number_format((float) session('total_price') + 0, 2, '.', '');

        $payments_setting = $this->order->payments_setting_for_hyperpay();

        //check envinment

        if ($payments_setting['userid']->environment == '0') {

            $env_url = "https://test.oppwa.com/v1/checkouts";

            $order_url = "test";

        } else {

            $env_url = "https://oppwa.com/v1/checkouts";

            $order_url = "live";

        }

        if (Auth::guard('customer')->check()) {

            $email = auth()->guard('customer')->user()->email;

        } else {

            $email = session('shipping_address')->email;

        }

        $url = $env_url;

        $data = "authentication.userId=" . $payments_setting['userid']->value .

        "&authentication.password=" . $payments_setting['password']->value .

        "&authentication.entityId=" . $payments_setting['entityid']->value .

        "&amount=" . $amount .

        "&currency=SAR" .

        "&paymentType=DB" .

        "&customer.email=" . $email .

        "&testMode=EXTERNAL" .

        "&merchantTransactionId=" . uniqid();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // this should be set to true in production

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseData = curl_exec($ch);

        if (curl_errno($ch)) {

            return curl_error($ch);

        }

        curl_close($ch);

        $data = json_decode($responseData);

        if ($data->result->code == '000.200.100') {

            $result['token'] = $data->id;

            $result['webURL'] = $replaceURL;

            $result['order_url'] = $order_url;

            return view("web.hyperpay", $title)->with('result', $result);

        } else {

            return redirect()->back()->with('error', $data->result->description);

        }

    }

    //checkpayment

    public function checkpayment(Request $request)
    {

        $title = array('pageTitle' => Lang::get('website.Checkout'));

        $result = array();

        $payments_setting = $this->order->payments_setting_for_hyperpay();

        //check envinment

        if ($payments_setting['userid']->environment == '0') {

            $env_url = "https://test.oppwa.com";

        } else {

            $env_url = "https://oppwa.com";

        }

        $url = $env_url . $request->resourcePath;

        $url .= "?authentication.userId=" . $payments_setting['userid']->value;

        $url .= "&authentication.password=" . $payments_setting['password']->value;

        $url .= "&authentication.entityId=" . $payments_setting['entityid']->value;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // this should be set to true in production

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseData = curl_exec($ch);

        if (curl_errno($ch)) {

            return curl_error($ch);

        }

        curl_close($ch);

        $data = json_decode($responseData);

        if (preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $data->result->code)) {

            $transaction_id = $data->ndc;

            session(['paymentResponseData' => $data]);

            session(['paymentResponse' => 'success']);

            return redirect('/checkout');

        } else {

            session(['paymentResponseData' => $data->result->description]);

            session(['paymentResponse' => 'error']);

            return redirect('/checkout');

        }

    }

    //changeresponsestatus

    public function changeresponsestatus(Request $request)
    {

        session(['paymentResponseData' => '']);

        session(['paymentResponse' => '']);

    }

    //updatestatus

    public function updatestatus(Request $request)
    {

        //  dd($request->orders_status);

        if (!empty($request->orders_id)) {

            $date_added = date('Y-m-d h:i:s');

            $comments = '';

            $ordersCheck = $this->order->ordersCheck($request);

            if (count($ordersCheck) > 0) {

                $orders_history_id = $this->Coreorder->updateRecord($request);

                // dd($orders_history_id );

                return redirect()->back()->with('message', Lang::get("labels.OrderStatusChangedMessage"));

            } else {

                return redirect()->back()->with('error', Lang::get("labels.OrderStatusChangedMessage"));

            }

        } else {

            return redirect()->back()->with('error', Lang::get("labels.OrderStatusChangedMessage"));

        }

    }

    //paystack

    public function paystackTransaction(REQUEST $request)
    {

        $result = array();

        //Set other parameters as keys in the $postdata array

        //"reference" => '7PVGX8MEk85tgeEpVDtDD'

        if (Auth::guard('customer')->check()) {

            $email = auth()->guard('customer')->user()->email;

        } else {

            $email = session('shipping_address')->email;

        }

        $payments_setting = $this->order->payments_setting_for_paystack();

        $amount = number_format((float) session('total_price') + 0, 2);

        $amount = $amount * 100;

        // dd($amount);

        $postdata = array('email' => $email, 'amount' => $amount, "reference" => uniqid());

        $url = "https://api.paystack.co/transaction/initialize";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata)); //Post Fields

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [

            'Authorization: Bearer ' . $payments_setting['secret_key']->value,

            'Content-Type: application/json',

        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response === false) {

            throw new \Exception('CURL Error: ' . curl_error($ch), curl_errno($ch));

        }

        curl_close($ch);

        // if ($response) {

        //     $result = json_decode($response, true);

        // }

        print_r($response);

    }

    public function authorizepaystackTransaction(REQUEST $request)
    {

        $result = array();

        //The parameter after verify/ is the transaction reference to be verified

        $url = 'https://api.paystack.co/transaction/verify/' . $request->reference;

        $payments_setting = $this->order->payments_setting_for_paystack();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt(

            $ch, CURLOPT_HTTPHEADER, [

                'Authorization: Bearer ' . $payments_setting['secret_key']->value]

        );

        $request = curl_exec($ch);

        curl_close($ch);

        if ($request) {

            $result = json_decode($request, true);

            $message = $result['message'];

            // dd($result);

            if ($result) {

                //message

                session(['paymentResponseData' => $message]);

                if (!empty($result['data']) and count($result['data']) > 0) {

                    //something came in

                    if ($result['data']['status'] == 'success') {

                        // the transaction was successful, you can deliver value

                        /*

                        @ also remember that if this was a card transaction, you can store the

                        @ card authorization to enable you charge the customer subsequently.

                        @ The card authorization is in:

                        @ $result['data']['authorization']['authorization_code'];

                        @ PS: Store the authorization with this email address used for this transaction.

                        @ The authorization will only work with this particular email.

                        @ If the user changes his email on your system, it will be unusable

                         */

                        //echo "Transaction was successful";

                        session(['paymentResponse' => 'success']);

                        session(['payment_json' => $result]);

                    } else {

                        session(['paymentResponse' => 'error']);

                        // the transaction was not successful, do not deliver value'

                        // print_r($result);  //uncomment this line to inspect the result, to check why it failed.

                        //echo "Transaction was not successful: Last gateway response was: ".$result['data']['gateway_response'];

                    }

                } else {

                    session(['paymentResponse' => 'error']);

                }

            } else {

                //print_r($result);

                session(['paymentResponse' => 'error']);

                $message = "Opps! Something went wrong please check merchant account seeting.";

                session(['paymentResponseData' => $message]);

            }

        } else {

            //var_dump($request);

            session(['paymentResponse' => 'error']);

            $message = "Opps! Something went wrong please check merchant account seeting.";

            session(['paymentResponseData' => $message]);

        }

        return redirect('checkout');

    }

}