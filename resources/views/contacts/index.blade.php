<x-app-layout> {{-- Assumes your main layout is x-app-layout --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Contacts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- Filter Section --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Filter Contacts</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="name_filter" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" id="name_filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="email_filter" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="text" id="email_filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="gender_filter" class="block text-sm font-medium text-gray-700">Gender</label>
                            <select id="gender_filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">All</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        {{-- Bonus: Custom Field Filtering --}}
                        <div>
                            <label for="custom_field_name_filter" class="block text-sm font-medium text-gray-700">Custom Field Name</label>
                            <select id="custom_field_name_filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select Field</option>
                                @foreach($customFieldDefinitions as $definition)
                                    <option value="{{ $definition->field_name }}">{{ $definition->field_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="custom_field_value_filter" class="block text-sm font-medium text-gray-700">Custom Field Value</label>
                            <input type="text" id="custom_field_value_filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <button id="apply_filters" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Apply Filters
                        </button>
                        <button id="reset_filters" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150">
                            Reset Filters
                        </button>
                    </div>
                </div>

                {{-- Contact List Header and Buttons --}}
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Contact List</h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('contacts.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Add New Contact
                        </a>
                        <button id="merge_selected_contacts" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" disabled>
                            Merge Selected (0)
                        </button>
                        <a href="{{ route('contacts.trashed') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Trashed Contacts
                        </a>
                    </div>
                </div>

                {{-- DataTable Display --}}
                <div class="overflow-x-auto">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Merge Modal (for master selection) --}}
    <div id="mergeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Select Master Contact</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Please choose one contact to be the "master" record. Data from the other contact will be merged into it.
                    </p>
                    <div id="merge_contacts_selection" class="mt-4 text-left">
                        {{-- Radio buttons for master selection will be injected here by JS --}}
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmMergeSelectionBtn" class="px-4 py-2 bg-indigo-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Proceed to Merge Confirmation
                    </button>
                    <button id="closeMergeModalBtn" class="mt-2 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Yajra DataTables scripts --}}
        {{ $dataTable->scripts() }}

        <script>
            $(document).ready(function() {
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                
                const contactsDataTable = window.LaravelDataTables['contacts-table'];
                let selectedContactIds = []; 
                function updateMergeButton() {
                    const button = $('#merge_selected_contacts');
                    
                    button.text(`Merge Selected (${selectedContactIds.length})`);
                    if (selectedContactIds.length === 2) {
                        button.prop('disabled', false);
                    } else {
                        button.prop('disabled', true);
                    }
                }

                $(document).on('change', '.contact-select-checkbox', function() {
                    const contactId = $(this).data('id');
                    
                    if (this.checked) {
                        
                        if (selectedContactIds.length < 2) {
                            selectedContactIds.push(contactId);
                        } else {
                            alert('You can only select up to two contacts for merging.');
                            $(this).prop('checked', false); 
                        }
                    } else {
                        selectedContactIds = selectedContactIds.filter(id => id !== contactId);
                    }
                    updateMergeButton();
                });

                
                contactsDataTable.on('draw.dt', function() {
                    $('.contact-select-checkbox').each(function() {
                        const contactId = $(this).data('id');
                        if (selectedContactIds.includes(contactId)) {
                            $(this).prop('checked', true);
                        } else {
                            $(this).prop('checked', false);
                        }
                    });
                    updateMergeButton(); 
                });


                
                $('#apply_filters').on('click', function() {
                    const nameFilter = $('#name_filter').val();
                    const emailFilter = $('#email_filter').val();
                    const genderFilter = $('#gender_filter').val();
                    const customFieldName = $('#custom_field_name_filter').val();
                    const customFieldValue = $('#custom_field_value_filter').val();

                    
                    contactsDataTable.ajax.url('{{ route("contacts.data") }}' +
                        '?name_filter=' + encodeURIComponent(nameFilter) +
                        '&email_filter=' + encodeURIComponent(emailFilter) +
                        '&gender_filter=' + encodeURIComponent(genderFilter) +
                        '&custom_field_name=' + encodeURIComponent(customFieldName) +
                        '&custom_field_value=' + encodeURIComponent(customFieldValue)
                    ).load();
                });

                
                $('#reset_filters').on('click', function() {
                    $('#name_filter').val('');
                    $('#email_filter').val('');
                    $('#gender_filter').val('');
                    $('#custom_field_name_filter').val('');
                    $('#custom_field_value_filter').val('');
                   
                    contactsDataTable.ajax.url('{{ route("contacts.data") }}').load();
                });

        
                $(document).on('click', '.delete-contact', function(e) {
                    e.preventDefault();
                    const contactId = $(this).data('id');

                    if (confirm('Are you sure you want to move this contact to trash?')) {
                        $.ajax({
                            url: `/contacts/${contactId}`,
                            type: 'DELETE',
                            success: function(response) {
                                if (response.success) {
                                    alert(response.message);
                                    contactsDataTable.ajax.reload(null, false);
                                    
                                    selectedContactIds = selectedContactIds.filter(id => id !== contactId);
                                    updateMergeButton();
                                } else {
                                    alert('Error: ' + (response.message || 'Could not delete contact.'));
                                }
                            },
                            error: function(xhr) {
                                alert('An error occurred during deletion.');
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });

                
                $('#merge_selected_contacts').on('click', function() {
                    if (selectedContactIds.length === 2) {
                        const contact1_id = selectedContactIds[0];
                        const contact2_id = selectedContactIds[1];
                        
                        
                        $.ajax({
                            url: `{{ route('contacts.merge.init', ['contact1' => '__ID1__', 'contact2' => '__ID2__']) }}`
                                   .replace('__ID1__', contact1_id)
                                   .replace('__ID2__', contact2_id),
                            type: 'GET',
                            success: function(response) {
                               
                                const selectionDiv = $('#merge_contacts_selection');
                                selectionDiv.empty(); 

                                const contact1 = response.contact1;
                                const contact2 = response.contact2;

                                selectionDiv.append(`
                                    <div class="flex items-center mb-2 p-2 border rounded-md">
                                        <input type="radio" id="master_contact_${contact1.id}" name="master_contact" value="${contact1.id}" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                        <label for="master_contact_${contact1.id}" class="ml-2 block text-sm font-medium text-gray-700">
                                            <strong>${contact1.name || 'N/A Name'}</strong> (${contact1.email || 'N/A Email'})
                                            <br>
                                            <span class="text-xs text-gray-500">Phone: ${contact1.phone || 'N/A'} | Gender: ${contact1.gender || 'N/A'}</span>
                                            ${contact1.custom_fields && contact1.custom_fields.length > 0 ?
                                                '<div class="mt-1 text-xs text-gray-500">Custom Fields: ' +
                                                contact1.custom_fields.map(cf => `${cf.definition.field_name}: ${cf.value}`).join(', ') +
                                                '</div>' : ''
                                            }
                                        </label>
                                    </div>
                                    <div class="flex items-center mb-2 p-2 border rounded-md">
                                        <input type="radio" id="master_contact_${contact2.id}" name="master_contact" value="${contact2.id}" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                        <label for="master_contact_${contact2.id}" class="ml-2 block text-sm font-medium text-gray-700">
                                            <strong>${contact2.name || 'N/A Name'}</strong> (${contact2.email || 'N/A Email'})
                                            <br>
                                            <span class="text-xs text-gray-500">Phone: ${contact2.phone || 'N/A'} | Gender: ${contact2.gender || 'N/A'}</span>
                                            ${contact2.custom_fields && contact2.custom_fields.length > 0 ?
                                                '<div class="mt-1 text-xs text-gray-500">Custom Fields: ' +
                                                contact2.custom_fields.map(cf => `${cf.definition.field_name}: ${cf.value}`).join(', ') +
                                                '</div>' : ''
                                            }
                                        </label>
                                    </div>
                                `);
                                $('#mergeModal').removeClass('hidden'); // Show the modal
                            },
                            error: function(xhr) {
                                alert('Error preparing merge: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.'));
                                console.error(xhr.responseText);
                            }
                        });
                    } else {
                        alert('Please select exactly two contacts to merge.');
                    }
                });

                
                $('#closeMergeModalBtn').on('click', function() {
                    $('#mergeModal').addClass('hidden');
                    
                    selectedContactIds = [];
                    $('.contact-select-checkbox').prop('checked', false); 
                    updateMergeButton();
                });

                
                $('#confirmMergeSelectionBtn').on('click', function() {
                    const masterId = $('input[name="master_contact"]:checked').val();
                    if (!masterId) {
                        alert('Please select a master contact.');
                        return;
                    }

                    
                    const secondaryId = selectedContactIds.find(id => String(id) !== masterId);

                    if (confirm(`Confirm merge: Contact ID ${masterId} will be the master, and Contact ID ${secondaryId} will be merged into it. This action cannot be easily undone. Are you absolutely sure?`)) {
                        $.ajax({
                            url: '{{ route("contacts.merge.confirm") }}',
                            type: 'POST',
                            data: {
                                master_contact_id: masterId,
                                secondary_contact_id: secondaryId
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert(response.message);
                                    $('#mergeModal').addClass('hidden'); 
                                    contactsDataTable.ajax.reload(null, false); 
                                    selectedContactIds = [];
                                    $('.contact-select-checkbox').prop('checked', false); 
                                    updateMergeButton();
                                } else {
                                    alert('Merge failed: ' + (response.message || 'Unknown error.'));
                                }
                            },
                            error: function(xhr) {
                                alert('An error occurred during merge confirmation: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });
            }); 
        </script>
    @endpush
</x-app-layout>