<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Webkul\Shop\Http\Requests\ContactRequest;
use Webkul\Shop\Http\Requests\Customer\LoginRequest;
use Webkul\Shop\Mail\ContactUs;

class CustomerController extends APIController
{
    /**
     * Login Customer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        if (! auth()->guard('customer')->attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => trans('shop::app.customers.login-form.invalid-credentials'),
            ], Response::HTTP_FORBIDDEN);
        }

        if (! auth()->guard('customer')->user()->status) {
            auth()->guard('customer')->logout();

            return response()->json([
                'message' => trans('shop::app.customers.login-form.not-activated'),
            ], Response::HTTP_FORBIDDEN);
        }

        if (! auth()->guard('customer')->user()->is_verified) {
            Cookie::queue(Cookie::make('enable-resend', 'true', 1));

            Cookie::queue(Cookie::make('email-for-resend', $request->get('email'), 1));

            auth()->guard('customer')->logout();

            return response()->json([
                'message' => trans('shop::app.customers.login-form.verify-first'),
            ], Response::HTTP_FORBIDDEN);
        }

        /**
         * Event passed to prepare cart after login.
         */
        Event::dispatch('customer.after.login', auth()->guard()->user());

        return response()->json([]);
    }

    public function sendContactUsMail(Request $request)
    {
        return "yes";

        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
            ], 422);
        }

        $contactRequest = $validator->validated();

        try {

            Mail::queue(new ContactUs([
                'name' => $contactRequest['firstName'] . ' ' . $contactRequest['lastName'],
                'email' => $contactRequest['email'],
                'contact' => $contactRequest['contact'],
                'message' => $contactRequest['message'],
            ]));

             return response()->json([
                'message' => "Thank you for contacting us. We will get back to you soon.",
                'status' => 'success',
            ], 200);

        } catch (\Exception $e) {
            // session()->flash('error', $e->getMessage());
            return response()->json([
                'message' => "There was an error sending your message. Please try again later.",
                'status' => 'error',
            ], 500);

            // report($e);
        }

        // return back();

    }
}
