/**
 * ============================================================================
 * Menu Studio — CanvasManager
 * ============================================================================
 * Motor de renderizado DOM del menú. Genera el HTML del menú dentro del
 * canvas basándose en style_config_json y content_json.
 * Gestiona zoom, selección de elementos, y actualización en tiempo real.
 */

class CanvasManager {
    /**
     * @param {MenuEditor} editor - Referencia al editor principal
     */
    constructor(editor) {
        this.editor = editor;
        this.canvas = document.getElementById('menuCanvas');
        this.wrapper = document.getElementById('canvasWrapper');
        this.zoomLevel = 1;
        this.selectedElement = null;
    }

    /**
     * Inicializar: configurar dimensiones y bindear zoom
     */
    init() {
        this.setDimensions();
        this.bindZoomControls();
        this.render();
        this.autoFitZoom();
    }

    /**
     * Establecer dimensiones del canvas según el formato del menú
     */
    setDimensions() {
        const dims = this.editor.styles?.dimensions || { width: 210, height: 297, unit: 'mm' };

        // Convertir mm a px (aprox 96 DPI, factor 3.78)
        const pxPerMm = 3.78;
        const width = Math.round(dims.width * pxPerMm);
        const height = Math.round(dims.height * pxPerMm);

        this.canvas.style.width = width + 'px';
        this.canvas.style.minHeight = height + 'px';

        this.canvasWidth = width;
        this.canvasHeight = height;
    }

