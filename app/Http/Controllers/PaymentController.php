<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\HashSecret;

class PaymentController extends Controller
{
    // Fetch all payments
    public function index(Request $request) {
        try {
            $perPage = $request->input('per_page', 10);
            $payments = Payment::paginate($perPage);
            $paymentsArray = $payments->getCollection()->map(function ($payment) {
                return [
                    'id' => HashSecret::decrypt($payment->id),
                    'tour_id' => $payment->tour_id,
                    'number_of_tickers' => $payment->number_of_tickers,
                    'total_price' => $payment->total_price,
                    'user_id' => $payment->user_id,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'notes' => $payment->notes,
                    'transaction_id' => $payment->transaction_id,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                ];
            });
            return response()->json([
                'payments' => $paymentsArray,
                'links' => [
                    'next' => $payments->nextPageUrl(),
                    'prev' => $payments->previousPageUrl(),
                ],
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch payments.'], 500);
        }
    }

    // Store a new payment
    public function store(Request $request) {
        try {
            $validatedData = $request->validate([
                'tour_id' => 'nullable',
                'number_of_tickers' => 'required|integer',
                'total_price' => 'required|integer',
                'user_id' => 'nullable',
                'payment_method' => 'required|in:transfer,cash',
                'status' => 'required|in:pending,completed,failed,refunded',
                'notes' => 'nullable|string',
                'transaction_id' => 'nullable|string',
            ]);
            $payment = Payment::create($validatedData);
            return response()->json(['payment' => $payment], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Show a specific payment
    public function show($id) {
        try {
            return response()->json(['data' => Payment::findOrFail($id)], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Payment not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch payment.'], 500);
        }
    }

    // Update a specific payment
    public function update(Request $request, $id) {
        try {
            $payment = Payment::findOrFail($id);
            $payment->update($request->all());
            return response()->json(['data' => $payment], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update payment.'], 500);
        }
    }

    // Delete a specific payment
    public function destroy($id) {
        try {
            Payment::destroy($id);
            return response()->json(['message' => 'Payment deleted successfully.'], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete payment.'], 500);
        }
    }

    function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }

    /**
     * Payment with momo
     * @return void
     */
    public function momo_payment(Request $request){
        try {
            // $urlCheckout = "http://127.0.0.1:8000/api/payments/momo/ipn";
            $urlCheckout = "http://localhost:3000/minh-hiep/payment/success";
            $price = (string)$request->total_price;
            $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
            $partnerCode = 'MOMOBKUN20180529';
            $accessKey = 'klm05TvNBzhg7h7j';
            $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
            $orderInfo = "Thanh toán qua MoMo";
            $amount = $price;
            $orderId = time() . "";
            $redirectUrl = $urlCheckout;
            $ipnUrl = $urlCheckout;
            $extraData = "";
            $requestId = time() . "";
            $requestType = "payWithATM";
            //before sign HMAC SHA256 signature
            $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
            $signature = hash_hmac("sha256", $rawHash, $secretKey);
            $data = array('partnerCode' => $partnerCode,
                'partnerName' => "Test",
                "storeId" => "MomoTestStore",
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature);
            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);  // decode json

            //Create Payment

            $request->merge(['transaction_id' => $jsonResult['orderId']]);
            $this->store($request);
            //Return url
            return response()->json([
                'payUrl' => $jsonResult['payUrl']
            ], 200);

        }catch(\Exception $e) {
            return response()->json([
                'message' => "Some thing wrong",
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle when payment success
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function momoIPN(Request $request)
    {
        try {
            $payment = Payment::where('transaction_id', $request->orderId)->first();
            if ($payment) {
                // Cập nhật trạng thái thanh toán
                $payment->status = "completed"; // cập nhật trạng thái
                $payment->transaction_id = $request->transId; // lưu ID giao dịch
                $payment->save(); // lưu thay đổi

                return response()->json([
                    'message' => 'Payment status updated',
                ], 200);
            } else {
                return response()->json(['message' => 'Payment not found'], 404);
            }

        }catch(\Exception $e) {
            return response()->json([
                'message' => 'Some thing wrong',
                'error' => $e->getMessage()
            ], 404);
        }

    }
}