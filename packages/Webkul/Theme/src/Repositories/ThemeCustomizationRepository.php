<?php

namespace Webkul\Theme\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;
use Webkul\Theme\Contracts\ThemeCustomization;

class ThemeCustomizationRepository extends Repository
{
    /**
     * Units a hero carousel rotation interval can be expressed in.
     */
    public const HERO_INTERVAL_UNITS = ['seconds', 'minutes', 'hours'];

    /**
     * Seconds a hero slide stays on screen when nothing has been configured.
     */
    public const HERO_DEFAULT_INTERVAL = 6;

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ThemeCustomization::class;
    }

    /**
     * Update the specified theme
     *
     * @param  array  $data
     * @param  int  $id
     */
    public function update($data, $id): ThemeCustomization
    {
        $locale = core()->getRequestedLocaleCode();

        if ($data['type'] == 'static_content') {
            $data[$locale]['options']['html'] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data[$locale]['options']['html']);
            $data[$locale]['options']['css'] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data[$locale]['options']['css']);
        }

        if (in_array($data['type'], ['image_carousel', 'hero_carousel', 'services_content'])) {
            unset($data[$locale]['options']);
        }

        $theme = parent::update($data, $id);

        if (in_array($data['type'], ['image_carousel', 'hero_carousel', 'services_content'])) {
            $this->uploadImage(request()->all(), $theme);
        }

        return $theme;
    }

    /**
     * Mass update the status of themes in the repository.
     *
     * This method updates multiple records in the database based on the provided
     * theme IDs.
     *
     * @param  int  $themeIds
     * @return int The number of records updated.
     */
    public function massUpdateStatus(array $data, array $themeIds)
    {
        return $this->model->whereIn('id', $themeIds)->update($data);
    }

    /**
     * Upload images
     *
     * @return void|string
     */
    public function uploadImage(array $data, ThemeCustomization $theme)
    {
        $locale = core()->getRequestedLocaleCode();

        if (isset($data[$locale]['deleted_sliders'])) {
            foreach ($data[$locale]['deleted_sliders'] as $slider) {
                foreach (['image', 'mobile_image'] as $key) {
                    if (empty($slider[$key])) {
                        continue;
                    }

                    Storage::delete(str_replace('storage/', '', $slider[$key]));
                }
            }
        }

        $isHeroCarousel = ($data['type'] ?? '') == 'hero_carousel';

        /**
         * A hero carousel still has rotation settings to persist once its last slide
         * has been removed, so it does not take the early exit.
         */
        if (
            ! isset($data[$locale]['options'])
            && ! $isHeroCarousel
        ) {
            return;
        }

        $options = [];

        foreach ($data[$locale]['options'] ?? [] as $image) {
            if ($isHeroCarousel) {
                $options['images'][] = $this->prepareHeroSlide($image, $theme);
            } elseif (isset($image['service_icon'])) {
                $options['services'][] = [
                    'service_icon' => $image['service_icon'],
                    'description'  => $image['description'],
                    'title'        => $image['title'],
                ];
            } elseif ($image['image'] instanceof UploadedFile) {
                try {
                    $manager = new ImageManager;

                    $path = 'theme/'.$theme->id.'/'.Str::random(40).'.webp';

                    Storage::put($path, $manager->make($image['image'])->encode('webp'));
                } catch (\Exception $e) {
                    session()->flash('error', $e->getMessage());

                    return redirect()->back();
                }

                if (($data['type'] ?? '') == 'static_content') {
                    return Storage::url($path);
                }

                $options['images'][] = [
                    'image' => 'storage/'.$path,
                    'link'  => $image['link'],
                    'title' => $image['title'],
                ];
            } else {
                $options['images'][] = $image;
            }
        }

        if ($isHeroCarousel) {
            $options['settings'] = $this->prepareHeroSettings(
                $data[$locale]['settings'] ?? [],
                $theme->translate($locale)?->options['settings'] ?? []
            );
        }

        $translatedModel = $theme->translateOrNew($locale);
        $translatedModel->options = $options ?? [];
        $translatedModel->theme_customization_id = $theme->id;
        $translatedModel->save();
    }

    /**
     * Normalize a single hero carousel slide.
     *
     * A slide keeps its already stored image paths in `image`/`mobile_image` and only
     * replaces them when a fresh file arrives in `image_file`/`mobile_image_file`.
     */
    protected function prepareHeroSlide(array $slide, ThemeCustomization $theme): array
    {
        $image = $this->storeImage($slide['image_file'] ?? null, $theme) ?? ($slide['image'] ?? null);

        $mobileImage = $this->storeImage($slide['mobile_image_file'] ?? null, $theme) ?? ($slide['mobile_image'] ?? null);

        return [
            'image'              => $image,
            'mobile_image'       => $mobileImage,
            'heading'            => $slide['heading'] ?? null,
            'subheading'         => $slide['subheading'] ?? null,
            'text_position'      => in_array($slide['text_position'] ?? null, ['left', 'center', 'right'])
                ? $slide['text_position']
                : 'left',
            'cta_text'           => $slide['cta_text'] ?? null,
            'cta_link'           => $slide['cta_link'] ?? null,
            'secondary_cta_text' => $slide['secondary_cta_text'] ?? null,
            'secondary_cta_link' => $slide['secondary_cta_link'] ?? null,
        ];
    }

    /**
     * Rotation settings for a hero carousel.
     *
     * The interval is kept as a value plus a unit rather than a raw duration so the
     * admin form can show back "5 minutes" instead of "300 seconds".
     *
     * `rotation_started_at` anchors the schedule to the clock: the slide on screen is
     * derived from how much time has passed since it, so every visitor sees the same
     * slide at the same moment and long intervals work without anyone keeping a tab
     * open. It is kept across ordinary saves and only reset when the rotation itself
     * changes, otherwise editing a heading would restart the cycle.
     */
    protected function prepareHeroSettings(array $settings, array $previous = []): array
    {
        $unit = in_array($settings['interval_unit'] ?? null, self::HERO_INTERVAL_UNITS)
            ? $settings['interval_unit']
            : 'seconds';

        $value = (int) ($settings['interval_value'] ?? self::HERO_DEFAULT_INTERVAL);

        $prepared = [
            'autoplay'       => (bool) ($settings['autoplay'] ?? true),
            'interval_value' => max(1, min(999, $value ?: self::HERO_DEFAULT_INTERVAL)),
            'interval_unit'  => $unit,
        ];

        $rotationChanged = empty($previous['rotation_started_at'])
            || $prepared['autoplay'] !== (bool) ($previous['autoplay'] ?? true)
            || $prepared['interval_value'] !== (int) ($previous['interval_value'] ?? 0)
            || $prepared['interval_unit'] !== ($previous['interval_unit'] ?? null);

        $prepared['rotation_started_at'] = $rotationChanged
            ? now()->getTimestamp()
            : (int) $previous['rotation_started_at'];

        return $prepared;
    }

    /**
     * Convert an uploaded file to webp and store it against the theme.
     *
     * Returns null when there is nothing to upload, so callers can fall back to an
     * existing path.
     */
    protected function storeImage(mixed $file, ThemeCustomization $theme): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        try {
            $manager = new ImageManager;

            $path = 'theme/'.$theme->id.'/'.Str::random(40).'.webp';

            Storage::put($path, $manager->make($file)->encode('webp'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return null;
        }

        return 'storage/'.$path;
    }
}
