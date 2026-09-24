/**
 * ============================================================================
 * Menu Studio — MenuEditor (Clase Principal / Orquestador)
 * ============================================================================
 * Clase central que inicializa y coordina todos los módulos del editor.
 * Gestiona el estado del menú (styles + content), auto-guardado,
 * undo/redo, y la comunicación con la API del backend.
 */

class MenuEditor {
    constructor() {
        // Datos inyectados desde PHP
        const config = window.MENU_STUDIO || {};
        this.apiBase = config.apiBase || '/MENU%20MAKER/public/api';
        this.menuId = config.menuId;
        this.menuData = config.menuData || {};

        // Estado del menú (fuente de verdad)
        this.styles = this.menuData.style_config_json || {};
        this.content = this.menuData.content_json || { sections: [] };

        // Estado de edición
        this.isDirty = false;
        this.autoSaveTimer = null;
        this.autoSaveDelay = 3000; // 3 segundos de inactividad

        // Undo/Redo
        this.undoStack = [];
        this.redoStack = [];
        this.maxUndoSteps = 50;

        // Guardar snapshot inicial
        this.saveSnapshot();

        // Inicializar módulos
        this.fontManager = new FontManager(this);
        this.colorManager = new ColorManager(this);
        this.canvasManager = new CanvasManager(this);
        this.toolbarManager = new ToolbarManager(this);
        this.dragDropManager = new DragDropManager(this);
        this.exportManager = new ExportManager(this);
    }

