(function () {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    const collapseBtn = document.getElementById('collapseBtn');
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const overlay = document.getElementById('sidebarOverlay');
    const tooltip = document.getElementById('navTooltip');
    const search = document.getElementById('sidebarSearch');
    const clear = document.getElementById('searchClear');
    const empty = document.getElementById('searchEmptyMsg');
    const groups = Array.from(sidebar.querySelectorAll('.sidebar-group'));
    const read = key => { try { return localStorage.getItem(key); } catch { return null; } };
    const write = (key, value) => { try { localStorage.setItem(key, value); } catch {} };
    let collapsed = read('sidebarCollapsed') === 'true', previousFocus = null, snapshot = null, preferences = {};
    try { preferences = JSON.parse(read('sidebarGroupState') || '{}') || {}; } catch {}
    function setOpen(group, open) {
        const btn = group.querySelector('.has-sub'), menu = group.querySelector('.submenu');
        btn.classList.toggle('open', open);
        btn.setAttribute('aria-expanded', String(open));
        menu.classList.toggle('open', open);
        menu.hidden = !open;
    }
    function applyCollapsed() {
        sidebar.classList.toggle('collapsed', collapsed);
        sidebar.style.width = collapsed ? '60px' : '224px';
        collapseBtn?.setAttribute('aria-expanded', String(!collapsed));
        collapseBtn?.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        groups.forEach(group => { group.querySelector('.submenu').inert = collapsed && window.innerWidth >= 640; });
        tooltip?.classList.remove('show');
    }
    function closeMobile() {
        const wasOpen = sidebar.classList.contains('mobile-open');
        sidebar.classList.remove('mobile-open'); overlay?.classList.remove('visible');
        document.body.classList.remove('sidebar-open'); mobileBtn?.setAttribute('aria-expanded', 'false');
        sidebar.inert = window.innerWidth < 640;
        sidebar.setAttribute('aria-hidden', String(sidebar.inert));
        if (wasOpen) previousFocus?.focus({preventScroll: true});
        previousFocus = null;
    }
    function openMobile() {
        previousFocus = document.activeElement;
        sidebar.classList.add('mobile-open'); overlay?.classList.add('visible');
        document.body.classList.add('sidebar-open'); mobileBtn?.setAttribute('aria-expanded', 'true');
        sidebar.inert = false; sidebar.setAttribute('aria-hidden', 'false');
        document.getElementById('mobileSidebarClose')?.focus();
    }
    groups.forEach(group => {
        const menu = group.querySelector('.submenu');
        setOpen(group, !!menu.querySelector('[aria-current="page"]') || preferences[menu.id] === true);
        group.querySelector('.has-sub').addEventListener('click', () => {
            if (collapsed && window.innerWidth >= 640) {
                collapsed = false; write('sidebarCollapsed', 'false'); applyCollapsed(); setOpen(group, true);
            } else setOpen(group, menu.hidden);
            if (!search.value.trim()) { preferences[menu.id] = !menu.hidden; write('sidebarGroupState', JSON.stringify(preferences)); }
        });
    });
    applyCollapsed(); closeMobile();
    collapseBtn?.addEventListener('click', () => { collapsed = !collapsed; write('sidebarCollapsed', String(collapsed)); applyCollapsed(); });
    mobileBtn?.addEventListener('click', () => sidebar.classList.contains('mobile-open') ? closeMobile() : openMobile());
    overlay?.addEventListener('click', closeMobile);
    document.getElementById('mobileSidebarClose')?.addEventListener('click', closeMobile);
    sidebar.addEventListener('click', event => { if (event.target.closest('a') && window.innerWidth < 640) closeMobile(); });
    window.addEventListener('resize', () => { closeMobile(); applyCollapsed(); });
    function filter() {
        const term = search.value.trim().toLowerCase();
        clear.classList.toggle('hidden', !term);
        if (term && !snapshot) snapshot = groups.map(group => !group.querySelector('.submenu').hidden);
        let matches = 0;
        sidebar.querySelectorAll('#sideNav > a').forEach(link => {
            link.hidden = !!term && !link.dataset.label.toLowerCase().includes(term);
            if (!link.hidden) matches++;
        });
        groups.forEach((group, index) => {
            const groupMatch = group.dataset.groupLabel.toLowerCase().includes(term);
            let count = 0;
            group.querySelectorAll('a').forEach(link => {
                link.hidden = !!term && !groupMatch && !link.dataset.label.toLowerCase().includes(term);
                if (!link.hidden) count++;
            });
            group.hidden = count === 0; matches += count;
            if (term) setOpen(group, count > 0);
            else if (snapshot) setOpen(group, snapshot[index]);
        });
        if (!term) snapshot = null;
        empty.classList.toggle('hidden', !term || matches > 0);
    }
    search?.addEventListener('input', filter);
    clear?.addEventListener('click', () => { search.value = ''; filter(); search.focus(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            if (search.value) { search.value = ''; filter(); }
            else if (sidebar.classList.contains('mobile-open')) closeMobile();
        }
        if (event.key === 'Tab' && sidebar.classList.contains('mobile-open')) {
            const elements = Array.from(sidebar.querySelectorAll('a, button, input')).filter(el => el.getClientRects().length && !el.disabled && !el.closest('[hidden]'));
            const first = elements[0], last = elements[elements.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        }
    });
    sidebar.querySelectorAll('.nav-item[data-label]').forEach(item => {
        const show = () => {
            if (!collapsed || window.innerWidth < 640 || !tooltip) return;
            tooltip.textContent = item.dataset.label;
            tooltip.style.top = (item.getBoundingClientRect().top + item.offsetHeight / 2 - 11) + 'px';
            tooltip.classList.add('show');
        };
        item.addEventListener('mouseenter', show); item.addEventListener('focus', show);
        item.addEventListener('mouseleave', () => tooltip?.classList.remove('show'));
        item.addEventListener('blur', () => tooltip?.classList.remove('show'));
    });
})();
