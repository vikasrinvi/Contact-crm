<?php

namespace App\Events\ContactActions;

use App\Models\Contact;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ContactUpdated {

    use Dispatchable, SerializesModels;

    public Contact $contact;
    public array $oldValues; 
    
    public function __construct(Contact $contact, array $oldValues) {

        $this->contact = $contact;
        $this->oldValues = $oldValues;
    }
}