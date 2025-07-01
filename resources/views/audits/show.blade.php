<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Details: ') . $audit->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Audit Information</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>Event:</strong> <span class="uppercase font-bold">{{ str_replace('_', ' ', $audit->event) }}</span></p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Auditable Type:</strong> {{ class_basename($audit->auditable_type) }}</p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Auditable ID:</strong>
                            @if($audit->auditable)
                                <a href="{{ route('contacts.show', $audit->auditable_id) }}" class="text-indigo-600 hover:underline">
                                    {{ $audit->auditable_id }} ({{ $audit->auditable->name ?? 'N/A' }})
                                </a>
                            @else
                                {{ $audit->auditable_id }} (Original contact deleted)
                            @endif
                        </p>
                        <p class="text-sm text-gray-600 mb-2"><strong>Performed By:</strong> {{ $audit->user->name ?? 'System/Unknown User' }}</p>
                        <p class="text-sm text-gray-600 mb-2"><strong>When:</strong> {{ $audit->created_at->format('M d, Y H:i A') }} (IST)</p>
                        <p class="text-sm text-gray-600 mb-2"><strong>IP Address:</strong> {{ $audit->ip_address ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600 mb-2"><strong>URL:</strong> <span class="break-all">{{ $audit->url ?? 'N/A' }}</span></p>
                        <p class="text-sm text-gray-600 mb-2"><strong>User Agent:</strong> <span class="break-all">{{ $audit->user_agent ?? 'N/A' }}</span></p>
                    </div>

                    <div>
                        {{-- Display Old Values --}}
                        @if ($audit->old_values)
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Old Values</h3>
                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200 mb-4">
                                <pre class="text-sm text-gray-800 overflow-auto">{{ json_encode($audit->old_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif

                        {{-- Display New Values --}}
                        @if ($audit->new_values)
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">New Values</h3>
                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200 mb-4">
                                <pre class="text-sm text-gray-800 overflow-auto">{{ json_encode($audit->new_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif

                        {{-- Display Custom Details (especially for 'merged' event) --}}
                        @if ($audit->custom_details)
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Custom Details</h3>
                            <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                                @if ($audit->event === 'merged')
                                    @php
                                        $mergeDetails = $audit->custom_details['merge_details'] ?? [];
                                        $secondaryContactName = $audit->custom_details['secondary_contact_name'] ?? 'Unknown Secondary';
                                        $secondaryContactId = $audit->custom_details['secondary_contact_id'] ?? 'N/A';
                                    @endphp
                                    <h4 class="text-md font-semibold text-gray-800 mb-2">
                                        Merged From:
                                        <a href="{{ route('contacts.show', $secondaryContactId) }}" class="text-blue-600 hover:underline">
                                            {{ $secondaryContactName }} (ID: {{ $secondaryContactId }})
                                        </a>
                                    </h4>

                                    @if (!empty($mergeDetails['standard_fields_affected']))
                                        <h5 class="text-sm font-semibold text-gray-700 mt-3">Standard Field Changes:</h5>
                                        <ul class="list-disc list-inside text-sm text-gray-600">
                                            @foreach ($mergeDetails['standard_fields_affected'] as $fieldChange)
                                                <li>
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $fieldChange['field_name'])) }}</strong>:
                                                    @if ($fieldChange['action'] === 'ADDED_FROM_SECONDARY')
                                                        Added from secondary ({{ $fieldChange['secondary_value'] }}).
                                                    @elseif ($fieldChange['action'] === 'MASTER_VALUE_KEPT')
                                                        Master's "{{ $fieldChange['master_value_before_merge'] }}" kept, secondary's "{{ $fieldChange['secondary_value'] }}" discarded.
                                                    @elseif ($fieldChange['action'] === 'UPDATED_BY_SECONDARY')
                                                        Updated from "{{ $fieldChange['master_value_before_merge'] }}" to "{{ $fieldChange['master_value_after_merge'] }}" (from secondary).
                                                    @elseif ($fieldChange['action'] === 'COMBINED_VALUES')
                                                        Combined values: "{{ $fieldChange['master_value_before_merge'] }}" and "{{ $fieldChange['secondary_value'] }}" resulted in "{{ $fieldChange['master_value_after_merge'] }}".
                                                    @else
                                                        {{ $fieldChange['action'] }}
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if (!empty($mergeDetails['custom_fields_affected']))
                                        <h5 class="text-sm font-semibold text-gray-700 mt-3">Custom Field Changes:</h5>
                                        <ul class="list-disc list-inside text-sm text-gray-600">
                                            @foreach ($mergeDetails['custom_fields_affected'] as $fieldChange)
                                                <li>
                                                    <strong>{{ $fieldChange['field_name'] }} (Custom)</strong>:
                                                    @if ($fieldChange['action'] === 'ADDED_FROM_SECONDARY')
                                                        Added from secondary ({{ $fieldChange['secondary_value'] }}).
                                                    @elseif ($fieldChange['action'] === 'UPDATED_BY_SECONDARY')
                                                        Updated from "{{ $fieldChange['master_value_before_merge'] }}" to "{{ $fieldChange['master_value_after_merge'] }}" (from secondary).
                                                    @elseif ($fieldChange['action'] === 'MASTER_VALUE_KEPT')
                                                        Master's "{{ $fieldChange['master_value_before_merge'] }}" kept, secondary's "{{ $fieldChange['secondary_value'] }}" discarded.
                                                    @elseif ($fieldChange['action'] === 'COMBINED_VALUES')
                                                        Combined values: "{{ $fieldChange['master_value_before_merge'] }}" and "{{ $fieldChange['secondary_value'] }}" resulted in "{{ $fieldChange['master_value_after_merge'] }}".
                                                    @else
                                                        {{ $fieldChange['action'] }}
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if (!empty($mergeDetails['relationships_reassigned']))
                                        <h5 class="text-sm font-semibold text-gray-700 mt-3">Relationships Reassigned:</h5>
                                        <ul class="list-disc list-inside text-sm text-gray-600">
                                            @foreach ($mergeDetails['relationships_reassigned']['emails'] ?? [] as $item)
                                                <li>Email (ID: {{ $item['id'] }}): {{ $item['value'] }} moved.</li>
                                            @endforeach
                                            @foreach ($mergeDetails['relationships_reassigned']['phones'] ?? [] as $item)
                                                <li>Phone (ID: {{ $item['id'] }}): {{ $item['value'] }} moved.</li>
                                            @endforeach
                                            @foreach ($mergeDetails['relationships_reassigned']['notes'] ?? [] as $item)
                                                <li>Note (ID: {{ $item['id'] }}): "{{ $item['snippet'] }}" moved.</li>
                                            @endforeach
                                        </ul>
                                    @endif

                                @else
                                    <pre class="text-sm text-gray-800 overflow-auto">{{ json_encode($audit->custom_details, JSON_PRETTY_PRINT) }}</pre>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <a href="{{ route('audits.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to Audit List
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>