<?php

namespace Webkul\Alteration\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Webkul\Alteration\Models\AlterationPartner;
use Webkul\Alteration\Services\PartnerLocator;
use Webkul\Shop\Http\Controllers\Controller;

class LocatorController extends Controller
{
    public function __construct(protected PartnerLocator $locator) {}

    /**
     * Public "Find an alteration partner" page.
     */
    public function index()
    {
        return view('alteration::shop.locator.index');
    }

    /**
     * Nearest vetted, live partners for a country + city. Only public fields are exposed.
     */
    public function search(): JsonResponse
    {
        $data = request()->validate([
            'country' => ['required', 'string', 'size:2'],
            'city'    => ['nullable', 'string', 'max:120'],
        ]);

        $partners = $this->locator->search($data['country'], $data['city'] ?? null);

        return new JsonResponse([
            'data' => $partners->map(fn (AlterationPartner $partner) => [
                'id'           => $partner->id,
                'name'         => $partner->name,
                'city'         => $partner->city,
                'address'      => $partner->address,
                'phone'        => $partner->phone,
                'whatsapp'     => $partner->whatsapp,
                'whatsapp_url' => $partner->whatsapp ? 'https://wa.me/'.preg_replace('/\D+/', '', $partner->whatsapp) : null,
                'email'        => $partner->email,
                'website'      => $partner->website,
                'conditions'   => $partner->side_conditions,
                'same_city'    => $partner->same_city,
                'distance_km'  => $partner->distance_km,
                'prices'       => $partner->prices
                    ->filter(fn ($price) => $price->fixType)
                    ->sortBy(fn ($price) => $price->fixType->sort)
                    ->map(fn ($price) => [
                        'fix_type'        => $price->fixType->name,
                        'price'           => core()->formatPrice((float) $price->price, $partner->currency ?: core()->getBaseCurrencyCode()),
                        'turnaround_days' => $price->turnaround_days,
                    ])
                    ->values(),
            ]),
        ]);
    }
}
