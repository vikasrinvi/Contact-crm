<?php

namespace App\Events\ContactActions;

use App\Models\Contact;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ContactMerged {
    use Dispatchable, SerializesModels;

    public Contact $masterContact, $secondaryContact;

    public array $mergeDetails;

    public function __construct(Contact $masterContact, Contact $secondaryContact, array $mergeDetails) 
    {
        $this->masterContact = $masterContact;
        $this->secondaryContact = $secondaryContact;
        $this->mergeDetails = $mergeDetails;
    }
}