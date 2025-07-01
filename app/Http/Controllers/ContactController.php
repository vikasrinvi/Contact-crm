<?php

namespace App\Http\Controllers;

use App\DataTables\ContactsDataTable;
use App\Models\Contact;
use App\Models\ContactCustomField;
use App\Models\CustomFieldDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable; 

class ContactController extends Controller
{

    
    public function index(ContactsDataTable $dataTable)
    {
        $customFieldDefinitions = CustomFieldDefinition::all();
        return $dataTable->render('contacts.index', compact('customFieldDefinitions'));
    }

    
    public function getContactsData(ContactsDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    
    public function create()
    {
        $customFieldDefinitions = CustomFieldDefinition::all();
        return view('contacts.create', compact('customFieldDefinitions'));
    }

   
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Male,Female,Other',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'additional_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120',
        ];

        $customFieldDefinitions = CustomFieldDefinition::all();
        foreach ($customFieldDefinitions as $definition) {
            $rule = 'nullable|string|max:2000';
            if ($definition->field_type === 'date') {
                $rule = 'nullable|date';
            } elseif ($definition->field_type === 'number') {
                $rule = 'nullable|numeric';
            }
            if ($definition->is_required) {
                $rule = 'required|' . $rule;
            }
            $rules['custom_fields.' . $definition->id] = $rule;
        }

        $validatedData = $request->validate($rules);

        try {
            DB::beginTransaction();

            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('public/contacts/profile_images');
            }

            $additionalFilePath = null;
            if ($request->hasFile('additional_file')) {
                $additionalFilePath = $request->file('additional_file')->store('public/contacts/additional_files');
            }

