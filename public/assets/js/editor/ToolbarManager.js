/**
 * ============================================================================
 * Menu Studio — ToolbarManager
 * ============================================================================
 * Gestiona la barra lateral izquierda del editor: tabs de navegación,
 * lista de secciones/platillos, fondos, layout y opciones de visibilidad.
 */

class ToolbarManager {
    /**
     * @param {MenuEditor} editor - Referencia al editor principal
     */
    constructor(editor) {
        this.editor = editor;
    }

    /**
     * Inicializar: bindear tabs, fondos, layout y visibilidad
     */
    init() {
        this.bindTabs();
        this.bindBrandControls();
        this.bindBackgroundControls();
        this.bindLayoutControls();
        this.bindVisibilityToggles();
        this.bindSectionSearchControls();
        this.renderSectionsList();
    }

    /**
     * Bindear controles de búsqueda rápida y colapsar/expandir secciones
     */
    bindSectionSearchControls() {
        const searchInput = document.getElementById('searchDishesInput');
        const clearBtn = document.getElementById('btnClearSearchDishes');
        const btnExpand = document.getElementById('btnExpandAllSections');
        const btnCollapse = document.getElementById('btnCollapseAllSections');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.trim().toLowerCase();
                if (clearBtn) clearBtn.style.display = query ? 'flex' : 'none';

                const sectionEls = document.querySelectorAll('#sectionsList .section-item');
                sectionEls.forEach(secEl => {
                    const secTitle = secEl.querySelector('.section-item__title')?.value.toLowerCase() || '';
                    const dishEls = secEl.querySelectorAll('.dish-item');
                    let hasMatchingDish = false;

                    dishEls.forEach(dishEl => {
                        const name = dishEl.querySelector('.dish-item__name')?.value.toLowerCase() || '';
                        const desc = dishEl.querySelector('.dish-item__desc')?.value.toLowerCase() || '';
                        const price = dishEl.querySelector('.dish-item__price')?.value || '';
                        const matches = !query || name.includes(query) || desc.includes(query) || price.includes(query) || secTitle.includes(query);

                        dishEl.style.display = matches ? 'flex' : 'none';
                        if (matches && query) hasMatchingDish = true;
                    });

                    if (!query) {
                        secEl.style.display = 'block';
                    } else if (secTitle.includes(query) || hasMatchingDish) {
                        secEl.style.display = 'block';
                        const body = secEl.querySelector('.section-item__body');
                        const toggle = secEl.querySelector('[data-section-toggle]');
                        if (body) body.classList.add('open');
                        if (toggle) toggle.classList.add('open');
                    } else {
                        secEl.style.display = 'none';
                    }
                });
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                }
            });
        }

        if (btnExpand) {
            btnExpand.addEventListener('click', () => {
                document.querySelectorAll('#sectionsList .section-item__body').forEach(b => b.classList.add('open'));
                document.querySelectorAll('#sectionsList [data-section-toggle]').forEach(t => t.classList.add('open'));
            });
        }

        if (btnCollapse) {
            btnCollapse.addEventListener('click', () => {
                document.querySelectorAll('#sectionsList .section-item__body').forEach(b => b.classList.remove('open'));
                document.querySelectorAll('#sectionsList [data-section-toggle]').forEach(t => t.classList.remove('open'));
            });
        }
    }

    /**
     * Bindear navegación de tabs del sidebar
     */
    bindTabs() {
        document.querySelectorAll('.sidebar-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Desactivar todos los tabs y paneles
                document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.sidebar-panel').forEach(p => p.classList.remove('active'));

                // Activar el tab clickeado y su panel
                tab.classList.add('active');
                const panel = document.getElementById(`panel-${tab.dataset.tab}`);
                if (panel) panel.classList.add('active');
            });
        });
    }

    /**
     * Renderizar la lista de secciones y platillos en el sidebar
     */
    renderSectionsList() {
        const container = document.getElementById('sectionsList');
        if (!container) return;

        const sections = this.editor.content?.sections || [];
        container.innerHTML = '';

        // Actualizar contador general
        const totalItems = sections.reduce((acc, s) => acc + (s.items?.length || 0), 0);
        const counterEl = document.getElementById('dishesCounterBadge');
        if (counterEl) {
            counterEl.textContent = `${totalItems} Platillos (${sections.length} Secciones)`;
        }

        sections.forEach((section, sIdx) => {
            const sectionEl = document.createElement('div');
            sectionEl.className = 'section-item';
            sectionEl.dataset.sectionId = section.id;
            sectionEl.setAttribute('draggable', 'true');

            const items = section.items || [];

            sectionEl.innerHTML = `
                <div class="section-item__header" data-toggle-section="${section.id}">
                    <span class="material-icons-round section-item__drag" title="Arrastrar para reordenar">drag_indicator</span>
                    <input type="text" class="section-item__title" value="${this.escapeHtml(section.title)}"
                           data-section-id="${section.id}" data-field="title"
                           onclick="event.stopPropagation()">
                    <span class="section-item__count">${items.length}</span>
                    <button class="section-item__toggle material-icons-round" data-section-toggle="${section.id}">expand_more</button>
                </div>
                <div class="section-item__body" id="sectionBody-${section.id}">
                    <div class="dishes-list" data-section-id="${section.id}">
                        ${items.map((item, iIdx) => this.renderDishListItem(item, section.id)).join('')}
                    </div>
                    <button class="add-dish-btn" data-add-dish="${section.id}">
                        <span class="material-icons-round">add</span>
                        Añadir platillo
                    </button>
                </div>
            `;

            container.appendChild(sectionEl);
        });

        // Bindear eventos de los elementos generados
        this.bindSectionEvents();
    }

    /**
     * Renderizar un item de platillo en la lista del sidebar
     */
    renderDishListItem(item, sectionId) {
        return `
            <div class="dish-item" data-item-id="${item.id}" data-section-id="${sectionId}" draggable="true">
                <span class="material-icons-round dish-item__drag">drag_indicator</span>
                <div class="dish-item__info">
                    <input type="text" class="dish-item__name" value="${this.escapeHtml(item.name || '')}"
                           data-item-id="${item.id}" data-field="name">
                    <input type="text" class="dish-item__desc" value="${this.escapeHtml(item.description || '')}"
                           data-item-id="${item.id}" data-field="description"
                           placeholder="Descripción...">
                </div>
                <input type="number" class="dish-item__price" value="${item.price || ''}" step="1"
                       data-item-id="${item.id}" data-field="price" placeholder="$0">
                <div class="dish-item__actions">
                    <button class="dish-item__action-btn material-icons-round"
                            data-delete-item="${item.id}" data-section-id="${sectionId}"
                            title="Eliminar">delete</button>
                </div>
            </div>
        `;
    }

    /**
     * Bindear eventos de la lista de secciones
     */
    bindSectionEvents() {
        // Toggle abrir/cerrar sección
        document.querySelectorAll('[data-toggle-section]').forEach(header => {
            header.addEventListener('click', (e) => {
                if (e.target.tagName === 'INPUT') return; // No cerrar al editar título
                const sectionId = header.dataset.toggleSection;
                const body = document.getElementById(`sectionBody-${sectionId}`);
                const toggle = header.querySelector('[data-section-toggle]');
                if (body) body.classList.toggle('open');
                if (toggle) toggle.classList.toggle('open');
            });
        });

        // Editar título de sección inline
        document.querySelectorAll('.section-item__title').forEach(input => {
            input.addEventListener('change', (e) => {
                const sectionId = e.target.dataset.sectionId;
                this.editor.updateSection(sectionId, 'title', e.target.value);
            });
        });

        // Editar platillo inline (nombre, descripción, precio)
        document.querySelectorAll('.dish-item__name, .dish-item__desc, .dish-item__price').forEach(input => {
            input.addEventListener('change', (e) => {
                const itemId = e.target.dataset.itemId;
                const field = e.target.dataset.field;
                let value = e.target.value;

                if (field === 'price') {
                    value = parseFloat(value) || 0;
                }

                this.editor.updateItem(itemId, field, value);
            });
        });

        // Añadir platillo
        document.querySelectorAll('[data-add-dish]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const sectionId = e.target.closest('[data-add-dish]').dataset.addDish;
                this.editor.addItem(sectionId);
            });
        });

        // Eliminar platillo
        document.querySelectorAll('[data-delete-item]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const itemId = e.target.dataset.deleteItem;
                const sectionId = e.target.dataset.sectionId;
                if (confirm('¿Eliminar este platillo?')) {
                    this.editor.removeItem(sectionId, itemId);
                }
            });
        });

        // Añadir sección
        document.getElementById('btnAddSection')?.addEventListener('click', () => {
            this.editor.addSection();
        });
    }

    /**
     * Bindear controles de fondo
     */
    bindBackgroundControls() {
        // Tipo de fondo
        document.querySelectorAll('.bg-type-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.bgType;

                document.querySelectorAll('.bg-type-btn').forEach(b => b.classList.remove('active'));
                e.currentTarget.classList.add('active');

                // Mostrar/ocultar controles apropiados
                document.querySelectorAll('.bg-controls').forEach(c => c.classList.add('hidden'));
                const controls = document.getElementById(`bg${this.capitalize(type)}Controls`);
                if (controls) controls.classList.remove('hidden');

                this.editor.styles.background = this.editor.styles.background || {};
                this.editor.styles.background.type = type;

                // Aplicar valores según tipo
                if (type === 'solid') {
                    const color = document.getElementById('bgSolidColor')?.value || '#1a1a2e';
                    this.editor.styles.background.value = color;
                } else if (type === 'gradient') {
                    const c1 = document.getElementById('bgGradientColor1')?.value || '#1a1a2e';
                    const c2 = document.getElementById('bgGradientColor2')?.value || '#16213e';
                    const angle = document.getElementById('bgGradientAngle')?.value || 180;
                    this.editor.styles.background.value = { type: 'linear', angle: parseInt(angle), stops: [c1, c2] };
                }

                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });

        // Color sólido
        document.getElementById('bgSolidColor')?.addEventListener('input', (e) => {
            const hex = document.getElementById('bgSolidHex');
            if (hex) hex.value = e.target.value;
            this.editor.styles.background = { type: 'solid', value: e.target.value };
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        document.getElementById('bgSolidHex')?.addEventListener('change', (e) => {
            let val = e.target.value.trim();
            if (!val.startsWith('#')) val = '#' + val;
            const picker = document.getElementById('bgSolidColor');
            if (picker) picker.value = val;
            this.editor.styles.background = { type: 'solid', value: val };
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        // Gradiente
        ['bgGradientColor1', 'bgGradientColor2', 'bgGradientAngle'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', () => {
                const c1 = document.getElementById('bgGradientColor1')?.value || '#1a1a2e';
                const c2 = document.getElementById('bgGradientColor2')?.value || '#16213e';
                const angle = parseInt(document.getElementById('bgGradientAngle')?.value || 180);

                document.getElementById('bgGradientAngleVal').textContent = angle + '°';

                this.editor.styles.background = {
                    type: 'gradient',
                    value: { type: 'linear', angle, stops: [c1, c2] }
                };
                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });

        // Texturas
        document.querySelectorAll('.texture-option').forEach(opt => {
            opt.addEventListener('click', (e) => {
                document.querySelectorAll('.texture-option').forEach(o => o.classList.remove('active'));
                e.currentTarget.classList.add('active');

                const texture = e.currentTarget.dataset.texture;
                this.editor.styles.background = { type: 'texture', value: texture };
                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });
    }

    /**
     * Bindear controles de layout
     */
    bindLayoutControls() {
        // Columnas
        document.querySelectorAll('[data-columns]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('[data-columns]').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');

                const cols = parseInt(e.target.dataset.columns);
                if (!this.editor.styles.layout) this.editor.styles.layout = {};
                this.editor.styles.layout.columns = cols;

                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });

        // Márgenes
        ['marginTop', 'marginBottom', 'marginLeft', 'marginRight'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', (e) => {
                const side = id.replace('margin', '').toLowerCase();
                if (!this.editor.styles.layout) this.editor.styles.layout = {};
                if (!this.editor.styles.layout.margin) this.editor.styles.layout.margin = {};
                this.editor.styles.layout.margin[side] = parseInt(e.target.value) || 0;

                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });

        // Separadores
        document.getElementById('toggleDividers')?.addEventListener('change', (e) => {
            if (!this.editor.styles.layout) this.editor.styles.layout = {};
            this.editor.styles.layout.showDividers = e.target.checked;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        document.querySelectorAll('[data-divider]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('[data-divider]').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');

                if (!this.editor.styles.layout) this.editor.styles.layout = {};
                this.editor.styles.layout.dividerStyle = e.target.dataset.divider;

                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });

        // Price leaders
        document.getElementById('togglePriceLeaders')?.addEventListener('change', (e) => {
            if (!this.editor.styles.dishBlock) this.editor.styles.dishBlock = {};
            this.editor.styles.dishBlock.showPriceLeaders = e.target.checked;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        // Price position
        document.getElementById('pricePosition')?.addEventListener('change', (e) => {
            if (!this.editor.styles.dishBlock) this.editor.styles.dishBlock = {};
            this.editor.styles.dishBlock.pricePosition = e.target.value;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });
    }

    /**
     * Bindear toggles de visibilidad (precios, descripciones, alérgenos, imágenes)
     */
    bindVisibilityToggles() {
        const toggleMap = {
            togglePrices: 'showPrices',
            toggleDescriptions: 'showDescription',
            toggleAllergens: 'showAllergens',
            toggleImages: 'showImages',
        };

        Object.entries(toggleMap).forEach(([elementId, configKey]) => {
            document.getElementById(elementId)?.addEventListener('change', (e) => {
                if (!this.editor.styles.dishBlock) this.editor.styles.dishBlock = {};
                this.editor.styles.dishBlock[configKey] = e.target.checked;
                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });
    }

    /**
     * Sincronizar controles UI con los estilos actuales
     */
    syncControlsFromStyles() {
        const layout = this.editor.styles?.layout || {};
        const dishBlock = this.editor.styles?.dishBlock || {};

        // Columnas
        document.querySelectorAll('[data-columns]').forEach(btn => {
            btn.classList.toggle('active', parseInt(btn.dataset.columns) === (layout.columns || 2));
        });

        // Márgenes
        const margin = layout.margin || {};
        if (margin.top !== undefined) document.getElementById('marginTop').value = margin.top;
        if (margin.bottom !== undefined) document.getElementById('marginBottom').value = margin.bottom;
        if (margin.left !== undefined) document.getElementById('marginLeft').value = margin.left;
        if (margin.right !== undefined) document.getElementById('marginRight').value = margin.right;

        // Dividers
        const divToggle = document.getElementById('toggleDividers');
        if (divToggle) divToggle.checked = layout.showDividers !== false;

        // Price leaders
        const leadersToggle = document.getElementById('togglePriceLeaders');
        if (leadersToggle) leadersToggle.checked = dishBlock.showPriceLeaders !== false;

        // Visibility toggles
        const descToggle = document.getElementById('toggleDescriptions');
        if (descToggle) descToggle.checked = dishBlock.showDescription !== false;
        const allergenToggle = document.getElementById('toggleAllergens');
        if (allergenToggle) allergenToggle.checked = dishBlock.showAllergens !== false;

        // ── Sincronizar Header & Marca ──
        const header = this.editor.styles?.header || {};
        const watermark = this.editor.styles?.watermark || {};

        const restName = header.restaurantName !== undefined 
            ? header.restaurantName 
            : (this.editor.menuData?.restaurant_name || '');
        const inputRestName = document.getElementById('inputRestaurantName');
        if (inputRestName) inputRestName.value = restName;

        const toggleName = document.getElementById('toggleRestaurantName');
        if (toggleName) toggleName.checked = header.showRestaurantName !== false;

        const subTitle = header.subtitle !== undefined 
            ? header.subtitle 
            : (this.editor.menuData?.title || '');
        const inputSub = document.getElementById('inputMenuSubtitle');
        if (inputSub) inputSub.value = subTitle;

        const toggleSub = document.getElementById('toggleMenuSubtitle');
        if (toggleSub) toggleSub.checked = header.showSubtitle !== false;

        const toggleHDiv = document.getElementById('toggleHeaderDivider');
        if (toggleHDiv) toggleHDiv.checked = header.showDivider !== false;

        // Logo
        const toggleLogo = document.getElementById('toggleLogo');
        if (toggleLogo) toggleLogo.checked = header.showLogo !== false;

        const logoUrl = header.logoUrl || this.editor.menuData?.restaurant_logo;
        this.updateLogoPreviewUI(logoUrl);

        const logoWidth = header.logoWidth || 90;
        const widthRange = document.getElementById('logoWidthRange');
        if (widthRange) widthRange.value = logoWidth;
        const widthVal = document.getElementById('logoWidthVal');
        if (widthVal) widthVal.textContent = `${logoWidth}px`;

        const logoPos = header.logoPosition || 'above';
        document.querySelectorAll('#logoPositionGroup [data-pos]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.pos === logoPos);
        });

        const logoShape = header.logoShape || 'original';
        document.querySelectorAll('#logoShapeGroup [data-shape]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.shape === logoShape);
        });

        // Watermark
        const toggleWm = document.getElementById('toggleWatermark');
        if (toggleWm) toggleWm.checked = watermark.enabled === true;

        const wmControls = document.getElementById('watermarkControls');
        if (wmControls) {
            wmControls.classList.toggle('hidden', watermark.enabled !== true);
            wmControls.style.display = watermark.enabled === true ? 'block' : 'none';
        }

        const wmType = watermark.type || 'logo';
        document.querySelectorAll('#watermarkTypeGroup [data-wm-type]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.wmType === wmType);
        });

        const textGroup = document.getElementById('wmTextGroup');
        const customGroup = document.getElementById('wmCustomGroup');
        if (textGroup) {
            textGroup.classList.toggle('hidden', wmType !== 'text');
            textGroup.style.display = wmType === 'text' ? 'block' : 'none';
        }
        if (customGroup) {
            customGroup.classList.toggle('hidden', wmType !== 'custom');
            customGroup.style.display = wmType === 'custom' ? 'block' : 'none';
        }

        const wmTextInput = document.getElementById('watermarkTextInput');
        if (wmTextInput) wmTextInput.value = watermark.text || restName || '';

        const wmOpacity = document.getElementById('watermarkOpacity');
        if (wmOpacity) {
            const opVal = watermark.opacity !== undefined ? watermark.opacity : 0.08;
            wmOpacity.value = opVal;
            const opLabel = document.getElementById('watermarkOpacityVal');
            if (opLabel) opLabel.textContent = `${Math.round(opVal * 100)}%`;
        }

        const wmScale = document.getElementById('watermarkScale');
        if (wmScale) {
            const scVal = watermark.scale !== undefined ? watermark.scale : 0.85;
            wmScale.value = scVal;
            const scLabel = document.getElementById('watermarkScaleVal');
            if (scLabel) scLabel.textContent = `${Math.round(scVal * 100)}%`;
        }

        const wmRot = document.getElementById('watermarkRotation');
        if (wmRot) {
            const rotVal = watermark.rotation !== undefined ? watermark.rotation : -15;
            wmRot.value = rotVal;
            const rotLabel = document.getElementById('watermarkRotationVal');
            if (rotLabel) rotLabel.textContent = `${rotVal}°`;
        }
    }

    /**
     * Bindear controles de marca, encabezado, logotipo y marca de agua
     */
    bindBrandControls() {
        if (!this.editor.styles.header) {
            this.editor.styles.header = {};
        }
        if (!this.editor.styles.watermark) {
            this.editor.styles.watermark = {};
        }

        // ── 1. Nombre del Restaurante / Lugar ──
        const inputName = document.getElementById('inputRestaurantName');
        inputName?.addEventListener('input', (e) => {
            this.updateRestaurantName(e.target.value);
        });

        const toggleName = document.getElementById('toggleRestaurantName');
        toggleName?.addEventListener('change', (e) => {
            if (!this.editor.styles.header) this.editor.styles.header = {};
            this.editor.styles.header.showRestaurantName = e.target.checked;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        // ── Subtítulo / Lema ──
        const inputSub = document.getElementById('inputMenuSubtitle');
        inputSub?.addEventListener('input', (e) => {
            this.updateMenuSubtitle(e.target.value);
        });

        const toggleSub = document.getElementById('toggleMenuSubtitle');
        toggleSub?.addEventListener('change', (e) => {
            if (!this.editor.styles.header) this.editor.styles.header = {};
            this.editor.styles.header.showSubtitle = e.target.checked;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        // ── Divider del Header ──
        const toggleHDiv = document.getElementById('toggleHeaderDivider');
        toggleHDiv?.addEventListener('change', (e) => {
            if (!this.editor.styles.header) this.editor.styles.header = {};
            this.editor.styles.header.showDivider = e.target.checked;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        // ── 2. Logotipo ──
        const toggleLogo = document.getElementById('toggleLogo');
        toggleLogo?.addEventListener('change', (e) => {
            if (!this.editor.styles.header) this.editor.styles.header = {};
            this.editor.styles.header.showLogo = e.target.checked;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        const dropzone = document.getElementById('logoDropzone');
        const fileInput = document.getElementById('logoFileInput');
        const selectBtn = document.getElementById('btnSelectLogoFile');
        const changeBtn = document.getElementById('btnChangeLogo');
        const removeBtn = document.getElementById('btnRemoveLogo');

        const triggerLogoFile = (e) => {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            fileInput?.click();
        };

        dropzone?.addEventListener('click', (e) => {
            if (e.target.closest('#btnSelectLogoFile')) return;
            triggerLogoFile(e);
        });
        selectBtn?.addEventListener('click', triggerLogoFile);
        changeBtn?.addEventListener('click', triggerLogoFile);

        // Drag and drop en dropzone
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone?.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone?.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone?.addEventListener('drop', (e) => {
            const files = e.dataTransfer?.files;
            if (files && files.length > 0) {
                this.handleLogoUpload(files[0]);
            }
        });

        fileInput?.addEventListener('change', (e) => {
            if (e.target.files && e.target.files.length > 0) {
                this.handleLogoUpload(e.target.files[0]);
            }
        });

        removeBtn?.addEventListener('click', () => {
            if (!this.editor.styles.header) this.editor.styles.header = {};
            this.editor.styles.header.logoUrl = null;
            if (this.editor.menuData) {
                this.editor.menuData.restaurant_logo = null;
            }
            if (fileInput) fileInput.value = '';
            this.updateLogoPreviewUI(null);
            this.editor.canvasManager.render();
            this.editor.markDirty();
            this.editor.save();
        });

        // Tamaño de logo
        const widthRange = document.getElementById('logoWidthRange');
        const widthVal = document.getElementById('logoWidthVal');
        widthRange?.addEventListener('input', (e) => {
            const val = parseInt(e.target.value);
            if (!this.editor.styles.header) this.editor.styles.header = {};
            this.editor.styles.header.logoWidth = val;
            if (widthVal) widthVal.textContent = `${val}px`;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        // Posición de logo
        document.querySelectorAll('#logoPositionGroup [data-pos]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('#logoPositionGroup [data-pos]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                if (!this.editor.styles.header) this.editor.styles.header = {};
                this.editor.styles.header.logoPosition = btn.dataset.pos;
                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });

        // Forma de logo
        document.querySelectorAll('#logoShapeGroup [data-shape]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('#logoShapeGroup [data-shape]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                if (!this.editor.styles.header) this.editor.styles.header = {};
                this.editor.styles.header.logoShape = btn.dataset.shape;
                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });

        // ── 3. Marca de Agua ──
        const toggleWm = document.getElementById('toggleWatermark');
        const wmControls = document.getElementById('watermarkControls');
        toggleWm?.addEventListener('change', (e) => {
            const enabled = e.target.checked;
            if (!this.editor.styles.watermark) this.editor.styles.watermark = {};
            this.editor.styles.watermark.enabled = enabled;
            if (wmControls) {
                wmControls.classList.toggle('hidden', !enabled);
                wmControls.style.display = enabled ? 'block' : 'none';
            }
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        // Tipo de watermark
        document.querySelectorAll('#watermarkTypeGroup [data-wm-type]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('#watermarkTypeGroup [data-wm-type]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const type = btn.dataset.wmType;
                if (!this.editor.styles.watermark) this.editor.styles.watermark = {};
                this.editor.styles.watermark.type = type;

                const textGroup = document.getElementById('wmTextGroup');
                const customGroup = document.getElementById('wmCustomGroup');
                if (textGroup) {
                    textGroup.classList.toggle('hidden', type !== 'text');
                    textGroup.style.display = type === 'text' ? 'block' : 'none';
                }
                if (customGroup) {
                    customGroup.classList.toggle('hidden', type !== 'custom');
                    customGroup.style.display = type === 'custom' ? 'block' : 'none';
                }

                this.editor.canvasManager.render();
                this.editor.markDirty();
            });
        });

        // Texto watermark
        const wmTextInput = document.getElementById('watermarkTextInput');
        wmTextInput?.addEventListener('input', (e) => {
            if (!this.editor.styles.watermark) this.editor.styles.watermark = {};
            this.editor.styles.watermark.text = e.target.value;
            const wmText = document.querySelector('.mc-watermark__text');
            if (wmText) {
                wmText.textContent = e.target.value;
            } else {
                this.editor.canvasManager.render();
            }
            this.editor.markDirty();
        });

        // Custom Watermark file
        const btnUploadWm = document.getElementById('btnUploadWatermarkCustom');
        const wmFileInput = document.getElementById('watermarkFileInput');
        btnUploadWm?.addEventListener('click', () => wmFileInput?.click());
        wmFileInput?.addEventListener('change', (e) => {
            if (e.target.files && e.target.files.length > 0) {
                this.handleCustomWatermarkUpload(e.target.files[0]);
            }
        });

        // Opacidad
        const wmOpacity = document.getElementById('watermarkOpacity');
        const wmOpacityVal = document.getElementById('watermarkOpacityVal');
        wmOpacity?.addEventListener('input', (e) => {
            const val = parseFloat(e.target.value);
            if (!this.editor.styles.watermark) this.editor.styles.watermark = {};
            this.editor.styles.watermark.opacity = val;
            if (wmOpacityVal) wmOpacityVal.textContent = `${Math.round(val * 100)}%`;
            const wmContent = document.querySelector('.mc-watermark__img, .mc-watermark__text');
            if (wmContent) {
                wmContent.style.opacity = val;
            } else {
                this.editor.canvasManager.render();
            }
            this.editor.markDirty();
        });

        // Escala
        const wmScale = document.getElementById('watermarkScale');
        const wmScaleVal = document.getElementById('watermarkScaleVal');
        wmScale?.addEventListener('input', (e) => {
            const val = parseFloat(e.target.value);
            if (!this.editor.styles.watermark) this.editor.styles.watermark = {};
            this.editor.styles.watermark.scale = val;
            if (wmScaleVal) wmScaleVal.textContent = `${Math.round(val * 100)}%`;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });

        // Rotación
        const wmRot = document.getElementById('watermarkRotation');
        const wmRotVal = document.getElementById('watermarkRotationVal');
        wmRot?.addEventListener('input', (e) => {
            const val = parseInt(e.target.value);
            if (!this.editor.styles.watermark) this.editor.styles.watermark = {};
            this.editor.styles.watermark.rotation = val;
            if (wmRotVal) wmRotVal.textContent = `${val}°`;
            this.editor.canvasManager.render();
            this.editor.markDirty();
        });
    }

    /**
     * Subir archivo de logotipo al servidor o convertir a DataURL
     */
    async handleLogoUpload(file) {
        if (!file) return;

        // Auto-activar toggle de logo si estaba apagado
        if (!this.editor.styles.header) this.editor.styles.header = {};
        this.editor.styles.header.showLogo = true;
        const toggleLogo = document.getElementById('toggleLogo');
        if (toggleLogo) toggleLogo.checked = true;

        // Preview inmediato con FileReader para feedback visual sin retraso
        const reader = new FileReader();
        reader.onload = (e) => {
            const dataUrl = e.target.result;
            if (!this.editor.styles.header) this.editor.styles.header = {};
            this.editor.styles.header.logoUrl = dataUrl;
            this.updateLogoPreviewUI(dataUrl);
            this.editor.canvasManager.render();
        };
        reader.readAsDataURL(file);

        // Subir al servidor vía API
        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', 'logo');
            formData.append('restaurant_id', this.editor.menuData?.restaurant_id || 1);

            const response = await fetch(`${this.editor.apiBase}/media/upload`, {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();
            if (result.success && result.data?.file_path) {
                const serverUrl = result.data.file_path;
                if (!this.editor.styles.header) this.editor.styles.header = {};
                this.editor.styles.header.logoUrl = serverUrl;
                if (this.editor.menuData) {
                    this.editor.menuData.restaurant_logo = serverUrl;
                }
                this.updateLogoPreviewUI(serverUrl);
                this.editor.canvasManager.render();
            }
        } catch (err) {
            console.warn('Subida de logo al servidor falló, usando preview local:', err);
        }

        this.editor.markDirty();
        this.editor.save();
    }

    /**
     * Subir imagen para la marca de agua personalizada
     */
    async handleCustomWatermarkUpload(file) {
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const dataUrl = e.target.result;
            if (!this.editor.styles.watermark) this.editor.styles.watermark = {};
            this.editor.styles.watermark.imageUrl = dataUrl;
            const previewEl = document.getElementById('wmCustomPreview');
            if (previewEl) {
                previewEl.textContent = `Archivo: ${file.name}`;
                previewEl.classList.remove('hidden');
                previewEl.style.display = 'block';
            }
            this.editor.canvasManager.render();
        };
        reader.readAsDataURL(file);

        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', 'background');
            formData.append('restaurant_id', this.editor.menuData?.restaurant_id || 1);

            const response = await fetch(`${this.editor.apiBase}/media/upload`, {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();
            if (result.success && result.data?.file_path) {
                if (!this.editor.styles.watermark) this.editor.styles.watermark = {};
                this.editor.styles.watermark.imageUrl = result.data.file_path;
                this.editor.canvasManager.render();
            }
        } catch (err) {
            console.warn('Subida de marca de agua falló, usando preview local:', err);
        }

        this.editor.markDirty();
        this.editor.save();
    }

    /**
     * Actualizar interfaz del selector de logo (dropzone vs preview)
     */
    updateLogoPreviewUI(url) {
        const dropzone = document.getElementById('logoDropzone');
        const previewWrapper = document.getElementById('logoPreviewWrapper');
        const previewImg = document.getElementById('logoPreviewImg');

        if (url) {
            if (dropzone) {
                dropzone.classList.add('hidden');
                dropzone.style.display = 'none';
            }
            if (previewWrapper) {
                previewWrapper.classList.remove('hidden');
                previewWrapper.style.display = 'flex';
            }
            if (previewImg) {
                previewImg.src = url;
                previewImg.style.display = 'block';
            }
        } else {
            if (dropzone) {
                dropzone.classList.remove('hidden');
                dropzone.style.display = 'block';
            }
            if (previewWrapper) {
                previewWrapper.classList.add('hidden');
                previewWrapper.style.display = 'none';
            }
            if (previewImg) {
                previewImg.src = '';
                previewImg.style.display = 'none';
            }
        }
    }

    /**
     * Actualizar nombre de restaurante en vivo en todos los inputs y el canvas
     */
    updateRestaurantName(name) {
        if (!this.editor.styles.header) this.editor.styles.header = {};
        this.editor.styles.header.restaurantName = name;
        if (this.editor.menuData) {
            this.editor.menuData.restaurant_name = name;
        }
        const input = document.getElementById('inputRestaurantName');
        if (input && input.value !== name) input.value = name;
        const propInput = document.getElementById('propHeaderName');
        if (propInput && propInput.value !== name) propInput.value = name;

        // Actualizar barra superior del editor
        const topbarTitle = document.getElementById('editorMenuTitle');
        if (topbarTitle) topbarTitle.textContent = name || 'Menu Studio';

        // Actualizar canvas en vivo
        const canvasRestName = document.querySelector('.mc-restaurant-name');
        if (canvasRestName) {
            canvasRestName.textContent = name;
        } else {
            this.editor.canvasManager.render();
        }

        // Actualizar marca de agua de texto o fallback
        const wmText = document.querySelector('.mc-watermark__text');
        if (wmText && (!this.editor.styles.watermark?.text || this.editor.styles.watermark?.type === 'logo')) {
            wmText.textContent = name;
        }

        this.editor.markDirty();
    }

    /**
     * Actualizar subtítulo en vivo en todos los inputs y el canvas
     */
    updateMenuSubtitle(subtitle) {
        if (!this.editor.styles.header) this.editor.styles.header = {};
        this.editor.styles.header.subtitle = subtitle;
        const input = document.getElementById('inputMenuSubtitle');
        if (input && input.value !== subtitle) input.value = subtitle;
        const propInput = document.getElementById('propHeaderSubtitle');
        if (propInput && propInput.value !== subtitle) propInput.value = subtitle;

        const canvasSub = document.querySelector('.mc-menu-title');
        if (canvasSub) {
            canvasSub.textContent = subtitle;
        } else {
            this.editor.canvasManager.render();
        }

        this.editor.markDirty();
    }

    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

window.ToolbarManager = ToolbarManager;
