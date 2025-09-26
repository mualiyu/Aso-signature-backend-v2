<?php

namespace Webkul\Shop\Http\Controllers;

use Webkul\CMS\Repositories\PageRepository;
use Webkul\Designer\Models\Designer;
use Webkul\Marketing\Repositories\URLRewriteRepository;


class DesignerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        // protected URLRewriteRepository $urlRewriteRepository,
        protected PageRepository $pageRepository,
        protected Designer $designer
    ) {}

    /**
     * To extract the page content and load it in the respective view file
     *
     * @param  string  $urlKey
     * @return \Illuminate\View\View
     */
    public function view($slug)
    {
        $designer = $this->designer->where('slug', $slug)->first();
        // return $designer;

        if (! $designer) {
            abort(404);
        }

        // return view('shop::designer.view')->with(['designer' => $designer, 'page' => $page]);
        return view('shop::designer.view', [
                'designer' => $designer,
                'params'   => [
                    'sort'  => request()->query('sort'),
                    'limit' => request()->query('limit'),
                    'mode'  => request()->query('mode'),
                ],
            ]);
    }
}
