<?php
$headerConfig = $menu['style_config_json']['header'] ?? [];
$watermarkConfig = $menu['style_config_json']['watermark'] ?? [];
$initRestName = $headerConfig['restaurantName'] ?? $menu['restaurant_name'] ?? '';
$initSubtitle = $headerConfig['subtitle'] ?? $menu['title'] ?? '';
$initLogoUrl = $headerConfig['logoUrl'] ?? $menu['restaurant_logo'] ?? '';
$initShowLogo = ($headerConfig['showLogo'] ?? false) && !empty($initLogoUrl);
$initWatermarkEnabled = $watermarkConfig['enabled'] ?? false;
$initWatermarkType = $watermarkConfig['type'] ?? 'logo';
$initWatermarkText = $watermarkConfig['text'] ?? $initRestName;
$assetVersion = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Editor') ?> — <?= APP_NAME ?></title>

    <!-- Google Fonts: cargar las del menú + UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700;800;900&family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Lato:wght@100;300;400;700;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Bebas+Neue&family=Abril+Fatface&family=Open+Sans:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@300;400;500;600;700&family=Merriweather:wght@300;400;700;900&family=Oswald:wght@200;300;400;500;600;700&family=Raleway:wght@100;200;300;400;500;600;700;800;900&family=Great+Vibes&family=Dancing+Script:wght@400;500;600;700&family=DM+Serif+Display&family=Josefin+Sans:wght@100;200;300;400;500;600;700&family=Crimson+Text:wght@400;600;700&family=Libre+Baskerville:wght@400;700&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    <!-- Editor Styles -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css?v=<?= $assetVersion ?>">
