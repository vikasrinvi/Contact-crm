<?php

namespace App\Models;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactPhone extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'phone',
        'type',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}