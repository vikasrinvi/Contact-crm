<?php

namespace App\Models;

use App\Models\ContactCustomField;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldDefinition extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'field_name',
        'field_type',
        'is_required',
    ];

    
    protected $casts = [
        'is_required' => 'boolean',
    ];

   
    public function contactCustomFields()
    {
        return $this->hasMany(ContactCustomField::class);
    }
}