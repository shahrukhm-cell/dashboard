import './bootstrap';

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
    if (colorDisplay) colorDisplay.textContent = hex.toUpperCase();
}

const savedColor = localStorage.getItem('nexus-theme-color') || colorInput?.value || '#8b5cf6';
if (colorInput) colorInput.value = savedColor;
applyColor(savedColor);
colorInput?.addEventListener('input', (event) => applyColor(event.target.value));
document.querySelector('[data-save-theme]')?.addEventListener('click', () => localStorage.setItem('nexus-theme-color', colorInput.value));

const savedMode = localStorage.getItem('nexus-theme-mode');
const systemTheme = window.matchMedia('(prefers-color-scheme: light)');
if (savedMode === 'light' || (!savedMode && systemTheme.matches)) body.classList.add('light-mode');
document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    body.classList.toggle('light-mode');
    localStorage.setItem('nexus-theme-mode', body.classList.contains('light-mode') ? 'light' : 'dark');
});

document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => {
    document.querySelector('[data-sidebar]').classList.toggle('open');
    document.querySelector('[data-overlay]').classList.toggle('active');
});
document.querySelector('[data-overlay]')?.addEventListener('click', () => {
    document.querySelector('[data-sidebar]').classList.remove('open');
    document.querySelector('[data-overlay]').classList.remove('active');
});