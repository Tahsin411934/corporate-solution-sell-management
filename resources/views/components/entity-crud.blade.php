@props([
    'id' => 'entity',
    'title' => 'Management',
    'icon' => 'fa-solid fa-list',
    'columns' => [],
    'dtColumns' => [],
    'ajaxUrl' => '',
    'storeUrl' => '',
    'updateUrl' => '',
    'destroyUrl' => '',
    'showUrl' => '',
    'formTitle' => null,
    'idField' => 'id',
    'order' => null,
    'exportButtons' => true,
    'createPermission' => null,
])

@php
    $key = preg_replace('/[^A-Za-z0-9]/', '', $id);
    $tableId = $key . 'Table';
    $buttonId = $id . 'AddButton';
    $drawerId = $id . '-drawer';
    $overlayId = $id . '-overlay';
    $formId = $id . '-form';
    $hiddenId = $id . '-id';
    $formTitle = $formTitle ?: 'Add ' . $title;
    $entityCrudConfig = [
        'key' => $key,
        'table' => $tableId,
        'button' => $buttonId,
        'drawer' => $drawerId,
        'overlay' => $overlayId,
        'form' => $formId,
        'hidden' => $hiddenId,
        'store' => $storeUrl,
        'update' => $updateUrl,
        'destroy' => $destroyUrl,
        'show' => $showUrl,
    ];
@endphp

<x-data-table
    :id="$tableId"
    :title="$title"
    :icon="$icon"
    :button-id="$buttonId"
    :button-text="'Add ' . $title"
    :create-permission="$createPermission"
    :columns="$columns"
    :ajax-url="$ajaxUrl"
    :dt-columns="$dtColumns"
    :export-buttons="$exportButtons"
    :order="$order"
/>

<x-drawer :id="$drawerId" :overlay-id="$overlayId" :title="$formTitle" submit-on-click="document.getElementById('{{ $formId }}').requestSubmit()">
    <form id="{{ $formId }}" action="{{ $storeUrl }}" method="POST" class="space-y-5" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="{{ $idField }}" id="{{ $hiddenId }}">
        {{ $slot }}
    </form>
</x-drawer>

@push('scripts')
<script>
(() => {
    const cfg = @json($entityCrudConfig);
    const form = document.getElementById(cfg.form);
    const hidden = document.getElementById(cfg.hidden);
    const table = () => $('#' + cfg.table).DataTable();
    const reset = () => { form.reset(); hidden.value = ''; };

    document.getElementById(cfg.button)?.addEventListener('click', () => {
        reset();
        openGlobalDrawer(cfg.drawer, cfg.overlay);
    });

    window.Crud.register(cfg.key, 'reload', () => table().ajax.reload(null, false));
    window.Crud.register(cfg.key, 'reset', reset);
})();
</script>
@endpush
