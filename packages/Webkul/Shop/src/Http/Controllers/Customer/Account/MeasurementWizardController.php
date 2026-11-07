<?php

namespace Webkul\Shop\Http\Controllers\Customer\Account;

use Illuminate\Http\RedirectResponse;
use Webkul\Customer\Services\MeasurementWizardService;
use Webkul\Shop\Http\Controllers\Controller;

class MeasurementWizardController extends Controller
{
    public function __construct(
        protected MeasurementWizardService $wizardService
    ) {}

    /**
     * Launch the standalone Smart Measurement wizard.
     *
     * Builds a short-lived signed token for the current customer and redirects
     * the browser to the measurement service. On completion the service posts
     * the finished profile back to our signed callback endpoint.
     */
    public function launch(): RedirectResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! config('measurement_service.url')) {
            session()->flash('error', 'The Smart Measurement tool is not configured yet.');

            return redirect()->route('shop.customers.account.measurements.index');
        }

        $returnUrl = request()->query('redirect')
            ?: route('shop.customers.account.measurements.index');

        $profileId = (int) request()->query('profile') ?: null;

        $url = $this->wizardService->launchUrl($customer, $profileId, $returnUrl);

        return redirect()->away($url);
    }
}
