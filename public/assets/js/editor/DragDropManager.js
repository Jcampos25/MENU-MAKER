/**
 * ============================================================================
 * Menu Studio — DragDropManager
 * ============================================================================
 * Gestiona drag & drop para reordenar secciones y platillos
 * en la lista del sidebar, usando la API nativa de HTML5.
 */

class DragDropManager {
    /**
     * @param {MenuEditor} editor - Referencia al editor principal
     */
    constructor(editor) {
        this.editor = editor;
        this.draggedItem = null;
        this.draggedType = null; // 'section' o 'dish'
    }

    /**
     * Inicializar: bindear eventos de drag & drop
     */
    init() {
        this.bindDragEvents();
    }

    /**
     * Bindear eventos de drag & drop en secciones y platillos
     */
    bindDragEvents() {
        // ── Secciones ──
        const sectionsList = document.getElementById('sectionsList');
        if (sectionsList) {
            this.setupDragZone(sectionsList, '.section-item', 'section');
        }

        // ── Platillos (múltiples listas) ──
        document.querySelectorAll('.dishes-list').forEach(list => {
            this.setupDragZone(list, '.dish-item', 'dish');
        });
    }

    /**
     * Configurar una zona de drag & drop
     * @param {HTMLElement} container - Contenedor de los items arrastrables
     * @param {string}      selector  - Selector CSS de los items
     * @param {string}      type      - Tipo: 'section' o 'dish'
     */
    setupDragZone(container, selector, type) {
        container.querySelectorAll(selector).forEach(item => {
            // Drag Start
            item.addEventListener('dragstart', (e) => {
                this.draggedItem = item;
                this.draggedType = type;
                item.style.opacity = '0.4';
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.sectionId || item.dataset.itemId);
            });

            // Drag End
            item.addEventListener('dragend', () => {
                item.style.opacity = '1';
                this.draggedItem = null;
                this.draggedType = null;

                // Limpiar indicadores
                container.querySelectorAll(selector).forEach(el => {
                    el.style.borderTop = '';
                    el.style.borderBottom = '';
                });
            });

            // Drag Over
            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                if (this.draggedItem === item) return;

                // Indicador visual
                const rect = item.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;

                container.querySelectorAll(selector).forEach(el => {
                    el.style.borderTop = '';
                    el.style.borderBottom = '';
                });

                if (e.clientY < midY) {
                    item.style.borderTop = '2px solid #d4af37';
                } else {
                    item.style.borderBottom = '2px solid #d4af37';
                }
            });

            // Drag Leave
            item.addEventListener('dragleave', () => {
                item.style.borderTop = '';
                item.style.borderBottom = '';
            });

            // Drop
            item.addEventListener('drop', (e) => {
                e.preventDefault();
                item.style.borderTop = '';
                item.style.borderBottom = '';

                if (this.draggedItem === item || !this.draggedItem) return;

                if (type === 'section') {
                    this.reorderSections(this.draggedItem, item);
                } else if (type === 'dish') {
                    this.reorderDishes(this.draggedItem, item);
                }
            });
        });
    }

    /**
     * Reordenar secciones
     */
    reorderSections(draggedEl, targetEl) {
        const sections = this.editor.content.sections;
        const fromId = draggedEl.dataset.sectionId;
        const toId = targetEl.dataset.sectionId;

        const fromIdx = sections.findIndex(s => s.id === fromId);
        const toIdx = sections.findIndex(s => s.id === toId);

        if (fromIdx === -1 || toIdx === -1) return;

        // Mover en el array
        const [moved] = sections.splice(fromIdx, 1);
        sections.splice(toIdx, 0, moved);

        // Actualizar sortOrder
        sections.forEach((s, i) => s.sortOrder = i);

        // Re-renderizar todo
        this.editor.toolbarManager.renderSectionsList();
        this.editor.canvasManager.render();
        this.editor.markDirty();

        // Re-bindear drag events
        this.bindDragEvents();
    }

    /**
     * Reordenar platillos dentro de una sección
     */
    reorderDishes(draggedEl, targetEl) {
        const sectionId = draggedEl.dataset.sectionId;
        const itemId = draggedEl.dataset.itemId;
        const targetItemId = targetEl.dataset.itemId;

        const section = this.editor.content.sections.find(s => s.id === sectionId);
        if (!section) return;

        const fromIdx = section.items.findIndex(i => i.id === itemId);
        const toIdx = section.items.findIndex(i => i.id === targetItemId);

        if (fromIdx === -1 || toIdx === -1) return;

        // Mover en el array
        const [moved] = section.items.splice(fromIdx, 1);
        section.items.splice(toIdx, 0, moved);

        // Actualizar sortOrder
        section.items.forEach((item, i) => item.sortOrder = i);

        // Re-renderizar
        this.editor.toolbarManager.renderSectionsList();
        this.editor.canvasManager.render();
        this.editor.markDirty();

        // Re-bindear
        this.bindDragEvents();
    }
}

window.DragDropManager = DragDropManager;
