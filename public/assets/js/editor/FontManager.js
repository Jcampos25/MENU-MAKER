/**
 * ============================================================================
 * Menu Studio — FontManager
 * ============================================================================
 * Gestiona la carga dinámica de Google Fonts, fuentes personalizadas,
 * y la aplicación de estilos tipográficos al menú en tiempo real.
 */

class FontManager {
    /**
     * @param {MenuEditor} editor - Referencia al editor principal
     */
    constructor(editor) {
        this.editor = editor;
        this.loadedFonts = new Set();

        // Lista de Google Fonts curadas para restaurantes
        this.googleFonts = [
            { family: 'Playfair Display', category: 'serif' },
            { family: 'Montserrat',       category: 'sans-serif' },
            { family: 'Lato',             category: 'sans-serif' },
            { family: 'Inter',            category: 'sans-serif' },
            { family: 'Poppins',          category: 'sans-serif' },
            { family: 'Roboto',           category: 'sans-serif' },
            { family: 'Open Sans',        category: 'sans-serif' },
            { family: 'Bebas Neue',       category: 'display' },
            { family: 'Abril Fatface',    category: 'display' },
            { family: 'Cormorant Garamond', category: 'serif' },
            { family: 'Merriweather',     category: 'serif' },
            { family: 'Oswald',           category: 'sans-serif' },
            { family: 'Raleway',          category: 'sans-serif' },
            { family: 'Great Vibes',      category: 'handwriting' },
            { family: 'Dancing Script',   category: 'handwriting' },
            { family: 'Outfit',           category: 'sans-serif' },
            { family: 'DM Serif Display', category: 'serif' },
            { family: 'Josefin Sans',     category: 'sans-serif' },
            { family: 'Crimson Text',     category: 'serif' },
            { family: 'Libre Baskerville', category: 'serif' },
        ];
    }

    /**
     * Inicializar: poblar selectores de fuentes y bindear eventos
     */
    init() {
        this.populateFontSelectors();
        this.bindEvents();
        this.loadCurrentFonts();
    }

    /**
     * Poblar todos los <select> de fuentes con las opciones disponibles
     */
    populateFontSelectors() {
        const selectors = document.querySelectorAll('.font-select');
        const styles = this.editor.styles;

        selectors.forEach(select => {
            const role = select.dataset.role; // heading, subheading, body, price
            const currentFont = styles?.typography?.[role]?.fontFamily || '';

            select.innerHTML = '';

            // Grupo: Google Fonts
            const group = document.createElement('optgroup');
            group.label = 'Google Fonts';

            this.googleFonts.forEach(font => {
                const option = document.createElement('option');
                option.value = font.family;
                option.textContent = font.family;
                option.style.fontFamily = font.family;
                if (font.family === currentFont) {
                    option.selected = true;
                }
                group.appendChild(option);
            });

            select.appendChild(group);
        });
    }

    /**
     * Cargar las fuentes actuales del menú
     */
    loadCurrentFonts() {
        const typography = this.editor.styles?.typography || {};
        const fonts = new Set();

        ['heading', 'subheading', 'body', 'price'].forEach(role => {
            if (typography[role]?.fontFamily) {
                fonts.add(typography[role].fontFamily);
            }
        });

        fonts.forEach(font => this.loadGoogleFont(font));
    }

