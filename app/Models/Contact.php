<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\ContactActions\ContactCreated;
use App\Events\ContactActions\ContactUpdated;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\ContactActions\ContactRestored;
use App\Events\ContactActions\ContactSoftDeleted;
use App\Events\ContactActions\ContactForceDeleted;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model {

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'profile_image',
        'additional_file',
        'merge_status',
        'merged_into_contact_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot() {
        parent::boot();

        static::created(function (Contact $contact) {
            ContactCreated::dispatch($contact);
        });

        static::updated(function (Contact $contact) {

            $oldValues = $contact->getOriginal();
            ContactUpdated::dispatch($contact, $oldValues);
        });

        static::deleted(function (Contact $contact) {

            if ($contact->isForceDeleting()) {
                ContactForceDeleted::dispatch($contact);
            } else {
                ContactSoftDeleted::dispatch($contact);
            }
        });

        static::restored(function (Contact $contact) {
            ContactRestored::dispatch($contact);
        });

        static::forceDeleted(function (Contact $contact) {
            ContactForceDeleted::dispatch($contact);
        });
    }

    public function customFields() {
        return $this->hasMany(ContactCustomField::class);
    }

    public function customFieldsWithDefinitions() {
        return $this->hasMany(ContactCustomField::class)->with('definition');
    }

    public function mergedInto() {
        return $this->belongsTo(Contact::class, 'merged_into_contact_id')->withTrashed();
    }

    public function mergedFrom() {
        return $this->hasMany(Contact::class, 'merged_into_contact_id')->withTrashed();
    }

    public function emails(): HasMany {
        return $this->hasMany(ContactEmail::class);
    }

    public function phones(): HasMany {
        return $this->hasMany(ContactPhone::class);
    }

    public function mergedIntoContact(): BelongsTo {
        return $this->belongsTo(Contact::class, 'merged_into_contact_id');
    }
}