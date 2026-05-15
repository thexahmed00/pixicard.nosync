<?php

namespace Webkul\Pos\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Pos\DataGrids\Admin\ReceiptDataGrid;
use Webkul\Pos\Repositories\PosReceiptRepository;

class ReceiptController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected PosReceiptRepository $posReceiptRepository) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ReceiptDataGrid::class)->process();
        }

        return view('pos::admin.receipts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('pos::admin.receipts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        request()->validate([
            'title'          => 'required',
            'header_content' => 'required',
            'footer_content' => 'required',
        ]);

        $this->posReceiptRepository->create(request()->all());

        return to_route('admin.pos.receipts.index')
            ->with('success', trans('pos::app.admin.receipts.create-success'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $receipt = $this->posReceiptRepository->find($id);

        return view('pos::admin.receipts.edit')
            ->with('receipt', $receipt);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function show(int $id)
    {
        $posReceipt = $this->posReceiptRepository
            ->with([
                'outlet' => [
                    'pos_user',
                ],
            ])
            ->find($id);

        return view('pos::admin.receipts.preview')
            ->with('posReceipt', $posReceipt);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(int $id)
    {
        $this->posReceiptRepository->update(request()->all(), $id);

        return to_route('admin.pos.receipts.index')
            ->with('success', trans('pos::app.admin.receipts.update-success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->posReceiptRepository->delete($id);

            return new JsonResponse([
                'message' => trans('pos::app.admin.receipts.delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the resources from storage.
     */
    public function massDestroy(MassDestroyRequest $request): JsonResponse
    {
        try {
            $this->posReceiptRepository->whereIn('id', $request->input('indices'))->delete();

            return new JsonResponse([
                'message' => trans('pos::app.admin.receipts.index.datagrid.mass-delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
