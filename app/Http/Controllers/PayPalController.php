<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\ExpressCheckout;
//use App\Models\Order;
//use Illuminate\Support\Facades\Mail;
//use App\Mail\OrderPaid;
use Cart;

class PayPalController extends Controller
{
    public function getExpressCheckout()
    {
        $checkoutData = $this->checkoutData();

        $provider = new ExpressCheckout();

        $response = $provider->setExpressCheckout($checkoutData);

        return redirect($response['paypal_link']);
    }

    private function checkoutData()
    {
        $cart = Cart::session(auth()->id());

        $cartItems = array_map(function ($item) {
            return [
                'name' => $item['name'],
                'price' => $item['price'],
                'qty' => $item['quantity']

            ];
        }, $cart->getContent()->toarray());



        $checkoutData = [
            'items' => $cartItems,
            'return_url' => route('paypal.success'),
            'cancel_url' => route('paypal.cancel'),
            'invoice_id' => uniqid(),
            'invoice_description' => " Order description ",
            'total' => $cart->getTotal(),


        ];

        return $checkoutData;
    }

    public function cancelPage()
    {
        dd('payment failed');
    }

    public function getExpressCheckoutSuccess(Request $request)
    {
        $token = $request->get('token');
        $payerId = $request->get('PayerID');
        $provider = new ExpressCheckout();
        $response = $provider->getExpressCheckoutDetails($token);
        $checkoutData = $this->checkoutData();

        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {

            // Perform transaction on PayPal
            $payment_status = $provider->doExpressCheckoutPayment($checkoutData, $token, $payerId);
            $status = $payment_status['PAYMENTINFO_0_PAYMENTSTATUS'];

            
           
        }

      //  return redirect('/')->withMessage('Payment unsuccessful!');

      dd('payment successful');
    }
}