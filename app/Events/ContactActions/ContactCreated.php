<?php

namespace App\Events\ContactActions;

use App\Models\Contact;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ContactCreated {

    use Dispatchable, SerializesModels;

    public Contact $contact;

    public function __construct(Contact $contact) {

        $this->contact = $contact;
    }
}