    /**
     * Renderizar el menú completo en el canvas DOM
     */
    render() {
        const styles = this.editor.styles;
        const content = this.editor.content;
        const colors = styles?.colors || {};
        const typo = styles?.typography || {};
        const layout = styles?.layout || {};
        const dishBlock = styles?.dishBlock || {};
        const header = styles?.header || {};
        const watermark = styles?.watermark || {};

        // ── Fondo ──
        this.renderBackground(styles?.background);

        // ── Construir HTML ──
        const margin = layout.margin || { top: 30, right: 25, bottom: 30, left: 25 };
        const columns = layout.columns || 2;

        let html = '';

        // ── Watermark (Marca de Agua) ──
        if (watermark.enabled) {
            html += this.renderWatermark(watermark, header);
        }

        // ── Header (Encabezado y Logo) ──
        const restaurantName = header.restaurantName !== undefined 
            ? header.restaurantName 
            : (this.editor.menuData?.restaurant_name || 'Nombre del Restaurante');
        const showRestaurantName = header.showRestaurantName !== false;

        const subtitle = header.subtitle !== undefined 
            ? header.subtitle 
            : (this.editor.menuData?.title || 'Menú');
        const showSubtitle = header.showSubtitle !== false;
        const showDivider = header.showDivider !== false;

        const logoUrl = header.logoUrl || this.editor.menuData?.restaurant_logo;
        const showLogo = header.showLogo !== false && !!logoUrl;
        const logoWidth = header.logoWidth || 90;
        const logoPosition = header.logoPosition || 'above';
        const logoShape = header.logoShape || 'original';

        let logoHtml = '';
        if (showLogo) {
            logoHtml = `<div class="mc-logo mc-logo--${logoShape}" style="width: ${logoWidth}px;">
                <img src="${this.escapeHtml(logoUrl)}" alt="Logo" style="width: 100%;">
            </div>`;
        }

        let textHtml = '';
        if (showRestaurantName) {
            textHtml += `<div class="mc-restaurant-name" style="
                font-family: '${typo.heading?.fontFamily || 'Playfair Display'}', serif;
                font-size: ${typo.heading?.fontSize || 32}px;
                font-weight: ${typo.heading?.fontWeight || 700};
                color: ${typo.heading?.color || colors.primary || '#d4af37'};
                letter-spacing: ${typo.heading?.letterSpacing || 2}px;
                text-transform: ${typo.heading?.textTransform || 'uppercase'};
            ">${this.escapeHtml(restaurantName)}</div>`;
        }

        if (showSubtitle && subtitle) {
            textHtml += `<div class="mc-menu-title" style="
                font-family: '${typo.subheading?.fontFamily || 'Montserrat'}', sans-serif;
                color: ${typo.subheading?.color || colors.muted || '#888888'};
                letter-spacing: ${typo.subheading?.letterSpacing || 1}px;
            ">${this.escapeHtml(subtitle)}</div>`;
        }

        html += `<div class="mc-header" style="padding: ${margin.top}px ${margin.right}px 20px ${margin.left}px;" title="Clic para editar encabezado y logo">`;
        html += `<div class="mc-header-container mc-header-container--${logoPosition}">`;
        if (logoPosition === 'left') {
            if (showLogo) html += logoHtml;
            html += `<div class="mc-header-text">${textHtml}</div>`;
        } else if (logoPosition === 'below') {
            html += `<div class="mc-header-text">${textHtml}</div>`;
            if (showLogo) html += logoHtml;
        } else {
            if (showLogo) html += logoHtml;
            html += `<div class="mc-header-text">${textHtml}</div>`;
        }
        html += `</div>`;

        if (showDivider) {
            html += `<div class="mc-divider" style="background: ${colors.primary || '#d4af37'};"></div>`;
        }
        html += `</div>`;

        // ── Secciones ──
        html += `<div class="mc-sections" style="padding: 0 ${margin.right}px ${margin.bottom}px ${margin.left}px;">`;

        const sections = content?.sections || [];
        sections.forEach((section, sIdx) => {
            html += `<div class="mc-section" data-section-id="${section.id}">`;

            // Título de sección
            html += `<div class="mc-section-title" style="
                font-family: '${typo.subheading?.fontFamily || 'Montserrat'}', sans-serif;
                font-size: ${typo.subheading?.fontSize || 18}px;
                font-weight: ${typo.subheading?.fontWeight || 500};
                color: ${typo.subheading?.color || colors.muted || '#c0c0c0'};
                letter-spacing: ${typo.subheading?.letterSpacing || 1}px;
            ">`;
            html += this.escapeHtml(section.title || 'Sección');
            html += `<style>.mc-section[data-section-id="${section.id}"] .mc-section-title::after { background: ${colors.primary || '#d4af37'}; }</style>`;
            html += `</div>`;

            // Divider entre secciones
            if (layout.showDividers && sIdx > 0) {
                html += this.renderDivider(layout.dividerStyle, colors);
            }

            // Items en columnas
            const visibleItems = (section.items || []).filter(item => item.visible !== false);
            html += `<div class="mc-columns mc-columns--${columns}">`;

            // Distribuir items entre columnas
            if (columns === 1) {
                html += '<div>';
                visibleItems.forEach(item => {
                    html += this.renderDishItem(item, typo, colors, dishBlock);
                });
                html += '</div>';
            } else {
                const itemsPerCol = Math.ceil(visibleItems.length / columns);
                for (let col = 0; col < columns; col++) {
                    html += '<div>';
                    const colItems = visibleItems.slice(col * itemsPerCol, (col + 1) * itemsPerCol);
                    colItems.forEach(item => {
                        html += this.renderDishItem(item, typo, colors, dishBlock);
                    });
                    html += '</div>';
                }
            }

            html += `</div>`; // mc-columns
            html += `</div>`; // mc-section
        });

        html += `</div>`; // mc-sections

        // ── Footer ──
        const menuCurrency = content?.sections?.find(s => s.items?.length > 0)?.items?.[0]?.currency || 'C$';
        html += `<div class="mc-footer" style="color: ${colors.muted || '#888888'};">`;
        html += `Precios en ${menuCurrency} • Impuestos incluidos • Comedor Machu`;
        html += `</div>`;

        this.canvas.innerHTML = html;

        // Bindear clicks en elementos del canvas
        this.bindCanvasClicks();
    }

