<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\DataGrids\Theme\ThemeDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Admin\Http\Resources\ProductResource;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class ThemeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        public ThemeCustomizationRepository $themeCustomizationRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Display a listing resource for the available tax rates.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ThemeDataGrid::class)->process();
        }

        return view('admin::settings.themes.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function store()
    {
        if (request()->has('id')) {
            $this->validate(request(), [
                core()->getRequestedLocaleCode().'.options.*.image'             => 'image|extensions:jpeg,jpg,png,svg,webp',
                core()->getRequestedLocaleCode().'.options.*.image_file'        => 'image|extensions:jpeg,jpg,png,svg,webp',
                core()->getRequestedLocaleCode().'.options.*.mobile_image_file' => 'image|extensions:jpeg,jpg,png,svg,webp',
            ]);

            $theme = $this->themeCustomizationRepository->find(request()->input('id'));

            return $this->themeCustomizationRepository->uploadImage(request()->all(), $theme);
        }

        $validated = $this->validate(request(), [
            'name'       => 'required',
            'sort_order' => 'required|numeric',
            'type'       => 'required|in:product_carousel,category_carousel,static_content,image_carousel,hero_carousel,footer_links,services_content,designer_carousel',
            'channel_id' => 'required|in:'.implode(',', (core()->getAllChannels()->pluck('id')->toArray())),
            'theme_code' => 'required',
        ]);

        Event::dispatch('theme_customization.create.before');

        $theme = $this->themeCustomizationRepository->create($validated);

        Event::dispatch('theme_customization.create.after', $theme);

        return new JsonResponse([
            'redirect_url' => route('admin.settings.themes.edit', $theme->id),
        ]);
    }

    /**
     * Edit the theme
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $theme = $this->themeCustomizationRepository->find($id);

        return view('admin::settings.themes.edit', compact('theme'));
    }

    /**
     * Products a product carousel can be hand-arranged from. The carousel's own
     * filters narrow the list first, an optional `query` searches it by name.
     */
    public function products(int $id): JsonResource
    {
        $theme = $this->themeCustomizationRepository->findOrFail($id);

        $filters = collect(request()->input('filters', []))
            ->filter(fn ($value, $key) => is_string($key) && is_scalar($value) && $value !== '')
            ->except([
                'sort',
                'limit',
                'page',
                'product_ids',
                'channel_id',
                'status',
                'visible_individually',
                'url_key',
                'name',
                'query',
            ])
            ->all();

        $params = array_merge($filters, [
            'channel_id'           => $theme->channel_id,
            'status'               => 1,
            'visible_individually' => 1,
            'sort'                 => 'name-asc',
            'limit'                => product_toolbar()->getAvailableLimits()->last(),
        ]);

        if (filled(request('query'))) {
            $params['name'] = request('query');
        }

        $products = $this->productRepository
            ->setSearchEngine('database')
            ->getAll($params);

        return ProductResource::collection($products);
    }

    /**
     * Update the specified resource
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(int $id)
    {
        $this->validate(request(), [
            'name'       => 'required',
            'sort_order' => 'required|numeric',
            'type'       => 'required|in:product_carousel,category_carousel,static_content,image_carousel,hero_carousel,footer_links,services_content,designer_carousel',
            'channel_id' => 'required|in:'.implode(',', (core()->getAllChannels()->pluck('id')->toArray())),
            'theme_code' => 'required',
        ]);

        $locale = request('locale');

        if (request('type') === 'product_carousel') {
            $this->validate(request(), [
                $locale.'.options.filters.product_ids' => 'required_if:'.$locale.'.options.filters.sort,manual',
            ], [
                $locale.'.options.filters.product_ids.required_if' => trans('admin::app.settings.themes.edit.manual-products.required'),
            ]);
        }

        $data = request()->only(
            'locale',
            'type',
            'name',
            'sort_order',
            'channel_id',
            'theme_code',
            'status',
            $locale
        );

        Event::dispatch('theme_customization.update.before', $id);

        $data['status'] = request()->input('status') == 'on';

        $theme = $this->themeCustomizationRepository->update($data, $id);

        Event::dispatch('theme_customization.update.after', $theme);

        session()->flash('success', trans('admin::app.settings.themes.update-success'));

        return redirect()->route('admin.settings.themes.index');
    }

    /**
     * Delete a specified theme.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        Event::dispatch('theme_customization.delete.before', $id);

        $this->themeCustomizationRepository->delete($id);

        Storage::deleteDirectory('theme/'.$id);

        Event::dispatch('theme_customization.delete.after', $id);

        return new JsonResponse([
            'message' => trans('admin::app.settings.themes.delete-success'),
        ], 200);
    }

    public function massUpdate(MassUpdateRequest $massUpdateRequest): JsonResponse
    {
        $selectedThemeIds = $massUpdateRequest->input('indices');

        $this->themeCustomizationRepository->massUpdateStatus([
            'status' => $massUpdateRequest->input('value'),
        ], $selectedThemeIds);

        return new JsonResponse([
            'message' => trans('admin::app.settings.themes.update-success'),
        ]);
    }

    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $selectedThemeIds = $massDestroyRequest->input('indices');

        foreach ($selectedThemeIds as $themeId) {
            $this->themeCustomizationRepository->delete($themeId);
        }

        return new JsonResponse([
            'message' => trans('admin::app.settings.themes.update-success'),
        ]);
    }
}
