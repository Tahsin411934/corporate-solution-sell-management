<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Corporate Solution - Invoice Management</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200..800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />

    @stack('head')
</head>

<body class="app-shell bg-[#e4ebf1] font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <div class="app-workspace min-w-0 flex-1 flex flex-col overflow-hidden">

        <!-- Navigation -->
        @include('layouts.navigation')

        <main class="app-main min-w-0 min-h-0 flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 relative flex flex-col">
            @include('components.global-loader')
            <div class="app-content min-w-0 flex-1 w-full">
                {{ $slot }}
            </div>

        </main>
    </div>



    <!-- JS LIBRARIES -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function () {
            $('[data-local-select2]').each(function () {
                const $select = $(this);
                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        dropdownParent: $select.closest('[id$="-drawer"]').length ? $select.closest('[id$="-drawer"]') : $(document.body),
                        width: '100%',
                        allowClear: !$select.prop('required'),
                        placeholder: $select.find('option:first').text() || 'Select an option'
                    });
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Sidebar JS -->
    <script src="{{ asset('js/sidebar.js') }}"></script>

    <script>
        window.Crud = window.Crud || {
            callbacks: {},
            register(entity, action, callback) {
                this.callbacks[entity] = this.callbacks[entity] || {};
                this.callbacks[entity][action] = callback;
            },
            get(entity, action) {
                return this.callbacks[entity]?.[action] ?? null;
            }
        };
    </script>
    @stack('scripts')

    <!-- Global loader, AJAX lifecycle and CSRF setup -->
    <script>
        (function () {
            'use strict';
            var isInitialPageLoad = true;

            function isDataTableProcessing() {
                return $('.dataTables_processing:visible').length > 0 ||
                    $('body').hasClass('dt-custom-loading');
            }

            function hideLoader() {
                if (!isDataTableProcessing()) {
                    $('#global-loader').addClass('hidden');
                    isInitialPageLoad = false;
                }
            }

            $(document).on('ajaxStart', function () {
                if (isInitialPageLoad && !isDataTableProcessing()) {
                    $('#global-loader').removeClass('hidden');
                }
            });

            $(document).on('ajaxStop', function () {
                setTimeout(hideLoader, 100);
            });

            if (document.readyState === 'complete') {
                hideLoader();
            } else {
                $(window).on('load', hideLoader);
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        })();
    </script>

    <!-- ✅ GLOBAL LOADER HANDLING - FINAL WORKING VERSION -->
    

</body>

</html>