    /**
     * Renderizar marca de agua (watermark) en el fondo del lienzo
     */
    renderWatermark(watermark, header) {
        const type = watermark.type || 'logo';
        const opacity = watermark.opacity !== undefined ? watermark.opacity : 0.08;
        const scale = watermark.scale !== undefined ? watermark.scale : 0.85;
        const rotation = watermark.rotation !== undefined ? watermark.rotation : -15;

        let content = '';

        if (type === 'logo') {
            const logoUrl = header?.logoUrl || this.editor.menuData?.restaurant_logo;
            if (logoUrl) {
                content = `<img src="${this.escapeHtml(logoUrl)}" class="mc-watermark__img" alt="Watermark Logo" style="transform: scale(${scale}) rotate(${rotation}deg); opacity: ${opacity};">`;
            } else {
                // Si no hay logo subido aún, mostrar el nombre del restaurante como marca visible
                const fallbackText = header?.restaurantName || this.editor.menuData?.restaurant_name || 'COMEDOR MACHU';
                content = `<div class="mc-watermark__text" style="font-size: ${Math.round(48 * scale)}px; transform: rotate(${rotation}deg); opacity: ${opacity}; color: ${this.editor.styles?.colors?.text || '#ffffff'};">${this.escapeHtml(fallbackText)}</div>`;
            }
        } else if (type === 'custom') {
            if (watermark.imageUrl) {
                content = `<img src="${this.escapeHtml(watermark.imageUrl)}" class="mc-watermark__img" alt="Watermark Custom" style="transform: scale(${scale}) rotate(${rotation}deg); opacity: ${opacity};">`;
            } else {
                const fallbackText = header?.restaurantName || this.editor.menuData?.restaurant_name || 'COMEDOR MACHU';
                content = `<div class="mc-watermark__text" style="font-size: ${Math.round(40 * scale)}px; transform: rotate(${rotation}deg); opacity: ${opacity}; color: ${this.editor.styles?.colors?.text || '#ffffff'};">${this.escapeHtml(fallbackText)}</div>`;
            }
        } else if (type === 'text') {
            const text = watermark.text || header?.restaurantName || this.editor.menuData?.restaurant_name || 'COMEDOR MACHU';
            content = `<div class="mc-watermark__text" style="font-size: ${Math.round(48 * scale)}px; transform: rotate(${rotation}deg); opacity: ${opacity}; color: ${this.editor.styles?.colors?.text || '#ffffff'};">${this.escapeHtml(text)}</div>`;
        }

        if (!content) return '';

        return `<div class="mc-watermark"><div class="mc-watermark__content">${content}</div></div>`;
    }

