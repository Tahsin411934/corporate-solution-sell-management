@props([
    'id' => 'customTable',
    'title' => 'Management',
    'icon' => 'fa-solid fa-list',
    'buttonId' => 'btnAddNew',
    'buttonText' => 'Add New',
    'buttonLink' => null,
    'createPermission' => null, // Permission group (e.g., 'units') — hides the Add New button unless granted
    'columns' => [],
    'ajaxUrl' => '',
    'dtColumns' => [],
    'exportButtons' => true,
    'filters' => [],
    'order' => [[0, 'desc']],
    'scrollHeight' => '60vh',
])

@php
    // Show the "Add New" / create button only when the role has the create permission.
    $canCreate = ! $createPermission || auth()->user()?->can($createPermission.'.create');
@endphp

<div class="reusable-data-table bg-white dark:bg-gray-800 shadow-md rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    {{-- TABLE HEADER --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 gap-4">
        <div class="flex items-center space-x-2.5">
            <i class="{{ $icon }} text-primary text-lg"></i>
            <span class="font-bold text-gray-800 dark:text-gray-200 tracking-tight text-base">{{ $title }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-3 justify-end">

            @if ($canCreate)
                @if ($buttonLink)
                    <a href="{{ $buttonLink }}"
                        class="bg-primary hover:bg-primary text-white px-4 py-2 rounded-lg shadow-sm flex items-center gap-2 transition-all duration-200 text-sm font-medium whitespace-nowrap active:scale-95">
                        <i class="fa fa-plus-circle"></i> {{ $buttonText }}
                    </a>
                @elseif ($buttonId)
                    <button id="{{ $buttonId }}"
                        class="bg-primary hover:bg-primary text-white px-4 py-2 rounded-lg shadow-sm flex items-center gap-2 transition-all duration-200 text-sm font-medium whitespace-nowrap active:scale-95">
                        <i class="fa fa-plus-circle"></i> {{ $buttonText }}
                    </button>
                @endif
            @endif
        </div>
    </div>

    {{-- TABLE BODY --}}
    <div class="p-3 sm:p-6">
        <div class="min-w-0">
            <div class="table-load-error hidden mb-3 text-sm text-red-600" role="alert">Unable to load records. <button type="button" class="table-retry underline">Retry</button></div>
            <table id="{{ $id }}" class="w-full border-collapse rounded-lg text-sm text-gray-700">
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th 
                                class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider
                                       {{ $column === 'Action' || $column === 'Actions' ? 'text-center' : '' }}">
                                {{ $column }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            const tableId = @json($id);
            const tableElement = $(document.getElementById(tableId));
            const panel = tableElement.closest(".reusable-data-table");
            const columnConfig = @json($dtColumns);
            columnConfig.forEach(function(column) {
                if (typeof column.render === 'string' && column.render.trim().startsWith('function')) {
                    column.render = (0, eval)('(' + column.render + ')');
                }
            });
            const exportColumns = columnConfig.map((column, index) =>
                column.exportable === false || ["action", "actions", "row_actions"].includes(column.data) ? null : index
            ).filter(index => index !== null);

            function buildAjaxData(filters) {
                return function(d) {
                    Object.keys(filters || {}).forEach(key => {
                        let selector = filters[key];
                        let value = $(selector).val();
                        d[key] = value;
                    });
                }
            }
            let tableButtons = [];

            @if ($exportButtons)
                tableButtons = [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel mr-1.5 text-green-500"></i> Excel',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa-solid fa-file-pdf mr-1.5 text-red-500"></i> PDF',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa-solid fa-print mr-1.5 text-gray-500"></i> Print',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa-solid fa-table-columns mr-1.5 text-primary"></i> Columns',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm'
                    },
                ];
            @endif

            // Bind before initialization so the native DataTables processing
            // event is not missed on the initial request or later reloads.
            tableElement.on('processing.dt', function(e, settings, processing) {
                $(this).closest('.dataTables_wrapper')
                    .toggleClass('dt-is-loading', processing)
                    .find('.dataTables_processing')
                    .css('display', processing ? 'block' : 'none');
            });

            tableElement.on('preXhr.dt', function() {
                panel.find('.table-load-error').addClass('hidden');
                $(this).closest('.dataTables_wrapper').addClass('dt-is-loading');
            }).on('xhr.dt error.dt', function(e, settings, json) {
                if (e.type === 'error' || json === null || (json && json.error)) {
                    panel.find('.table-load-error').removeClass('hidden');
                    $(this).closest('.dataTables_wrapper').find('.dataTables_processing').hide();
                }
                $(this).closest('.dataTables_wrapper').removeClass('dt-is-loading');
                if (e.type === 'xhr' && json === null) return true;
            });

            const table = tableElement.DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: @json($ajaxUrl),
                    data: buildAjaxData(@json($filters ?? []))
                },
                columns: columnConfig,
                dom: '<"flex flex-col md:flex-row items-center justify-between gap-4 mb-4"lBf>rt<"flex flex-col md:flex-row items-center justify-between gap-4 mt-4"ip>',
                buttons: tableButtons,
                scrollX: true,
                scrollY: @json($scrollHeight),
                scrollCollapse: true,
                // Keep every column reachable through horizontal scrolling.
                responsive: false,
                order: @json($order),
                language: {
                    search: "",
                    searchPlaceholder: "Search records...",
                    lengthMenu: "Show _MENU_ entries"
                }
            });

            panel.find('.table-retry').on('click', function() { table.ajax.reload(null, false); });

            const scrollBody = panel.find('.dataTables_scrollBody');
            scrollBody.attr({ tabindex: 0, role: 'region', 'aria-label': @json($title.' — scrollable table') });
            if (window.ResizeObserver) {
                let previousWidth = 0;
                let resizeFrame;
                const observer = new ResizeObserver(function(entries) {
                    const width = entries[0].contentRect.width;
                    if (width > 0 && width !== previousWidth) {
                        previousWidth = width;
                        cancelAnimationFrame(resizeFrame);
                        resizeFrame = requestAnimationFrame(function() { table.columns.adjust(); });
                    }
                });
                observer.observe(panel[0]);
                tableElement.on('destroy.dt', function() {
                    observer.disconnect();
                    cancelAnimationFrame(resizeFrame);
                });
            }

            $(document).on('change', '.dt-filter-' + tableId, function() {
                table.ajax.reload();
            });

            // Keep the component's button styling and remove only the
            // DataTables default button class. Do not depend on an optional
            // external buttons container.
            @if ($exportButtons)
                table.buttons().container().find('.dt-button').removeClass('dt-button');
            @endif

            // Global delete function for URL-based action buttons.
            // Defined here so any page using x-data-table can delete rows
            // (e.g. Purchase Orders, Suppliers, Inventory Locations, etc.).
            var deleteEntity = function(url, sourceTable) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#4b5563',
                    confirmButtonText: 'Yes, delete it!'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                if (res.status === 'success') {
                                    Toastify({
                                        text: res.message || 'Deleted successfully',
                                        duration: 3000,
                                        gravity: 'bottom',
                                        position: 'right',
                                        style: { background: 'linear-gradient(135deg, #dc2626, #f87171)' }
                                    }).showToast();
                                    if (sourceTable) {
                                        sourceTable.ajax.reload(null, false);
                                    } else {
                                        location.reload();
                                    }
                                } else {
                                    Swal.fire('Error', res.message || 'Error deleting', 'error');
                                }
                            },
                            error: function(xhr) {
                                let msg = 'Server error';
                                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                                Swal.fire('Error', msg, 'error');
                            }
                        });
                    }
                });
            };
            window.Crud.register('shared', 'delete-url', deleteEntity);

            if (!$(document).data('crud-action-delegated')) {
                $(document).data('crud-action-delegated', true);

                $(document).on('click.crudActions', '.js-crud-action', function () {
                    var button = $(this);
                    var action = button.data('crud-action');
                    var callbackName = button.data('crud-callback');
                    var id = button.data('crud-id');

                    if (action === 'delete-url') {
                        var deleteUrl = window.Crud.get('shared', 'delete-url');
                        if (typeof deleteUrl === 'function') {
                            var sourceNode = button.closest('.dataTables_wrapper').find('table.dataTable').filter(function () {
                                return $.fn.dataTable.isDataTable(this);
                            }).first();
                            deleteUrl(button.data('crud-url'), sourceNode.length ? sourceNode.DataTable() : null);
                        }
                        return;
                    }

                    var actionSuffix = action.charAt(0).toUpperCase() + action.slice(1);
                    var entity = callbackName && callbackName.replace(new RegExp(actionSuffix + '$', 'i'), '');
                    var callback = entity
                        ? window.Crud.get(entity.replace(/[-_]/g, '').toLowerCase(), action)
                        : null;

                    if (typeof callback === 'function') {
                        callback(id);
                    }
                });

            }
        });
    </script>
