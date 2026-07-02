<?php

namespace Webkul\Shop\Mail\Order;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Webkul\Shop\Mail\Mailable;

class StatusUpdatedNotification extends Mailable
{
    /**
     * Create a new StatusUpdatedNotification instance.
     *
     * @return void
     */
    public function __construct(public $order) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [
                new Address($this->order->customer_email, $this->order->customer_full_name),
            ],
            subject: trans('shop::app.emails.orders.status-updated.subject', [
                'order_id' => $this->order->increment_id,
            ]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'shop::emails.orders.status-updated',
        );
    }
}