    /**
     * Renderizar un platillo individual
     */
    renderDishItem(item, typo, colors, dishBlock) {
        let html = `<div class="mc-dish" data-item-id="${item.id}">`;

        // Línea principal: Nombre + Leaders + Precio
        html += `<div class="mc-dish-header">`;

        html += `<span class="mc-dish-name" style="
            font-family: '${typo.body?.fontFamily || 'Lato'}', sans-serif;
            font-size: ${typo.body?.fontSize || 13}px;
            font-weight: 600;
            color: ${typo.body?.color || colors.text || '#e0e0e0'};
        ">${this.escapeHtml(item.name || '')}</span>`;

        // Puntos guía
        if (dishBlock.showPriceLeaders && dishBlock.pricePosition === 'right') {
            const leader = dishBlock.leaderChar || '·';
            html += `<span class="mc-dish-leaders" style="color: ${colors.muted || '#888888'};">${leader.repeat(80)}</span>`;
        }

        // Precio
        if (dishBlock.showPrices !== false && item.price != null && item.price !== '') {
            const curr = item.currency || 'C$';
            const priceVal = parseFloat(item.price) || 0;
            const priceText = priceVal > 0 ? `${curr} ${priceVal.toFixed(0)}` : '';
            if (priceText) {
                html += `<span class="mc-dish-price" style="
                    font-family: '${typo.price?.fontFamily || 'Montserrat'}', sans-serif;
                    font-size: ${typo.price?.fontSize || 14}px;
                    font-weight: ${typo.price?.fontWeight || 600};
                    color: ${typo.price?.color || colors.primary || '#d4af37'};
                ">${priceText}</span>`;
            }
        }

        html += `</div>`; // mc-dish-header

        // Descripción
        if (dishBlock.showDescription && item.description) {
            html += `<div class="mc-dish-description" style="
                font-family: '${typo.body?.fontFamily || 'Lato'}', sans-serif;
                font-size: ${Math.max(9, (typo.body?.fontSize || 13) - 2)}px;
                color: ${colors.muted || '#888888'};
                line-height: ${typo.body?.lineHeight || 1.6};
            ">${this.escapeHtml(item.description)}</div>`;
        }

        // Badges (alérgenos + especiales)
        if (dishBlock.showAllergens) {
            const badges = [...(item.allergens || []), ...(item.badges || [])];
            if (badges.length > 0) {
                html += `<div class="mc-dish-badges">`;
                badges.forEach(badge => {
                    const label = this.getBadgeLabel(badge);
                    const icon = this.getBadgeIcon(badge);
                    html += `<span class="mc-badge badge-${badge}" style="color: ${this.getBadgeColor(badge)};">
                        ${icon} ${label}
                    </span>`;
                });
                html += `</div>`;
            }
        }

        html += `</div>`; // mc-dish
        return html;
    }

    /**
     * Renderizar separador entre secciones
     */
    renderDivider(style, colors) {
        const color = colors.primary || '#d4af37';
        switch (style) {
            case 'dots':
                return `<div style="text-align:center; opacity:0.3; color:${color}; letter-spacing:6px; margin:12px 0; font-size:10px;">· · · · · · · · · · ·</div>`;
            case 'line':
                return `<div style="border-top:1px solid ${color}; opacity:0.15; margin:16px 0;"></div>`;
            case 'dashed':
                return `<div style="border-top:1px dashed ${color}; opacity:0.2; margin:16px 0;"></div>`;
            case 'glow':
                return `<div style="border-top:1px solid ${color}; opacity:0.3; margin:16px 0; box-shadow: 0 0 8px ${color};"></div>`;
            default:
                return '';
        }
    }

    /**
     * Renderizar fondo del canvas
     */
    renderBackground(background) {
        if (!background) {
            this.canvas.style.background = '#1a1a2e';
            return;
        }

        switch (background.type) {
            case 'solid':
                this.canvas.style.background = background.value || '#1a1a2e';
                break;

            case 'gradient':
                const grad = background.value || {};
                const stops = grad.stops || ['#1a1a2e', '#16213e'];
                const angle = grad.angle || 180;
                this.canvas.style.background = `linear-gradient(${angle}deg, ${stops.join(', ')})`;
                break;

            case 'texture':
                this.applyTexture(background.value || 'none');
                break;

            default:
                this.canvas.style.background = '#1a1a2e';
        }
    }

    /**
     * Aplicar textura predefinida al canvas
     */
    applyTexture(textureName) {
        const textures = {
            none: '#1a1a2e',
            kraft: '#c4a86b',
            chalkboard: '#2d3436',
            marble: 'linear-gradient(135deg, #f5f5f5 25%, #e8e8e8 50%, #f5f5f5 75%)',
            linen: '#faf0e6',
            wood: 'linear-gradient(180deg, #8B6914 0%, #A67B2E 20%, #7B5B11 40%, #9B6E22 60%, #8B6914 80%)',
        };

        const bg = textures[textureName] || textures.none;
        this.canvas.style.background = bg;
    }

