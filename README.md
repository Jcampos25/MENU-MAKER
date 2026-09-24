# 🍽️ MENU MAKER — Menu Studio

Sistema profesional para el diseño, maquetación, gestión y personalización de cartas y menús gastronómicos de alta gama.

---

## 🌟 Características Principales

- **🎨 Editor Visual en Tiempo Real:** Motor de renderizado DOM con soporte para formato A4, tipografías distinguidas (*Playfair Display*, *Abril Fatface*, *Montserrat*, *Lato*, *Poppins*, *Inter*), fondos degradados, texturas y marcas de agua.
- **📑 Catálogo Gastronómico Completo (104 Platillos):** Estructurado en 11 secciones formales a partir de la base de datos de Comedor Machu:
  1. *Desayunos Típicos & Especiales* (9 platillos)
  2. *Cenas Tradicionales & Parrilleras* (6 platillos)
  3. *Especialidades A la Carta* (17 platillos)
  4. *Especialidades del Día* (19 platillos)
  5. *Antojitos Tradicionales & Típicos* (9 platillos)
  6. *Platillos Personalizados & Guarniciones* (6 platillos, incluyendo el platillo personalizado especial)
  7. *Bebidas Naturales de la Casa* (21 platillos)
  8. *Bebidas & Refrescos Embotellados* (9 platillos)
  9. *Postres Tradicionales* (3 platillos)
  10. *Porciones Extras & Complementos* (4 platillos)
  11. *Servicio para Llevar & Empaques* (1 platillo)
- **⭐ Platillo Personalizado Especial:** Configuración formal con descripción culinaria de alta gama y distintivo de *Recomendación del Chef*.
- **📐 2 Plantillas Profesionales Exclusivas:**
  - **Hacienda Tradicional:** Diseño colonial gourmet con fondo ébano cálido, detalles en oro viejo y terracota, tipografía clásica en 3 columnas.
  - **Bistró Ejecutivo Dark Slate:** Estilo vanguardista moderno con paleta pizarra noche, tipografía sans-serif geométrica y acentos en azul cielo y oro champán.
- **🔍 Búsqueda Rápida y Gestión Dinámica:** Filtrado en vivo de secciones y platillos, controles de expansión/colapso masivo, reordenamiento por *drag & drop* y guardado automático con soporte para deshacer/rehacer (*Undo/Redo*).
- **💵 Soporte de Moneda Local:** Formato de precios en Córdobas (`C$`) con cálculo y notas fiscales integradas.

---

## 🛠️ Tecnologías

- **Backend:** PHP 8.0+ (Arquitectura MVC limpia, Router nativo, PDO Singleton con MySQL).
- **Frontend:** Vanilla JavaScript (ES6+ modular: `CanvasManager`, `ToolbarManager`, `FontManager`, `ColorManager`, `DragDropManager`, `ExportManager`).
- **Estilos:** Vanilla CSS con variables de diseño, glassmorphism, modo oscuro y micro-animaciones.
- **Base de Datos:** MySQL 8.0 / MariaDB (charset `utf8mb4`).

---

## 🚀 Instalación y Puesta en Marcha

### 1. Clonar el Repositorio
```bash
git clone https://github.com/Jcampos25/MENU-MAKER.git
cd MENU-MAKER
```

### 2. Configuración en XAMPP / Servidor Web
Colocar la carpeta del proyecto en `htdocs`:
```
C:\xampp\htdocs\MENU MAKER
```

### 3. Base de Datos
1. Iniciar Apache y MySQL en XAMPP.
2. Crear la base de datos `menu_studio`:
   ```sql
   CREATE DATABASE `menu_studio` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Importar el esquema base ubicado en `database/schema.sql`.

### 4. Acceso
Abrir en el navegador:
```
http://localhost/MENU%20MAKER/public/
```

---

## 📁 Estructura del Proyecto

```
MENU MAKER/
├── app/
│   ├── Controllers/     # Controladores MVC y API JSON
│   ├── Models/          # Modelos de base de datos (Menu, Template, Restaurant, etc.)
│   └── Views/           # Vistas (Dashboard, Editor visual, Layouts)
├── config/              # Configuración de base de datos y aplicación
├── core/                # Enrutador, Request, Response, Controller y Model base
├── database/            # Scripts SQL y migraciones
├── public/              # Punto de entrada Front Controller (index.php) y assets
│   ├── assets/          # CSS, JavaScript modular y recursos
│   └── uploads/         # Logos y multimedia del restaurante
└── README.md
```

---

## 📄 Licencia

Desarrollado para Comedor Machu — Gestión y Diseño Gastronómico.