    /**
     * Cargar una Google Font dinámicamente via @font-face
     * @param {string} fontFamily - Nombre de la fuente
     */
    loadGoogleFont(fontFamily) {
        if (this.loadedFonts.has(fontFamily)) return;

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(fontFamily)}:wght@100;200;300;400;500;600;700;800;900&display=swap`;
        document.head.appendChild(link);

        this.loadedFonts.add(fontFamily);
    }

    /**
     * Bindear eventos de los controles de tipografía
     */
    bindEvents() {
        // ── Font Family Select ──
        document.querySelectorAll('.font-select').forEach(select => {
            select.addEventListener('change', (e) => {
                const role = e.target.dataset.role;
                const fontFamily = e.target.value;

                this.loadGoogleFont(fontFamily);
                this.updateTypography(role, 'fontFamily', fontFamily);
            });
        });

        // ── Font Size (Range Slider) ──
        document.querySelectorAll('[id^="fontSize"]').forEach(range => {
            if (range.type === 'range') {
                range.addEventListener('input', (e) => {
                    const role = e.target.dataset.role;
                    const size = parseInt(e.target.value);

                    // Sincronizar con input numérico
                    const numInput = document.getElementById(`fontSize${this.capitalize(role)}Num`);
                    if (numInput) numInput.value = size;

                    this.updateTypography(role, 'fontSize', size);
                });
            }
        });

        // ── Font Size (Number Input) ──
        document.querySelectorAll('[id$="Num"]').forEach(numInput => {
            if (numInput.id.startsWith('fontSize')) {
                numInput.addEventListener('change', (e) => {
                    const role = e.target.dataset.role;
                    const size = parseInt(e.target.value);

                    // Sincronizar con slider
                    const rangeInput = document.getElementById(`fontSize${this.capitalize(role)}`);
                    if (rangeInput) rangeInput.value = size;

                    this.updateTypography(role, 'fontSize', size);
                });
            }
        });

        // ── Font Weight ──
        document.querySelectorAll('[id^="fontWeight"]').forEach(select => {
            select.addEventListener('change', (e) => {
                const role = e.target.dataset.role;
                this.updateTypography(role, 'fontWeight', e.target.value);
            });
        });

        // ── Letter Spacing ──
        document.querySelectorAll('[id^="letterSpacing"]').forEach(range => {
            if (range.type !== 'range') return;
            range.addEventListener('input', (e) => {
                const role = e.target.dataset.role;
                const value = parseFloat(e.target.value);

                const display = document.getElementById(`letterSpacing${this.capitalize(role)}Val`);
                if (display) display.textContent = value + 'px';

                this.updateTypography(role, 'letterSpacing', value);
            });
        });

        // ── Line Height (body only) ──
        const lineHeightBody = document.getElementById('lineHeightBody');
        if (lineHeightBody) {
            lineHeightBody.addEventListener('input', (e) => {
                const value = parseFloat(e.target.value);
                document.getElementById('lineHeightBodyVal').textContent = value.toFixed(1);
                this.updateTypography('body', 'lineHeight', value);
            });
        }

        // ── Text Transform Buttons ──
        document.querySelectorAll('[data-transform]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const role = e.target.closest('[data-role]')?.dataset.role || e.target.dataset.role;
                const transform = e.target.dataset.transform;

                // Toggle active state within button group
                const group = e.target.closest('.btn-group');
                if (group) {
                    group.querySelectorAll('.btn-group__btn').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                }

                this.updateTypography(role, 'textTransform', transform);
            });
        });
    }

    /**
     * Actualizar una propiedad de tipografía y re-renderizar
     * @param {string} role     - Rol (heading, subheading, body, price)
     * @param {string} property - Propiedad CSS
     * @param {*}      value    - Nuevo valor
     */
    updateTypography(role, property, value) {
        if (!this.editor.styles.typography) {
            this.editor.styles.typography = {};
        }
        if (!this.editor.styles.typography[role]) {
            this.editor.styles.typography[role] = {};
        }

        this.editor.styles.typography[role][property] = value;

        // Re-renderizar el canvas
        this.editor.canvasManager.render();

        // Marcar como modificado
        this.editor.markDirty();
    }

    /**
     * Sincronizar controles UI con los estilos actuales del menú
     */
    syncControlsFromStyles() {
        const typography = this.editor.styles?.typography || {};

        ['heading', 'subheading', 'body', 'price'].forEach(role => {
            const config = typography[role] || {};

            // Font family
            const fontSelect = document.getElementById(`font${this.capitalize(role)}`);
            if (fontSelect && config.fontFamily) {
                fontSelect.value = config.fontFamily;
            }

            // Font size
            const sizeRange = document.getElementById(`fontSize${this.capitalize(role)}`);
            const sizeNum = document.getElementById(`fontSize${this.capitalize(role)}Num`);
            if (sizeRange && config.fontSize) {
                sizeRange.value = config.fontSize;
            }
            if (sizeNum && config.fontSize) {
                sizeNum.value = config.fontSize;
            }

            // Font weight
            const weightSelect = document.getElementById(`fontWeight${this.capitalize(role)}`);
            if (weightSelect && config.fontWeight) {
                weightSelect.value = config.fontWeight;
            }

            // Letter spacing
            const spacingRange = document.getElementById(`letterSpacing${this.capitalize(role)}`);
            const spacingVal = document.getElementById(`letterSpacing${this.capitalize(role)}Val`);
            if (spacingRange && config.letterSpacing !== undefined) {
                spacingRange.value = config.letterSpacing;
            }
            if (spacingVal && config.letterSpacing !== undefined) {
                spacingVal.textContent = config.letterSpacing + 'px';
            }

            // Text transform buttons
            if (config.textTransform) {
                const btns = document.querySelectorAll(`[data-transform][data-role="${role}"]`);
                btns.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.transform === config.textTransform);
                });
            }
        });

        // Line height (body)
        const lineHeight = document.getElementById('lineHeightBody');
        const lineHeightVal = document.getElementById('lineHeightBodyVal');
        if (lineHeight && typography.body?.lineHeight) {
            lineHeight.value = typography.body.lineHeight;
        }
        if (lineHeightVal && typography.body?.lineHeight) {
            lineHeightVal.textContent = typography.body.lineHeight.toFixed(1);
        }
    }

    /**
     * Capitalizar primera letra
     */
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

// Exportar al global
window.FontManager = FontManager;
