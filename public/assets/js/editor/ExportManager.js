/**
 * ============================================================================
 * Menu Studio — ExportManager
 * ============================================================================
 * Gestiona la exportación del menú a PDF y PNG.
 * Usa canvas.toDataURL() para PNG y jsPDF para PDF.
 */

class ExportManager {
    /**
     * @param {MenuEditor} editor - Referencia al editor principal
     */
    constructor(editor) {
        this.editor = editor;
        this.jsPdfLoaded = false;
        this.html2canvasLoaded = false;
    }

    /**
     * Inicializar: bindear botones de exportación y preview
     */
    init() {
        document.getElementById('btnExport')?.addEventListener('click', () => this.exportPDF());
        document.getElementById('btnPreview')?.addEventListener('click', () => this.showPreview());
    }

    /**
     * Cargar librería jsPDF bajo demanda
     */
    async loadJsPdf() {
        if (this.jsPdfLoaded) return;
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            script.onload = () => { this.jsPdfLoaded = true; resolve(); };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Cargar librería html2canvas bajo demanda
     */
    async loadHtml2Canvas() {
        if (this.html2canvasLoaded) return;
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
            script.onload = () => { this.html2canvasLoaded = true; resolve(); };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Exportar el menú como PDF
     */
    async exportPDF() {
        const btn = document.getElementById('btnExport');
        const originalText = btn.innerHTML;

        try {
            // Mostrar estado de carga
            btn.innerHTML = '<span class="material-icons-round">hourglass_empty</span> Generando...';
            btn.disabled = true;

            // Cargar librerías
            await this.loadJsPdf();
            await this.loadHtml2Canvas();

            const canvas = document.getElementById('menuCanvas');

            // Deseleccionar cualquier elemento activo para no capturar bordes de selección
            this.editor.canvasManager.deselectAll();

            // Guardar zoom actual y resetear para captura
            const wrapper = document.getElementById('canvasWrapper');
            const currentTransform = wrapper.style.transform;
            wrapper.style.transform = 'scale(1)';

            // Capturar como imagen con alta resolución
            const captureCanvas = await html2canvas(canvas, {
                scale: 2, // 2x para mejor calidad
                useCORS: true,
                backgroundColor: null,
                logging: false,
            });

            // Restaurar zoom
            wrapper.style.transform = currentTransform;

            // Obtener dimensiones del menú
            const dims = this.editor.styles?.dimensions || { width: 210, height: 297 };
            const orientation = dims.width > dims.height ? 'landscape' : 'portrait';

            // Crear PDF
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: orientation,
                unit: 'mm',
                format: [dims.width, dims.height],
            });

            // Añadir imagen al PDF
            const imgData = captureCanvas.toDataURL('image/jpeg', 0.95);
            pdf.addImage(imgData, 'JPEG', 0, 0, dims.width, dims.height);

            // Descargar
            const title = this.editor.menuData?.title || 'menu';
            const fileName = title.replace(/[^a-zA-Z0-9áéíóúñÁÉÍÓÚÑ ]/g, '').replace(/\s+/g, '_');
            pdf.save(`${fileName}.pdf`);

        } catch (error) {
            console.error('Export error:', error);
            alert('Error al exportar el PDF. Por favor intente de nuevo.');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    /**
     * Exportar como PNG
     */
    async exportPNG() {
        try {
            await this.loadHtml2Canvas();

            const canvas = document.getElementById('menuCanvas');
            const wrapper = document.getElementById('canvasWrapper');
            const currentTransform = wrapper.style.transform;
            wrapper.style.transform = 'scale(1)';

            const captureCanvas = await html2canvas(canvas, {
                scale: 3, // 3x para alta resolución
                useCORS: true,
                backgroundColor: null,
                logging: false,
            });

            wrapper.style.transform = currentTransform;

            // Descargar
            const link = document.createElement('a');
            const title = this.editor.menuData?.title || 'menu';
            link.download = title.replace(/[^a-zA-Z0-9 ]/g, '').replace(/\s+/g, '_') + '.png';
            link.href = captureCanvas.toDataURL('image/png');
            link.click();
        } catch (error) {
            console.error('PNG export error:', error);
            alert('Error al exportar como PNG.');
        }
    }

    /**
     * Mostrar vista previa del menú en un modal
     */
    showPreview() {
        const modal = document.getElementById('previewModal');
        const body = document.getElementById('previewBody');

        if (!modal || !body) return;

        // Clonar el canvas
        const canvas = document.getElementById('menuCanvas');
        const clone = canvas.cloneNode(true);
        clone.style.width = canvas.style.width;
        clone.style.minHeight = canvas.style.minHeight;
        clone.style.margin = '0 auto';
        clone.style.boxShadow = '0 8px 32px rgba(0,0,0,0.5)';
        clone.style.borderRadius = '4px';

        body.innerHTML = '';
        body.appendChild(clone);

        modal.classList.add('active');
    }
}

window.ExportManager = ExportManager;
