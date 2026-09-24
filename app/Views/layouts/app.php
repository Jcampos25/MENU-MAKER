<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Menu Studio — Plataforma profesional de diseño de menús para restaurantes">
    <title><?= htmlspecialchars($pageTitle ?? 'Menu Studio') ?> — <?= APP_NAME ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    <!-- App Styles -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
</head>
<body class="app-body">

    <!-- ═══ Top Navigation ═══ -->
    <nav class="top-nav">
        <div class="top-nav__brand">
            <span class="top-nav__icon material-icons-round">restaurant_menu</span>
            <h1 class="top-nav__title"><?= APP_NAME ?></h1>
            <span class="top-nav__version">v<?= APP_VERSION ?></span>
        </div>
        <div class="top-nav__center">
            <a href="<?= APP_URL ?>/" class="top-nav__link <?= ($pageTitle ?? '') === 'Dashboard' ? 'active' : '' ?>">
                <span class="material-icons-round">dashboard</span> Dashboard
            </a>
            <a href="<?= APP_URL ?>/templates" class="top-nav__link <?= ($pageTitle ?? '') === 'Plantillas' ? 'active' : '' ?>">
                <span class="material-icons-round">auto_awesome_mosaic</span> Plantillas
            </a>
        </div>
        <div class="top-nav__right">
            <?php if (isset($restaurant)): ?>
            <div class="top-nav__restaurant">
                <span class="material-icons-round">storefront</span>
                <span><?= htmlspecialchars($restaurant['name'] ?? '') ?></span>
            </div>
            <?php endif; ?>
            <div class="top-nav__avatar">
                <span class="material-icons-round">account_circle</span>
            </div>
        </div>
    </nav>

    <!-- ═══ Flash Messages ═══ -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="flash flash--success" id="flashSuccess">
        <span class="material-icons-round">check_circle</span>
        <span><?= htmlspecialchars($_SESSION['success']) ?></span>
        <button class="flash__close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
    <div class="flash flash--error" id="flashError">
        <span class="material-icons-round">error</span>
        <div>
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
        <button class="flash__close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php unset($_SESSION['errors']); endif; ?>

    <!-- ═══ Main Content ═══ -->
    <main class="main-content">
        <?= $content ?>
    </main>

    <script src="<?= ASSETS_URL ?>/js/app.js"></script>
</body>
</html>
