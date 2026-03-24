# 📋 Informe de Proyecto — MercadoSordo
**Plataforma de e-commerce para la Comunidad Sorda de Chile**

> **Autor:** Nac Abarca  
> **Repositorio:** [github.com/NacAbarca/mercadosordo](https://github.com/NacAbarca/mercadosordo)  
> **Versión:** v1.0.0  
> **Fecha:** Marzo 2026  
> **Branch:** `main` (mergeado desde `feat/order-management`)  
> **Producción:** [mercadosordo-production.up.railway.app](https://mercadosordo-production.up.railway.app)

---

## 1. Resumen Ejecutivo

MercadoSordo es una plataforma de comercio electrónico tipo marketplace desarrollada con identidad visual de la Comunidad Sorda de Chile. Permite a vendedores publicar productos y a compradores adquirirlos de forma segura, con un protocolo de confirmación de transacciones en múltiples pasos inspirado en exchanges tipo escrow, métodos de pago configurables por vendedor y un sistema completo de gestión de órdenes con seguimiento en tiempo real.

La plataforma fue desarrollada completamente desde cero en PHP 8.2 sin frameworks ni Composer, con Vue 3 en el frontend vía CDN, y fue desplegada exitosamente en Railway con MySQL 8. El MVP incluye 76 endpoints REST, 22 tablas en base de datos, 13 vistas SPA y más de 6.400 líneas de código fuente.

---

## 2. Stack Tecnológico

| Capa | Tecnología |
|---|---|
| **Backend** | PHP 8.2 — sin Composer, sin frameworks, arquitectura propia |
| **Frontend** | Vue 3 (CDN) + Bootstrap 5.3 + Bootstrap Icons 1.11 |
| **Base de datos** | MySQL 8.0+ |
| **Servidor** | PHP Built-in (dev) / Railway + Nixpacks (prod) |
| **Deploy** | Railway — [mercadosordo-production.up.railway.app](https://mercadosordo-production.up.railway.app) |
| **Tipografía** | Helvetica Neue, Helvetica, Arial |
| **Identidad visual** | Paleta Comunidad Sorda — Azul `#1B4F8A` + Dorado `#F4C430` |
| **Control de versiones** | Git + GitHub |

---

## 3. Arquitectura del Sistema

```
mercadosordo/
├── public/                  # Document root
│   ├── index.php            # Entry point único
│   ├── .htaccess            # Rewrite rules + seguridad
│   └── uploads/
│       ├── avatars/         # Fotos de perfil
│       └── products/        # Imágenes de productos
├── src/
│   ├── Core.php             # DB, Router, Request, Response, Auth, Middlewares
│   └── Controllers.php      # 11 controladores (1.600+ líneas)
├── routes/
│   └── api.php              # 76 rutas REST
├── views/
│   └── app.php              # SPA completa Vue 3 (4.400+ líneas)
├── config/
│   └── database.php         # Lee env vars (Railway compatible)
├── database/
│   ├── schema.sql           # Schema inicial
│   ├── migrate_v2.sql       # delivery_type, external_link, short_desc
│   ├── migrate_v3.sql       # rut, rut_verified, birthdate
│   ├── migrate_v4.sql       # vendor_payment_accounts, vendor_bank_accounts, payments
│   └── migrate_v5.sql       # order_confirmations, order_disputes, notifications, order_messages
├── railway.json             # Railway deploy config
└── nixpacks.toml            # Build PHP 8.2 + extensiones
```

### Patrón arquitectónico

```
Request → public/index.php → Router (Core.php)
       → AuthMiddleware / AdminMiddleware / RateLimitMiddleware
       → Controller@method → DB (PDO Singleton)
       → Response::json() | Response::view()
```

---

## 4. Base de Datos

### 4.1 Tablas (22 tablas totales)

| Tabla | Descripción |
|---|---|
| `users` | Usuarios con roles buyer/seller/admin, RUT chileno verificado |
| `user_tokens` | Tokens de autenticación (auth, reset, api) con TTL |
| `user_addresses` | Direcciones de envío por usuario |
| `categories` | Categorías con soporte de subcategorías (self-join) |
| `products` | Catálogo con FULLTEXT search, slugs únicos, variantes |
| `product_images` | Galería múltiple (máx. 8) con orden y primaria |
| `product_attributes` | Atributos clave-valor por producto |
| `product_variants` | Variantes (talla, color, etc.) |
| `orders` | Órdenes con desglose IVA 19%, comisión 5%, neto vendedor |
| `order_items` | Líneas de orden con snapshot de precio |
| `order_tracking` | Historial de estados de la orden |
| `order_confirmations` | Protocolo de seguridad en 4 pasos |
| `order_disputes` | Reclamos y disputas comprador-vendedor |
| `order_messages` | Chat por orden |
| `carts` | Carritos guest (cookie) + autenticado (user_id) |
| `cart_items` | Items del carrito |
| `vendor_payment_accounts` | OAuth Mercado Pago por vendedor |
| `vendor_bank_accounts` | Cuenta bancaria + métodos habilitados por vendedor |
| `payments` | Registro de transacciones (MP + Khipu) |
| `reviews` | Reseñas con moderación y actualización de rating |
| `notifications` | Centro de notificaciones por usuario |
| `audit_logs` | Log de auditoría de acciones admin |

### 4.2 Relaciones clave

```
users ─┬─< orders (buyer_id, seller_id)
       ├─< user_addresses
       ├─< user_tokens
       ├─< vendor_payment_accounts
       ├─< vendor_bank_accounts
       └─< notifications

orders ─┬─< order_items
        ├─< order_tracking
        ├─< order_confirmations
        ├─< order_disputes
        ├─< order_messages
        └─< payments

products ─┬─< product_images
          ├─< product_attributes
          ├─< product_variants
          └─< cart_items
```

### 4.3 ENUM status en orders

```sql
ENUM('pending','paid','processing','dispatched','in_transit',
     'delivered','completed','dispute','refunded','cancelled')
```

---

## 5. Controladores y Funcionalidades (11 clases)

### AuthController
Registro, login con merge carrito guest, logout, me, forgot/reset password. Tokens bcrypt cost-12 + random_bytes(32) TTL 7 días.

### ProductController
Listado paginado con FULLTEXT search y filtros (precio, condición, categoría, ordenamiento). Detalle con galería. CRUD con ownership. Soft delete. Mis productos del vendedor.

### CartController
Carrito guest (cookie `ms_cart`) + autenticado. Merge automático al login. Validación de stock. Regla: vendedor no puede agregar sus propios productos. Admin no puede agregar al carrito.

### OrderController
`checkout()` — transacción atómica: BEGIN, valida stock, INSERT order + items, calcula IVA 19% + comisión 5% + neto vendedor, COMMIT, vacía carrito. `show()` devuelve items + tracking.

### OrderManagementController
Panel vendedor completo:
- `vendorOrders()` — lista órdenes recibidas con desglose financiero
- `vendorAccept()` — paso 1: acepta, notifica comprador
- `vendorDispatch()` — paso 2: carrier + tracking + auto-complete en 7 días
- `buyerConfirm()` — paso 3: comprador confirma, fondos liberados
- `cancelOrder()` — restaura stock automáticamente
- `openDispute()` — reclamo formal
- `getNotifications()` / `markNotificationRead()` / `markAllRead()`
- `getMessages()` / `sendMessage()` — chat con notificación

### ProfileController
5 tabs: datos personales + RUT (módulo 11, inmutable) + contraseña + direcciones + métodos de pago. Avatar upload JPG/PNG/WebP máx 2MB.

### ProductImageController
Upload con validación MIME real. Reordenar drag & drop. Eliminar con reasignación de primaria.

### MercadoPagoController
OAuth por vendedor → access_token → createPreference con marketplace_fee 5% → webhookIPN.

### BankTransferController
Cuenta bancaria del vendedor. 4 métodos configurables: transferencia, MP link, billetera digital, texto libre. Khipu API. Endpoint público de métodos por vendedor.

### AdminController
Dashboard con KPIs: ingresos (hoy/semana/mes/total + comisión), órdenes (pendientes/disputas), usuarios, reputación. Gráfico SVG ingresos 30 días. Top vendedores, compradores, productos más vendidos, más vistos. Actividad reciente.

### ReviewController
Crear reseña + actualizar rating_avg y rating_count del producto.

---

## 6. API REST — 76 Rutas

Ver README.md para listado completo.

**Grupos:**
- Públicas: 15 rutas
- Autenticadas: 54 rutas
- Admin (auth + role): 7 rutas

---

## 7. Frontend SPA (Vue 3 — 13 vistas)

| Vista | Descripción |
|---|---|
| Home | Hero banner + productos destacados |
| Catálogo | Grid con filtros + paginación |
| Detalle producto | Galería + buy box + reseñas |
| Carrito | Items + resumen |
| Checkout | 3 pasos: resumen → dirección → pago |
| Login / Registro | Formularios con validación |
| Mis compras | Lista + comprobante imprimible + tracking |
| Mis ventas | CRUD + galería drag & drop + estadísticas |
| Mis pedidos | Panel vendedor + protocolo 4 pasos + chat |
| Perfil | 5 tabs completos |
| Notificaciones | Badge 👋🏻 + navegación inteligente |
| Admin | Dashboard KPIs / usuarios / productos / órdenes |

---

## 8. Seguridad

### 8.1 Autenticación
- Tokens `random_bytes(32)` hex, 64 chars, TTL configurable
- Passwords `bcrypt` cost-12
- Revocación en logout y cambio de contraseña
- Rate limiting 60 req/min por IP

### 8.2 Validación
- Prepared statements PDO — SQL injection imposible
- Validación MIME real en uploads (no confía en extensión)
- RUT chileno módulo 11 + inmutabilidad una vez guardado
- Ownership verificado en todas las operaciones sensibles

### 8.3 Protocolo de seguridad de órdenes (escrow)
```
PAID → [vendedor acepta] → PROCESSING
     → [vendedor despacha + tracking] → DISPATCHED
     → [comprador confirma O auto 7 días] → COMPLETED
```

### 8.4 Reglas de negocio
- Vendedor no puede comprar sus propios productos (frontend + backend)
- Admin no puede realizar compras
- Cancelación restaura stock automáticamente
- Disputas con estados formales de resolución

### 8.5 Headers
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
```

---

## 9. Usabilidad

- Carrito guest persistente con merge automático al login
- Checkout lineal 3 pasos con stepper visual
- Métodos de pago dinámicos configurados por vendedor
- Comprobante de compra imprimible
- Chat por orden con timestamps
- Notificaciones badge 👋🏻 con fondo `rgba(27,79,138,0.08)` para no leídas
- Navegación inteligente: click notificación → detalle orden correcto
- Dashboard Admin con gráfico SVG inline de ingresos

---

## 10. Métodos de Pago

| Método | Implementación |
|---|---|
| Mercado Pago | OAuth por vendedor + link directo + API preferencias con comisión |
| Transferencia bancaria | Khipu API + datos bancarios manuales |
| Billetera digital | Mach, Tenpo, BICE, Copec Pay |
| Texto libre | Instrucciones personalizadas 500 chars |

**Desglose por venta:**
```
Precio de venta:          $100.000
IVA 19% (incluido):      - $15.966
Comisión plataforma 5%:  -  $5.000
──────────────────────────────────
Neto vendedor:             $79.034
```

---

## 11. Identidad Visual — Comunidad Sorda

| Elemento | Valor |
|---|---|
| Color primario | `#1B4F8A` — Azul profundo (navbar) |
| Color secundario | `#0E3060` — Azul oscuro (category nav) |
| Color acento | `#F4C430` — Dorado (botón búsqueda, activo) |
| Color fondo | `#EBF2FB` — Azul muy suave |
| Color texto | `#0A1628` — Casi negro azulado |
| Tipografía | Helvetica Neue, Helvetica, Arial |
| Tipografía display | Sora (marca) |
| Radio bordes | 8px estándar |

---

## 12. Deploy Railway

### Configuración

**`railway.json`**
```json
{
  "build": { "builder": "NIXPACKS" },
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t public",
    "healthcheckPath": "/",
    "restartPolicyType": "ON_FAILURE"
  }
}
```

**`nixpacks.toml`**
```toml
[phases.setup]
nixPkgs = ["php82", "php82Extensions.pdo", "php82Extensions.pdo_mysql",
           "php82Extensions.mbstring", "php82Extensions.fileinfo",
           "php82Extensions.curl", "php82Extensions.gd"]
```

### Variables de entorno (PHP service)

```env
MYSQLHOST=mysql.railway.internal
MYSQLPORT=3306
MYSQLDATABASE=railway
MYSQLUSER=root
MYSQLPASSWORD=****
APP_URL=https://mercadosordo-production.up.railway.app
MP_LINK_PAGO=https://link.mercadopago.cl/mercadosordo
MP_COMMISSION=5.0
BANK_COMMISSION=5.0
```

### URL de producción

**[https://mercadosordo-production.up.railway.app](https://mercadosordo-production.up.railway.app)**

---

## 13. Instalación Local

```bash
git clone https://github.com/NacAbarca/mercadosordo.git
cd mercadosordo
mysql -u root -p -e "CREATE DATABASE mercadosordo CHARACTER SET utf8mb4;"
mysql -u root -p mercadosordo < database/schema.sql
mysql -u root -p mercadosordo < database/migrate_v2.sql
mysql -u root -p mercadosordo < database/migrate_v3.sql
mysql -u root -p mercadosordo < database/migrate_v4.sql
mysql -u root -p mercadosordo < database/migrate_v5.sql
mkdir -p public/uploads/products public/uploads/avatars
php -S localhost:8080 -t public
```

---

## 14. Métricas del Proyecto

| Métrica | Valor |
|---|---|
| Líneas de código total | 6.400+ líneas |
| Controladores PHP | 11 clases |
| Rutas API REST | 76 endpoints |
| Tablas de base de datos | 22 tablas |
| Migraciones | 5 versiones |
| Vistas SPA | 13 vistas |
| Archivos fuente | 15 archivos |
| Dependencias externas | 0 (sin Composer) |
| Librerías CDN | Bootstrap 5.3, Bootstrap Icons 1.11, Vue 3.4 |
| Versión | v1.0.0 |

---

## 15. Roadmap

### ✅ Completado v1.0.0
- [x] Auth completo (registro, login, reset password)
- [x] Catálogo con filtros y búsqueda FULLTEXT
- [x] Carrito guest + autenticado con merge al login
- [x] Checkout 3 pasos + métodos de pago por vendedor
- [x] Perfil con RUT chileno, avatar, direcciones
- [x] Mis Ventas con galería drag & drop
- [x] Panel vendedor + protocolo seguridad 4 pasos (escrow)
- [x] Pagos duales (MP OAuth + transferencia bancaria)
- [x] Chat comprador ↔ vendedor por orden
- [x] Notificaciones con badge 👋🏻 + navegación inteligente
- [x] Dashboard Admin con KPIs y gráficos SVG
- [x] Reglas de negocio (vendedor no se compra a sí mismo, admin no compra)
- [x] Identidad visual Comunidad Sorda
- [x] Deploy producción Railway

### 🔜 Pendiente v1.1.0
- [ ] Notificaciones email transaccionales (SMTP)
- [ ] Dominio propio mercadosordo.cl
- [ ] Búsqueda avanzada (Meilisearch)
- [ ] Wishlists/Favoritos en UI
- [ ] PWA / Mobile app
- [ ] Tests unitarios PHPUnit

---

## 16. Licencia y Créditos

**Desarrollado por:** Nac Abarca  
**GitHub:** [github.com/NacAbarca](https://github.com/NacAbarca)  
**Año:** 2026  
**Stack:** PHP 8.2 · Vue 3 · Bootstrap 5 · MySQL 8  
**Deploy:** Railway  

> *MercadoSordo — Hecho en Chile 🇨🇱 con identidad de la Comunidad Sorda*

---

*Actualizado el 23 de Marzo de 2026 — v1.0.0 en producción.*
