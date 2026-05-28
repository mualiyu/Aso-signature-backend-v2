<?php

namespace Webkul\Shipping\Services;

use Carbon\Carbon;

/**
 * Builds plannedShippingDateAndTime for MyDHL from admin configuration.
 */
class DhlPlannedShippingDate
{
    /**
     * MyDHL format: 2026-05-27T10:00:00 GMT+01:00
     */
    public static function formatForApi(): string
    {
        $date = static::resolve();

        return $date->format('Y-m-d\TH:i:s').' GMT'.$date->format('P');
    }

    public static function resolve(): Carbon
    {
        return (new self)->compute();
    }

    protected function config(string $key, $default = null)
    {
        return core()->getConfigData('sales.carriers.dhl.'.$key) ?? $default;
    }

    public function compute(): Carbon
    {
        $strategy = (string) $this->config('pickup_date_strategy', 'auto');
        $cutoffHour = max(0, min(23, (int) $this->config('pickup_cutoff_hour', 15)));
        $offsetDays = max(0, min(30, (int) $this->config('pickup_offset_days', 0)));
        $skipWeekends = $this->configBool('pickup_skip_weekends', true);
        $businessDaysOnly = $this->configBool('pickup_offset_business_days', true);
        [$hour, $minute] = $this->parsePickupTime((string) $this->config('pickup_time', '10:00'));

        $date = $this->baseDate();

        switch ($strategy) {
            case 'today':
                break;

            case 'next_business_day':
                $date = $date->copy()->addDay();
                break;

            case 'offset_days':
                $date = $this->addDays($date, max(1, $offsetDays), $businessDaysOnly);
                $offsetDays = 0;
                break;

            case 'auto':
            default:
                if ($date->hour >= $cutoffHour) {
                    $date = $date->copy()->addDay();
                }
                break;
        }

        if ($offsetDays > 0) {
            $date = $this->addDays($date, $offsetDays, $businessDaysOnly);
        }

        if ($skipWeekends) {
            $date = $this->skipWeekends($date);
        }

        $date = $date->copy()->setTime($hour, $minute, 0);

        // DHL requires a future pickup moment.
        if ($date->lte(now())) {
            $date = $this->skipWeekends($date->copy()->addDay())->setTime($hour, $minute, 0);
        }

        return $date;
    }

    protected function baseDate(): Carbon
    {
        $tz = trim((string) $this->config('pickup_timezone', ''));

        if ($tz !== '') {
            try {
                return now($tz);
            } catch (\Exception) {
                // Fall through to app timezone.
            }
        }

        return now();
    }

    protected function configBool(string $key, bool $default): bool
    {
        $value = $this->config($key, $default);

        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function parsePickupTime(string $time): array
    {
        $time = trim($time);

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $m)) {
            return [max(0, min(23, (int) $m[1])), max(0, min(59, (int) $m[2]))];
        }

        return [10, 0];
    }

    protected function addDays(Carbon $date, int $days, bool $businessDaysOnly): Carbon
    {
        $cursor = $date->copy();

        if (! $businessDaysOnly) {
            return $cursor->addDays($days);
        }

        $added = 0;
        while ($added < $days) {
            $cursor = $cursor->addDay();
            if ($cursor->dayOfWeek !== Carbon::SATURDAY && $cursor->dayOfWeek !== Carbon::SUNDAY) {
                $added++;
            }
        }

        return $cursor;
    }

    protected function skipWeekends(Carbon $date): Carbon
    {
        $cursor = $date->copy();

        while ($cursor->dayOfWeek === Carbon::SATURDAY || $cursor->dayOfWeek === Carbon::SUNDAY) {
            $cursor = $cursor->addDay();
        }

        return $cursor;
    }
}
