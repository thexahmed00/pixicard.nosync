<?php

namespace Webkul\Pos\Queries\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\GraphQLAPI\Validators\CustomException;
use Webkul\Pos\Repositories\PosDrawerRepository;
use Webkul\Pos\Repositories\PosOrderRepository;

class CashierQuery extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected PosDrawerRepository $posDrawerRepository,
        protected PosOrderRepository $posOrderRepository
    ) {}

    /**
     * Get the payment details.
     */
    public function getDrawerDetails(): array
    {
        if (! core()->getConfigData('pos.settings.general.status')) {
            throw new CustomException('status_off');
        }

        $drawer = $this->getTodaysDrawer();

        if (! $drawer) {
            return [];
        }

        $order = $this->getTodaysOrdersDetails();

        $drawerDetails = [
            'opening_amount'         => $openingAmount = $drawer->opening_amount,
            'cash_payment_sale'      => $cashPaymentSale = $order->cash_payment_sale ?? 0,
            'other_payment_sale'     => $otherPaymentSale = $order->other_payment_sale ?? 0,
            'expected_drawer_amount' => $totalSale = $openingAmount + $cashPaymentSale + $otherPaymentSale,
            'difference_amount'      => $totalSale - $openingAmount,
            'remark'                 => $drawer->remark,
        ];

        return $drawerDetails;
    }

    /**
     * Get the total sales of the cashier.
     */
    public function getTodaySales($rootValue, array $args, GraphQLContext $context): array
    {
        $drawer = $this->getTodaysDrawer();

        $order = $this->getTodaysOrdersDetails();

        $todayOrders = $this->posOrderRepository->getModel()
            ->leftJoin('orders', 'orders.id', '=', 'pos_order.order_id')
            ->leftJoin('order_payment', 'order_payment.order_id', '=', 'pos_order.order_id')
            ->whereDate('pos_order.created_at', today())
            ->where('user_id', auth()->guard('pos_user')->user()->id)

            ->when(! empty($args['order_id']), function ($query) use ($args) {
                return $query->where('pos_order.order_id', $args['order_id']);
            })
            ->when(! empty($args['time']), function ($query) use ($args) {
                return $query->where('pos_order.created_at', 'LIKE', "%{$args['time']}%");
            })
            ->when(! empty($args['order_total']), function ($query) use ($args) {
                return $query->where('orders.sub_total', $args['order_total']);
            })
            ->when(! empty($args['payment_mode']), function ($query) use ($args) {
                return $query->where('order_payment.method', $args['payment_mode']);
            })

            ->select(
                'pos_order.order_id',
                'order_payment.method_title as payment_mode',
                'orders.sub_total as order_total',
                'pos_order.created_at',
            )
            ->get();

        return [
            'opening_amount'     => core()->formatPrice($drawer->opening_amount ?? 0),
            'cash_payment_sale'  => core()->formatPrice($order->cash_payment_sale ?? 0),
            'other_payment_sale' => core()->formatPrice($order->other_payment_sale ?? 0),
            'orders'             => $todayOrders,
        ];
    }

    /**
     * Get sales history of the cashier.
     */
    public function getSaleHistory($rootValue, array $args, GraphQLContext $context): array
    {
        $salesHistory = $this->posOrderRepository->getModel()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(cash_total) as total_cash')
            ->selectRaw('SUM(card_total) as total_card')
            ->where('user_id', auth()->guard('pos_user')->user()->id)
            ->when(! empty($args['date']), function ($query) use ($args) {
                return $query->whereDate('created_at', $args['date']);
            })
            ->when(! empty($args['cash_payment']), function ($query) use ($args) {
                return $query->where('cash_total', $args['cash_payment']);
            })
            ->when(! empty($args['other_payment']), function ($query) use ($args) {
                return $query->where('card_total', $args['other_payment']);
            })
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->paginate(12);

        $orders = [];

        foreach ($salesHistory as $key => $sale) {
            $orders[$key]['created_at'] = core()->formatDate($sale->date, 'd F, Y');
            $orders[$key]['cash_payment'] = core()->formatPrice($sale->total_cash);
            $orders[$key]['other_payment'] = core()->formatPrice($sale->total_card);
            $orders[$key]['total_sale'] = core()->formatPrice($sale->total_cash + $sale->total_card);
            $orders[$key]['remark'] = 'N/A';
        }

        return $orders;
    }

    /**
     * Get today's drawer.
     */
    protected function getTodaysDrawer(): ?object
    {
        $drawer = $this->posDrawerRepository->getModel()
            ->whereDate('created_at', today())
            ->whereStatus(1)
            ->where('user_id', auth()->guard('pos_user')->user()->id)
            ->first();

        return $drawer;
    }

    /**
     * Get today's orders.
     */
    protected function getTodaysOrdersDetails(): ?object
    {
        $order = $this->posOrderRepository->getModel()
            ->whereDate('created_at', today())
            ->where('user_id', auth()->guard('pos_user')->user()->id)
            ->select(
                DB::raw('SUM(cash_total) as cash_payment_sale'),
                DB::raw('SUM(card_total) as other_payment_sale')
            )
            ->first();

        return $order;
    }
}
