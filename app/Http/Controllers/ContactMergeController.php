<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Services\ContactMergeService;
use Illuminate\Http\Request;
use Throwable; 



class ContactMergeController extends Controller {

    protected $contactMergeService;

    public function __construct(ContactMergeService $contactMergeService)
    {
        $this->contactMergeService = $contactMergeService;
    }

    public function initiateMerge(Contact $contact1, Contact $contact2) {

        if (!$contact1 || !$contact2) {
            return response()->json(['success' => false, 'message' => 'One or both contacts not found.'], 404);
        }

        $contact1->load('emails', 'phones', 'customFields.definition');
        $contact2->load('emails', 'phones', 'customFields.definition');

        return response()->json([
            'success' => true,
            'message' => 'Contacts retrieved for merge selection.',
            'contact1' => $contact1,
            'contact2' => $contact2,
        ]);
    }

    public function confirmMerge(Request $request) {
        $request->validate([
            'master_contact_id' => 'required|exists:contacts,id',
            'secondary_contact_id' => 'required|exists:contacts,id|different:master_contact_id',
        ]);

        $master = Contact::findOrFail($request->input('master_contact_id'));
        $secondary = Contact::findOrFail($request->input('secondary_contact_id'));

        try {

            $this->contactMergeService->merge($master, $secondary);

            return response()->json(['success' => true, 'message' => 'Contacts merged successfully!']);

        } catch (Throwable $e) {
            \Log::error("Error merging contacts: " . $e->getMessage(), [
                'master_id' => $master->id ?? 'N/A',
                'secondary_id' => $secondary->id ?? 'N/A',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to merge contacts.', 'error' => $e->getMessage()], 500);
        }
    }
}