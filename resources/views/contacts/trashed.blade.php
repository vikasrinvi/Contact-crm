<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trashed Contacts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="flex justify-end items-center mb-4">
                    <a href="{{ route('contacts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to Active Contacts
                    </a>
                </div>

                <div class="overflow-x-auto">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- This renders the DataTable HTML and initializes it via JavaScript --}}
        {{ $dataTable->scripts() }}

        <script>
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            
            $('#contacts-table').on('init.dt', function () {
                const trashedContactsDataTable = window.LaravelDataTables['contacts-table'];
                
                $(document).on('click', '.restore-contact', function(e) {
                    e.preventDefault();
                    const contactId = $(this).data('id');
                    console.log('Restore button clicked for ID:', contactId); 

                    if (confirm('Are you sure you want to restore this contact?')) {
                        $.ajax({
                            url: `/contacts/${contactId}/restore`,
                            type: 'POST',
                            success: function(response) {
                                if (response.success) {
                                    alert(response.message);
                                    trashedContactsDataTable.ajax.reload(null, false); 
                                } else {
                                    alert('Error: ' + (response.message || 'Could not restore contact.'));
                                }
                            },
                            error: function(xhr) {
                                alert('An error occurred during restoration.');
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });

                
                $(document).on('click', '.force-delete-contact', function(e) {
                    e.preventDefault();
                    const contactId = $(this).data('id');
                    console.log('Force Delete button clicked for ID:', contactId); 


                    if (confirm('WARNING: This will permanently delete the contact and all associated custom field values and files. This action cannot be undone. Are you absolutely sure?')) {
                        $.ajax({
                            url: `/contacts/${contactId}/force-delete`,
                            type: 'DELETE', 
                            success: function(response) {
                                if (response.success) {
                                    alert(response.message);
                                    trashedContactsDataTable.ajax.reload(null, false); 
                                } else {
                                    alert('Error: ' + (response.message || 'Could not permanently delete contact.'));
                                }
                            },
                            error: function(xhr) {
                                alert('An error occurred during permanent deletion.');
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });

            }).on('error.dt', function(e, settings, techNote, message) {
                
                console.error('An error occurred during DataTable initialization:', message);
            });
        </script>
    @endpush
</x-app-layout>