<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Webkul\Shop\Helpers\HowToMeasure;
use Webkul\Shop\Http\Requests\ContactRequest;
use Webkul\Shop\Mail\ContactUs;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class HomeController extends Controller
{
    /**
     * Using const variable for status
     */
    const STATUS = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ThemeCustomizationRepository $themeCustomizationRepository) {}

    /**
     * Loads the home page for the storefront.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // return core()->getCurrentCurrencyCode();
        visitor()->visit();

        $customizations = $this->themeCustomizationRepository->orderBy('sort_order')->findWhere([
            'status'     => self::STATUS,
            'channel_id' => core()->getCurrentChannel()->id,
            'theme_code' => core()->getCurrentChannel()->theme,
        ]);

        return view('shop::home.index', compact('customizations'));
    }

    /**
     * Loads the home page for the storefront if something wrong.
     *
     * @return \Exception
     */
    public function notFound()
    {
        abort(404);
    }

    /**
     * Summary of contact.
     *
     * @return \Illuminate\View\View
     */
    public function contactUs()
    {
        return view('shop::home.contact-us');
    }

    /**
     * Loads the "How to Measure" guide page.
     *
     * @return \Illuminate\View\View
     */
    public function howToMeasure(HowToMeasure $howToMeasure)
    {
        return view('shop::home.how-to-measure', [
            'measurements' => $howToMeasure->measurements(),
            'videos'       => $howToMeasure->videos(),
        ]);
    }

    /**
     * Loads the "How It Works" page: the seven steps from design to delivery.
     *
     * Each key maps to a translation block under `shop::app.home.how-it-works.steps`
     * and to an illustration in `shop::components.how-it-works.illustrations`.
     *
     * @return \Illuminate\View\View
     */
    public function howItWorks()
    {
        $steps = [
            'choose-design',
            'submit-measurements',
            'review',
            'production',
            'quality-check',
            'delivery',
            'support',
        ];

        return view('shop::home.how-it-works', compact('steps'));
    }

    // thankYou
    public function thankYou()
    {
        // return view('shop::home.thank-you');
        //re direct to home page if user try to access thank you page directly
        return redirect()->route('shop.home.index');
    }

    /**
     * Summary of store.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendContactUsMail(ContactRequest $contactRequest)
    {
        try {
            Mail::queue(new ContactUs($contactRequest->only([
                'name',
                'email',
                'contact',
                'message',
            ])));

            session()->flash('success', trans('shop::app.home.thanks-for-contact'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            report($e);
        }

        return back();
    }
}
