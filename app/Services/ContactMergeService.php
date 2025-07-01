<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ContactEmail;
use App\Models\ContactPhone;
use App\Models\ContactCustomField;
// use App\Models\Note;
// use App\Models\Activity;
// use App\Models\Deal;
// use App\Models\Tag;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

// Import the ContactMerged event
use App\Events\ContactActions\ContactMerged;




class ContactMergeService
{

    public function merge(Contact $masterContact, Contact $secondaryContact): bool
    {
        $masterContact->load([
            'emails',
            'phones',
            'customFields.definition',
            
        ]);
        $secondaryContact->load([
            'emails',
            'phones',
            'customFields.definition',
            
        ]);

        
        $mergeDetails = [
            'secondary_contact_original_name' => $secondaryContact->name,
            'standard_fields_affected' => [],
            'custom_fields_affected' => [],
            'relationships_reassigned' => [
                'emails' => [],
                'phones' => [],
                'notes' => [], 
            ],
        ];

        DB::beginTransaction();
        try {
            
            $this->mergeStandardFields($masterContact, $secondaryContact, $mergeDetails['standard_fields_affected']);

           
            $this->handleCustomFieldMerge($masterContact, $secondaryContact, $mergeDetails['custom_fields_affected']);

        
            $this->reassignRelationships($masterContact, $secondaryContact, $mergeDetails['relationships_reassigned']);

            
            $masterContact->load('emails', 'phones');

            
            $this->deduplicateRelatedEmailsAndPhones($masterContact);

            $masterContact->save();

            $secondaryContact->is_merged = true;
            $secondaryContact->merged_into_contact_id = $masterContact->id;
            $secondaryContact->save();
            $secondaryContact->delete(); 

            DB::commit();

            ContactMerged::dispatch($masterContact, $secondaryContact, $mergeDetails);

            return true;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Error merging contacts in ContactMergeService: " . $e->getMessage(), [
                'master_id' => $masterContact->id ?? 'N/A',
                'secondary_id' => $secondaryContact->id ?? 'N/A',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    

    protected function mergeStandardFields(Contact $masterContact, Contact $secondaryContact, array &$auditLog): void
    {
        

        $masterContact->loadMissing('emails', 'phones');


        $masterOriginalAttributes = $masterContact->getOriginal();

   
        $masterEmailBefore = $masterOriginalAttributes['email'] ?? null;
        $secondaryEmail = $secondaryContact->email;
        $action = 'NO_CHANGE'; 

        if (empty($masterContact->email) && !empty($secondaryContact->email)) {
           
            $masterContact->email = $secondaryContact->email;
            $action = 'ADDED_FROM_SECONDARY';
        } elseif (!empty($masterContact->email) && !empty($secondaryContact->email)) {
            
            $masterEmailLower = strtolower($masterContact->email);
            $secondaryEmailLower = strtolower($secondaryContact->email);
            

            if ($masterEmailLower !== $secondaryEmailLower) {
                

                $existsInMasterRelated = $masterContact->emails->first(function ($ce) use ($secondaryEmailLower) {
                    return strtolower($ce->email) === $secondaryEmailLower;
                });

                

                if (!$existsInMasterRelated) {
                   
                    ContactEmail::create([
                        'contact_id' => $masterContact->id,
                        'email' => $secondaryContact->email,
                        'type' => 'alternate',
                        'is_primary' => false,
                    ]);
                    
                    $action = 'MASTER_VALUE_KEPT_SECONDARY_ADDED_AS_RELATED'; 
                } else {
                    
                    $action = 'MASTER_VALUE_KEPT_SECONDARY_EXISTS_IN_RELATED'; 
                }
            } else {
                
                $action = 'MASTER_VALUE_KEPT'; 
            }
        }
        
        if ($action !== 'NO_CHANGE' || ($masterEmailBefore !== ($masterContact->email ?? null) || $secondaryEmail !== ($masterContact->email ?? null))) {
            $auditLog[] = [
                'field_name' => 'email',
                'master_value_before_merge' => $masterEmailBefore,
                'secondary_value' => $secondaryEmail,
                'master_value_after_merge' => $masterContact->email,
                'action' => $action,
            ];
        }


        $masterPhoneBefore = $masterOriginalAttributes['phone'] ?? null;
        $secondaryPhone = $secondaryContact->phone;
        $action = 'NO_CHANGE'; 

        
        if (empty($masterContact->phone) && !empty($secondaryContact->phone)) {
            
            $masterContact->phone = $secondaryContact->phone;
            $action = 'ADDED_FROM_SECONDARY';
        } elseif (!empty($masterContact->phone) && !empty($secondaryContact->phone)) {
            
            $normalizedMasterPhone = preg_replace('/\D/', '', $masterContact->phone);
            $normalizedSecondaryPhone = preg_replace('/\D/', '', $secondaryContact->phone);
            

            if ($normalizedMasterPhone !== $normalizedSecondaryPhone) {
                

                $existsInMasterRelated = $masterContact->phones->first(function ($cp) use ($normalizedSecondaryPhone) {
                    return preg_replace('/\D/', '', $cp->phone) === $normalizedSecondaryPhone;
                });

                

                if (!$existsInMasterRelated) {
                    
                    ContactPhone::create([
                        'contact_id' => $masterContact->id,
                        'phone' => $secondaryContact->phone,
                        'type' => 'alternate',
                        'is_primary' => false,
                    ]);
                    
                    $action = 'MASTER_VALUE_KEPT_SECONDARY_ADDED_AS_RELATED'; 
                } else {
                    
                    $action = 'MASTER_VALUE_KEPT_SECONDARY_EXISTS_IN_RELATED'; 
                }
            } else {
                
                $action = 'MASTER_VALUE_KEPT'; 
            }
        }
        
        if ($action !== 'NO_CHANGE' || ($masterPhoneBefore !== ($masterContact->phone ?? null) || $secondaryPhone !== ($masterContact->phone ?? null))) {
            $auditLog[] = [
                'field_name' => 'phone',
                'master_value_before_merge' => $masterPhoneBefore,
                'secondary_value' => $secondaryPhone,
                'master_value_after_merge' => $masterContact->phone,
                'action' => $action,
            ];
        }


        $commaSeparatedFields = [
            'address',
        ];

        foreach ($commaSeparatedFields as $field) {
            $masterValueBefore = $masterOriginalAttributes[$field] ?? null;
            $secondaryValue = $secondaryContact->$field;
            $action = 'NO_CHANGE';

            if (empty($masterContact->$field) && !empty($secondaryContact->$field)) {
                
                $masterContact->$field = $secondaryContact->$field;
                $action = 'ADDED_FROM_SECONDARY';
            } elseif (!empty($masterContact->$field) && !empty($secondaryContact->$field)) {
                
                $masterValues = array_map('trim', explode(',', $masterContact->$field));
                $secondaryValues = array_map('trim', explode(',', $secondaryContact->$field));
                $combinedValues = array_unique(array_filter(array_merge($masterValues, $secondaryValues)));
                $newMasterValue = implode(', ', $combinedValues);

                if ($masterContact->$field !== $newMasterValue) {
                    $masterContact->$field = $newMasterValue;
                    $action = 'COMBINED_VALUES';
                } else {
                    $action = 'MASTER_VALUE_KEPT'; 
                }
               
            }
            if ($action !== 'NO_CHANGE' || ($masterValueBefore !== ($masterContact->$field ?? null) || $secondaryValue !== ($masterContact->$field ?? null))) {
                $auditLog[] = [
                    'field_name' => $field,
                    'master_value_before_merge' => $masterValueBefore,
                    'secondary_value' => $secondaryValue,
                    'master_value_after_merge' => $masterContact->$field,
                    'action' => $action,
                ];
            }
        }

        $simpleMergeFields = ['gender', 'profile_image', 'additional_file'];
        foreach ($simpleMergeFields as $field) {
            $masterValueBefore = $masterOriginalAttributes[$field] ?? null;
            $secondaryValue = $secondaryContact->$field;
            $action = 'NO_CHANGE';

            if (empty($masterContact->$field) && !empty($secondaryContact->$field)) {
               
                $masterContact->$field = $secondaryContact->$field;
                $action = 'ADDED_FROM_SECONDARY';
            } else if (!empty($masterContact->$field) && !empty($secondaryContact->$field) && $masterContact->$field !== $secondaryContact->$field) {
                
                $action = 'MASTER_VALUE_KEPT';
            }

            if ($action !== 'NO_CHANGE' || ($masterValueBefore !== ($masterContact->$field ?? null) || $secondaryValue !== ($masterContact->$field ?? null))) {
                $auditLog[] = [
                    'field_name' => $field,
                    'master_value_before_merge' => $masterValueBefore,
                    'secondary_value' => $secondaryValue,
                    'master_value_after_merge' => $masterContact->$field,
                    'action' => $action,
                ];
            }
        }

       
    }

    
    protected function handleCustomFieldMerge(Contact $master, Contact $secondary, array &$auditLog): void
    {
       

        
        $master->load('customFields.definition'); 
        $masterCustomFieldsMap = $master->customFields->keyBy('custom_field_definition_id');


        foreach ($secondary->customFields as $secondaryCustomField) {
            $definitionId = $secondaryCustomField->custom_field_definition_id;
            $masterCustomField = $masterCustomFieldsMap->get($definitionId);

            $masterValueBefore = $masterCustomField ? $masterCustomField->value : null;
            $secondaryValue = $secondaryCustomField->value;
            $masterValueAfter = $masterValueBefore;
            $action = 'NO_CHANGE';

            if ($masterCustomField) {
                

                
                if (empty($masterCustomField->value) && !empty($secondaryCustomField->value)) {
                    $masterCustomField->value = $secondaryCustomField->value;
                    $masterCustomField->save();
                    $masterValueAfter = $secondaryCustomField->value;
                    $action = 'UPDATED_BY_SECONDARY';
                } elseif (
                    !empty($masterCustomField->value) &&
                    !empty($secondaryCustomField->value) &&
                    $masterCustomField->value !== $secondaryCustomField->value
                ) {
                    $fieldDefinitionType = $secondaryCustomField->definition?->field_type; 

                    $textBasedTypes = ['text', 'textarea', 'string', 'select', 'multiselect', 'radio'];
                    if (in_array($fieldDefinitionType, $textBasedTypes)) {
                        $masterValues = array_map('trim', explode(',', $masterCustomField->value));
                        $secondaryValueExistsInMaster = false;
                        foreach ($masterValues as $masterVal) {
                            if (trim(strtolower($masterVal)) === trim(strtolower($secondaryCustomField->value))) {
                                $secondaryValueExistsInMaster = true;
                                break;
                            }
                        }

                        if (!$secondaryValueExistsInMaster) {
                            $masterCustomField->value .= ', ' . $secondaryCustomField->value;
                            $masterCustomField->save();
                            $masterValueAfter = $masterCustomField->value;
                            $action = 'COMBINED_VALUES';
                            
                        } else {
                            $action = 'MASTER_VALUE_KEPT_SECONDARY_EXISTS_IN_COMBINED';
                            
                        }
                    } else {
                        $action = 'MASTER_VALUE_KEPT'; 
                        
                    }
                } else {
                    $action = 'NO_CHANGE'; 
                }

  
                $secondaryCustomField->delete();
                

            } else {
                
                $secondaryCustomField->contact_id = $master->id;
                $secondaryCustomField->save();
                $masterValueAfter = $secondaryCustomField->value; 
                $action = 'ADDED_FROM_SECONDARY';
                
            }

            
            if ($action !== 'NO_CHANGE') {
                $auditLog[] = [
                    'field_definition_id' => $definitionId,
                    'field_name' => $secondaryCustomField->definition->name ?? 'Unknown Custom Field',
                    'master_value_before_merge' => $masterValueBefore,
                    'secondary_value' => $secondaryValue,
                    'master_value_after_merge' => $masterValueAfter,
                    'action' => $action,
                ];
            }
        }
        
    }


    protected function reassignRelationships(Contact $master, Contact $secondary, array &$auditLog): void
    {
    
        $emailsToReassign = $secondary->emails()->pluck('id', 'email')->toArray();
        if (!empty($emailsToReassign)) {
            $secondary->emails()->update(['contact_id' => $master->id]);
            foreach($emailsToReassign as $email => $id) {
                 $auditLog['emails'][] = [
                    'id' => $id,
                    'value' => $email,
                    'action' => 'MOVED_TO_MASTER',
                ];
            }
            
        }

       
        $phonesToReassign = $secondary->phones()->pluck('id', 'phone')->toArray();
        if (!empty($phonesToReassign)) {
            $secondary->phones()->update(['contact_id' => $master->id]);
             foreach($phonesToReassign as $phone => $id) {
                 $auditLog['phones'][] = [
                    'id' => $id,
                    'value' => $phone,
                    'action' => 'MOVED_TO_MASTER',
                ];
            }
            
        }

        if (class_exists(\App\Models\Note::class) && $secondary->relationLoaded('notes') && $secondary->notes->isNotEmpty()) {
            $notesToReassign = $secondary->notes()->pluck('id', 'title')->toArray(); 
            if(!empty($notesToReassign)) {
                $secondary->notes()->update(['contact_id' => $master->id]);
                 foreach($notesToReassign as $title => $id) {
                    
                    $originalNote = \App\Models\Note::find($id);
                    $auditLog['notes'][] = [
                        'id' => $id,
                        'snippet' => substr($originalNote->content, 0, 50) . '...',
                        'action' => 'MOVED_TO_MASTER',
                    ];
                }
                
            }
        }
       

        
    }

    
    protected function deduplicateRelatedEmailsAndPhones(Contact $masterContact): void
    {

        $masterContact->load('emails'); 
        $masterEmails = $masterContact->emails->sortByDesc('is_primary'); 
        $seenEmails = [];

        foreach ($masterEmails as $email) {
            $normalizedEmail = strtolower($email->email);
            if (in_array($normalizedEmail, $seenEmails)) {
                
                $email->delete();
            } else {
                $seenEmails[] = $normalizedEmail;
            }
        }

        
        $masterContact->load('emails'); 
        $remainingEmails = $masterContact->emails->sortByDesc('is_primary');
        $primaryEmailFound = false;

        foreach ($remainingEmails as $email) {
            if ($email->is_primary) {
                if (!$primaryEmailFound) {
                    $primaryEmailFound = true; 
                } else {
                    
                    $email->is_primary = false;
                    $email->save();
                    
                }
            }
        }

        
        if (!$primaryEmailFound && $remainingEmails->isNotEmpty()) {
            $firstEmail = $remainingEmails->first(); 
            if ($firstEmail) { 
                $firstEmail->is_primary = true;
                $firstEmail->save();
                
            }
        }
        

       
        $masterContact->load('phones');
        $masterPhones = $masterContact->phones->sortByDesc('is_primary');
        $seenPhones = [];

        foreach ($masterPhones as $phone) {
            $normalizedPhone = preg_replace('/\D/', '', $phone->phone); 
            if (in_array($normalizedPhone, $seenPhones)) {
                
                $phone->delete();
            } else {
                $seenPhones[] = $normalizedPhone;
            }
        }

        
        $masterContact->load('phones'); 
        $remainingPhones = $masterContact->phones->sortByDesc('is_primary');
        $primaryPhoneFound = false;

        foreach ($remainingPhones as $phone) {
            if ($phone->is_primary) {
                if (!$primaryPhoneFound) {
                    $primaryPhoneFound = true;
                } else {
                    $phone->is_primary = false;
                    $phone->save();
                    
                }
            }
        }

       
        if (!$primaryPhoneFound && $remainingPhones->isNotEmpty()) {
            $firstPhone = $remainingPhones->first();
            if ($firstPhone) { 
                $firstPhone->is_primary = true;
                $firstPhone->save();
                
            }
        }
    
    }
}