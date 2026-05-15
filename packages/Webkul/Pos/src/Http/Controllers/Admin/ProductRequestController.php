<?php

namespace Webkul\Pos\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Pos\DataGrids\Admin\ProductRequestDataGrid;
use Webkul\Pos\Repositories\PosProductRequestRepository;
use Webkul\Product\Helpers\ProductType;
use Webkul\Product\Repositories\ProductInventoryRepository;

class ProductRequestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductInventoryRepository $productInventoryRepository,
        protected PosProductRequestRepository $posProductRequestRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ProductRequestDataGrid::class)->process();
        }

        return view('pos::admin.requests.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function view(int $id)
    {
        $productRequest = $this->posProductRequestRepository
            ->with([
                'product',
                'user' => [
                    'outlet' => [
                        'inventory_source',
                    ],
                ],
            ])
            ->findOrFail($id);

        return view('pos::admin.requests.view')
            ->with('productRequest', $productRequest);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(int $id)
    {
        request()->validate([
            'request_status' => 'required|integer',
        ]);

        if ($this->updateInventory(request()->input('request_status'), $id)) {
            session()->flash('success', trans('pos::app.admin.requests.update-success'));
        } else {
            session()->flash('error', trans('pos::app.admin.requests.update-failed'));
        }

        return to_route('admin.pos.requests.index');
    }

    /**
     * Mass Update
     */
    public function massUpdate(MassUpdateRequest $request): JsonResponse
    {
        try {
            $records_count = 0;

            foreach ($request->input('indices') as $request_id) {
                if ($this->updateInventory($request->input('value'), $request_id)) {
                    $records_count += 1;
                }
            }

            if ($records_count > 0) {
                return new JsonResponse([
                    'message' => trans('pos::app.admin.requests.index.datagrid.mass-update-success'),
                ]);
            }

            return new JsonResponse([
                'message' => trans('pos::app.admin.requests.index.datagrid.mass-update-error'),
            ], 500);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update inventory based on request status
     */
    public function updateInventory(int $option, int $id): bool
    {
        $count = 0;

        $productRequest = $this->posProductRequestRepository
            ->with([
                'product',
                'user' => [
                    'outlet',
                ],
            ])
            ->findOrFail($id);

        $lowStock = $productRequest->user->outlet->low_stock_qty ?? 10;

        $product = $productRequest->product;

        $productArray = [$product];

        if (ProductType::hasVariants($product->type)) {
            $productArray = $product->variants()->get();
        }

        foreach ($productArray as $product) {
            $productInventory = $this->productInventoryRepository->findOneWhere([
                'product_id'          => $product->id,
                'inventory_source_id' => $productRequest->user->outlet->inventory_source_id,
            ]);

            if (
                ! $productInventory
                || $productInventory->qty > $lowStock
            ) {
                continue;
            }

            // Complete Request
            if (
                $option == 1
                && $productRequest->request_status != 1
            ) {
                $productInventory->qty += $productRequest->requested_quantity;
                $productInventory->save();

                $productRequest->request_status = 1;
                $productRequest->save();

                $count = 1;
            }

            // Decline Request
            if (
                $option == 2
                && $productRequest->request_status != 1
            ) {
                $productRequest->request_status = 2;
                $productRequest->save();

                $count = 1;
            }

            // Pending Request
            if (
                $option == 0
                && $productRequest->request_status == 2
            ) {
                $productRequest->request_status = 0;
                $productRequest->save();

                $count = 1;
            }
        }

        return $count ? true : false;
    }
}
