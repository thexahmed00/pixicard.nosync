<?php

namespace Webkul\Marketplace\Http\Controllers\Seller;

use Illuminate\Http\Request;
use Webkul\Pos\Repositories\PosUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Marketplace\DataGrids\Seller\PosAgentDataGrid;
use Webkul\Pos\Repositories\PosOutletRepository;
use Webkul\Pos\Http\Requests\UserForm;
use Webkul\Pos\Mail\NewRegistrationNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PosUserController extends Controller
{
    protected $posUserRepository;

    public function __construct(PosUserRepository $posUserRepository,PosOutletRepository $posOutletRepository)
    {
        $this->posUserRepository = $posUserRepository;
        $this->posOutletRepository =  $posOutletRepository;
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return datagrid(PosAgentDataGrid::class)->process();
        }

        return view('marketplace::pos_users.index');
       
    }

    public function create()
    {
        $seller_email = auth()->guard('seller')->user()->email;
        
        $outlets = $this->posOutletRepository->where('email', $seller_email)->get();
        
        return view('marketplace::pos_users.create')
            ->with('outlets', $outlets);
    }

    public function store(UserForm $request)
    {
        $data = $request->validated();

        $data['created_by'] = auth()->guard('seller')->id();

        $posUser = $this->posUserRepository->create(Arr::except($data, 'image'));
        
        if (request()->hasFile('image')) {
        $file   = $request->file('image');
        $folder = "pos/{$posUser->id}";

        // Generate random hashed filename
        $filename = $file->hashName();

        // Store file in storage/app/public/seller/{id}/
        $path = $file->storeAs($folder, $filename, 'public');

        $posUser->image = $path;

       // $posUser->image = current(request()->file('image'))->store('pos/'.$posUser->id);
        $posUser->save();

        }

        try {
            Mail::send(new NewRegistrationNotification($posUser));
        } catch (\Exception $e) {
        }

        return redirect()->route('seller.pos_users.index')->with('success', trans('pos::app.admin.users.users.create-success'));
    }

    public function edit($id)
    {
        $posUser = $this->posUserRepository->findOrFail($id);

        $seller_email = auth()->guard('seller')->user()->email;
        
        $outlets = $this->posOutletRepository->where('email', $seller_email)->get();

        return view('marketplace::pos_users.edit')
            ->with([
                'posUser' => $posUser,
                'outlets' => $outlets,
            ]);
    }

    public function update(UserForm $request, int $id)
    {

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $posUser = $this->posUserRepository->update(Arr::except($data, 'image'), $id);

        if (request()->hasFile('image')) {
            $file   = $request->file('image');
        $folder = "pos/{$posUser->id}";

        // Generate random hashed filename
        $filename = $file->hashName();

        // Store file in storage/app/public/seller/{id}/
        $path = $file->storeAs($folder, $filename, 'public');
        $posUser->image = $path;
           // $posUser->image = current(request()->file('image'))->store("pos/{$posUser->id}");
        } else {
            if (! request()->has('image.image')) {
                if (! empty(request()->input('image.image'))) {
                    Storage::delete($posUser->image);
                }

                $posUser->image = null;
            }
        }
        
        //$posUser->created_by = auth()->guard('admin')->id();

        $posUser->save();


        return redirect()->route('seller.pos_users.index')->with('success', trans('pos::app.admin.users.users.update-success'));
    }

    public function destroy($id)
    {
           try {
            $this->posUserRepository->delete($id);

            return new JsonResponse([
                'message' => trans('pos::app.admin.users.users.delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('pos::app.admin.users.users.delete-failed'),
            ], 500);
        }
        
    }


}
