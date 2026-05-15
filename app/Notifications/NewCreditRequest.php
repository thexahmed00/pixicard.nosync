<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Webkul\Marketplace\Models\Seller;
use Webkul\Marketplace\Models\SellerWallet;

class NewCreditRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $creditRequest;
    protected $seller;

    /**
     * Create a new notification instance.
     *
     * @param \Webkul\Marketplace\Models\SellerWallet $creditRequest
     * @param \Webkul\Marketplace\Models\Seller $seller
     * @return void
     */
    public function __construct(SellerWallet $creditRequest, Seller $seller)
    {
        $this->creditRequest = $creditRequest;
        $this->seller = $seller;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // This tells Laravel to store the notification in the database.
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * This data will be stored as JSON in the 'data' column of the 'notifications' table.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'amount'        => $this->creditRequest->amount,
            'seller_name'   => $this->seller->name, // Assumes seller name is on the related customer model
            'message'       => "New credit request of {$this->creditRequest->amount} SAR from seller {$this->seller->name}.",
            // TODO: Update this URL to point to your admin page for managing seller wallets or credit requests.
            'url'           => route('admin.marketplace.sellers.index'),
        ];
    }
}

