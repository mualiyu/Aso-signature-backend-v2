<?php

namespace Webkul\Alteration\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Webkul\Alteration\Models\AlterationClaim;
use Webkul\Shop\Mail\Mailable;

class AdminNewClaimNotification extends Mailable
{
    public function __construct(public AlterationClaim $claim) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [
                new Address(
                    core()->getAdminEmailDetails()['email'],
                    core()->getAdminEmailDetails()['name']
                ),
            ],
            subject: trans('alteration::app.emails.admin-new-claim.subject', [
                'id'    => $this->claim->id,
                'order' => $this->claim->order->increment_id,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'alteration::emails.admin-new-claim');
    }
}
