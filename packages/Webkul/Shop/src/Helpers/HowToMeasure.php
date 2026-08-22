<?php

namespace Webkul\Shop\Helpers;

/**
 * Builds the "How to Measure" content (measurements + walkthrough videos)
 * from `Config/how-to-measure.php` and the lang files.
 *
 * Used by the full page (HomeController::howToMeasure) and by the JSON
 * endpoint that feeds the global modal (API\HowToMeasureController).
 */
class HowToMeasure
{
    /**
     * The measurements, ready for the `v-how-to-measure` component.
     */
    public function measurements(): array
    {
        return collect($this->definitions()['measurements'] ?? [])->map(function ($measurement) {
            $langKey = 'shop::app.home.how-to-measure.measurements.'.$measurement['id'];

            return [
                'id'       => $measurement['id'],
                'letter'   => $measurement['letter'],
                'keywords' => $measurement['keywords'],
                'label'    => trans($langKey.'.label'),
                'title'    => trans($langKey.'.title'),
                'how'      => trans($langKey.'.how'),
                'tip'      => trans($langKey.'.tip'),
                'icon'     => view('shop::components.how-to-measure.icons.'.$measurement['id'])->render(),
            ];
        })->values()->all();
    }

    /**
     * The walkthrough videos, ready for the `v-measurement-video` component.
     */
    public function videos(): array
    {
        return collect($this->definitions()['videos'] ?? [])->map(function ($video) {
            $langKey = 'shop::app.home.how-to-measure.videos.'.$video['id'];

            return [
                'id'    => $video['id'],
                'src'   => $video['src'],
                'label' => trans($langKey.'.label'),
                'title' => trans($langKey.'.title'),
                'desc'  => trans($langKey.'.desc'),
            ];
        })->values()->all();
    }

    /**
     * The raw definitions from `Config/how-to-measure.php`.
     *
     * Laravel skips `mergeConfigFrom()` while the config is cached, so a server whose
     * `config:cache` predates this file would see an empty list. Fall back to the file
     * directly so the content never renders empty.
     */
    protected function definitions(): array
    {
        $definitions = config('shop.how-to-measure');

        if (! empty($definitions['measurements'])) {
            return $definitions;
        }

        $path = dirname(__DIR__).'/Config/how-to-measure.php';

        return file_exists($path) ? require $path : [];
    }
}
