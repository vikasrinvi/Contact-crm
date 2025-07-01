<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">

    <style>
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-color: #d1d5db; /* gray-300 */
            border-radius: 0.375rem; /* rounded-md */
            padding: 0.5rem 0.75rem; /* py-2 px-3 */
            margin-left: 0.5rem;
            margin-right: 0.5rem;
        }
        .dataTables_wrapper .dt-buttons button {
            background-color: #4f46e5; /* indigo-600 */
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            margin-right: 0.25rem;
            margin-bottom: 0.5rem;
            transition: background-color 0.2s ease-in-out;
        }
        .dataTables_wrapper .dt-buttons button:hover {
            background-color: #4338ca; /* indigo-700 */
        }
        .dataTables_wrapper .paginate_button {
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            margin: 0 0.25rem;
            cursor: pointer;
        }
        .dataTables_wrapper .paginate_button.current,
        .dataTables_wrapper .paginate_button:hover {
            background-color: #6366f1; /* indigo-500 */
            color: white !important;
            border-color: #6366f1;
        }
        .dataTables_wrapper .dataTables_info {
            padding-top: 0.85em;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation') {{-- Breeze navigation --}}

        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main>
            {{ $slot }}
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>

    @stack('scripts')
</body>
</html>