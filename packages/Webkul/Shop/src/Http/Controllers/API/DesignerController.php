<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Designer\Models\Designer;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Resources\DesignerTreeResource;

class DesignerController extends APIController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected Designer $designerRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Get all categories.
     */
    public function index(): JsonResource
    {
        /**
         * These are the default parameters. By default, only the enabled category
         * will be shown in the current locale.
         */
        $defaultParams = [
            'status' => 1,
            'locale' => app()->getLocale(),
        ];

        $designers = $this->designerRepository->where('status', '1')->with('logo')->get(); //getAll(array_merge($defaultParams, request()->all()));

        return new JsonResource($designers);

    }

    public function list(): JsonResource
    {
        $designers = $this->designerRepository
            ->where('status', '1')
            ->with('logo')
            ->orderBy('name', 'ASC')
            ->get();

        return new JsonResource($designers);
    }

    public function tree(): JsonResource
    {
        $designers = Designer::where('status', 1)->orderBy('name', 'ASC')->get();

        return DesignerTreeResource::collection($designers);
    }

    /**
     * Get filterable attributes for category.
     */
    // public function getAttributes(): JsonResource
    // {
    //     if (! request('category_id')) {
    //         $filterableAttributes = $this->attributeRepository->getFilterableAttributes();

    //         return AttributeResource::collection($filterableAttributes);
    //     }

    //     $category = $this->categoryRepository->findOrFail(request('category_id'));

    //     if (empty($filterableAttributes = $category->filterableAttributes)) {
    //         $filterableAttributes = $this->attributeRepository->getFilterableAttributes();
    //     }

    //     return AttributeResource::collection($filterableAttributes);
    // }

    /**
     * Get product maximum price.
     */
    // public function getProductMaxPrice($categoryId = null): JsonResource
    // {
    //     $maxPrice = $this->productRepository->getMaxPrice(['category_id' => $categoryId]);

    //     return new JsonResource([
    //         'max_price' => core()->convertPrice($maxPrice),
    //     ]);
    // }
}
