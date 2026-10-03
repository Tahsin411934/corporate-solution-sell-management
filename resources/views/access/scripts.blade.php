@push('scripts')
<script>
$(function () {
    const entity = @json($entity);
    const baseUrl = @json($baseUrl);
    const form = document.getElementById(entity + '-form');
    const drawer = $('#' + entity + '-drawer');
    const method = $(form).find('[name="_method"]');
    const save = drawer.find('button[id="saveBtn"]');
    const table = () => $('#' + entity + 'Table').DataTable();

    $('#' + entity + 'AddButton').on('click', function () {
        form.action = baseUrl;
        method.val('POST');
        $(form).find('select').each(function () {
            if (this.multiple) $(this).val([]);
            $(this).trigger('change');
        });
        $(form).find('[name="password"]').prop('required', true);
        drawer.find('#drawerTitle').text('Add ' + entity);
        drawer.find('#drawerButtonText').text('Create ' + entity);
        $(form).find('.access-errors').remove();
    });

    $(document).on('click', '.access-edit', function () {
        if (this.dataset.entity !== entity) return;
        const record = JSON.parse(this.dataset.record);
        form.reset();
        form.action = baseUrl + '/' + encodeURIComponent(record.id);
        method.val('PUT');
        $('#' + entity + '-id').val(record.id);
        $(form).find('[name="name"]').val(record.name);
        $(form).find('[name="email"]').val(record.email || '');
        Object.entries(record).forEach(([name, value]) => {
            const input = form.elements.namedItem(name);
            if (input && name !== 'id' && (value === null || typeof value !== 'object')) {
                input.value = typeof value === 'boolean' ? (value ? '1' : '0') : value ?? '';
            }
        });
        $(form).find('[name="password"]').prop('required', false);
        $(form).find('[name="roles[]"]').val(record.roles || []).trigger('change');
        $(form).find('select').trigger('change');
        $(form).find('input[name="permissions[]"]').each(function () {
            this.checked = (record.permissions || []).includes(this.value);
        });
        drawer.find('#drawerTitle').text('Edit ' + entity);
        drawer.find('#drawerButtonText').text('Save changes');
        $(form).find('.access-errors').remove();
        openGlobalDrawer(entity + '-drawer', entity + '-overlay');
    });

    $(form).on('submit', function (event) {
        event.preventDefault();
        if (save.prop('disabled')) return;
        save.prop('disabled', true);
        $(form).find('.access-errors').remove();
        $.ajax({url: form.action, type: 'POST', data: new FormData(form), processData: false, contentType: false, headers: {Accept: 'application/json'}})
            .done(function () {
                closeGlobalDrawer(entity + '-drawer', entity + '-overlay');
                table().ajax.reload(null, false);
            })
            .fail(function (xhr) {
                const response = xhr.responseJSON || {};
                const messages = response.errors ? Object.values(response.errors).flat().join('\n') : response.message || 'Unable to save. Please try again.';
                $('<div class="access-errors text-red-600 text-sm whitespace-pre-line" role="alert"></div>').text(messages).prependTo(form);
            })
            .always(() => save.prop('disabled', false));
    });

    $(document).on('click', '.access-delete', function () {
        if (this.dataset.entity !== entity) return;
        const url = this.dataset.url;
        Swal.fire({title: 'Delete ' + entity + '?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete'}).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({url, type: 'POST', data: {_method: 'DELETE', _token: $(form).find('[name="_token"]').val()}, headers: {Accept: 'application/json'}})
                .done(() => table().ajax.reload(null, false))
                .fail(xhr => Swal.fire('Unable to delete', xhr.responseJSON?.message || 'Please try again.', 'error'));
        });
    });
});
</script>
@endpush
