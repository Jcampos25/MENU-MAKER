-- ============================================================================
-- MENU STUDIO — Schema SQL Completo
-- Motor: InnoDB | Charset: utf8mb4_unicode_ci | MySQL 8.0+
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS `menu_studio`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `menu_studio`;

-- ============================================================================
-- 1. USUARIOS
-- ============================================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120)    NOT NULL,
  `email`         VARCHAR(255)    NOT NULL,
  `password_hash` VARCHAR(255)    NOT NULL,
  `role`          ENUM('admin','owner','designer') NOT NULL DEFAULT 'owner',
  `avatar_url`    VARCHAR(500)    DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. RESTAURANTES
-- ============================================================================
DROP TABLE IF EXISTS `restaurants`;
CREATE TABLE `restaurants` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED    NOT NULL,
  `name`          VARCHAR(200)    NOT NULL,
  `slug`          VARCHAR(200)    DEFAULT NULL,
  `logo_url`      VARCHAR(500)    DEFAULT NULL,
  `brand_colors`  JSON            DEFAULT NULL COMMENT 'Paleta de colores de marca {"primary":"#xxx","secondary":"#xxx","accent":"#xxx"}',
  `address`       VARCHAR(500)    DEFAULT NULL,
  `phone`         VARCHAR(30)     DEFAULT NULL,
  `website`       VARCHAR(255)    DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_restaurants_user` (`user_id`),
  UNIQUE KEY `uk_restaurants_slug` (`slug`),
  CONSTRAINT `fk_restaurants_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. PLANTILLAS
