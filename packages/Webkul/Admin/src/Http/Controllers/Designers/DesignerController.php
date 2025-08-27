<?php

namespace Webkul\Admin\Http\Controllers\Designers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Webkul\Admin\DataGrids\Customers\CustomerDataGrid;
use Webkul\Admin\DataGrids\Customers\View\InvoiceDataGrid;
use Webkul\Admin\DataGrids\Customers\View\OrderDataGrid;
use Webkul\Admin\DataGrids\Customers\View\ReviewDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Admin\Mail\Customer\NewCustomerNotification;


use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerNoteRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Designer\Models\Designer;
use Webkul\Designer\Models\DesignerImage;

class DesignerController extends Controller
{
    /**
     * Ajax request for orders.
     */
    public const ORDERS = 'orders';

    /**
     * Ajax request for invoices.
     */
    public const INVOICES = 'invoices';

    /**
     * Ajax request for reviews.
     */
    public const REVIEWS = 'reviews';

    /**
     * Static pagination count.
     *
     * @var int
     */
    public const COUNT = 10;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository,
        protected CustomerNoteRepository $customerNoteRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $designers = Designer::with(['products', 'images'])->get();

        return view('admin::designers.designers.index', compact('designers'));
    }

    public function create()
    {

        return view('admin::designers.designers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {

        $validatedData = request()->validate([
            'logo_path'     => 'required',
            'banner_path'   => 'nullable',
            'name'          => 'string|required|unique:designers,name',
            'email'         => 'required|unique:designers,email',
            'phone'         => 'unique:designers,phone',
            'description'   => 'string|nullable',
            'website'       => 'url|nullable',
            'instagram'     => 'url|nullable',
            'facebook'      => 'url|nullable',
            'twitter'       => 'url|nullable',
            'pinterest'     => 'url|nullable',
            'linkedin'      => 'url|nullable',
            'youtube'       => 'url|nullable',
            'status'        => 'required|in:1,0',
        ]);

        // add slug
        // $validatedData['slug'] = Str::slug($validatedData['name']);
        // manually
        $validatedData['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $validatedData['name'])));
        $validatedData['slug'] = rtrim($validatedData['slug'], '-');

        $designer = Designer::create($validatedData);

        // return new JsonResponse([
        //     'data'    => $designer,
        //     'message' => trans('admin::app.designers.designers.index.create.create-success'),
        // ]);

        if ($designer) {

            $logoPath = request()->file('logo_path')->store('designer_images', 'public');

            if (request()->has('banner_path') && request()->file('banner_path') != null) {
                $bannerPath = request()->file('banner_path')->store('designer_images', 'public');
            }

            $designerImage = new DesignerImage();
            $designerImage->designer_id = $designer->id;
            $designerImage->src = $logoPath;
            $designerImage->alt = "logo_path";
            $designerImage->save();

            $designerImage = new DesignerImage();
            $designerImage->designer_id = $designer->id;
            $designerImage->src = $bannerPath;
            $designerImage->alt = "banner_path";
            $designerImage->save();


            return redirect()->route('admin.designers.designers.index')->with('success', 'Designer created successfully');
        }

        return redirect()->route('admin.designers.designers.index')->with('error', 'Designer creation failed');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(int $id)
    {
        // return  request()->all();
        $validator = Validator::make(request()->all(), [
            'logo_path'     => 'nullable',
            'banner_path'   => 'nullable',
            'name'          => 'string|required|unique:designers,name,' . $id,
            'email'         => 'required|unique:designers,email,' . $id,
            'phone'         => 'unique:designers,phone,' . $id,
            'description'   => 'string|nullable',
            'website'       => 'url|nullable',
            'instagram'     => 'url|nullable',
            'facebook'      => 'url|nullable',
            'twitter'       => 'url|nullable',
            'pinterest'     => 'url|nullable',
            'linkedin'      => 'url|nullable',
            'youtube'       => 'url|nullable',
            'status'        => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $validatedData = $validator->validated();

        // add slug
        // $validatedData['slug'] = Str::slug($validatedData['name']);
        // manually
        $validatedData['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $validatedData['name'])));
        $validatedData['slug'] = rtrim($validatedData['slug'], '-');

        $designer = Designer::find($id);

        if (!$designer) {
            return redirect()->back()->with(['error' => 'Designer not found'], 404);
        }

        $designer->update($validatedData);

        if (request()->has('logo_path') && request()->file('logo_path') != null) {
            $logoPath = request()->file('logo_path')->store('designer_images', 'public');
            $designerImage = new DesignerImage();
            $designerImage->designer_id = $designer->id;
            $designerImage->src = $logoPath;
            $designerImage->alt = "logo_path";
            $designerImage->save();
        }

        if (request()->has('banner_path') && request()->file('banner_path') != null) {
            $bannerPath = request()->file('banner_path')->store('designer_images', 'public');
            $designerImage = new DesignerImage();
            $designerImage->designer_id = $designer->id;
            $designerImage->src = $bannerPath;
            $designerImage->alt = "banner_path";
            $designerImage->save();
        }

        // return response()->json([
        //     'message' => 'Designer updated successfully',
        //     'data'    => $designer->fresh(),
        // ]);

        if ($designer) {
            return redirect()->back()->with('success', 'Designer updated successfully');
        }

        return redirect()->back()->with('error', 'Designer update failed');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $customer = $this->customerRepository->findorFail($id);

        if (! $customer) {
            return response()->json(['message' => trans('admin::app.customers.customers.delete-failed')], 400);
        }

        if (! $this->customerRepository->haveActiveOrders($customer)) {

            $this->customerRepository->delete($id);

            session()->flash('success', trans('admin::app.customers.customers.delete-success'));

            return redirect()->route('admin.customers.customers.index');
        }

        session()->flash('error', trans('admin::app.customers.customers.view.order-pending'));

        return redirect()->route('admin.customers.customers.index');
    }

    /**
     * Login as customer
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAsCustomer(int $id)
    {
        $customer = $this->customerRepository->findOrFail($id);

        auth()->guard('customer')->login($customer);

        session()->flash('success', trans('admin::app.customers.customers.index.login-message', ['customer_name' => $customer->name]));

        return redirect(route('shop.customers.account.profile.index'));
    }

    /**
     * To store the response of the note.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeNotes(int $id)
    {
        $this->validate(request(), [
            'note' => 'string|required',
        ]);

        Event::dispatch('customer.note.create.before', $id);

        $customerNote = $this->customerNoteRepository->create([
            'customer_id'       => $id,
            'note'              => request()->input('note'),
            'customer_notified' => request()->input('customer_notified', 0),
        ]);

        Event::dispatch('customer.note.create.after', $customerNote);

        session()->flash('success', trans('admin::app.customers.customers.view.note-created-success'));

        return redirect()->route('admin.customers.customers.view', $id);
    }

    /**
     * View all details of customer.
     */
    public function show(int $id)
    {
        $designer = Designer::findOrFail($id);

        if (request()->ajax()) {
            switch (request()->query('type')) {
                case self::ORDERS:
                    return datagrid(OrderDataGrid::class)->process();

                case self::INVOICES:
                    return datagrid(InvoiceDataGrid::class)->process();

                case self::REVIEWS:
                    return datagrid(ReviewDataGrid::class)->process();
            }
        }


        $products = $designer->products()->paginate(5);

        return view('admin::designers.designers.edit', compact('designer', 'products'));
    }

    /**
     * Result of search customer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        $customers = $this->customerRepository->scopeQuery(function ($query) {
            return $query->where('email', 'like', '%' . urldecode(request()->input('query')) . '%')
                ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', '%' . urldecode(request()->input('query')) . '%')
                ->orderBy('created_at', 'desc');
        })->paginate(self::COUNT);

        return response()->json($customers);
    }

    /**
     * To mass update the customer.
     */
    public function massUpdate(MassUpdateRequest $massUpdateRequest): JsonResponse
    {
        $selectedCustomerIds = $massUpdateRequest->input('indices');

        foreach ($selectedCustomerIds as $customerId) {
            Event::dispatch('customer.update.before', $customerId);

            $customer = $this->customerRepository->update([
                'status' => $massUpdateRequest->input('value'),
            ], $customerId);

            Event::dispatch('customer.update.after', $customer);
        }

        return new JsonResponse([
            'message' => trans('admin::app.customers.customers.index.datagrid.update-success'),
        ]);
    }

    /**
     * To mass delete the customer.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $customers = $this->customerRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        try {
            /**
             * Ensure that customers do not have any active orders before performing deletion.
             */
            foreach ($customers as $customer) {
                if ($this->customerRepository->haveActiveOrders($customer)) {
                    throw new \Exception(trans('admin::app.customers.customers.index.datagrid.order-pending'));
                }
            }

            /**
             * After ensuring that they have no active orders delete the corresponding customer.
             */
            foreach ($customers as $customer) {
                Event::dispatch('customer.delete.before', $customer);

                $this->customerRepository->delete($customer->id);

                Event::dispatch('customer.delete.after', $customer);
            }

            return new JsonResponse([
                'message' => trans('admin::app.customers.customers.index.datagrid.delete-success'),
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
