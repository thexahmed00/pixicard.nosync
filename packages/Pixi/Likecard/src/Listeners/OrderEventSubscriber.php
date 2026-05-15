<?php

namespace Pixi\Likecard\Listeners;

use Illuminate\Support\Facades\Log;
use Pixi\Likecard\Services\LikeCard;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderEventSubscriber implements ShouldQueue
{
    /**
     * The constructor can be empty.
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the order created event.
     * This method is called when the event is fired.
     *
     * @param object $order The order object from the event.
     */
    public function onOrderCreated($order): void
    {
       Log::channel('likecard_sync')->info('-----------------Event Fired!----------');
       Log::info('-----------Event Fired!---------------------');
        if (
            $order->status == 'completed' ||
            ($order->status == 'processing' && $order->payment->method == 'edfapay')
        ) {
            Log::channel('likecard_sync')->info('EVENT SUBSCRIBER: Order Created Event Fired!', ['order_id' => $order->id]);
            Log::info('EVENT SUBSCRIBER: Order Created Event Fired!', ['order_id' => $order->id]);
            Log::debug($order);
            foreach ($order->items as $item) {
                if (str_starts_with($item->sku, 'lc-pr-')) {
                    // 2. Pass the logger instance to your other methods.
                    $this->purchaseFromLikeCard($item);
                }
            }
        }
    
        
    }

    /**
     * Make the API call to LikeCard and update the order item.
     *
     * @param object $item The order item.
     * @param LoggerInterface $cronLogger The logger instance.
     */
    protected function purchaseFromLikeCard($item): void
    {
        Log::channel('likecard_sync')->debug('Starting purchaseFromLikeCard for item.', [
            'item_id' => $item->id,
            'sku'     => $item->sku
        ]);

        try {
            LikeCard::init();
            
            $likecard_product_id = (int) str_replace('lc-pr-', '', (string) $item->sku);
            $quantity_ordered = max(1, (int)($item->qty_ordered ?? 1));

            // Arrays to collect results from each API call in the loop
            $all_responses = [];
            $all_voucher_codes = [];

            Log::channel('likecard_sync')->info("Item requires {$quantity_ordered} voucher(s). Starting loop.");

            // Loop for each quantity of the item
            for ($i = 0; $i < $quantity_ordered; $i++) {
                Log::channel('likecard_sync')->info("Processing quantity " . ($i + 1) . " of " . $quantity_ordered);

                // The payload should be for a single product, as we are looping
                $orderData = [
                    'productId' => 376  
                    // $likecard_product_id,
                ];

                // Make the API call for one unit
                $lc = LikeCard::createOrder($orderData)->make();
                
                // Add the full response to our collection
                $all_responses[] = $lc;

                // Extract the serial code using the CORRECT path
                $voucherCode = data_get($lc, 'serials.0.serialCode');

                if (! empty($voucherCode)) {
                    $all_voucher_codes[] = $voucherCode;
                    Log::channel('likecard_sync')->info("-> Found voucher code: {$voucherCode}");
                } else {
                    Log::channel('likecard_sync')->warning("-> Voucher code NOT FOUND in response for quantity " . ($i + 1));
                }
            }

            // After the loop, save the combined results
            Log::channel('likecard_sync')->info("Loop finished. Preparing to save combined data.");
            
            // Store all raw responses as a single JSON string
            $item->likecard_response = json_encode($all_responses);

            // Check if we collected any voucher codes
            if (! empty($all_voucher_codes)) {
                // Combine all codes into a single string, separated by commas
                $item->voucher_code = implode(', ', $all_voucher_codes);
                Log::channel('likecard_sync')->info('Combined voucher codes to be saved:', ['codes' => $item->voucher_code]);
            } else {
                Log::channel('likecard_sync')->warning('No voucher codes were collected after the loop.');
            }

            // Save the item to the database ONCE after the loop
            $item->save();
            Log::channel('likecard_sync')->info('Item data saved successfully.');

        } catch (\Exception $e) {
            Log::channel('likecard_sync')->error("-> Exception in purchaseFromLikeCard for SKU {$item->sku}: " . $e->getMessage());
        }
    }


    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events): void
    {
        $events->listen(
            'sales.order.update-status.after',
            [OrderEventSubscriber::class, 'onOrderCreated']
        );
    }
}

