<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Contact Details: ') . $contact->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        {{-- Basic Information from the Main Contact Table --}}
                        <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>Name:</strong> {{ $contact->name }}</p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Email:</strong> {{ $contact->email }}</p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Phone:</strong> {{ $contact->phone ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Gender:</strong> {{ $contact->gender ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Status:</strong> <span class="capitalize">{{ $contact->merge_status }}</span></p>
                        @if($contact->merged_into_contact_id)
                            <p class="text-sm text-gray-600 mb-2"><strong>Merged Into:</strong>
                                @if($contact->mergedInto)
                                    <a href="{{ route('contacts.show', $contact->mergedInto->id) }}" class="text-indigo-600 hover:underline">{{ $contact->mergedInto->name }}</a>
                                @else
                                    [Deleted Contact]
                                @endif
                            </p>
                        @endif

                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-4">Files</h3>
                        <div class="space-y-2">
                            @if($contact->profile_image)
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm text-gray-600"><strong>Profile Image:</strong></p>
                                    <img src="{{ Storage::url($contact->profile_image) }}" alt="Profile Image" class="w-16 h-16 object-cover rounded-full border border-gray-200">
                                    <a href="{{ Storage::url($contact->profile_image) }}" target="_blank" class="text-indigo-600 hover:underline text-sm">View</a>
                                </div>
                            @else
                                <p class="text-sm text-gray-600"><strong>Profile Image:</strong> No Image</p>
                            @endif

                            @if($contact->additional_file)
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm text-gray-600"><strong>Additional File:</strong></p>
                                    <a href="{{ Storage::url($contact->additional_file) }}" target="_blank" class="text-indigo-600 hover:underline text-sm">View File</a>
                                </div>
                            @else
                                <p class="text-sm text-gray-600"><strong>Additional File:</strong> No File</p>
                            @endif
                        </div>
                    </div>
                        <p class="text-sm text-gray-600 mb-2"><strong>Status:</strong> <span class="capitalize">{{ $contact->merge_status }}</span></p>

                        {{-- Show if this contact was merged into another --}}
                        @if($contact->merged_into_contact_id)
                            <p class="text-sm text-gray-600 mb-2"><strong>Merged Into:</strong>
                                @if($contact->mergedInto)
                                    <a href="{{ route('contacts.show', $contact->mergedInto->id) }}" class="text-indigo-600 hover:underline">{{ $contact->mergedInto->name }}</a>
                                @else
                                    [Deleted Contact]
                                @endif
                            </p>
                        @endif

                        {{-- Secondary Contact Info (Emails and Phones from Merged Records) --}}
                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-4">Secondary Contact Info (from Merged Records)</h3>
                        <div>
                            <p class="text-sm text-gray-600 mb-1"><strong>Additional Emails:</strong></p>
                            @forelse($contact->emails as $email)
                                <p class="ml-4 text-sm text-gray-700">{{ $email->email }}</p>
                            @empty
                                <p class="ml-4 text-sm text-gray-700">N/A</p>
                            @endforelse

                            <p class="text-sm text-gray-600 mt-3 mb-1"><strong>Additional Phone Numbers:</strong></p>
                            @forelse($contact->phones as $phone)
                                <p class="ml-4 text-sm text-gray-700">{{ $phone->phone }}</p>
                            @empty
                                <p class="ml-4 text-sm text-gray-700">N/A</p>
                            @endforelse
                        </div>

                        {{-- File Attachments --}}
                       
                    </div>

                    <div>
                        {{-- Custom Fields of the Main Contact --}}
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Custom Fields</h3>
                        <div class="space-y-3">
                            @forelse($contact->customFields as $customField)
                                <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                    <p class="text-sm font-medium text-gray-700">{{ $customField->definition->field_name }}:</p>
                                    {{-- Handle multi-value custom fields (e.g., 'multiselect') by splitting their comma-separated string --}}
                                    @php
                                        $isMultiValue = ($customField->definition->field_type === 'multiselect' || (property_exists($customField->definition, 'can_be_multi_value') && $customField->definition->can_be_multi_value));
                                        $displayValues = $isMultiValue && !empty($customField->value) ? array_map('trim', explode(',', $customField->value)) : [$customField->value];
                                    @endphp
                                    @forelse($displayValues as $val)
                                        <p class="text-sm text-gray-800 {{ $isMultiValue ? 'ml-4' : '' }}">{{ $val ?? 'N/A' }}</p>
                                    @empty
                                        <p class="text-sm text-gray-800">N/A</p>
                                    @endforelse
                                </div>
                            @empty
                                <p class="text-gray-500">No custom fields for this contact.</p>
                            @endforelse
                        </div>

                        {{-- Contacts Merged Into This Contact --}}
                        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-4">Contacts Merged Into This</h3>
                        <div class="space-y-3">
                            @forelse($contact->mergedFrom as $mergedContact)
                                <div class="bg-green-50 p-3 rounded-md border border-green-200">
                                    <p class="text-sm font-medium text-gray-700">
                                        <a href="{{ route('contacts.show', $mergedContact->id) }}" class="text-green-700 hover:underline">
                                            {{ $mergedContact->name }} (ID: {{ $mergedContact->id }})
                                        </a>
                                    </p>
                                    {{-- Display emails of the merged contact --}}
                                    <p class="text-xs text-gray-600 mt-2 mb-1"><strong>Emails:</strong> {{ $mergedContact->email ?? 'N/A' }} (primary)</p>
                                    @forelse($mergedContact->emails as $email)
                                        <p class="ml-2 text-xs text-gray-700">{{ $email->email }}</p>
                                    @empty
                                        <p class="ml-2 text-xs text-gray-700">N/A</p>
                                    @endforelse

                                    {{-- Display phone numbers of the merged contact --}}
                                    <p class="text-xs text-gray-600 mt-2 mb-1"><strong>Phone Numbers:</strong> {{ $mergedContact->phone ?? 'N/A' }} (primary)</p>
                                    @forelse($mergedContact->phones as $phone)
                                        <p class="ml-2 text-xs text-gray-700">{{ $phone->phone_number }}</p>
                                    @empty
                                        <p class="ml-2 text-xs text-gray-700">N/A</p>
                                    @endforelse

                                    @if ($mergedContact->gender)
                                        <p class="text-xs text-gray-600">Gender: {{ $mergedContact->gender }}</p>
                                    @endif
                                    @if ($mergedContact->address)
                                        <p class="text-xs text-gray-600">Address: {{ $mergedContact->address }}</p>
                                    @endif

                                    {{-- Display custom fields of merged contacts --}}
                                    @if($mergedContact->customFields->isNotEmpty())
                                        <p class="text-xs font-medium text-gray-700 mt-2">Custom Fields:</p>
                                        @foreach($mergedContact->customFields as $mcCustomField)
                                            <p class="ml-2 text-xs text-gray-600">
                                                {{ $mcCustomField->definition->field_name }}: {{ $mcCustomField->value ?? 'N/A' }}
                                            </p>
                                        @endforeach
                                    @endif
                                </div>
                            @empty
                                <p class="text-gray-500">No contacts have been merged into this one.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('contacts.edit', $contact->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Edit Contact
                    </a>
                    <a href="{{ route('contacts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>