    /**
     * Bindear clicks en elementos del canvas para selección
     */
    bindCanvasClicks() {
        // Click en platillos
        this.canvas.querySelectorAll('.mc-dish').forEach(dish => {
            dish.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectElement(dish, 'dish');
            });
        });

        // Click en secciones
        this.canvas.querySelectorAll('.mc-section-title').forEach(title => {
            title.addEventListener('click', (e) => {
                e.stopPropagation();
                const section = title.closest('.mc-section');
                this.selectElement(section, 'section');
            });
        });

        // Click en encabezado (marca, nombre del lugar y logo)
        const headerEl = this.canvas.querySelector('.mc-header');
        if (headerEl) {
            headerEl.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectElement(headerEl, 'header');
            });
        }

        // Click en el canvas vacío: deseleccionar
        this.canvas.addEventListener('click', (e) => {
            if (e.target === this.canvas) {
                this.deselectAll();
            }
        });
    }

    /**
     * Seleccionar un elemento en el canvas
     */
    selectElement(element, type) {
        this.deselectAll();
        element.classList.add('selected');
        this.selectedElement = { element, type };

        // Mostrar panel de propiedades
        const propsPanel = document.getElementById('editorProperties');
        if (propsPanel) {
            propsPanel.classList.remove('hidden');
            this.showProperties(element, type);
        }
    }

    /**
     * Deseleccionar todos los elementos
     */
    deselectAll() {
        this.canvas.querySelectorAll('.selected').forEach(el => el.classList.remove('selected'));
        this.selectedElement = null;

        const propsPanel = document.getElementById('editorProperties');
        if (propsPanel) propsPanel.classList.add('hidden');
    }

    /**
     * Mostrar propiedades del elemento seleccionado en el panel derecho
     */
    showProperties(element, type) {
        const title = document.getElementById('propertiesTitle');
        const content = document.getElementById('propertiesContent');

        if (type === 'header') {
            title.textContent = 'Encabezado y Marca';
            const header = this.editor.styles?.header || {};
            const restaurantName = header.restaurantName !== undefined 
                ? header.restaurantName 
                : (this.editor.menuData?.restaurant_name || '');
            const subtitle = header.subtitle !== undefined 
                ? header.subtitle 
                : (this.editor.menuData?.title || '');

            content.innerHTML = `
                <div class="sidebar-panel__section">
                    <div class="form-group">
                        <label class="form-label">Nombre del Lugar / Restaurante</label>
                        <input type="text" class="form-input" id="propHeaderName" value="${this.escapeHtml(restaurantName)}"
                               oninput="window.menuEditor.toolbarManager.updateRestaurantName(this.value)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subtítulo / Lema del Menú</label>
                        <input type="text" class="form-input" id="propHeaderSubtitle" value="${this.escapeHtml(subtitle)}"
                               oninput="window.menuEditor.toolbarManager.updateMenuSubtitle(this.value)">
                    </div>
                    <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--ms-border);">
                        <button type="button" class="btn btn--small btn--primary" style="width:100%; display:flex; align-items:center; justify-content:center; gap:8px;"
                                onclick="document.querySelector('[data-tab=brand]')?.click()">
                            <span class="material-icons-round" style="font-size:18px;">storefront</span>
                            Abrir panel de Marca y Logotipo
                        </button>
                    </div>
                </div>
            `;
        } else if (type === 'dish') {
            const itemId = element.dataset.itemId;
            const item = this.editor.findItemById(itemId);
            title.textContent = 'Propiedades del Platillo';
            content.innerHTML = item ? `
                <div class="sidebar-panel__section">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-input" value="${this.escapeHtml(item.name || '')}"
                               onchange="window.menuEditor.updateItem('${itemId}', 'name', this.value)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-input" rows="3"
                                  onchange="window.menuEditor.updateItem('${itemId}', 'description', this.value)">${this.escapeHtml(item.description || '')}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Precio</label>
                        <input type="number" class="form-input" step="0.01" value="${item.price || 0}"
                               onchange="window.menuEditor.updateItem('${itemId}', 'price', parseFloat(this.value))">
                    </div>
                </div>
            ` : '<p>Elemento no encontrado</p>';
        } else if (type === 'section') {
            const sectionId = element.dataset.sectionId;
            const section = this.editor.findSectionById(sectionId);
            title.textContent = 'Propiedades de Sección';
            content.innerHTML = section ? `
                <div class="sidebar-panel__section">
                    <div class="form-group">
                        <label class="form-label">Título de la Sección</label>
                        <input type="text" class="form-input" value="${this.escapeHtml(section.title || '')}"
                               onchange="window.menuEditor.updateSection('${sectionId}', 'title', this.value)">
                    </div>
                </div>
            ` : '<p>Sección no encontrada</p>';
        }
    }

    /**
     * Controles de Zoom
     */
    bindZoomControls() {
        document.getElementById('btnZoomIn')?.addEventListener('click', () => this.zoom(0.1));
        document.getElementById('btnZoomOut')?.addEventListener('click', () => this.zoom(-0.1));
        document.getElementById('btnZoomFit')?.addEventListener('click', () => this.autoFitZoom());

        // Zoom con Ctrl + Scroll
        document.getElementById('editorCanvasArea')?.addEventListener('wheel', (e) => {
            if (e.ctrlKey) {
                e.preventDefault();
                this.zoom(e.deltaY > 0 ? -0.05 : 0.05);
            }
        }, { passive: false });
    }

    /**
     * Aplicar zoom
     */
    zoom(delta) {
        this.zoomLevel = Math.max(0.3, Math.min(2, this.zoomLevel + delta));
        this.wrapper.style.transform = `scale(${this.zoomLevel})`;
        document.getElementById('zoomLevel').textContent = Math.round(this.zoomLevel * 100) + '%';
    }

    /**
     * Auto-ajustar zoom al viewport
     */
    autoFitZoom() {
        const area = document.getElementById('editorCanvasArea');
        if (!area) return;

        const areaWidth = area.clientWidth - 80;
        const areaHeight = area.clientHeight - 80;
        const canvasWidth = this.canvasWidth || 595;
        const canvasHeight = this.canvasHeight || 842;

        const scaleX = areaWidth / canvasWidth;
        const scaleY = areaHeight / canvasHeight;
        this.zoomLevel = Math.min(scaleX, scaleY, 1);

        this.wrapper.style.transform = `scale(${this.zoomLevel})`;
        document.getElementById('zoomLevel').textContent = Math.round(this.zoomLevel * 100) + '%';
    }

    /**
     * Escapar HTML para prevenir XSS
     */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    /**
     * Obtener etiqueta de badge
     */
    getBadgeLabel(badge) {
        const labels = {
            vegetarian: 'Vegetariano',
            vegan: 'Vegano',
            gluten_free: 'Sin Gluten',
            spicy: 'Picante',
            chef_recommendation: 'Chef',
            dairy_free: 'Sin Lácteos',
            nut_free: 'Sin Nueces',
        };
        return labels[badge] || badge;
    }

    /**
     * Obtener ícono de badge
     */
    getBadgeIcon(badge) {
        const icons = {
            vegetarian: '🌿',
            vegan: '🌱',
            gluten_free: '🌾',
            spicy: '🌶️',
            chef_recommendation: '⭐',
            dairy_free: '🥛',
            nut_free: '🥜',
        };
        return icons[badge] || '•';
    }

    /**
     * Obtener color de badge
     */
    getBadgeColor(badge) {
        const colorMap = {
            vegetarian: '#22c55e',
            vegan: '#16a34a',
            gluten_free: '#f59e0b',
            spicy: '#ef4444',
            chef_recommendation: '#d4af37',
            dairy_free: '#3b82f6',
            nut_free: '#a855f7',
        };
        return colorMap[badge] || '#888888';
    }
}

window.CanvasManager = CanvasManager;
