/**
 * ============================================================================
 * Menu Studio — ColorManager
 * ============================================================================
 * Gestiona la paleta de colores del menú: color pickers individuales,
 * paletas predefinidas y aplicación global en tiempo real.
 */

class ColorManager {
    /**
     * @param {MenuEditor} editor - Referencia al editor principal
     */
    constructor(editor) {
        this.editor = editor;

        // Paletas predefinidas curadas para restaurantes
        this.presets = {
            elegant:  { primary: '#d4af37', secondary: '#1a1a2e', accent: '#c0392b', text: '#e0e0e0', muted: '#888888' },
            rustic:   { primary: '#3e2723', secondary: '#f5e6d3', accent: '#ff6f00', text: '#4e342e', muted: '#8d6e63' },
            modern:   { primary: '#111111', secondary: '#ffffff', accent: '#e63946', text: '#333333', muted: '#999999' },
            ocean:    { primary: '#0077b6', secondary: '#caf0f8', accent: '#ff6b6b', text: '#023e8a', muted: '#90e0ef' },
            forest:   { primary: '#2d6a4f', secondary: '#f1faee', accent: '#e76f51', text: '#1b4332', muted: '#95d5b2' },
            neon:     { primary: '#ff6ec7', secondary: '#0f0c29', accent: '#fbbf24', text: '#d1d5db', muted: '#6b7280' },
            burgundy: { primary: '#c9b037', secondary: '#2c0a1a', accent: '#8b0000', text: '#f0d9b5', muted: '#6a3636' },
            sakura:   { primary: '#c9184a', secondary: '#fff0f3', accent: '#ff758f', text: '#590d22', muted: '#ffccd5' },
        };
    }

    /**
     * Inicializar: bindear eventos de los pickers y paletas
     */
    init() {
        this.bindColorPickers();
        this.bindHexInputs();
        this.bindPalettePresets();
    }

    /**
     * Bindear eventos de color pickers (<input type="color">)
     */
    bindColorPickers() {
        document.querySelectorAll('.color-input[data-color]').forEach(picker => {
            picker.addEventListener('input', (e) => {
                const colorRole = e.target.dataset.color;
                const value = e.target.value;

                // Sincronizar con hex input
                const hexInput = document.getElementById(`color${this.capitalize(colorRole)}Hex`);
                if (hexInput) hexInput.value = value;

                this.updateColor(colorRole, value);
            });
        });
    }

    /**
     * Bindear eventos de inputs de hex (texto)
     */
    bindHexInputs() {
        document.querySelectorAll('.color-hex[data-color]').forEach(input => {
            input.addEventListener('change', (e) => {
                const colorRole = e.target.dataset.color;
                let value = e.target.value.trim();

                // Validar formato hex
                if (!value.startsWith('#')) value = '#' + value;
                if (!/^#[0-9A-Fa-f]{6}$/.test(value)) return;

                // Sincronizar con color picker
                const picker = document.getElementById(`color${this.capitalize(colorRole)}`);
                if (picker) picker.value = value;

                this.updateColor(colorRole, value);
            });
        });
    }

    /**
     * Bindear clicks en paletas predefinidas
     */
    bindPalettePresets() {
        document.querySelectorAll('.palette-preset').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const paletteName = e.currentTarget.dataset.palette;
                const palette = this.presets[paletteName];

                if (!palette) return;

                // Marcar como activa
                document.querySelectorAll('.palette-preset').forEach(b => b.classList.remove('active'));
                e.currentTarget.classList.add('active');

                // Aplicar toda la paleta
                this.applyPalette(palette);
            });
        });
    }

    /**
     * Actualizar un color individual en los estilos del menú
     * @param {string} colorRole - Rol del color (primary, secondary, accent, text, muted)
     * @param {string} value     - Valor hex del color
     */
    updateColor(colorRole, value) {
        if (!this.editor.styles.colors) {
            this.editor.styles.colors = {};
        }

        this.editor.styles.colors[colorRole] = value;

        // Si cambia el secondary, actualizar también el fondo (si es sólido)
        if (colorRole === 'secondary' && this.editor.styles.background?.type === 'solid') {
            this.editor.styles.background.value = value;
        }

        // Actualizar colores de tipografía relacionados
        this.syncTypographyColors(colorRole, value);

        // Re-renderizar
        this.editor.canvasManager.render();
        this.editor.markDirty();
    }

    /**
     * Sincronizar colores de tipografía cuando cambian los colores globales
     */
    syncTypographyColors(colorRole, value) {
        const typo = this.editor.styles.typography;
        if (!typo) return;

        switch (colorRole) {
            case 'primary':
                if (typo.heading) typo.heading.color = value;
                if (typo.price) typo.price.color = value;
                break;
            case 'text':
                if (typo.body) typo.body.color = value;
                break;
            case 'muted':
                if (typo.subheading) typo.subheading.color = this.lighten(value, 20);
                break;
        }
    }

    /**
     * Aplicar una paleta completa al menú
     * @param {Object} palette - Objeto con los 5 colores
     */
    applyPalette(palette) {
        // Actualizar colores en estilos
        this.editor.styles.colors = { ...palette };

        // Actualizar fondo
        if (!this.editor.styles.background) {
            this.editor.styles.background = { type: 'solid', value: palette.secondary };
        }
        if (this.editor.styles.background.type === 'solid') {
            this.editor.styles.background.value = palette.secondary;
        }

        // Actualizar tipografía
        const typo = this.editor.styles.typography || {};
        if (typo.heading) typo.heading.color = palette.primary;
        if (typo.subheading) typo.subheading.color = this.lighten(palette.muted, 20);
        if (typo.body) typo.body.color = palette.text;
        if (typo.price) typo.price.color = palette.primary;

        // Sincronizar UI de color pickers
        this.syncPickersFromStyles();

        // Re-renderizar
        this.editor.canvasManager.render();
        this.editor.markDirty();
    }

    /**
     * Sincronizar los color pickers con los estilos actuales
     */
    syncPickersFromStyles() {
        const colors = this.editor.styles?.colors || {};

        Object.entries(colors).forEach(([role, value]) => {
            const picker = document.getElementById(`color${this.capitalize(role)}`);
            const hex = document.getElementById(`color${this.capitalize(role)}Hex`);
            if (picker) picker.value = value;
            if (hex) hex.value = value;
        });

        // Sincronizar fondo sólido
        const bgSolid = document.getElementById('bgSolidColor');
        const bgHex = document.getElementById('bgSolidHex');
        if (bgSolid && colors.secondary) {
            bgSolid.value = colors.secondary;
        }
        if (bgHex && colors.secondary) {
            bgHex.value = colors.secondary;
        }
    }

    /**
     * Aclarar un color hex
     * @param {string} hex - Color hex
     * @param {number} percent - Porcentaje de aclarado
     * @returns {string} Color hex aclarado
     */
    lighten(hex, percent) {
        hex = hex.replace('#', '');
        const r = Math.min(255, parseInt(hex.substring(0, 2), 16) + Math.round(255 * percent / 100));
        const g = Math.min(255, parseInt(hex.substring(2, 4), 16) + Math.round(255 * percent / 100));
        const b = Math.min(255, parseInt(hex.substring(4, 6), 16) + Math.round(255 * percent / 100));
        return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
    }

    /**
     * Capitalizar primera letra
     */
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

window.ColorManager = ColorManager;