</head>
<body class="editor-body">

    <!-- ═══════════════════════════════════════════════════════════════════
         TOP BAR — Barra superior del editor
         ═══════════════════════════════════════════════════════════════════ -->
    <header class="editor-topbar">
        <!-- Left: Back + Menu Title -->
        <div class="editor-topbar__left">
            <a href="<?= APP_URL ?>/menus" class="editor-topbar__back" title="Volver al Dashboard">
                <span class="material-icons-round">arrow_back</span>
            </a>
            <div class="editor-topbar__divider"></div>
            <span class="editor-topbar__icon material-icons-round">restaurant_menu</span>
            <input type="text" class="editor-topbar__title-input" id="menuTitleInput"
                   value="<?= htmlspecialchars($menu['title']) ?>" maxlength="200">
            <span class="editor-topbar__save-status" id="saveStatus">
                <span class="material-icons-round">cloud_done</span> Guardado
            </span>
        </div>

        <!-- Center: Zoom Controls -->
        <div class="editor-topbar__center">
            <button class="editor-topbar__btn" id="btnZoomOut" title="Alejar">
                <span class="material-icons-round">remove</span>
            </button>
            <span class="editor-topbar__zoom" id="zoomLevel">100%</span>
            <button class="editor-topbar__btn" id="btnZoomIn" title="Acercar">
                <span class="material-icons-round">add</span>
            </button>
            <button class="editor-topbar__btn" id="btnZoomFit" title="Ajustar a ventana">
                <span class="material-icons-round">fit_screen</span>
            </button>
        </div>

        <!-- Right: Actions -->
        <div class="editor-topbar__right">
            <button class="editor-topbar__btn" id="btnUndo" title="Deshacer (Ctrl+Z)">
                <span class="material-icons-round">undo</span>
            </button>
            <button class="editor-topbar__btn" id="btnRedo" title="Rehacer (Ctrl+Y)">
                <span class="material-icons-round">redo</span>
            </button>
            <div class="editor-topbar__divider"></div>
            <button class="btn btn--ghost btn--small" id="btnPreview">
                <span class="material-icons-round">visibility</span> Vista Previa
            </button>
            <button class="btn btn--primary btn--small btn--glow" id="btnExport">
                <span class="material-icons-round">download</span> Exportar PDF
            </button>
        </div>
    </header>

    <!-- ═══════════════════════════════════════════════════════════════════
         MAIN EDITOR AREA — 3 column layout
         ═══════════════════════════════════════════════════════════════════ -->
    <div class="editor-layout">

        <!-- ═══ LEFT SIDEBAR — Herramientas ═══ -->
        <aside class="editor-sidebar" id="editorSidebar">
            <!-- Sidebar Tab Navigation -->
            <nav class="sidebar-tabs">
                <button class="sidebar-tab active" data-tab="sections" title="Secciones y Platillos">
                    <span class="material-icons-round">menu_book</span>
                    <span class="sidebar-tab__label">Platos</span>
                </button>
                <button class="sidebar-tab" data-tab="brand" title="Marca, Encabezado y Logo">
                    <span class="material-icons-round">storefront</span>
                    <span class="sidebar-tab__label">Marca</span>
                </button>
                <button class="sidebar-tab" data-tab="styles" title="Tipografía y Estilos">
                    <span class="material-icons-round">text_fields</span>
                    <span class="sidebar-tab__label">Texto</span>
                </button>
                <button class="sidebar-tab" data-tab="colors" title="Colores">
                    <span class="material-icons-round">palette</span>
                    <span class="sidebar-tab__label">Color</span>
                </button>
                <button class="sidebar-tab" data-tab="backgrounds" title="Fondos">
                    <span class="material-icons-round">wallpaper</span>
                    <span class="sidebar-tab__label">Fondo</span>
                </button>
                <button class="sidebar-tab" data-tab="layout" title="Diseño">
                    <span class="material-icons-round">dashboard_customize</span>
                    <span class="sidebar-tab__label">Layout</span>
                </button>
            </nav>

            <!-- ═══ Tab Content: Secciones & Platillos ═══ -->
            <div class="sidebar-panel active" id="panel-sections">
                <div class="sidebar-panel__header">
                    <h3>Secciones y Platillos</h3>
                    <button class="btn-icon" id="btnAddSection" title="Añadir sección">
                        <span class="material-icons-round">add_circle</span>
                    </button>
                </div>

                <div class="sections-search-box" style="margin-bottom: 12px; position: relative;">
                    <span class="material-icons-round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--ms-text-muted); pointer-events: none;">search</span>
                    <input type="text" id="searchDishesInput" class="form-input" placeholder="Buscar platillo o sección..." style="padding-left: 36px; padding-right: 32px; font-size: 13px; height: 36px; width: 100%;">
                    <button type="button" id="btnClearSearchDishes" class="btn-icon" style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); display: none; padding: 2px;" title="Limpiar búsqueda">
                        <span class="material-icons-round" style="font-size: 16px;">close</span>
                    </button>
                </div>
                <div class="sections-actions-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span id="dishesCounterBadge" style="font-size: 11px; font-weight: 600; color: var(--ms-accent); background: rgba(212, 175, 55, 0.1); padding: 2px 8px; border-radius: 12px; border: 1px solid rgba(212, 175, 55, 0.3);">104 Platillos</span>
                    <div style="display: flex; gap: 4px;">
                        <button type="button" id="btnExpandAllSections" class="btn btn--small btn--ghost" style="padding: 2px 8px; font-size: 11px;" title="Expandir todas las secciones">
                            Expandir
                        </button>
                        <button type="button" id="btnCollapseAllSections" class="btn btn--small btn--ghost" style="padding: 2px 8px; font-size: 11px;" title="Colapsar todas las secciones">
                            Colapsar
                        </button>
                    </div>
                </div>

                <div class="sections-list" id="sectionsList">
                    <!-- Se llena dinámicamente por JS -->
                </div>

                <!-- Opciones de visibilidad -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Visibilidad</h4>
                    <label class="toggle-option">
                        <input type="checkbox" id="togglePrices" checked>
                        <span class="toggle-option__slider"></span>
                        <span>Mostrar precios</span>
                    </label>
                    <label class="toggle-option">
                        <input type="checkbox" id="toggleDescriptions" checked>
                        <span class="toggle-option__slider"></span>
                        <span>Mostrar descripciones</span>
                    </label>
                    <label class="toggle-option">
                        <input type="checkbox" id="toggleAllergens" checked>
                        <span class="toggle-option__slider"></span>
                        <span>Mostrar alérgenos</span>
                    </label>
                    <label class="toggle-option">
                        <input type="checkbox" id="toggleImages">
                        <span class="toggle-option__slider"></span>
                        <span>Mostrar imágenes de platos</span>
                    </label>
                </div>
            </div>

            <!-- ═══ Tab Content: Marca, Encabezado y Logo ═══ -->
            <div class="sidebar-panel" id="panel-brand">
                <div class="sidebar-panel__header">
                    <h3>Marca y Encabezado</h3>
                </div>

                <!-- 1. Encabezado / Título del Lugar -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Nombre del Lugar / Restaurante</h4>
                    <div class="form-group">
                        <label class="form-label">Nombre Comercial</label>
                        <input type="text" class="form-input" id="inputRestaurantName" value="<?= htmlspecialchars($initRestName) ?>" placeholder="Ej. COMEDOR MACHU">
                    </div>
                    <label class="toggle-option" style="margin-top: 8px;">
                        <input type="checkbox" id="toggleRestaurantName" <?= ($headerConfig['showRestaurantName'] ?? true) ? 'checked' : '' ?>>
                        <span class="toggle-option__slider"></span>
                        <span>Mostrar nombre en el encabezado</span>
                    </label>

                    <div class="form-group" style="margin-top: 14px;">
                        <label class="form-label">Subtítulo / Lema del Menú</label>
                        <input type="text" class="form-input" id="inputMenuSubtitle" value="<?= htmlspecialchars($initSubtitle) ?>" placeholder="Ej. Menú Ejecutivo / Carta del Día">
                    </div>
                    <label class="toggle-option" style="margin-top: 8px;">
                        <input type="checkbox" id="toggleMenuSubtitle" <?= ($headerConfig['showSubtitle'] ?? true) ? 'checked' : '' ?>>
                        <span class="toggle-option__slider"></span>
                        <span>Mostrar subtítulo</span>
                    </label>

                    <label class="toggle-option" style="margin-top: 10px;">
                        <input type="checkbox" id="toggleHeaderDivider" <?= ($headerConfig['showDivider'] ?? true) ? 'checked' : '' ?>>
                        <span class="toggle-option__slider"></span>
                        <span>Línea divisoria del encabezado</span>
                    </label>
                </div>

                <!-- 2. Logotipo del Restaurante -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Logotipo</h4>
                    <label class="toggle-option" style="margin-bottom: 12px;">
                        <input type="checkbox" id="toggleLogo" <?= $initShowLogo ? 'checked' : '' ?>>
                        <span class="toggle-option__slider"></span>
                        <span>Mostrar logo en el menú</span>
                    </label>

                    <!-- Dropzone / Selector de Logo -->
                    <div class="logo-upload-box" id="logoUploadBox">
                        <div class="logo-dropzone <?= !empty($initLogoUrl) ? 'hidden' : '' ?>" id="logoDropzone">
                            <span class="material-icons-round logo-dropzone__icon">cloud_upload</span>
                            <div class="logo-dropzone__text">Arrastra tu logo aquí o <span>haz clic para subir</span></div>
                            <span class="logo-dropzone__hint">PNG, JPG o SVG (fondo transparente recomendado)</span>
                            <button type="button" class="btn btn--small btn--ghost" id="btnSelectLogoFile" style="margin-top:6px;">
                                <span class="material-icons-round">folder_open</span> Seleccionar Imagen
                            </button>
                        </div>
                        <input type="file" id="logoFileInput" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="display:none;">

                        <div class="logo-preview-wrapper <?= empty($initLogoUrl) ? 'hidden' : '' ?>" id="logoPreviewWrapper">
                            <img id="logoPreviewImg" src="<?= htmlspecialchars($initLogoUrl) ?>" alt="Logo" class="logo-preview-img">
                            <div class="logo-preview-actions">
                                <button type="button" class="btn btn--small btn--ghost" id="btnChangeLogo">Cambiar</button>
                                <button type="button" class="btn btn--small btn--ghost btn--danger-ghost" id="btnRemoveLogo">Quitar</button>
                            </div>
                        </div>
                    </div>

                    <!-- Ajustes de Logo -->
                    <div class="logo-settings" id="logoSettings">
                        <div class="font-control__row" style="margin-top: 12px;">
                            <label class="font-control__label">Tamaño</label>
                            <input type="range" class="form-range" id="logoWidthRange" min="30" max="220" value="<?= (int)($headerConfig['logoWidth'] ?? 90) ?>">
                            <span class="font-control__value" id="logoWidthVal"><?= (int)($headerConfig['logoWidth'] ?? 90) ?>px</span>
                        </div>

                        <div class="sidebar-panel__section" style="padding: 10px 0 0 0; border-top: none;">
                            <label class="form-label" style="margin-bottom: 6px;">Posición del Logo</label>
                            <div class="btn-group" id="logoPositionGroup">
                                <?php $pos = $headerConfig['logoPosition'] ?? 'above'; ?>
                                <button type="button" class="btn-group__btn <?= $pos === 'above' ? 'active' : '' ?>" data-pos="above">Arriba</button>
                                <button type="button" class="btn-group__btn <?= $pos === 'left' ? 'active' : '' ?>" data-pos="left">Lado Izq.</button>
                                <button type="button" class="btn-group__btn <?= $pos === 'below' ? 'active' : '' ?>" data-pos="below">Abajo</button>
                            </div>
                        </div>

                        <div class="sidebar-panel__section" style="padding: 10px 0 0 0; border-top: none;">
                            <label class="form-label" style="margin-bottom: 6px;">Forma del Logo</label>
                            <div class="btn-group" id="logoShapeGroup">
                                <?php $shape = $headerConfig['logoShape'] ?? 'original'; ?>
                                <button type="button" class="btn-group__btn <?= $shape === 'original' ? 'active' : '' ?>" data-shape="original">Original</button>
                                <button type="button" class="btn-group__btn <?= $shape === 'rounded' ? 'active' : '' ?>" data-shape="rounded">Redondeado</button>
                                <button type="button" class="btn-group__btn <?= $shape === 'circle' ? 'active' : '' ?>" data-shape="circle">Circular</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Marca de Agua (Watermark) -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Marca de Agua</h4>
                    <label class="toggle-option">
                        <input type="checkbox" id="toggleWatermark" <?= $initWatermarkEnabled ? 'checked' : '' ?>>
                        <span class="toggle-option__slider"></span>
                        <span>Activar marca de agua de fondo</span>
                    </label>

                    <div class="watermark-controls <?= !$initWatermarkEnabled ? 'hidden' : '' ?>" id="watermarkControls" style="margin-top: 14px;">
                        <label class="form-label" style="margin-bottom: 6px;">Origen de la Marca</label>
                        <div class="btn-group" id="watermarkTypeGroup">
                            <button type="button" class="btn-group__btn <?= $initWatermarkType === 'logo' ? 'active' : '' ?>" data-wm-type="logo">Logo</button>
                            <button type="button" class="btn-group__btn <?= $initWatermarkType === 'custom' ? 'active' : '' ?>" data-wm-type="custom">Imagen</button>
                            <button type="button" class="btn-group__btn <?= $initWatermarkType === 'text' ? 'active' : '' ?>" data-wm-type="text">Texto</button>
                        </div>

                        <!-- Si es texto -->
                        <div class="form-group <?= $initWatermarkType !== 'text' ? 'hidden' : '' ?>" id="wmTextGroup" style="margin-top: 10px;">
                            <label class="form-label">Texto de la marca de agua</label>
                            <input type="text" class="form-input" id="watermarkTextInput" value="<?= htmlspecialchars($initWatermarkText) ?>" placeholder="Ej. COMEDOR MACHU">
                        </div>

                        <!-- Si es imagen personalizada -->
                        <div class="form-group <?= $initWatermarkType !== 'custom' ? 'hidden' : '' ?>" id="wmCustomGroup" style="margin-top: 10px;">
                            <button type="button" class="btn btn--small btn--ghost" id="btnUploadWatermarkCustom" style="width:100%;">
                                <span class="material-icons-round">upload_file</span> Subir imagen de agua
                            </button>
                            <input type="file" id="watermarkFileInput" accept="image/*" style="display:none;">
                            <div id="wmCustomPreview" class="hidden" style="margin-top: 6px; font-size: 11px; color: var(--ms-text-muted);"></div>
                        </div>

                        <!-- Ajustes de Transparencia, Escala y Rotación -->
                        <div class="font-control__row" style="margin-top: 12px;">
                            <label class="font-control__label">Opacidad</label>
                            <input type="range" class="form-range" id="watermarkOpacity" min="0.02" max="0.40" step="0.01" value="<?= (float)($watermarkConfig['opacity'] ?? 0.08) ?>">
                            <span class="font-control__value" id="watermarkOpacityVal"><?= round((float)($watermarkConfig['opacity'] ?? 0.08) * 100) ?>%</span>
                        </div>

                        <div class="font-control__row">
                            <label class="font-control__label">Tamaño</label>
                            <input type="range" class="form-range" id="watermarkScale" min="0.2" max="2.0" step="0.05" value="<?= (float)($watermarkConfig['scale'] ?? 0.85) ?>">
                            <span class="font-control__value" id="watermarkScaleVal"><?= round((float)($watermarkConfig['scale'] ?? 0.85) * 100) ?>%</span>
                        </div>

                        <div class="font-control__row">
                            <label class="font-control__label">Rotación</label>
                            <input type="range" class="form-range" id="watermarkRotation" min="-90" max="90" step="5" value="<?= (int)($watermarkConfig['rotation'] ?? -15) ?>">
                            <span class="font-control__value" id="watermarkRotationVal"><?= (int)($watermarkConfig['rotation'] ?? -15) ?>°</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Tab Content: Tipografía ═══ -->
            <div class="sidebar-panel" id="panel-styles">
                <div class="sidebar-panel__header">
                    <h3>Tipografía</h3>
                </div>

                <!-- Heading Font -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Títulos</h4>
                    <div class="font-control">
                        <select class="form-select font-select" id="fontHeading" data-role="heading">
                            <option value="">Cargando fuentes...</option>
                        </select>
                        <div class="font-control__row">
                            <label class="font-control__label">Tamaño</label>
                            <input type="range" class="form-range" id="fontSizeHeading" min="14" max="72" value="32" data-role="heading">
                            <input type="number" class="form-input form-input--tiny" id="fontSizeHeadingNum" min="8" max="120" value="32" data-role="heading">
                            <span class="font-control__unit">px</span>
                        </div>
                        <div class="font-control__row">
                            <label class="font-control__label">Peso</label>
                            <select class="form-select form-select--small" id="fontWeightHeading" data-role="heading">
                                <option value="300">Light</option>
                                <option value="400">Regular</option>
                                <option value="500">Medium</option>
                                <option value="600">Semi Bold</option>
                                <option value="700" selected>Bold</option>
                                <option value="800">Extra Bold</option>
                                <option value="900">Black</option>
                            </select>
                        </div>
                        <div class="font-control__row">
                            <label class="font-control__label">Espaciado</label>
                            <input type="range" class="form-range" id="letterSpacingHeading" min="-2" max="15" value="2" step="0.5" data-role="heading">
                            <span class="font-control__value" id="letterSpacingHeadingVal">2px</span>
                        </div>
                        <div class="font-control__row">
                            <label class="font-control__label">Transformar</label>
                            <div class="btn-group">
                                <button class="btn-group__btn active" data-transform="uppercase" data-role="heading" title="MAYÚSCULAS">AA</button>
                                <button class="btn-group__btn" data-transform="capitalize" data-role="heading" title="Title Case">Aa</button>
                                <button class="btn-group__btn" data-transform="lowercase" data-role="heading" title="minúsculas">aa</button>
                                <button class="btn-group__btn" data-transform="none" data-role="heading" title="Normal">—</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subheading Font -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Subtítulos / Categorías</h4>
                    <div class="font-control">
                        <select class="form-select font-select" id="fontSubheading" data-role="subheading">
                            <option value="">Cargando fuentes...</option>
                        </select>
                        <div class="font-control__row">
                            <label class="font-control__label">Tamaño</label>
                            <input type="range" class="form-range" id="fontSizeSubheading" min="10" max="48" value="18" data-role="subheading">
                            <input type="number" class="form-input form-input--tiny" id="fontSizeSubheadingNum" min="8" max="80" value="18" data-role="subheading">
                            <span class="font-control__unit">px</span>
                        </div>
                        <div class="font-control__row">
                            <label class="font-control__label">Peso</label>
                            <select class="form-select form-select--small" id="fontWeightSubheading" data-role="subheading">
                                <option value="300">Light</option>
                                <option value="400">Regular</option>
                                <option value="500" selected>Medium</option>
                                <option value="600">Semi Bold</option>
                                <option value="700">Bold</option>
                            </select>
                        </div>
                        <div class="font-control__row">
                            <label class="font-control__label">Espaciado</label>
                            <input type="range" class="form-range" id="letterSpacingSubheading" min="-2" max="10" value="1" step="0.5" data-role="subheading">
                            <span class="font-control__value" id="letterSpacingSubheadingVal">1px</span>
                        </div>
                    </div>
                </div>

                <!-- Body Font -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Descripciones</h4>
                    <div class="font-control">
                        <select class="form-select font-select" id="fontBody" data-role="body">
                            <option value="">Cargando fuentes...</option>
                        </select>
                        <div class="font-control__row">
                            <label class="font-control__label">Tamaño</label>
                            <input type="range" class="form-range" id="fontSizeBody" min="8" max="24" value="13" data-role="body">
                            <input type="number" class="form-input form-input--tiny" id="fontSizeBodyNum" min="6" max="36" value="13" data-role="body">
                            <span class="font-control__unit">px</span>
                        </div>
                        <div class="font-control__row">
                            <label class="font-control__label">Interlineado</label>
                            <input type="range" class="form-range" id="lineHeightBody" min="1" max="3" value="1.6" step="0.1" data-role="body">
                            <span class="font-control__value" id="lineHeightBodyVal">1.6</span>
                        </div>
                    </div>
                </div>

                <!-- Price Font -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Precios</h4>
                    <div class="font-control">
                        <select class="form-select font-select" id="fontPrice" data-role="price">
                            <option value="">Cargando fuentes...</option>
                        </select>
                        <div class="font-control__row">
                            <label class="font-control__label">Tamaño</label>
                            <input type="range" class="form-range" id="fontSizePrice" min="8" max="30" value="15" data-role="price">
                            <input type="number" class="form-input form-input--tiny" id="fontSizePriceNum" min="6" max="48" value="15" data-role="price">
                            <span class="font-control__unit">px</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Tab Content: Colores ═══ -->
            <div class="sidebar-panel" id="panel-colors">
                <div class="sidebar-panel__header">
                    <h3>Paleta de Colores</h3>
                </div>

                <!-- Individual Colors -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Colores del Menú</h4>
                    <div class="color-picker-grid">
                        <div class="color-picker-item">
                            <label>Principal</label>
                            <div class="color-input-wrapper">
                                <input type="color" class="color-input" id="colorPrimary" value="#d4af37" data-color="primary">
                                <input type="text" class="form-input form-input--tiny color-hex" id="colorPrimaryHex" value="#d4af37" data-color="primary">
                            </div>
                        </div>
                        <div class="color-picker-item">
                            <label>Fondo</label>
                            <div class="color-input-wrapper">
                                <input type="color" class="color-input" id="colorSecondary" value="#1a1a2e" data-color="secondary">
                                <input type="text" class="form-input form-input--tiny color-hex" id="colorSecondaryHex" value="#1a1a2e" data-color="secondary">
                            </div>
                        </div>
                        <div class="color-picker-item">
                            <label>Acento</label>
                            <div class="color-input-wrapper">
                                <input type="color" class="color-input" id="colorAccent" value="#c0392b" data-color="accent">
                                <input type="text" class="form-input form-input--tiny color-hex" id="colorAccentHex" value="#c0392b" data-color="accent">
                            </div>
                        </div>
                        <div class="color-picker-item">
                            <label>Texto</label>
                            <div class="color-input-wrapper">
                                <input type="color" class="color-input" id="colorText" value="#e0e0e0" data-color="text">
                                <input type="text" class="form-input form-input--tiny color-hex" id="colorTextHex" value="#e0e0e0" data-color="text">
                            </div>
                        </div>
                        <div class="color-picker-item">
                            <label>Secundario</label>
                            <div class="color-input-wrapper">
                                <input type="color" class="color-input" id="colorMuted" value="#888888" data-color="muted">
                                <input type="text" class="form-input form-input--tiny color-hex" id="colorMutedHex" value="#888888" data-color="muted">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preset Palettes -->
                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Paletas Predefinidas</h4>
                    <div class="palette-presets" id="palettePresets">
                        <button class="palette-preset" data-palette="elegant" title="Elegant Noir">
                            <span style="background:#d4af37"></span>
                            <span style="background:#1a1a2e"></span>
                            <span style="background:#c0392b"></span>
                            <span style="background:#e0e0e0"></span>
                            <span style="background:#888888"></span>
                        </button>
                        <button class="palette-preset" data-palette="rustic" title="Rustic Kraft">
                            <span style="background:#3e2723"></span>
                            <span style="background:#f5e6d3"></span>
                            <span style="background:#ff6f00"></span>
                            <span style="background:#4e342e"></span>
                            <span style="background:#8d6e63"></span>
                        </button>
                        <button class="palette-preset" data-palette="modern" title="Modern Minimal">
                            <span style="background:#111111"></span>
                            <span style="background:#ffffff"></span>
                            <span style="background:#e63946"></span>
                            <span style="background:#333333"></span>
                            <span style="background:#999999"></span>
                        </button>
                        <button class="palette-preset" data-palette="ocean" title="Ocean Breeze">
                            <span style="background:#0077b6"></span>
                            <span style="background:#caf0f8"></span>
                            <span style="background:#ff6b6b"></span>
                            <span style="background:#023e8a"></span>
                            <span style="background:#90e0ef"></span>
                        </button>
                        <button class="palette-preset" data-palette="forest" title="Forest Garden">
                            <span style="background:#2d6a4f"></span>
                            <span style="background:#f1faee"></span>
                            <span style="background:#e76f51"></span>
                            <span style="background:#1b4332"></span>
                            <span style="background:#95d5b2"></span>
                        </button>
                        <button class="palette-preset" data-palette="neon" title="Neon Night">
                            <span style="background:#ff6ec7"></span>
                            <span style="background:#0f0c29"></span>
                            <span style="background:#fbbf24"></span>
                            <span style="background:#d1d5db"></span>
                            <span style="background:#6b7280"></span>
                        </button>
                        <button class="palette-preset" data-palette="burgundy" title="Burgundy Wine">
                            <span style="background:#c9b037"></span>
                            <span style="background:#2c0a1a"></span>
                            <span style="background:#8b0000"></span>
                            <span style="background:#f0d9b5"></span>
                            <span style="background:#6a3636"></span>
                        </button>
                        <button class="palette-preset" data-palette="sakura" title="Sakura">
                            <span style="background:#c9184a"></span>
                            <span style="background:#fff0f3"></span>
                            <span style="background:#ff758f"></span>
                            <span style="background:#590d22"></span>
                            <span style="background:#ffccd5"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ═══ Tab Content: Fondos ═══ -->
            <div class="sidebar-panel" id="panel-backgrounds">
                <div class="sidebar-panel__header">
                    <h3>Fondo del Menú</h3>
                </div>

                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Tipo de Fondo</h4>
                    <div class="bg-type-selector">
                        <button class="bg-type-btn active" data-bg-type="solid">
                            <span class="material-icons-round">square</span> Sólido
                        </button>
                        <button class="bg-type-btn" data-bg-type="gradient">
                            <span class="material-icons-round">gradient</span> Degradado
                        </button>
                        <button class="bg-type-btn" data-bg-type="texture">
                            <span class="material-icons-round">texture</span> Textura
                        </button>
                    </div>
                </div>

                <!-- Solid Color -->
                <div class="bg-controls" id="bgSolidControls">
                    <div class="color-input-wrapper">
                        <input type="color" class="color-input color-input--large" id="bgSolidColor" value="#1a1a2e">
                        <input type="text" class="form-input form-input--tiny color-hex" id="bgSolidHex" value="#1a1a2e">
                    </div>
                </div>

                <!-- Gradient -->
                <div class="bg-controls hidden" id="bgGradientControls">
                    <div class="font-control__row">
                        <label class="font-control__label">Color 1</label>
                        <input type="color" class="color-input" id="bgGradientColor1" value="#1a1a2e">
                    </div>
                    <div class="font-control__row">
                        <label class="font-control__label">Color 2</label>
                        <input type="color" class="color-input" id="bgGradientColor2" value="#16213e">
                    </div>
                    <div class="font-control__row">
                        <label class="font-control__label">Ángulo</label>
                        <input type="range" class="form-range" id="bgGradientAngle" min="0" max="360" value="180">
                        <span class="font-control__value" id="bgGradientAngleVal">180°</span>
                    </div>
                </div>

                <!-- Textures -->
                <div class="bg-controls hidden" id="bgTextureControls">
                    <div class="texture-grid">
                        <button class="texture-option active" data-texture="none" style="background:#1a1a2e" title="Sin textura"></button>
                        <button class="texture-option" data-texture="kraft" style="background:#c4a86b; background-image: repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(0,0,0,.03) 2px, rgba(0,0,0,.03) 4px)" title="Papel Kraft"></button>
                        <button class="texture-option" data-texture="chalkboard" style="background:#2d3436; background-image: radial-gradient(ellipse at 50% 50%, rgba(255,255,255,.03) 1px, transparent 1px); background-size: 4px 4px" title="Pizarra"></button>
                        <button class="texture-option" data-texture="marble" style="background: linear-gradient(135deg, #f5f5f5 25%, #e0e0e0 50%, #f5f5f5 75%)" title="Mármol"></button>
                        <button class="texture-option" data-texture="linen" style="background:#faf0e6; background-image: repeating-linear-gradient(0deg, transparent, transparent 1px, rgba(0,0,0,.02) 1px, rgba(0,0,0,.02) 2px)" title="Lino"></button>
                        <button class="texture-option" data-texture="wood" style="background: linear-gradient(180deg, #8B6914 0%, #A67B2E 20%, #7B5B11 40%, #9B6E22 60%, #8B6914 80%, #A67B2E 100%)" title="Madera"></button>
                    </div>
                </div>
            </div>

            <!-- ═══ Tab Content: Layout ═══ -->
            <div class="sidebar-panel" id="panel-layout">
                <div class="sidebar-panel__header">
                    <h3>Diseño y Layout</h3>
                </div>

                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Columnas</h4>
                    <div class="btn-group">
                        <button class="btn-group__btn" data-columns="1">1</button>
                        <button class="btn-group__btn active" data-columns="2">2</button>
                        <button class="btn-group__btn" data-columns="3">3</button>
                    </div>
                </div>

                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Márgenes (mm)</h4>
                    <div class="margin-controls">
                        <div class="font-control__row">
                            <label class="font-control__label">Superior</label>
                            <input type="number" class="form-input form-input--tiny" id="marginTop" value="30" min="0" max="80">
                        </div>
                        <div class="font-control__row">
                            <label class="font-control__label">Inferior</label>
                            <input type="number" class="form-input form-input--tiny" id="marginBottom" value="30" min="0" max="80">
                        </div>
                        <div class="font-control__row">
                            <label class="font-control__label">Izquierdo</label>
                            <input type="number" class="form-input form-input--tiny" id="marginLeft" value="25" min="0" max="60">
                        </div>
                        <div class="font-control__row">
                            <label class="font-control__label">Derecho</label>
                            <input type="number" class="form-input form-input--tiny" id="marginRight" value="25" min="0" max="60">
                        </div>
                    </div>
                </div>

                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Separadores</h4>
                    <label class="toggle-option">
                        <input type="checkbox" id="toggleDividers" checked>
                        <span class="toggle-option__slider"></span>
                        <span>Mostrar separadores</span>
                    </label>
                    <div class="btn-group" id="dividerStyleGroup" style="margin-top:8px">
                        <button class="btn-group__btn active" data-divider="dots">· · ·</button>
                        <button class="btn-group__btn" data-divider="line">───</button>
                        <button class="btn-group__btn" data-divider="dashed">- - -</button>
                        <button class="btn-group__btn" data-divider="none">Ninguno</button>
                    </div>
                </div>

                <div class="sidebar-panel__section">
                    <h4 class="sidebar-panel__subtitle">Bloque de Platillos</h4>
                    <label class="toggle-option">
                        <input type="checkbox" id="togglePriceLeaders" checked>
                        <span class="toggle-option__slider"></span>
                        <span>Puntos guía hacia precio</span>
                    </label>
                    <div class="font-control__row" style="margin-top:8px">
                        <label class="font-control__label">Posición del precio</label>
                        <select class="form-select form-select--small" id="pricePosition">
                            <option value="right" selected>Derecha</option>
                            <option value="below">Debajo del nombre</option>
                            <option value="inline">En línea</option>
                        </select>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ═══ CANVAS AREA — Lienzo central ═══ -->
        <main class="editor-canvas-area" id="editorCanvasArea">
            <div class="editor-canvas-wrapper" id="canvasWrapper">
                <div class="menu-canvas" id="menuCanvas">
                    <!-- El menú se renderiza aquí dinámicamente por JS -->
                </div>
            </div>
        </main>

        <!-- ═══ RIGHT PANEL — Propiedades del elemento seleccionado ═══ -->
        <aside class="editor-properties hidden" id="editorProperties">
            <div class="sidebar-panel__header">
                <h3 id="propertiesTitle">Propiedades</h3>
                <button class="btn-icon" onclick="document.getElementById('editorProperties').classList.add('hidden')">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div id="propertiesContent">
                <!-- Se llena dinámicamente al seleccionar un elemento -->
            </div>
        </aside>
    </div>

    <!-- ═══ Preview Modal ═══ -->
    <div class="modal-overlay" id="previewModal">
        <div class="modal modal--preview">
            <div class="modal__header">
                <h2 class="modal__title">
                    <span class="material-icons-round">visibility</span>
                    Vista Previa
                </h2>
                <button class="modal__close" onclick="document.getElementById('previewModal').classList.remove('active')">&times;</button>
            </div>
            <div class="modal__body" id="previewBody">
                <!-- Preview content rendered here -->
            </div>
        </div>
    </div>

    <!-- ═══ Data Injection ═══ -->
    <script>
        // Inyectar datos del menú desde PHP al JS
        window.MENU_STUDIO = {
            apiBase:    '<?= APP_URL ?>/api',
            assetsUrl:  '<?= ASSETS_URL ?>',
            uploadsUrl: '<?= UPLOADS_URL ?>',
            menuId:     <?= (int) $menu['id'] ?>,
            menuData:   <?= json_encode($menu, JSON_UNESCAPED_UNICODE) ?>,
            customFonts:<?= json_encode($customFonts ?? [], JSON_UNESCAPED_UNICODE) ?>,
        };
    </script>

    <!-- ═══ Editor JS Modules ═══ -->
    <script src="<?= ASSETS_URL ?>/js/editor/FontManager.js?v=<?= $assetVersion ?>"></script>
    <script src="<?= ASSETS_URL ?>/js/editor/ColorManager.js?v=<?= $assetVersion ?>"></script>
    <script src="<?= ASSETS_URL ?>/js/editor/CanvasManager.js?v=<?= $assetVersion ?>"></script>
    <script src="<?= ASSETS_URL ?>/js/editor/ToolbarManager.js?v=<?= $assetVersion ?>"></script>
    <script src="<?= ASSETS_URL ?>/js/editor/DragDropManager.js?v=<?= $assetVersion ?>"></script>
    <script src="<?= ASSETS_URL ?>/js/editor/ExportManager.js?v=<?= $assetVersion ?>"></script>
    <script src="<?= ASSETS_URL ?>/js/editor/MenuEditor.js?v=<?= $assetVersion ?>"></script>
</body>
</html>
