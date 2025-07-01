<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Custom Field') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form id="createCustomFieldForm" class="space-y-6">
                    @csrf

                    <div>
                        <label for="field_name" class="block text-sm font-medium text-gray-700">Field Name <span class="text-red-500">*</span></label>
                        <input type="text" name="field_name" id="field_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <p class="text-sm text-red-600 mt-1" id="field_name-error"></p>
                    </div>

                    <div>
                        <label for="field_type" class="block text-sm font-medium text-gray-700">Field Type <span class="text-red-500">*</span></label>
                        <select name="field_type" id="field_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">Select Type</option>
                            <option value="text">Text Input</option>
                            <option value="number">Number Input</option>
                            <option value="date">Date Input</option>
                            <option value="textarea">Textarea</option>
                            <option value="checkbox">Checkbox (True/False)</option>
                            {{-- <option value="radio">Radio Buttons (Requires Options)</option> --}}
                        </select>
                        <p class="text-sm text-red-600 mt-1" id="field_type-error"></p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_required" id="is_required" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <label for="is_required" class="ml-2 block text-sm font-medium text-gray-700">Is Required?</label>
                        <p class="text-sm text-red-600 ml-4" id="is_required-error"></p>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Create Custom Field
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $('#createCustomFieldForm').on('submit', function(e) {
                    e.preventDefault();

                    $('.text-red-600').text('');
                    $('.border-red-500').removeClass('border-red-500');

                    $.ajax({
                        url: '{{ route('custom-fields.store') }}',
                        type: 'POST',
                        data: $(this).serialize(), 
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                window.location.href = '{{ route('custom-fields.index') }}';
                            } else {
                                alert('Error: ' + (response.message || 'Could not create custom field.'));
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) { 
                                const errors = xhr.responseJSON.errors;
                                for (const field in errors) {
                                    $(`#${field}-error`).text(errors[field][0]);
                                    $(`[name="${field}"]`).addClass('border-red-500');
                                }
                                alert('Validation failed. Please check the form.');
                            } else {
                                alert('An unexpected error occurred. Please try again.');
                                console.error(xhr.responseText);
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>