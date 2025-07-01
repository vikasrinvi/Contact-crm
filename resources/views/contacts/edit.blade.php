<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Contact: ') . $contact->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form id="editContactForm" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT') {{-- Use PUT method for update --}}

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $contact->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <p class="text-sm text-red-600 mt-1" id="name-error"></p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $contact->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <p class="text-sm text-red-600 mt-1" id="email-error"></p>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $contact->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <p class="text-sm text-red-600 mt-1" id="phone-error"></p>
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                        <select name="gender" id="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender', $contact->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $contact->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender', $contact->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <p class="text-sm text-red-600 mt-1" id="gender-error"></p>
                    </div>

                    <div>
                        <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Image</label>
                        @if($contact->profile_image)
                            <div class="mt-2 flex items-center space-x-4">
                                <img src="{{ Storage::url($contact->profile_image) }}" alt="Profile Image" class="w-20 h-20 object-cover rounded-full">
                                <label class="flex items-center text-sm font-medium text-gray-700">
                                    <input type="checkbox" name="clear_profile_image" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                                    Remove existing image
                                </label>
                            </div>
                        @endif
                        <input type="file" name="profile_image" id="profile_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-sm text-gray-500 mt-1">Max 2MB. JPG, PNG, GIF. Leave blank to keep current, or select new to replace.</p>
                        <p class="text-sm text-red-600 mt-1" id="profile_image-error"></p>
                    </div>

                    <div>
                        <label for="additional_file" class="block text-sm font-medium text-gray-700">Additional File</label>
                        @if($contact->additional_file)
                            <div class="mt-2 flex items-center space-x-4">
                                <a href="{{ Storage::url($contact->additional_file) }}" target="_blank" class="text-indigo-600 hover:underline">View Current File</a>
                                <label class="flex items-center text-sm font-medium text-gray-700">
                                    <input type="checkbox" name="clear_additional_file" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                                    Remove existing file
                                </label>
                            </div>
                        @endif
                        <input type="file" name="additional_file" id="additional_file" accept=".pdf,.doc,.docx,.txt" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-sm text-gray-500 mt-1">Max 5MB. PDF, DOC, DOCX, TXT. Leave blank to keep current, or select new to replace.</p>
                        <p class="text-sm text-red-600 mt-1" id="additional_file-error"></p>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4">Custom Fields</h3>
                    <div class="space-y-4">
                        @forelse($customFieldDefinitions as $definition)
                            @php
                                $currentValue = $contact->customFields->firstWhere('custom_field_definition_id', $definition->id)->value ?? '';
                            @endphp
                            <div>
                                <label for="custom_fields[{{ $definition->id }}]" class="block text-sm font-medium text-gray-700">
                                    {{ $definition->field_name }}
                                    @if($definition->is_required) <span class="text-red-500">*</span> @endif
                                </label>
                                @if($definition->field_type == 'textarea')
                                    <textarea name="custom_fields[{{ $definition->id }}]" id="custom_fields[{{ $definition->id }}]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('custom_fields.' . $definition->id, $currentValue) }}</textarea>
                                @elseif($definition->field_type == 'date')
                                    <input type="date" name="custom_fields[{{ $definition->id }}]" id="custom_fields[{{ $definition->id }}]" value="{{ old('custom_fields.' . $definition->id, $currentValue) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @elseif($definition->field_type == 'number')
                                    <input type="number" name="custom_fields[{{ $definition->id }}]" id="custom_fields[{{ $definition->id }}]" value="{{ old('custom_fields.' . $definition->id, $currentValue) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @elseif($definition->field_type == 'checkbox')
                                    <input type="checkbox" name="custom_fields[{{ $definition->id }}]" id="custom_fields[{{ $definition->id }}]" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('custom_fields.' . $definition->id, $currentValue) == '1' ? 'checked' : '' }}>
                                @elseif($definition->field_type == 'radio')
                                     {{-- Same note as create: needs options defined in definition --}}
                                    <input type="text" name="custom_fields[{{ $definition->id }}]" id="custom_fields[{{ $definition->id }}]" value="{{ old('custom_fields.' . $definition->id, $currentValue) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Enter value">
                                @else {{-- Default to text input --}}
                                    <input type="text" name="custom_fields[{{ $definition->id }}]" id="custom_fields[{{ $definition->id }}]" value="{{ old('custom_fields.' . $definition->id, $currentValue) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @endif
                                <p class="text-sm text-red-600 mt-1" id="custom_fields.{{ $definition->id }}-error"></p>
                            </div>
                        @empty
                            <p class="text-gray-500">No custom fields defined yet.</p>
                        @endforelse
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Update Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // CSRF token setup
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $('#editContactForm').on('submit', function(e) {
                    e.preventDefault();

                    
                    $('.text-red-600').text('');
                    $('.border-red-500').removeClass('border-red-500');

                    const formData = new FormData(this);
                    
                    formData.append('_method', 'PUT');

                    $.ajax({
                        url: '{{ route('contacts.update', $contact->id) }}',
                        type: 'POST', 
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                window.location.href = '{{ route('contacts.index') }}'; 
                            } else {
                                alert('Error: ' + (response.message || 'Could not update contact.'));
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) { 
                                const errors = xhr.responseJSON.errors;
                                for (const field in errors) {
                                    const errorId = field.replace(/\./g, '\\.'); 
                                    $(`#${errorId}-error`).text(errors[field][0]);
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