<?php

namespace Webkul\Admin\Http\Controllers\Sales;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Admin\DataGrids\Sales\OrderShipmentDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\ShipmentRepository;
use Webkul\Shipping\Services\DHLShipmentService;
use Webkul\Shipping\Services\DhlShipmentCancellationService;
use Webkul\Shipping\Services\DhlTrackingService;

class ShipmentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderItemRepository $orderItemRepository,
        protected ShipmentRepository $shipmentRepository,
        protected DHLShipmentService $dhlShipmentService,
        protected DhlTrackingService $dhlTrackingService,
        protected DhlShipmentCancellationService $dhlShipmentCancellationService
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(OrderShipmentDataGrid::class)->process();
        }

        return view('admin::sales.shipments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(int $orderId)
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if (! $order->channel || ! $order->canShip()) {
            session()->flash('error', trans('admin::app.sales.shipments.create.creation-error'));

            return redirect()->back();
        }

        return view('admin::sales.shipments.create', compact('order'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(int $orderId)
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if (! $order->canShip()) {
            session()->flash('error', trans('admin::app.sales.shipments.create.order-error'));

            return redirect()->back();
        }

        $this->validate(request(), [
            'shipment.source'    => 'required',
            'shipment.items.*.*' => 'required|numeric|min:0',
        ]);

        $data = request()->only(['shipment', 'carrier_name']);

        if (! $this->isInventoryValidate($data)) {
            session()->flash('error', trans('admin::app.sales.shipments.create.quantity-invalid'));

            return redirect()->back();
        }

        $carrierCode = $data['shipment']['carrier_code'] ?? null;
        $dhlDocumentsMissing = false;

        if ($carrierCode === 'dhl') {
            $dhlResult = $this->dhlShipmentService->createShipment($order, $data['shipment']);

            if (! $dhlResult['success']) {
                session()->flash('error', 'DHL Shipment Error: '.($dhlResult['error'] ?? 'Unknown error'));

                return redirect()->back();
            }

            $data['shipment']['track_number'] = $dhlResult['data']['tracking_number'] ?? null;
            $data['shipment']['carrier_title'] = 'DHL Express';
            $data['shipment']['dhl_documents_path'] = $dhlResult['data']['dhl_documents_path'] ?? null;
            $data['shipment']['dhl_pickup_confirmation_number'] = $dhlResult['data']['dhl_pickup_confirmation_number'] ?? null;

            $dhlDocumentsMissing = ! empty($data['shipment']['track_number'])
                && empty($data['shipment']['dhl_documents_path']);
        }

        $shipment = $this->shipmentRepository->create(array_merge($data, [
            'order_id' => $orderId,
        ]));

        if ($carrierCode === 'dhl' && ! empty($shipment->track_number)) {
            $this->dhlTrackingService->refreshShipment($shipment);
        }

        session()->flash('success', trans('admin::app.sales.shipments.create.success'));

        if ($dhlDocumentsMissing) {
            session()->flash(
                'warning',
                trans('admin::app.sales.shipments.create.dhl-documents-warning')
            );
        }

        return redirect()->route('admin.sales.orders.view', $orderId);
    }

    /**
     * Checks if requested quantity available or not.
     *
     * @param  array  $data
     * @return bool
     */
    public function isInventoryValidate(&$data)
    {
        if (! isset($data['shipment']['items'])) {
            return;
        }

        $valid = false;

        $inventorySourceId = $data['shipment']['source'];

        foreach ($data['shipment']['items'] as $itemId => $inventorySource) {
            $qty = $inventorySource[$inventorySourceId];

            if ((int) $qty) {
                $orderItem = $this->orderItemRepository->find($itemId);

                if ($orderItem->qty_to_ship < $qty) {
                    return false;
                }

                if ($orderItem->getTypeInstance()->isComposite()) {
                    foreach ($orderItem->children as $child) {
                        if (! $child->qty_ordered) {
                            continue;
                        }

                        $finalQty = ($child->qty_ordered / $orderItem->qty_ordered) * $qty;

                        $availableQty = $child->product->inventories()
                            ->where('inventory_source_id', $inventorySourceId)
                            ->sum('qty');

                        if (
                            $child->qty_to_ship < $finalQty
                            || $availableQty < $finalQty
                        ) {
                            return false;
                        }
                    }
                } else {
                    $availableQty = $orderItem->product->inventories()
                        ->where('inventory_source_id', $inventorySourceId)
                        ->sum('qty');

                    if (
                        $orderItem->qty_to_ship < $qty
                        || $availableQty < $qty
                    ) {
                        return false;
                    }
                }

                $valid = true;
            } else {
                unset($data['shipment']['items'][$itemId]);
            }
        }

        return $valid;
    }

    /**
     * Show the view for the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function view(int $id)
    {
        $shipment = $this->shipmentRepository->findOrFail($id);

        return view('admin::sales.shipments.view', compact('shipment'));
    }

    /**
     * Download DHL shipping documents PDF generated at shipment creation.
     */
    public function downloadDhlDocuments(int $id): Response|RedirectResponse
    {
        $shipment = $this->shipmentRepository->findOrFail($id);

        return $this->downloadDhlDocumentsForShipment($shipment);
    }

    /**
     * Refresh DHL tracking status for a shipment.
     */
    public function refreshDhlTracking(int $id): RedirectResponse
    {
        $shipment = $this->shipmentRepository->findOrFail($id);

        if (! $this->isDhlShipment($shipment)) {
            session()->flash('error', trans('admin::app.sales.shipments.view.dhl-tracking-not-applicable'));

            return redirect()->back();
        }

        $result = $this->dhlTrackingService->refreshShipment($shipment);

        if (! $result['success']) {
            session()->flash('error', trans('admin::app.sales.shipments.view.dhl-tracking-error', [
                'message' => $result['error'] ?? 'Unknown error',
            ]));

            return redirect()->back();
        }

        session()->flash('success', trans('admin::app.sales.shipments.view.dhl-tracking-refreshed'));

        return redirect()->back();
    }

    /**
     * Cancel a DHL shipment: cancel the pickup at DHL (when one was booked), reverse all
     * local side-effects (inventory, qty_shipped, order status) and delete the shipment
     * so a corrected one can be created.
     */
    public function cancel(int $id): RedirectResponse
    {
        $shipment = $this->shipmentRepository->findOrFail($id);

        if (! $this->isDhlShipment($shipment)) {
            session()->flash('error', trans('admin::app.sales.shipments.view.cancel-not-applicable'));

            return redirect()->back();
        }

        $eligibility = $this->dhlShipmentCancellationService->checkCancellable($shipment);

        if (! $eligibility['cancellable']) {
            session()->flash('error', $eligibility['reason'] ?? trans('admin::app.sales.shipments.view.cancel-tracking-unverified', ['message' => 'Unknown error']));

            return redirect()->back();
        }

        $dhlResult = $this->dhlShipmentCancellationService->cancelAtDhl($shipment);

        $orderId = $shipment->order_id;
        $trackNumber = $shipment->track_number;
        $documentsPath = $shipment->dhl_documents_path;

        $this->shipmentRepository->cancel($shipment);

        if ($documentsPath) {
            try {
                Storage::disk('local')->delete($documentsPath);
            } catch (\Throwable $e) {
                Log::warning('DHL shipment cancel: could not delete documents PDF', [
                    'shipment_id' => $id,
                    'path'        => $documentsPath,
                    'message'     => $e->getMessage(),
                ]);
            }
        }

        Log::info('DHL shipment cancelled', [
            'admin_id'          => auth()->guard('admin')->id(),
            'shipment_id'       => $id,
            'order_id'          => $orderId,
            'tracking'          => $trackNumber,
            'dhl_pickup_cancel' => $dhlResult,
        ]);

        session()->flash('success', trans('admin::app.sales.shipments.view.cancel-success'));

        if (! $dhlResult['success']) {
            session()->flash('warning', trans('admin::app.sales.shipments.view.cancel-pickup-warning', [
                'message' => $dhlResult['error'] ?? 'Unknown error',
            ]));
        }

        return redirect()->route('admin.sales.orders.view', $orderId);
    }

    /**
     * @param  \Webkul\Sales\Contracts\Shipment  $shipment
     */
    protected function downloadDhlDocumentsForShipment($shipment): Response|RedirectResponse
    {
        if (empty($shipment->dhl_documents_path)) {
            session()->flash('error', trans('admin::app.sales.shipments.view.dhl-documents-missing'));

            return redirect()->back();
        }

        $path = $shipment->dhl_documents_path;

        if (! Storage::disk('local')->exists($path)) {
            session()->flash('error', trans('admin::app.sales.shipments.view.dhl-documents-missing'));

            return redirect()->back();
        }

        return Storage::disk('local')->download($path, 'dhl-shipment-'.$shipment->id.'.pdf');
    }

    /**
     * @param  \Webkul\Sales\Contracts\Shipment  $shipment
     */
    protected function isDhlShipment($shipment): bool
    {
        return $shipment->carrier_code === 'dhl'
            || $shipment->carrier_title === 'DHL Express'
            || str_starts_with(strtolower((string) ($shipment->carrier_code ?? '')), 'dhl');
    }
}
