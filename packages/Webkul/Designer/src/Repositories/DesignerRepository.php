<?php

namespace Webkul\Designer\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Designer\Contracts\Designer;
use Webkul\Core\Eloquent\Repository;
use Intervention\Image\ImageManager;

class DesignerRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Designer::class;
    }

    /**
     * Get categories.
     *
     * @return void
     */
    public function getAll(array $params = [])
    {
        $queryBuilder = $this->query()
            ->select('designers.*');

        foreach ($params as $key => $value) {
            switch ($key) {
                case 'status':
                    $queryBuilder->where('designers.status', $value);

                    break;
            }
        }

        return $queryBuilder->paginate($params['limit'] ?? 10);
    }

    /**
     * Create category.
     *
     * @return \Webkul\Designer\Contracts\Designer
     */
    public function create(array $data)
    {

        $designer = $this->model->create($data);

        $this->uploadImages($data, $designer);

        $this->uploadImages($data, $designer, 'banner_path');

        if (isset($data['attributes'])) {
            $designer->filterableAttributes()->sync($data['attributes']);
        }

        return $designer;
    }

    /**
     * Update category.
     *
     * @param  int  $id
     * @param  string  $attribute
     * @return \Webkul\Category\Contracts\Category
     */
    public function update(array $data, $id)
    {
        $designer = $this->find($id);

        // $data = $this->setSameAttributeValueToAllLocale($data, 'slug');

        $designer->update($data);

        $this->uploadImages($data, $designer);

        $this->uploadImages($data, $designer, 'banner_path');

        if (isset($data['attributes'])) {
            $designer->filterableAttributes()->sync($data['attributes']);
        }

        return $designer;
    }

    /**
     * Specify category tree.
     *
     * @param  int  $id
     * @return \Webkul\Category\Contracts\Category
     */
    public function getDesignerTree($id = null)
    {
        return $id
            ? $this->model::orderBy('name', 'ASC')->where('id', '!=', $id)->get()->toTree()
            : $this->model::orderBy('name', 'ASC')->get()->toTree();
    }

    /**
     * Specify category tree.
     *
     * @param  int  $id
     * @return \Illuminate\Support\Collection
     */
    public function getDesignerTreeWithoutDescendant($id = null)
    {
        return $id
            ? $this->model::orderBy('name', 'ASC')->where('id', '!=', $id)->whereNotDescendantOf($id)->get()->toTree()
            : $this->model::orderBy('name', 'ASC')->get()->toTree();
    }

    /**
     * Get root categories.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRootDesigners()
    {
        return $this->getModel()->where('status', '1')->get();
    }

    /**
     * get visible category tree.
     *
     * @param  int  $id
     * @return \Illuminate\Support\Collection
     */
    public function getVisibleDesignerTree($id = null)
    {
        return $id
            ? $this->model::orderBy('name', 'ASC')->where('status', 1)->descendantsAndSelf($id)->toTree($id)
            : $this->model::orderBy('name', 'ASC')->where('status', 1)->get()->toTree();
    }



    /**
     * Retrieve category from slug.
     *
     * @param  string  $slug
     * @return \Webkul\Designer\Contracts\Designer
     */
    public function findBySlug($slug)
    {
        if ($designer = $this->model->whereTranslation('slug', $slug)->first()) {
            return $designer;
        }
    }

    /**
     * Retrieve category from slug.
     *
     * @param  string  $slug
     * @return \Webkul\Designer\Contracts\Designer
     */
    public function findBySlugOrFail($slug)
    {
        return $this->model->whereTranslation('slug', $slug)->firstOrFail();
    }

    /**
     * Upload category's images.
     *
     * @param  array  $data
     * @param  \Webkul\Designer\Contracts\Designer  $designer
     * @param  string  $type
     * @return void
     */
    public function uploadImages($data, $designer, $type = 'logo_path')
    {
        if (isset($data[$type])) {
            foreach ($data[$type] as $imageId => $image) {
                $file = $type.'.'.$imageId;

                if (request()->hasFile($file)) {
                    if ($designer->{$type}) {
                        Storage::delete($designer->{$type});
                    }

                    $manager = new ImageManager;

                    $image = $manager->make(request()->file($file))->encode('webp');

                    $designer->{$type} = 'designer/'.$designer->id.'/'.Str::random(40).'.webp';

                    Storage::put($designer->{$type}, $image);

                    $designer->save();
                }
            }
        } else {
            if ($designer->{$type}) {
                Storage::delete($designer->{$type});
            }

            $designer->{$type} = null;

            $designer->save();
        }
    }



    /**
     * Set same value to all locales in category.
     *
     * To Do: Move column from the `category_translations` to `category` table. And remove
     * this created method.
     *
     * @param  string  $attributeNames
     * @return array
     */
    private function setSameAttributeValueToAllLocale(array $data, ...$attributeNames)
    {
        $requestedLocale = core()->getRequestedLocaleCode();

        $model = app()->make($this->model());

        foreach ($attributeNames as $attributeName) {
            foreach (core()->getAllLocales() as $locale) {
                if ($requestedLocale == $locale->code) {
                    foreach ($model->translatedAttributes as $attribute) {
                        if ($attribute === $attributeName) {
                            $data[$locale->code][$attribute] = $data[$requestedLocale][$attribute] ?? $data[$data['locale']][$attribute];
                        }
                    }
                }
            }
        }

        return $data;
    }
}
