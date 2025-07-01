<?php

namespace App\Listeners;

use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\ContactActions\ContactMerged;
use App\Events\ContactActions\ContactCreated;
use App\Events\ContactActions\ContactUpdated;
use App\Events\ContactActions\ContactRestored;
use App\Events\ContactActions\ContactSoftDeleted;
use App\Events\ContactActions\ContactForceDeleted;

// For getting IP, User Agent, URL

class AuditListener {
    protected $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * Handle ContactCreated events.
     */
    public function handleContactCreated(ContactCreated $event) {
        Audit::create([
            'auditable_type' => get_class($event->contact),
            'auditable_id' => $event->contact->id,
            'event' => 'created',
            'user_id' => Auth::id(),
            'new_values' => $event->contact->toArray(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
            'url' => $this->request->fullUrl(),
        ]);
    }

    /**
     * Handle ContactUpdated events.
     */
    public function handleContactUpdated(ContactUpdated $event) {
        Audit::create([
            'auditable_type' => get_class($event->contact),
            'auditable_id' => $event->contact->id,
            'event' => 'updated',
            'user_id' => Auth::id(),
            'old_values' => $event->oldValues,
            'new_values' => $event->contact->toArray(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
            'url' => $this->request->fullUrl(),
        ]);
    }

    /**
     * Handle ContactSoftDeleted events.
     */
    public function handleContactSoftDeleted(ContactSoftDeleted $event) {
        Audit::create([
            'auditable_type' => get_class($event->contact),
            'auditable_id' => $event->contact->id,
            'event' => 'deleted',
            'user_id' => Auth::id(),
            'old_values' => $event->contact->toArray(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
            'url' => $this->request->fullUrl(),
        ]);
    }

    /**
     * Handle ContactRestored events.
     */
    public function handleContactRestored(ContactRestored $event) {
        Audit::create([
            'auditable_type' => get_class($event->contact),
            'auditable_id' => $event->contact->id,
            'event' => 'restored',
            'user_id' => Auth::id(),
            'new_values' => $event->contact->toArray(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
            'url' => $this->request->fullUrl(),
        ]);
    }

    /**
     * Handle ContactForceDeleted events.
     */
    public function handleContactForceDeleted(ContactForceDeleted $event) {
        Audit::create([
            'auditable_type' => get_class($event->contact),
            'auditable_id' => $event->contact->id,
            'event' => 'force_deleted',
            'user_id' => Auth::id(),
            'old_values' => $event->contact->toArray(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
            'url' => $this->request->fullUrl(),
        ]);
    }

    /**
     * Handle ContactMerged events.
     */
    public function handleContactMerged(ContactMerged $event) {
        Audit::create([
            'auditable_type' => get_class($event->masterContact),
            'auditable_id' => $event->masterContact->id,
            'event' => 'merged',
            'user_id' => Auth::id(),
            'custom_details' => [
                'secondary_contact_id' => $event->secondaryContact->id,
                'secondary_contact_name' => $event->secondaryContact->name,
                'merge_details' => $event->mergeDetails,
            ],
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->header('User-Agent'),
            'url' => $this->request->fullUrl(),
        ]);

    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events) {
        $events->listen(
            ContactCreated::class,
            [AuditListener::class, 'handleContactCreated']
        );

        $events->listen(
            ContactUpdated::class,
            [AuditListener::class, 'handleContactUpdated']
        );

        $events->listen(
            ContactSoftDeleted::class,
            [AuditListener::class, 'handleContactSoftDeleted']
        );

        $events->listen(
            ContactRestored::class,
            [AuditListener::class, 'handleContactRestored']
        );

        $events->listen(
            ContactForceDeleted::class,
            [AuditListener::class, 'handleContactForceDeleted']
        );

        $events->listen(
            ContactMerged::class,
            [AuditListener::class, 'handleContactMerged']
        );
    }
}