<!-- ═══════════════════════════════════════════════════════════════════════
     Menu Studio — Dashboard View
     Lista de menús + modal de creación + galería de plantillas
     ═══════════════════════════════════════════════════════════════════════ -->

<div class="dashboard">

    <!-- ═══ Header ═══ -->
    <header class="dashboard__header">
        <div>
            <h2 class="dashboard__title">Mis Menús</h2>
            <p class="dashboard__subtitle">
                <?= $menuCount ?? 0 ?> menú<?= ($menuCount ?? 0) !== 1 ? 's' : '' ?> creado<?= ($menuCount ?? 0) !== 1 ? 's' : '' ?>
            </p>
        </div>
        <button class="btn btn--primary btn--glow" id="btnNewMenu" onclick="document.getElementById('modalNewMenu').classList.add('active')">
            <span class="material-icons-round">add</span>
            Crear Nuevo Menú
        </button>
    </header>

    <!-- ═══ Menu Grid ═══ -->
    <div class="menu-grid">
        <?php if (empty($menus)): ?>
        <div class="empty-state">
            <span class="material-icons-round empty-state__icon">menu_book</span>
            <h3>No tienes menús todavía</h3>
            <p>Comienza creando tu primer menú a partir de una plantilla profesional.</p>
            <button class="btn btn--primary" onclick="document.getElementById('modalNewMenu').classList.add('active')">
                <span class="material-icons-round">add</span>
                Crear Mi Primer Menú
            </button>
        </div>
        <?php else: ?>
            <?php foreach ($menus as $menu): ?>
            <div class="menu-card" data-menu-id="<?= $menu['id'] ?>">
                <!-- Thumbnail / Preview -->
                <div class="menu-card__preview" style="background-color: <?= htmlspecialchars($menu['style_config_json']['colors']['secondary'] ?? '#1a1a2e') ?>">
                    <div class="menu-card__preview-content" style="color: <?= htmlspecialchars($menu['style_config_json']['colors']['primary'] ?? '#d4af37') ?>">
                        <span class="material-icons-round" style="font-size: 48px;">restaurant_menu</span>
                        <span class="menu-card__preview-title"><?= htmlspecialchars($menu['title']) ?></span>
                    </div>
                    <!-- Status Badge -->
                    <span class="menu-card__status menu-card__status--<?= $menu['status'] ?>">
                        <?= match($menu['status']) {
                            'draft'     => '✏️ Borrador',
                            'published' => '✅ Publicado',
                            'archived'  => '📦 Archivado',
                            default     => $menu['status']
                        } ?>
                    </span>
                </div>

                <!-- Info -->
                <div class="menu-card__info">
                    <h3 class="menu-card__title"><?= htmlspecialchars($menu['title']) ?></h3>
                    <p class="menu-card__meta">
                        <span class="material-icons-round">auto_awesome_mosaic</span>
                        <?= htmlspecialchars($menu['template_title'] ?? 'Personalizado') ?>
                    </p>
                    <p class="menu-card__meta">
                        <span class="material-icons-round">schedule</span>
                        <?= date('d M Y, H:i', strtotime($menu['updated_at'])) ?>
                    </p>
                </div>

                <!-- Actions -->
                <div class="menu-card__actions">
                    <a href="<?= APP_URL ?>/menus/<?= $menu['id'] ?>/edit" class="btn btn--small btn--primary" title="Editar">
                        <span class="material-icons-round">edit</span> Editar
                    </a>
                    <form method="POST" action="<?= APP_URL ?>/menus/<?= $menu['id'] ?>/duplicate" style="display:inline">
                        <button type="submit" class="btn btn--small btn--ghost" title="Duplicar">
                            <span class="material-icons-round">content_copy</span>
                        </button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>/menus/<?= $menu['id'] ?>/delete"
                          style="display:inline"
                          onsubmit="return confirm('¿Eliminar este menú? Esta acción no se puede deshacer.')">
                        <button type="submit" class="btn btn--small btn--danger" title="Eliminar">
                            <span class="material-icons-round">delete</span>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ Modal: Crear Nuevo Menú ═══ -->
<div class="modal-overlay" id="modalNewMenu">
    <div class="modal">
        <div class="modal__header">
            <h2 class="modal__title">
                <span class="material-icons-round">add_circle</span>
                Crear Nuevo Menú
            </h2>
            <button class="modal__close" onclick="document.getElementById('modalNewMenu').classList.remove('active')">&times;</button>
        </div>

        <form method="POST" action="<?= APP_URL ?>/menus" class="modal__form">
            <!-- Nombre del menú -->
            <div class="form-group">
                <label for="menuTitle" class="form-label">Nombre del menú</label>
                <input type="text" id="menuTitle" name="title" class="form-input"
                       placeholder="Ej: Menú Cena Primavera 2024" required maxlength="200">
            </div>

            <!-- Selector de plantilla -->
            <div class="form-group">
                <label class="form-label">Selecciona una plantilla</label>
                <div class="template-grid" id="templateGrid">
                    <?php if (!empty($templates)): ?>
                        <?php foreach ($templates as $idx => $tpl): ?>
                        <label class="template-option <?= $idx === 0 ? 'selected' : '' ?>">
                            <input type="radio" name="template_id" value="<?= $tpl['id'] ?>"
                                   <?= $idx === 0 ? 'checked' : '' ?> class="template-option__radio">
                            <div class="template-option__preview"
                                 style="background-color: <?= htmlspecialchars($tpl['style_defaults_json']['colors']['secondary'] ?? '#1a1a2e') ?>;">
                                <span class="template-option__name"
                                      style="color: <?= htmlspecialchars($tpl['style_defaults_json']['colors']['primary'] ?? '#d4af37') ?>;">
                                    <?= htmlspecialchars($tpl['title']) ?>
                                </span>
                                <span class="template-option__dim"><?= $tpl['dimensions'] ?></span>
                                <?php if ($tpl['is_premium']): ?>
                                <span class="template-option__badge">PRO</span>
                                <?php endif; ?>
                            </div>
                            <span class="template-option__label"><?= htmlspecialchars($tpl['title']) ?></span>
                            <span class="template-option__category"><?= $tpl['category'] ?></span>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal__footer">
                <button type="button" class="btn btn--ghost"
                        onclick="document.getElementById('modalNewMenu').classList.remove('active')">Cancelar</button>
                <button type="submit" class="btn btn--primary btn--glow">
                    <span class="material-icons-round">rocket_launch</span>
                    Crear y Diseñar
                </button>
            </div>
        </form>
    </div>
</div>
