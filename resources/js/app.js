import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

window.Crud = window.Crud || {
    callbacks: {},
    register(entity, action, callback) {
        this.callbacks[entity] = this.callbacks[entity] || {};
        this.callbacks[entity][action] = callback;
    },
    get(entity, action) {
        return this.callbacks[entity]?.[action] ?? null;
    },
};

window.openGlobalDrawer = function (drawerId, overlayId) {
    const $drawer = $(`#${drawerId}`);
    const $overlay = $(`#${overlayId}`);

    $overlay.removeClass('opacity-0 pointer-events-none').addClass('opacity-100');
    $drawer.removeClass('translate-x-full');
    $('body').addClass('overflow-hidden');
};

window.closeGlobalDrawer = function (drawerId, overlayId) {
    const $drawer = drawerId ? $(`#${drawerId}`) : $('[id$="-drawer"], [id$="Drawer"]');
    const $overlay = overlayId ? $(`#${overlayId}`) : $('[id$="-overlay"], [id$="Overlay"], #drawer-overlay');

    $drawer.addClass('translate-x-full');
    $overlay.removeClass('opacity-100').addClass('opacity-0 pointer-events-none');
    $('body').removeClass('overflow-hidden');
};

$(document).ready(function () {
    $(document).on('click', '.js-global-drawer-close', function () {
        closeGlobalDrawer($(this).data('drawer-id'), $(this).data('overlay-id'));
    });

    $(document).on('click', '.js-drawer-submit', function () {
        const button = $(this);
        const callback = window.Crud.get(button.data('crud-entity'), button.data('crud-action'));

        if (typeof callback === 'function') callback();
    });

    $(document).on('click', '[id$="-overlay"], [id$="Overlay"], #drawer-overlay', function () {
        closeGlobalDrawer();
    });

    $(document).keydown(function (event) {
        if (event.key === 'Escape') closeGlobalDrawer();
    });
});
