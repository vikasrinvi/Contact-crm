<?php

namespace App\Models;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'user_id',
        'old_values',
        'new_values',
        'custom_details',
        'ip_address',
        'user_agent',
        'url',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'custom_details' => 'array',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function auditable()
    {
        return $this->morphTo();
    }

    public function contact(){
        return $this->belongsTo(Contact::class, 'auditable_id');
    }
}