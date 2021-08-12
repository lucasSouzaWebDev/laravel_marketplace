<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Payment\PagSeguro\CreditCard;
use App\Payment\PagSeguro\Notification;
use App\Store;
use Ramsey\Uuid\Uuid;
use App\UserOrder;
use App\Payment\PagSeguro\Boleto;

class CheckoutController extends Controller
{
    public function index()
    {
        try{
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            if(!session()->has('cart')){
                return redirect()->route('home');
            }
            $this->makePagSeguroSession();
    
            $cartItems = array_map(function ($line) {
                return $line['amount'] * $line['price'];
            }, session()->get('cart'));
    
            $cartItems = array_sum($cartItems);
            return view('checkout', compact('cartItems'));

        }catch(\Exception $e){
            session()->forget('pagseguro_session_code');
        }
    }

    public function proccess(Request $request)
    {
        try {
            $dataPost = $request->all();
            $user = auth()->user();
            $cartItems = session()->get('cart');
            $stores = array_unique(array_column($cartItems, 'store_id'));
            $reference = Uuid::uuid4();

            $payment = $dataPost['paymentType'] == 'BOLETO'
                    ? new Boleto($cartItems, $user, $reference, $dataPost['hash'])
                    : new CreditCard($cartItems, $user, $dataPost, $reference);
            $result = $payment->doPayment();
            date_default_timezone_set('America/Sao_Paulo');

            $userOrder = [
                'reference' => $reference,
                'pagseguro_code' => $result->getCode(),
                'pagseguro_status' => $result->getStatus(),
                'items' => serialize($cartItems),
                'store_id' => $stores[0],
/*                 'type' => $dataPost['paymentType'],
                'link_boleto' => $result->getPaymentLink() */
            ];

            $userOrder = $user->orders()->create($userOrder);
            $userOrder->stores()->sync($stores);

            // Notificar loja de novo pedido
            $store = (new Store)->notifyStoreOwners($stores);

            session()->forget('cart');
            session()->forget('pagseguro_session_code');

            $dataJson = [
                'status' => true,
                'message' => 'Pedido criado com sucesso!',
                'order' => $reference
            ];

            if($dataPost['paymentType'] == 'BOLETO'){
                $dataJson['link_boleto'] = $result->getPaymentLink();
            }

            return response()->json([
                'data' => $dataJson
            ]);
        } catch (\Exception  $exception) {
            $message = env('APP_DEBUG') ? simplexml_load_string($exception->getMessage()) : 'Erro ao processar pedido!';
            return response()->json([
                'data' => [
                    'status' => false,
                    'message' => $message
                ]
            ], 401);
        }
    }

    public function thanks()
    {
        return view('thanks');
    }

    public function notification()
    {
        try{
            $notification = new Notification();
            $notification = $notification->getTransaction();
            $reference = base64_decode($notification->getReference());
            $userOrder = UserOrder::whereReference($reference);
            $userOrder->update([
                'pagseguro_status' => $notification->getStatus()
            ]);

            if($notification->getStatus() == 3) {
                // processamentos de pedido pago
            }

            return response()->json([], 204);
        }catch (\Exception $exception){
            $message = env('APP_DEBUG') ? $exception->getMessage() : '';
            return response()->json(['error' => $message], 500);
        }
    }

    private function makePagSeguroSession()
    {
        if (!session()->has('pagseguro_session_code')) {
            $sessionCode = \PagSeguro\Services\Session::create(
                \PagSeguro\Configuration\Configure::getAccountCredentials()
            );

            session()->put('pagseguro_session_code', $sessionCode->getResult());
        }
    }
}
