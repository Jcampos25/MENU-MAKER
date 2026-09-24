/**
 * ============================================================================
 * Menu Studio — App.js (Dashboard Scripts)
 * ============================================================================
 * Scripts para la interfaz del dashboard: modales, template selection, etc.
 */

document.addEventListener('DOMContentLoaded', () => {
    // ── Template Selection Radio ──
    const templateGrid = document.getElementById('templateGrid');
    if (templateGrid) {
        templateGrid.querySelectorAll('.template-option').forEach(option => {
            option.addEventListener('click', () => {
                templateGrid.querySelectorAll('.template-option').forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
                const radio = option.querySelector('.template-option__radio');
                if (radio) radio.checked = true;
            });
        });
    }

    // ── Close modal on overlay click ──
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    // ── Close modal on Escape ──
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
        }
    });

    // ── Auto-dismiss flash messages ──
    document.querySelectorAll('.flash').forEach(flash => {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-20px)';
            setTimeout(() => flash.remove(), 300);
        }, 5000);
    });
});