            $contact = Contact::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'], 
                'phone' => $validatedData['phone'], 
                'gender' => $validatedData['gender'],
                'profile_image' => $profileImagePath,
                'additional_file' => $additionalFilePath,
            ]);

            if ($request->has('custom_fields')) {
                foreach ($request->input('custom_fields') as $definitionId => $value) {
                    if ($value !== null) {
                        ContactCustomField::create([
                            'contact_id' => $contact->id,
                            'custom_field_definition_id' => $definitionId,
                            'value' => $value,
                        ]);
                    }
                }
            }

            
            if (!empty($validatedData['email'])) {
                $contact->emails()->create([
                    'email' => $validatedData['email'],
                    'type' => 'primary',
                    'is_primary' => true,
                ]);
            }
            if (!empty($validatedData['phone'])) {
                $contact->phones()->create([
                    'phone' => $validatedData['phone'],
                    'type' => 'primary',
                    'is_primary' => true,
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Contact created successfully!', 'contact' => $contact]);

        } catch (Throwable $e) { 
            DB::rollBack();
            \Log::error("Error creating contact: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to create contact.', 'error' => $e->getMessage()], 500);
        }
    }


    public function show(Contact $contact): View
    {
        $contact->load([
            'emails', 
            'phones',                   
            'customFields.definition',  
            'mergedInto',
            'mergedFrom' => function($query) {
                $query->with(['emails', 'phones', 'customFields.definition']);
            },
        ]);

        $secondaryEmails = [];
        $secondaryPhones = [];

        foreach ($contact->mergedFrom as $mergedContact) {
            foreach ($mergedContact->emails as $email) {
                $secondaryEmails[] = $email->email;
            }

            foreach ($mergedContact->phones as $phone) {
                $secondaryPhones[] = $phone->phone_number;
            }

        }


        $masterContactEmails = $contact->emails->pluck('email')->toArray();
        $masterContactPhones = $contact->phones->pluck('phone_number')->toArray();

        
        $secondaryEmails = array_unique(array_filter($secondaryEmails, function($email) use ($masterContactEmails) {
            return !in_array($email, $masterContactEmails);
        }));

        $secondaryPhones = array_unique(array_filter($secondaryPhones, function($phone) use ($masterContactPhones) {
            return !in_array($phone, $masterContactPhones);
        }));

        return view('contacts.show', compact('contact', 'secondaryEmails', 'secondaryPhones'));
    }

    
    public function edit(Contact $contact)
    {
        $customFieldDefinitions = CustomFieldDefinition::all();
        $contact->load('customFields.definition', 'emails', 'phones'); 
        return view('contacts.edit', compact('contact', 'customFieldDefinitions'));
    }

    
    public function update(Request $request, Contact $contact)
    {
        $rules = [
            'name' => 'required|string|max:255',
            
            'email' => 'required|email|max:255|unique:contacts,email,' . $contact->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Male,Female,Other',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'additional_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120',
        ];

        $customFieldDefinitions = CustomFieldDefinition::all();
        foreach ($customFieldDefinitions as $definition) {
            $rule = 'nullable|string|max:2000';
            if ($definition->field_type === 'date') {
                $rule = 'nullable|date';
            } elseif ($definition->field_type === 'number') {
                $rule = 'nullable|numeric';
            }
            if ($definition->is_required) {
                $rule = 'required|' . $rule;
            }
            $rules['custom_fields.' . $definition->id] = $rule;
        }


        $validatedData = $request->validate($rules);

        try {
            DB::beginTransaction();

            $profileImagePath = $contact->profile_image;
            if ($request->hasFile('profile_image')) {
                if ($profileImagePath) {
                    Storage::delete($profileImagePath);
                }
                $profileImagePath = $request->file('profile_image')->store('public/contacts/profile_images');
            } elseif ($request->boolean('clear_profile_image')) {
                if ($profileImagePath) {
                    Storage::delete($profileImagePath);
                }
                $profileImagePath = null;
            }

            $additionalFilePath = $contact->additional_file;
            if ($request->hasFile('additional_file')) {
                if ($additionalFilePath) {
                    Storage::delete($additionalFilePath);
                }
                $additionalFilePath = $request->file('additional_file')->store('public/contacts/additional_files');
            } elseif ($request->boolean('clear_additional_file')) {
                if ($additionalFilePath) {
                    Storage::delete($additionalFilePath);
                }
                $additionalFilePath = null;
            }

            $contact->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'gender' => $validatedData['gender'],
                'profile_image' => $profileImagePath,
                'additional_file' => $additionalFilePath,
            ]);

            if ($request->has('custom_fields')) {
                foreach ($request->input('custom_fields') as $definitionId => $value) {
                    if ($value === null || $value === '') {
                        ContactCustomField::where('contact_id', $contact->id)
                            ->where('custom_field_definition_id', $definitionId)
                            ->delete();
                    } else {
                        ContactCustomField::updateOrCreate(
                            [
                                'contact_id' => $contact->id,
                                'custom_field_definition_id' => $definitionId,
                            ],
                            [
                                'value' => $value,
                            ]
                        );
                    }
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Contact updated successfully!', 'contact' => $contact]);

        } catch (Throwable $e) {
            DB::rollBack();
            \Log::error("Error updating contact: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to update contact.', 'error' => $e->getMessage()], 500);
        }
    }

    
    public function destroy(Contact $contact)
    {
        try {
            
            if ($contact->mergedFrom->isNotEmpty()) { 
                 return response()->json(['success' => false, 'message' => 'Cannot delete master contact with merged records. Please reassign or permanently delete merged secondary contacts first.'], 400);
            }

            $contact->delete(); 

            return response()->json(['success' => true, 'message' => 'Contact moved to trash (soft-deleted) successfully!']);
        } catch (Throwable $e) {
            \Log::error("Error soft deleting contact: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to soft delete contact.', 'error' => $e->getMessage()], 500);
        }
    }

    
    public function trashedContacts(ContactsDataTable $dataTable)
    {
        return $dataTable->forTrashed()->render('contacts.trashed', ['title' => 'Trashed Contacts']);
    }

    
    public function restoreContact($id) 
    {
        $contact = Contact::withTrashed()->findOrFail($id);

        if ($contact->restore()) {
            
            return response()->json(['success' => true, 'message' => 'Contact restored successfully!']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to restore contact.'], 500);
    }

    
    public function forceDeleteContact($id) 
    {
        $contact = Contact::withTrashed()->findOrFail($id);

        try {
            DB::beginTransaction();

            
            $contact->customFields()->forceDelete();

            
            $contact->emails()->forceDelete();
            $contact->phones()->forceDelete();

            
            if ($contact->profile_image) {
                Storage::delete($contact->profile_image);
            }
            if ($contact->additional_file) {
                Storage::delete($contact->additional_file);
            }
            $contact->mergedFrom()->update(['merged_into_contact_id' => null, 'is_merged' => false]);


            $contact->forceDelete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Contact permanently deleted!']);
        } catch (Throwable $e) {
            DB::rollBack();
            \Log::error("Error force deleting contact: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to permanently delete contact.', 'error' => $e->getMessage()], 500);
        }
    }


}