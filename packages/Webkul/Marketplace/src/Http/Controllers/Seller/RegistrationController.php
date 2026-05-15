<?php

namespace Webkul\Marketplace\Http\Controllers\Seller;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Core\Rules\Slug;
use Webkul\Marketplace\Repositories\SellerRepository;

class RegistrationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected SellerRepository $sellerRepository) {}

    /**
     * Display the registration form.
     */
    public function index(): View
    {
        return view('marketplace::seller.sign-up');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required'],
            'shop_title'     => ['required'],
            'phone'    => ['required', 'unique:marketplace_sellers,phone'],
            'email'    => ['required', 'email', 'unique:marketplace_sellers,email'],
            'url'      => ['required', 'unique:marketplace_sellers,url', 'lowercase', new Slug],
            'password' => ['required', 'confirmed', 'min:6'],
            //'certification'  => ['required', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

       // $this->sellerRepository->create(array_merge($validated, [
        //    'is_approved' => ! core()->getConfigData('marketplace.settings.seller.approval_required'),
       // ]));
       $seller = $this->sellerRepository->create(array_merge($validated, [
        'is_approved'   => ! core()->getConfigData('marketplace.settings.seller.approval_required'),
        'certification' => null,
        ]));

    // Handle file upload
    if ($request->hasFile('certification')) {
        $file   = $request->file('certification');
        $folder = "seller/{$seller->id}";

        // Generate random hashed filename
        $filename = $file->hashName();

        // Store file in storage/app/public/seller/{id}/
        $path = $file->storeAs($folder, $filename, 'public');

        // Save relative path in DB
        $seller->update([
            'certification' => $path,
        ]);
    }

        return to_route('seller.session.index')
            ->withSuccess(trans('marketplace::app.seller.signup.success'));
    }
}
