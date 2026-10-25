<?php

namespace Webkul\Alteration\Http\Controllers\Shop;

use Illuminate\Support\Facades\Mail;
use Webkul\Alteration\Http\Requests\StoreClaimRequest;
use Webkul\Alteration\Mail\AdminNewClaimNotification;
use Webkul\Alteration\Mail\CustomerClaimNotification;
use Webkul\Alteration\Models\AlterationClaim;
use Webkul\Alteration\Repositories\AlterationClaimRepository;
use Webkul\Alteration\Services\ClaimEligibility;
use Webkul\Customer\Data\MeasurementFields;
use Webkul\Sales\Models\Order;
use Webkul\Shop\Http\Controllers\Controller;

class ClaimController extends Controller
{
    public function __construct(
        protected AlterationClaimRepository $claimRepository,
        protected ClaimEligibility $eligibility
    ) {}

    /**
     * The customer's alteration requests.
     */
    public function index()
    {
        $claims = AlterationClaim::query()
            ->where('customer_id', auth()->guard('customer')->id())
            ->with('order')
            ->latest('id')
            ->get();

        return view('alteration::shop.claims.index', compact('claims'));
    }

    public function view(int $id)
    {
        $claim = AlterationClaim::query()
            ->where('customer_id', auth()->guard('customer')->id())
            ->with(['order', 'items.orderItem', 'images', 'partner.prices.fixType'])
            ->findOrFail($id);

        return view('alteration::shop.claims.view', compact('claim'));
    }

    /**
     * Claim form: confirmed measurements per item next to inputs for the garment's measurements.
     */
    public function create(int $orderId)
    {
        $order = $this->findCustomerOrder($orderId);

        $eligibility = $this->eligibility->check($order);

        if (! $eligibility['eligible']) {
            session()->flash('warning', trans('alteration::app.shop.claims.errors.'.$eligibility['reason']));

            return redirect()->route('shop.customers.account.orders.view', $order->id);
        }

        $measurementsByItem = $order->measurements()
            ->orderBy('id')
            ->get()
            ->groupBy('order_item_id');

        return view('alteration::shop.claims.create', [
            'order'              => $order,
            'deadline'           => $eligibility['deadline'],
            'measurementsByItem' => $measurementsByItem,
            'items'              => $order->items->keyBy('id'),
            'labels'             => MeasurementFields::labelMap(),
        ]);
    }

    public function store(StoreClaimRequest $request, int $orderId)
    {
        $order = $this->findCustomerOrder($orderId);

        $claim = $this->claimRepository->createForOrder(
            $order,
            $request->only(['reason', 'description', 'garment']),
            $request->file('photos', [])
        );

        $claim->load('order');

        try {
            Mail::queue(new AdminNewClaimNotification($claim));

            Mail::queue(new CustomerClaimNotification($claim, 'received'));
        } catch (\Exception $e) {
            report($e);
        }

        session()->flash('success', trans('alteration::app.shop.claims.submit-success'));

        return redirect()->route('shop.customers.account.alterations.view', $claim->id);
    }

    protected function findCustomerOrder(int $orderId): Order
    {
        return Order::query()
            ->where('customer_id', auth()->guard('customer')->id())
            ->findOrFail($orderId);
    }
}