-- ============================================================================
DROP TABLE IF EXISTS `templates`;
CREATE TABLE `templates` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(200)    NOT NULL,
  `description`   TEXT            DEFAULT NULL,
  `category`      ENUM('dinner','lunch','drinks','cocktails','desserts','brunch','kids','seasonal') NOT NULL DEFAULT 'dinner',
  `dimensions`    ENUM('A4','letter','trifold','bifold','mobile_qr','square') NOT NULL DEFAULT 'A4',
  `width_mm`      DECIMAL(8,2)   NOT NULL DEFAULT 210.00,
  `height_mm`     DECIMAL(8,2)   NOT NULL DEFAULT 297.00,
  `layout_json`   JSON           NOT NULL COMMENT 'Estructura base de la plantilla (posiciones, secciones, estilos por defecto)',
  `style_defaults_json` JSON     DEFAULT NULL COMMENT 'Estilos tipográficos y de color por defecto',
  `thumbnail_url` VARCHAR(500)   DEFAULT NULL,
  `is_premium`    TINYINT(1)     NOT NULL DEFAULT 0,
  `sort_order`    INT            NOT NULL DEFAULT 0,
  `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_templates_category` (`category`),
  INDEX `idx_templates_dimensions` (`dimensions`),
  INDEX `idx_templates_premium` (`is_premium`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. MENÚS (el corazón del sistema)
-- ============================================================================
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `restaurant_id`   INT UNSIGNED  NOT NULL,
  `template_id`     INT UNSIGNED  DEFAULT NULL,
  `title`           VARCHAR(200)  NOT NULL,
  `style_config_json` JSON        NOT NULL COMMENT 'Configuración visual: tipografías, colores, fondos, layout, opciones de bloques',
  `content_json`    JSON          NOT NULL COMMENT 'Contenido estructurado: secciones, platillos, precios, alérgenos, badges',
  `status`          ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  `version`         INT UNSIGNED  NOT NULL DEFAULT 1,
  `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_menus_restaurant` (`restaurant_id`),
  INDEX `idx_menus_template` (`template_id`),
  INDEX `idx_menus_status` (`status`),
  CONSTRAINT `fk_menus_restaurant`
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_menus_template`
    FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. ACTIVOS MULTIMEDIA
-- ============================================================================
DROP TABLE IF EXISTS `media_assets`;
CREATE TABLE `media_assets` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `restaurant_id`   INT UNSIGNED    NOT NULL,
  `type`            ENUM('logo','background','icon','dish_photo','texture','decorative') NOT NULL,
  `file_name`       VARCHAR(255)    NOT NULL,
  `file_path`       VARCHAR(500)    NOT NULL,
  `mime_type`       VARCHAR(50)     DEFAULT NULL,
  `file_size`       INT UNSIGNED    DEFAULT NULL COMMENT 'Tamaño en bytes',
  `width`           INT UNSIGNED    DEFAULT NULL,
  `height`          INT UNSIGNED    DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_media_restaurant` (`restaurant_id`),
  INDEX `idx_media_type` (`type`),
  CONSTRAINT `fk_media_restaurant`
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. FUENTES PERSONALIZADAS
-- ============================================================================
DROP TABLE IF EXISTS `custom_fonts`;
CREATE TABLE `custom_fonts` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `restaurant_id`   INT UNSIGNED    DEFAULT NULL COMMENT 'NULL = fuente global del sistema',
  `font_name`       VARCHAR(150)    NOT NULL,
  `font_family`     VARCHAR(150)    NOT NULL,
  `font_file_path`  VARCHAR(500)    NOT NULL,
  `font_format`     ENUM('woff2','woff','ttf','otf') NOT NULL DEFAULT 'woff2',
  `font_weight`     VARCHAR(20)     NOT NULL DEFAULT '400',
  `font_style`      ENUM('normal','italic') NOT NULL DEFAULT 'normal',
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_fonts_restaurant` (`restaurant_id`),
  INDEX `idx_fonts_family` (`font_family`),
  CONSTRAINT `fk_fonts_restaurant`
    FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DATOS SEED — Demo
-- ============================================================================

-- Usuario demo (password: MenuStudio2024!)
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) VALUES
('Chef Demo', 'demo@menustudio.com', '$2y$12$LJ3m5FwGtM9RkDq1sVmOaeYGHXqOe4rP7N8KQ1p2GxZv4h6T5XWXS', 'owner');

-- Restaurante demo
INSERT INTO `restaurants` (`user_id`, `name`, `slug`, `brand_colors`) VALUES
(1, 'La Trattoria di Roma', 'la-trattoria-di-roma', '{"primary":"#d4af37","secondary":"#1a1a2e","accent":"#c0392b","text":"#e0e0e0"}');

-- Plantillas de ejemplo
INSERT INTO `templates` (`title`, `description`, `category`, `dimensions`, `width_mm`, `height_mm`, `layout_json`, `style_defaults_json`, `thumbnail_url`, `is_premium`, `sort_order`) VALUES
(
  'Elegance Noir',
  'Menú elegante con fondo oscuro y acentos dorados. Ideal para restaurantes de alta cocina.',
  'dinner',
  'A4',
  210.00,
  297.00,
  '{"sections":[{"id":"header","type":"header","y":0,"height":80},{"id":"sec_1","type":"category","y":80,"height":200,"columns":2},{"id":"sec_2","type":"category","y":280,"height":200,"columns":2},{"id":"footer","type":"footer","y":480,"height":40}]}',
  '{"background":{"type":"solid","value":"#1a1a2e"},"typography":{"heading":{"fontFamily":"Playfair Display","fontSize":32,"fontWeight":"700","color":"#d4af37","letterSpacing":2,"textTransform":"uppercase"},"subheading":{"fontFamily":"Montserrat","fontSize":18,"fontWeight":"500","color":"#c0c0c0","letterSpacing":1},"body":{"fontFamily":"Lato","fontSize":13,"fontWeight":"400","color":"#e0e0e0","lineHeight":1.6},"price":{"fontFamily":"Montserrat","fontSize":15,"fontWeight":"600","color":"#d4af37"}},"colors":{"primary":"#d4af37","secondary":"#1a1a2e","accent":"#c0392b","text":"#e0e0e0","muted":"#888888"},"layout":{"columns":2,"gutter":20,"margin":{"top":30,"right":25,"bottom":30,"left":25},"showDividers":true,"dividerStyle":"dots"},"dishBlock":{"showPriceLeaders":true,"leaderChar":"·","showDescription":true,"showAllergens":true,"showImages":false,"pricePosition":"right"}}',
  NULL,
  0,
  1
),
(
  'Rustic Kraft',
  'Estilo rústico con textura de papel kraft. Perfecto para bistros y cafeterías artesanales.',
  'lunch',
  'A4',
  210.00,
  297.00,
  '{"sections":[{"id":"header","type":"header","y":0,"height":90},{"id":"sec_1","type":"category","y":90,"height":180,"columns":1},{"id":"sec_2","type":"category","y":270,"height":180,"columns":1},{"id":"footer","type":"footer","y":450,"height":30}]}',
  '{"background":{"type":"solid","value":"#f5e6d3"},"typography":{"heading":{"fontFamily":"Abril Fatface","fontSize":28,"fontWeight":"400","color":"#3e2723","letterSpacing":1,"textTransform":"none"},"subheading":{"fontFamily":"Open Sans","fontSize":16,"fontWeight":"600","color":"#5d4037","letterSpacing":0},"body":{"fontFamily":"Open Sans","fontSize":12,"fontWeight":"400","color":"#4e342e","lineHeight":1.5},"price":{"fontFamily":"Open Sans","fontSize":14,"fontWeight":"700","color":"#3e2723"}},"colors":{"primary":"#3e2723","secondary":"#f5e6d3","accent":"#ff6f00","text":"#4e342e","muted":"#8d6e63"},"layout":{"columns":1,"gutter":0,"margin":{"top":25,"right":30,"bottom":25,"left":30},"showDividers":true,"dividerStyle":"line"},"dishBlock":{"showPriceLeaders":true,"leaderChar":".","showDescription":true,"showAllergens":true,"showImages":false,"pricePosition":"right"}}',
  NULL,
  0,
  2
),
(
  'Modern Minimal',
  'Diseño limpio y minimalista con amplio espacio en blanco. Para restaurantes contemporáneos.',
  'dinner',
  'letter',
  215.90,
  279.40,
  '{"sections":[{"id":"header","type":"header","y":0,"height":70},{"id":"sec_1","type":"category","y":70,"height":190,"columns":2},{"id":"sec_2","type":"category","y":260,"height":190,"columns":2},{"id":"footer","type":"footer","y":450,"height":25}]}',
  '{"background":{"type":"solid","value":"#ffffff"},"typography":{"heading":{"fontFamily":"Inter","fontSize":26,"fontWeight":"300","color":"#111111","letterSpacing":4,"textTransform":"uppercase"},"subheading":{"fontFamily":"Inter","fontSize":15,"fontWeight":"500","color":"#333333","letterSpacing":2},"body":{"fontFamily":"Inter","fontSize":11,"fontWeight":"400","color":"#555555","lineHeight":1.7},"price":{"fontFamily":"Inter","fontSize":13,"fontWeight":"600","color":"#111111"}},"colors":{"primary":"#111111","secondary":"#ffffff","accent":"#e63946","text":"#333333","muted":"#999999"},"layout":{"columns":2,"gutter":30,"margin":{"top":35,"right":30,"bottom":35,"left":30},"showDividers":false,"dividerStyle":"none"},"dishBlock":{"showPriceLeaders":false,"leaderChar":"","showDescription":true,"showAllergens":true,"showImages":false,"pricePosition":"right"}}',
  NULL,
  0,
  3
),
(
  'Cocktail Lounge',
  'Carta de cócteles y bebidas con estilo nocturno y neón.',
  'cocktails',
  'A4',
  210.00,
  297.00,
  '{"sections":[{"id":"header","type":"header","y":0,"height":100},{"id":"sec_1","type":"category","y":100,"height":160,"columns":2},{"id":"sec_2","type":"category","y":260,"height":160,"columns":2},{"id":"footer","type":"footer","y":420,"height":30}]}',
  '{"background":{"type":"gradient","value":{"type":"linear","angle":180,"stops":["#0f0c29","#302b63","#24243e"]}},"typography":{"heading":{"fontFamily":"Bebas Neue","fontSize":36,"fontWeight":"400","color":"#ff6ec7","letterSpacing":3,"textTransform":"uppercase"},"subheading":{"fontFamily":"Poppins","fontSize":16,"fontWeight":"300","color":"#a78bfa","letterSpacing":1},"body":{"fontFamily":"Poppins","fontSize":12,"fontWeight":"300","color":"#d1d5db","lineHeight":1.6},"price":{"fontFamily":"Poppins","fontSize":14,"fontWeight":"600","color":"#fbbf24"}},"colors":{"primary":"#ff6ec7","secondary":"#0f0c29","accent":"#fbbf24","text":"#d1d5db","muted":"#6b7280"},"layout":{"columns":2,"gutter":25,"margin":{"top":30,"right":25,"bottom":30,"left":25},"showDividers":true,"dividerStyle":"glow"},"dishBlock":{"showPriceLeaders":false,"leaderChar":"","showDescription":true,"showAllergens":false,"showImages":false,"pricePosition":"right"}}',
  NULL,
  1,
  4
);

-- Menú demo
INSERT INTO `menus` (`restaurant_id`, `template_id`, `title`, `style_config_json`, `content_json`, `status`) VALUES
(
  1,
  1,
  'Menú Cena Primavera 2024',
  '{"dimensions":{"width":210,"height":297,"unit":"mm","format":"A4"},"background":{"type":"solid","value":"#1a1a2e"},"typography":{"heading":{"fontFamily":"Playfair Display","fontSize":32,"fontWeight":"700","color":"#d4af37","letterSpacing":2,"textTransform":"uppercase"},"subheading":{"fontFamily":"Montserrat","fontSize":18,"fontWeight":"500","color":"#c0c0c0","letterSpacing":1},"body":{"fontFamily":"Lato","fontSize":13,"fontWeight":"400","color":"#e0e0e0","lineHeight":1.6},"price":{"fontFamily":"Montserrat","fontSize":15,"fontWeight":"600","color":"#d4af37"}},"colors":{"primary":"#d4af37","secondary":"#1a1a2e","accent":"#c0392b","text":"#e0e0e0","muted":"#888888"},"layout":{"columns":2,"gutter":20,"margin":{"top":30,"right":25,"bottom":30,"left":25},"showDividers":true,"dividerStyle":"dots"},"dishBlock":{"showPriceLeaders":true,"leaderChar":"·","showDescription":true,"showAllergens":true,"showImages":false,"pricePosition":"right"}}',
  '{"sections":[{"id":"sec_001","title":"Entradas","icon":"appetizer","sortOrder":0,"items":[{"id":"item_001","name":"Carpaccio di Manzo","description":"Finas láminas de res con rúcula, parmesano y aceite de trufa","price":185.00,"currency":"MXN","image":null,"allergens":["gluten_free"],"badges":["chef_recommendation"],"visible":true,"sortOrder":0},{"id":"item_002","name":"Burrata con Tomate Heritage","description":"Burrata cremosa sobre tomates multicolor, albahaca fresca y reducción de balsámico","price":210.00,"currency":"MXN","image":null,"allergens":["vegetarian"],"badges":[],"visible":true,"sortOrder":1},{"id":"item_003","name":"Tartare de Atún","description":"Atún fresco picado con aguacate, sésamo negro y ponzu cítrico","price":245.00,"currency":"MXN","image":null,"allergens":["gluten_free"],"badges":["spicy"],"visible":true,"sortOrder":2}]},{"id":"sec_002","title":"Platos Fuertes","icon":"main_course","sortOrder":1,"items":[{"id":"item_004","name":"Risotto ai Funghi Porcini","description":"Arroz carnaroli cremoso con porcini frescos, mantequilla trufada y parmigiano 24 meses","price":320.00,"currency":"MXN","image":null,"allergens":["vegetarian"],"badges":["chef_recommendation"],"visible":true,"sortOrder":0},{"id":"item_005","name":"Ossobuco alla Milanese","description":"Jarrete de ternera braseado lentamente con gremolata y risotto azafrán","price":420.00,"currency":"MXN","image":null,"allergens":[],"badges":[],"visible":true,"sortOrder":1},{"id":"item_006","name":"Branzino al Cartoccio","description":"Lubina mediterránea al papillote con alcaparras, aceitunas taggiasca y limón confitado","price":385.00,"currency":"MXN","image":null,"allergens":["gluten_free"],"badges":[],"visible":true,"sortOrder":2}]},{"id":"sec_003","title":"Postres","icon":"dessert","sortOrder":2,"items":[{"id":"item_007","name":"Tiramisú Clásico","description":"Bizcocho savoiardi, mascarpone, café espresso y cacao amargo","price":155.00,"currency":"MXN","image":null,"allergens":[],"badges":[],"visible":true,"sortOrder":0},{"id":"item_008","name":"Panna Cotta al Frutto della Passione","description":"Crema de vainilla bourbon con coulis de maracuyá y crumble de almendra","price":145.00,"currency":"MXN","image":null,"allergens":["gluten_free"],"badges":[],"visible":true,"sortOrder":1}]}]}',
  'draft'
);

SET FOREIGN_KEY_CHECKS = 1;
