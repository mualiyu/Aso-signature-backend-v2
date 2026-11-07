<?php

namespace Webkul\Alteration\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Webkul\Alteration\Models\AlterationPartner;

/**
 * Finds vetted, live alteration partners nearest to a country + city, without any external
 * geocoding: the city's position is the average of partners already in that city, falling back to
 * the known hub coordinates in config('alteration.cities').
 */
class PartnerLocator
{
    /**
     * @return Collection<int, AlterationPartner> each with `same_city` and `distance_km` set
     */
    public function search(string $country, ?string $city = null, int $limit = 10): Collection
    {
        $country = strtoupper(trim($country));

        $city = $this->normaliseCity($city);

        $partners = AlterationPartner::query()
            ->listable()
            ->where('country', $country)
            ->with(['prices.fixType' => fn ($query) => $query->where('is_active', true)])
            ->get();

        $origin = $city ? $this->cityPosition($country, $city, $partners) : null;

        return $partners
            ->each(function (AlterationPartner $partner) use ($city, $origin) {
                $partner->same_city = $city !== null && $this->normaliseCity($partner->city) === $city;

                $partner->distance_km = $origin && $partner->latitude !== null && $partner->longitude !== null
                    ? round($this->haversine($origin[0], $origin[1], $partner->latitude, $partner->longitude), 1)
                    : null;
            })
            ->sort(function (AlterationPartner $a, AlterationPartner $b) {
                return [$b->same_city, $a->distance_km === null, $a->distance_km, $a->name]
                    <=> [$a->same_city, $b->distance_km === null, $b->distance_km, $b->name];
            })
            ->take($limit)
            ->values();
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    protected function cityPosition(string $country, string $city, Collection $partners): ?array
    {
        $inCity = $partners->filter(fn ($partner) => $this->normaliseCity($partner->city) === $city
            && $partner->latitude !== null
            && $partner->longitude !== null);

        if ($inCity->isNotEmpty()) {
            return [$inCity->avg('latitude'), $inCity->avg('longitude')];
        }

        $known = config('alteration.cities.'.$country.'.'.Str::slug($city, '_'));

        return $known ? [(float) $known[0], (float) $known[1]] : null;
    }

    protected function normaliseCity(?string $city): ?string
    {
        $city = Str::lower(trim((string) $city));

        return $city === '' ? null : $city;
    }

    /**
     * Great-circle distance in kilometres.
     */
    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