@endpush

@once
<style>
    .reusable-data-table { min-width: 0; width: 100%; max-width: 100%; }
    .reusable-data-table .dataTables_wrapper,
    .reusable-data-table .dataTables_scroll { min-width: 0; max-width: 100%; }
    .reusable-data-table .dataTables_scrollBody {
        overflow: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .reusable-data-table .dataTables_scrollBody:focus-visible {
        outline: 2px solid var(--primary-color);
        outline-offset: -2px;
    }
    .reusable-data-table table th,
    .reusable-data-table table td { white-space: nowrap; }
    .reusable-data-table .dt-buttons {
        display: flex; flex-wrap: wrap; gap: .5rem; max-width: 100%;
    }
    .reusable-data-table .dataTables_paginate { white-space: normal; }
    @media (max-width: 639px) {
        .reusable-data-table .dataTables_filter,
        .reusable-data-table .dataTables_filter label { display: block; width: 100%; }
        .reusable-data-table .dataTables_filter input { width: 100%; margin-left: 0; }
        .reusable-data-table .dataTables_info,
        .reusable-data-table .dataTables_paginate { width: 100%; text-align: center; }
        .reusable-data-table .dataTables_paginate .paginate_button { padding: .4em .6em; }
    }
    .reusable-data-table table tbody tr:nth-child(even) {
        background-color: #f8fafc !important;
    }

    .reusable-data-table table tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }

    .reusable-data-table table tbody tr {
        transition: all 0.15s ease-in-out;
        border-bottom: 1px solid #13293f;
    }

    .reusable-data-table table tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    .reusable-data-table table thead th {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        padding: 12px 16px !important;

    }

    .reusable-data-table table tbody td {
        padding: 12px 16px !important;
        color: #334155;
        vertical-align: middle;
        border: 1px solid #ced5df !important;
    }

    /* Dark mode overrides - same specificity as light mode */
    .dark .reusable-data-table table tbody tr:nth-child(even) {
        background-color: #1f2937 !important;
    }

    .dark .reusable-data-table table tbody tr:nth-child(odd) {
        background-color: #111827 !important;
    }

    .dark .reusable-data-table table tbody tr {
        border-bottom: 1px solid #374151 !important;
    }

    .dark .reusable-data-table table tbody tr:hover {
        background-color: #4b5563 !important;
    }

    .dark .reusable-data-table table thead th {
        background-color: #1f2937 !important;
        color: #9ca3af !important;
        border-bottom: 2px solid #4b5563 !important;
    }

    .dark .reusable-data-table table tbody td {
        color: #e5e7eb !important;
        border: 1px solid #374151 !important;
    }

    .reusable-data-table .dataTables_filter input {
        border: 1px solid #e2e8f0;
        padding: 0.45rem 0.85rem;
        border-radius: 0.5rem;
        outline: none;
        font-size: 0.875rem;
        transition: all 0.2s;
        width: min(320px, 100%);
        max-width: 100%;
        box-sizing: border-box;
    }

    .reusable-data-table .dataTables_filter input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    /* dropdown tyle */
    .reusable-data-table .dataTables_length select {
        border: 1px solid #e2e8f0;
        padding: 0.4rem 0.9rem !important;
        border-radius: 0.5rem;
        outline: none;
        font-size: 0.875rem;
        -webkit-appearance: none;
        /* Chrome, Safari, Opera */
        -moz-appearance: none;
        /* Firefox */
        appearance: none;
        /* Modern Browsers */
    }

    .reusable-data-table .dataTables_length select::-ms-expand {
        display: none;
    }

    /* DataTables keeps a hidden sizing header inside the scrolling body. */
    .reusable-data-table .dataTables_scrollBody table thead th {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        border-top: 0 !important;
        border-bottom: 0 !important;
    }

    .dataTables_wrapper .dataTables_buttons {
        display: none !important;
    }
</style>
@endonce