    /**
     * Inicializar el editor completo
     */
    init() {
        console.log('🍽️ Menu Studio Editor — Inicializando...');

        // Inicializar cada módulo
        this.canvasManager.init();
        this.fontManager.init();
        this.colorManager.init();
        this.toolbarManager.init();
        this.dragDropManager.init();
        this.exportManager.init();

        // Sincronizar controles UI con el estado actual
        this.fontManager.syncControlsFromStyles();
        this.colorManager.syncPickersFromStyles();
        this.toolbarManager.syncControlsFromStyles();

        // Bindear eventos globales
        this.bindGlobalEvents();

        console.log('✅ Menu Studio Editor — Listo');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CRUD de Contenido
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Añadir nueva sección al menú
     */
    addSection() {
        this.saveSnapshot();

        const newSection = {
            id: 'sec_' + this.generateId(),
            title: 'Nueva Sección',
            icon: 'appetizer',
            sortOrder: this.content.sections.length,
            items: [],
        };

        this.content.sections.push(newSection);
        this.refreshAll();
        this.markDirty();
    }

    /**
     * Añadir nuevo platillo a una sección
     * @param {string} sectionId - ID de la sección
     */
    addItem(sectionId) {
        this.saveSnapshot();

        const section = this.findSectionById(sectionId);
        if (!section) return;

        const newItem = {
            id: 'item_' + this.generateId(),
            name: 'Nuevo Platillo',
            description: '',
            price: 0,
            currency: this.content?.sections?.find(s => s.items?.length > 0)?.items?.[0]?.currency || 'C$',
            image: null,
            allergens: [],
            badges: [],
            visible: true,
            sortOrder: section.items.length,
        };

        section.items.push(newItem);
        this.refreshAll();
        this.markDirty();
    }

    /**
     * Actualizar un campo de un platillo
     * @param {string} itemId - ID del platillo
     * @param {string} field  - Campo a actualizar
     * @param {*}      value  - Nuevo valor
     */
    updateItem(itemId, field, value) {
        this.saveSnapshot();

        const item = this.findItemById(itemId);
        if (!item) return;

        item[field] = value;
        this.canvasManager.render();
        this.markDirty();
    }

    /**
     * Eliminar un platillo
     * @param {string} sectionId - ID de la sección
     * @param {string} itemId    - ID del platillo
     */
    removeItem(sectionId, itemId) {
        this.saveSnapshot();

        const section = this.findSectionById(sectionId);
        if (!section) return;

        section.items = section.items.filter(item => item.id !== itemId);
        this.refreshAll();
        this.markDirty();
    }

    /**
     * Actualizar un campo de una sección
     * @param {string} sectionId - ID de la sección
     * @param {string} field     - Campo a actualizar
     * @param {*}      value     - Nuevo valor
     */
    updateSection(sectionId, field, value) {
        this.saveSnapshot();

        const section = this.findSectionById(sectionId);
        if (!section) return;

        section[field] = value;
        this.canvasManager.render();
        this.markDirty();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Búsqueda de Elementos
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Buscar sección por ID
     */
    findSectionById(id) {
        return (this.content?.sections || []).find(s => s.id === id) || null;
    }

    /**
     * Buscar platillo por ID (en todas las secciones)
     */
    findItemById(id) {
        for (const section of (this.content?.sections || [])) {
            const item = (section.items || []).find(i => i.id === id);
            if (item) return item;
        }
        return null;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Auto-Guardado & API
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Marcar como modificado e iniciar timer de auto-guardado
     */
    markDirty() {
        this.isDirty = true;
        this.showSaveStatus('saving');

        // Reiniciar timer de auto-guardado
        clearTimeout(this.autoSaveTimer);
        this.autoSaveTimer = setTimeout(() => this.save(), this.autoSaveDelay);
    }

    /**
     * Guardar menú en el backend via API
     */
    async save() {
        if (!this.isDirty) return;

        try {
            this.showSaveStatus('saving');

            const bodyPayload = {
                title: document.getElementById('menuTitleInput')?.value || this.menuData.title,
                style_config_json: this.styles,
                content_json: this.content,
            };

            if (this.styles?.header?.restaurantName !== undefined) {
                bodyPayload.restaurant_name = this.styles.header.restaurantName;
            }
            if (this.styles?.header?.logoUrl !== undefined) {
                bodyPayload.restaurant_logo = this.styles.header.logoUrl;
            }

            const response = await fetch(`${this.apiBase}/menus/${this.menuId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(bodyPayload),
            });

            const result = await response.json();

            if (result.success) {
                this.isDirty = false;
                this.showSaveStatus('saved');
            } else {
                this.showSaveStatus('error');
                console.error('Save failed:', result.message);
            }
        } catch (error) {
            this.showSaveStatus('error');
            console.error('Save error:', error);
        }
    }

    /**
     * Mostrar estado de guardado en el topbar
     */
    showSaveStatus(status) {
        const el = document.getElementById('saveStatus');
        if (!el) return;

        switch (status) {
            case 'saving':
                el.className = 'editor-topbar__save-status saving';
                el.innerHTML = '<span class="material-icons-round">sync</span> Guardando...';
                break;
            case 'saved':
                el.className = 'editor-topbar__save-status';
                el.innerHTML = '<span class="material-icons-round">cloud_done</span> Guardado';
                break;
            case 'error':
                el.className = 'editor-topbar__save-status';
                el.style.color = '#ef4444';
                el.innerHTML = '<span class="material-icons-round">error</span> Error al guardar';
                setTimeout(() => { el.style.color = ''; }, 3000);
                break;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Undo / Redo
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Guardar un snapshot del estado actual para undo
     */
    saveSnapshot() {
        const snapshot = {
            styles: JSON.parse(JSON.stringify(this.styles)),
            content: JSON.parse(JSON.stringify(this.content)),
        };

        this.undoStack.push(snapshot);
        if (this.undoStack.length > this.maxUndoSteps) {
            this.undoStack.shift();
        }

        // Limpiar redo al hacer un nuevo cambio
        this.redoStack = [];
    }

    /**
     * Deshacer último cambio
     */
    undo() {
        if (this.undoStack.length <= 1) return; // Mantener al menos el snapshot inicial

        // Guardar estado actual en redo
        this.redoStack.push({
            styles: JSON.parse(JSON.stringify(this.styles)),
            content: JSON.parse(JSON.stringify(this.content)),
        });

        // Restaurar snapshot previo
        const snapshot = this.undoStack.pop();
        this.styles = snapshot.styles;
        this.content = snapshot.content;

        this.refreshAll();
        this.markDirty();
    }

    /**
     * Rehacer cambio deshecho
     */
    redo() {
        if (this.redoStack.length === 0) return;

        // Guardar estado actual en undo
        this.undoStack.push({
            styles: JSON.parse(JSON.stringify(this.styles)),
            content: JSON.parse(JSON.stringify(this.content)),
        });

        const snapshot = this.redoStack.pop();
        this.styles = snapshot.styles;
        this.content = snapshot.content;

        this.refreshAll();
        this.markDirty();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Utilidades
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Refrescar todo: sidebar + canvas
     */
    refreshAll() {
        this.toolbarManager.renderSectionsList();
        this.canvasManager.render();
        this.dragDropManager.bindDragEvents();
        this.fontManager.syncControlsFromStyles();
        this.colorManager.syncPickersFromStyles();
        this.toolbarManager.syncControlsFromStyles();
    }

    /**
     * Bindear eventos globales (teclado, título)
     */
    bindGlobalEvents() {
        // Undo/Redo con keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'z') {
                e.preventDefault();
                this.undo();
            }
            if (e.ctrlKey && e.key === 'y') {
                e.preventDefault();
                this.redo();
            }
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                this.save();
            }
        });

        // Botones de undo/redo en el topbar
        document.getElementById('btnUndo')?.addEventListener('click', () => this.undo());
        document.getElementById('btnRedo')?.addEventListener('click', () => this.redo());

        // Cambio de título del menú
        document.getElementById('menuTitleInput')?.addEventListener('change', () => {
            this.markDirty();
        });

        // Prevenir salir sin guardar
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                e.preventDefault();
                e.returnValue = '¿Tienes cambios sin guardar. ¿Seguro que quieres salir?';
            }
        });
    }

    /**
     * Generar ID único
     */
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// Inicializar cuando el DOM esté listo
// ═══════════════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    if (window.MENU_STUDIO && window.MENU_STUDIO.menuId) {
        window.menuEditor = new MenuEditor();
        window.menuEditor.init();
    }
});
