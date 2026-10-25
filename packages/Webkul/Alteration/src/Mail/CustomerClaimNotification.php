<?php

namespace Webkul\Alteration\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Webkul\Alteration\Models\AlterationClaim;
use Webkul\Shop\Mail\Mailable;

/**
 * Customer email for a claim: `received` when filed, `updated` on every later status change.
 */
class CustomerClaimNotification extends Mailable
{
    public function __construct(
        public AlterationClaim $claim,
        public string $type = 'updated'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [
                new Address(
                    $this->claim->order->customer_email,
                    $this->claim->order->customer_full_name
                ),
            ],
            subject: trans('alteration::app.emails.customer-claim-'.$this->type.'.subject', [
                'id' => $this->claim->id,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'alteration::emails.customer-claim');
    }
}
