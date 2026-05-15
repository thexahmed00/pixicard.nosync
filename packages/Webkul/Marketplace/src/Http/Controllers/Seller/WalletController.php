<?php

namespace Webkul\Marketplace\Http\Controllers\Seller;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pixi\Likecard\Services\EdfaPayService;
use Webkul\Marketplace\Models\SellerWallet;
use Webkul\Marketplace\DataGrids\Seller\WalletDataGrid;

class WalletController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return datagrid(WalletDataGrid::class)->process();
        }

        // This is the corrected line.
        // It now filters for records where the status is 'success' before summing the amount.
        $balance = SellerWallet::where('seller_id', auth()->guard('seller')->user()->id)
            ->where('status', 'success')
            ->sum('amount');

        return view('marketplace::seller.wallet.index', compact('balance'));
    }

    public function indexold()
    {
        $sellerId = auth()->guard('seller')->user()->id;

        $transactions = SellerWallet::where('seller_id', $sellerId)->latest()->get();
        $balance = SellerWallet::where('seller_id', $sellerId)->sum('amount');

        return view('marketplace_wallet::seller.wallet.index', compact('transactions', 'balance'));
    }

    public function create()
    {
        return view('marketplace::seller.wallet.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'recharge_option' => 'required|in:payment,credit', // Ensures the value is one of the two options
        ]);

        // 1. Get the authenticated seller
        $seller = auth()->guard('seller')->user();

        // 2. Get EdfaPay credentials (assuming you store them in config or env)
        //    If you have a settings page for this, fetch them from there.
        $merchantId = core()->getConfigData('sales.payment_methods.edfapay.merchant_id');
        $merchantPassword = core()->getConfigData('sales.payment_methods.edfapay.merchant_password');
        if ($request->recharge_option === 'credit') {

            // Create a new entry in the seller_wallet table with a 'pending' status
            SellerWallet::create([
                'seller_id' => $seller->id,
                'amount' => $request->amount,
                'type' => 'credit',
                'status' => 'pending_approval', // As per your table schema ('pending', 'success', 'failed')
            ]);

            // Redirect back with a success message for the credit request
            return redirect()->route('seller.wallet.index')
                ->with('success', 'Credit request submitted successfully and is pending approval.');
        }

        if (!$merchantId || !$merchantPassword) {
            Log::error('EdfaPay credentials are not configured for wallet recharge.');
            return redirect()->back()->with('error', 'Payment gateway is not configured.');
        }

        // 3. Initialize the EdfaPay service
        $edfaPay = EdfaPayService::init($merchantId, $merchantPassword);

        // 4. Prepare Payee (Seller) Details
        $payeeDetails = [
            'payer_first_name' => $seller->name,
            'payer_last_name' => $seller->name,
            'payer_email' => $seller->email,
            'payer_phone' => $seller->phone, // Ensure the seller model has a phone attribute
            'payer_ip' => $request->ip(),
        ];

        // 5. Prepare Transaction Details
        $transactionDetails = [
            'order_id' => 'wallet-recharge-' . $seller->id . '-' . time(), // Unique ID with seller context
            'order_amount' => number_format($request->amount, 2, '.', ''),
            'order_currency' => 'SAR', // Or get from your app's config
            'order_description' => 'Seller Wallet Recharge for ' . $seller->name,
            'term_url_3ds' => route('seller.wallet.callback'), // Redirect back after payment attempt
        ];

        try {
            // 6. Execute the payment initiation request
            $response = $edfaPay
                ->payee($payeeDetails)
                ->pay($transactionDetails)
                ->exec();

            // 7. Check for the redirect URL and redirect the seller
            if (isset($response['redirect_url'])) {
                return redirect()->away($response['redirect_url']);
            }

            Log::error('EdfaPay Wallet Error: Redirect URL not found.', ['response' => $response]);
            return redirect()->back()->with('error', 'Failed to initiate payment.');

        } catch (\Exception $e) {
            Log::error('EdfaPay Wallet Service Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred with the payment service.');
        }
    }

    public function callback(Request $request)
    {
        // Calculate the balance
        $balance = SellerWallet::where('seller_id', auth()->guard('seller')->user()->id)
            ->where('status', 'success')
            ->sum('amount');

        // Pass the balance to the view
        return view('marketplace::seller.wallet.index', compact('balance'));
    }


    public function destroy($id)
    {
        $wallet = SellerWallet::where('seller_id', auth()->guard('seller')->user()->id)
            ->findOrFail($id);

        $wallet->delete();

        return response()->json([
            'message' => 'Wallet entry deleted successfully.'
        ]);
    }

}


