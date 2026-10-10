<script>
    (() => {
        const fitInvoice = () => {
            const documentElement = document.querySelector('.invoice-compact');
            const content = documentElement?.querySelector('.invoice-content');
            if (!content) return;
            content.style.removeProperty('zoom');
            content.style.removeProperty('min-height');
            // Reserve 1 mm for browser pagination rounding. No text is clipped.
            const pageHeight = 296 * 96 / 25.4;
            const contentHeight = Math.max(content.scrollHeight, content.getBoundingClientRect().height);
            if (contentHeight > pageHeight) {
                const scale = Math.min(1, pageHeight / (contentHeight + 2));
                content.style.zoom = scale;
                content.style.minHeight = `${296 / scale}mm`;
            }
        };
        window.addEventListener('beforeprint', fitInvoice);
        const ready = document.fonts ? document.fonts.ready : Promise.resolve();
        ready.then(() => Promise.all([...document.images].map(image => image.decode().catch(() => {}))))
            .then(() => {
                fitInvoice();
                // Wait until the fitted document has been painted before opening print.
                requestAnimationFrame(() => window.print());
            });
    })();
</script>
