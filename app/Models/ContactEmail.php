<?php

namespace App\Models;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'email',
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