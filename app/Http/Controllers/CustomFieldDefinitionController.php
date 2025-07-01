<?php

namespace App\Http\Controllers;

use App\Models\CustomFieldDefinition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 

class CustomFieldDefinitionController extends Controller
{

    public function index()
    {
        $customFieldDefinitions = CustomFieldDefinition::all();
        return view('custom-fields.index', compact('customFieldDefinitions'));
    }


    public function create()
    {
        return view('custom-fields.create');
    }

    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'field_name' => 'required|string|max:255|unique:custom_field_definitions,field_name',
            'field_type' => 'required|string|in:text,number,date,textarea,checkbox,radio', // Define allowed types
            'is_required' => 'boolean',
        ]);

        try {
            $customFieldDefinition = CustomFieldDefinition::create($validatedData);
            return response()->json(['success' => true, 'message' => 'Custom field definition created successfully!', 'field' => $customFieldDefinition]);
        } catch (\Exception $e) {
            \Log::error("Error creating custom field definition: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create custom field definition.', 'error' => $e->getMessage()], 500);
        }
    }


    public function edit(CustomFieldDefinition $customField) // Laravel's route model binding
    {
        return view('custom-fields.edit', compact('customField'));
    }


    public function update(Request $request, CustomFieldDefinition $customField)
    {
        $validatedData = $request->validate([
            'field_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('custom_field_definitions')->ignore($customField->id), // Ignore current field's ID
            ],
            'field_type' => 'required|string|in:text,number,date,textarea,checkbox,radio',
            'is_required' => 'boolean',
        ]);

        try {
            $customField->update($validatedData);
            return response()->json(['success' => true, 'message' => 'Custom field definition updated successfully!', 'field' => $customField]);
        } catch (\Exception $e) {
            \Log::error("Error updating custom field definition: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update custom field definition.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(CustomFieldDefinition $customField)
    {
        try {
           
            $customField->delete();
            return response()->json(['success' => true, 'message' => 'Custom field definition deleted successfully!']);
        } catch (\Exception $e) {
            \Log::error("Error deleting custom field definition: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete custom field definition.', 'error' => $e->getMessage()], 500);
        }
    }
}