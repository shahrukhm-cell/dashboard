const root = document.documentElement;
const body = document.body;
const colorInput = document.querySelector('[data-color-input]');
const colorDisplay = document.querySelector('[data-color-display]');

function applyColor(hex) {
    const value = hex.replace('#', '');
    const red = parseInt(value.substring(0, 2), 16) / 255;
    const green = parseInt(value.substring(2, 4), 16) / 255;
    const blue = parseInt(value.substring(4, 6), 16) / 255;
    const max = Math.max(red, green, blue);
    const min = Math.min(red, green, blue);
    const delta = max - min;
    let hue = 0;

    if (delta) {
        hue = max === red ? ((green - blue) / delta) % 6 : max === green ? (blue - red) / delta + 2 : (red - green) / delta + 4;
    }

    const lightness = (max + min) / 2;
    const saturation = delta ? delta / (1 - Math.abs(2 * lightness - 1)) : 0;
    root.style.setProperty('--h', Math.round(hue * 60 + (hue < 0 ? 360 : 0)));
    root.style.setProperty('--s', `${Math.round(saturation * 100)}%`);
    root.style.setProperty('--l', `${Math.round(lightness * 100)}%`);
    root.style.setProperty('--accent', `hsl(${root.style.getPropertyValue('--h')}, ${root.style.getPropertyValue('--s')}, ${root.style.getPropertyValue('--l')})`);
    root.style.setProperty('--accent-soft', `hsla(${root.style.getPropertyValue('--h')}, ${root.style.getPropertyValue('--s')}, ${root.style.getPropertyValue('--l')}, .14)`);

    if (colorDisplay) colorDisplay.textContent = hex.toUpperCase();
}

function applySecondaryColor(hex) {
    root.style.setProperty('--accent-2', hex);
}

const tenantId = body.dataset.tenantId || 'guest';
const tenantColor = body.dataset.tenantColor || '#8b5cf6';
const tenantSecondaryColor = body.dataset.tenantSecondaryColor || '#22c55e';
const colorStorageKey = `nexus-theme-color-${tenantId}`;
const savedColor = localStorage.getItem(colorStorageKey) || tenantColor;
if (colorInput) colorInput.value = savedColor;
applyColor(savedColor);
applySecondaryColor(tenantSecondaryColor);
colorInput?.addEventListener('input', (event) => applyColor(event.target.value));
document.querySelector('[data-save-theme]')?.addEventListener('click', () => localStorage.setItem(colorStorageKey, colorInput.value));

const savedMode = localStorage.getItem('nexus-theme-mode');
const systemTheme = window.matchMedia('(prefers-color-scheme: light)');
if (savedMode === 'light' || (!savedMode && systemTheme.matches)) body.classList.add('light-mode');
systemTheme.addEventListener?.('change', (event) => {
    if (!localStorage.getItem('nexus-theme-mode')) body.classList.toggle('light-mode', event.matches);
});
document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    body.classList.toggle('light-mode');
    localStorage.setItem('nexus-theme-mode', body.classList.contains('light-mode') ? 'light' : 'dark');
});


const sidebarCollapseToggle = document.querySelector('[data-sidebar-collapse-toggle]');
const sidebarStorageKey = `nexus-sidebar-collapsed-${tenantId}`;
const setSidebarCollapsed = (collapsed) => {
    body.classList.toggle('sidebar-collapsed', collapsed);
    sidebarCollapseToggle?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    sidebarCollapseToggle?.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
    sidebarCollapseToggle?.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
};
setSidebarCollapsed(localStorage.getItem(sidebarStorageKey) === 'true');
sidebarCollapseToggle?.addEventListener('click', () => {
    const collapsed = !body.classList.contains('sidebar-collapsed');
    setSidebarCollapsed(collapsed);
    localStorage.setItem(sidebarStorageKey, collapsed ? 'true' : 'false');
});

document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => {
    document.querySelector('[data-sidebar]').classList.toggle('open');
    document.querySelector('[data-overlay]').classList.toggle('active');
});
document.querySelector('[data-overlay]')?.addEventListener('click', () => {
    document.querySelector('[data-sidebar]').classList.remove('open');
    document.querySelector('[data-overlay]').classList.remove('active');
});


document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.closest('.password-input-wrap')?.querySelector('input');
        if (!input) return;

        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        button.textContent = isHidden ? 'Hide' : 'Show';
        button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
    });
});
