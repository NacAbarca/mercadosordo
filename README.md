# 🔶 MercadoSordo — E-commerce Platform

> **Desarrollado por [Nac Abarca](https://github.com/NacAbarca)** · 2026  
> Stack: **PHP 8.2** · **Vue 3** · **Bootstrap 5.3** · **MySQL 8**  
> Arquitectura: **SPA + REST API** | Sin frameworks | Sin Composer  
> 🌐 **Producción:** [mercadosordo-production.up.railway.app](https://mercadosordo-production.up.railway.app)

---

## 📁 Estructura del Proyecto

```
mercadosordo/
├── config/
│   └── database.php          # Configuración DB (env vars Railway/local)
├── database/
│   ├── schema.sql             # Schema completo MySQL (22 tablas)
│   ├── migrate_v2.sql         # delivery_type, external_link, short_desc
│   ├── migrate_v3.sql         # rut, rut_verified, birthdate
│   ├── migrate_v4.sql         # vendor_payment_accounts, vendor_bank_accounts, payments
│   ├── migrate_v5.sql         # order_confirmations, order_disputes, notifications, order_messages
│   └── migrate_v6.sql         # tax_rate en vendor_bank_accounts (IVA configurable)
├── public/                    # Document root
│   ├── index.php              # Entry point único
│   ├── .htaccess              # Rewrite rules + seguridad
│   └── uploads/               # Imágenes locales (fallback sin R2)
├── routes/
│   └── api.php                # 77 rutas REST
├── src/
│   ├── Core.php               # DB, Router, Request, Response, Auth, Middlewares
│   ├── Controllers.php        # 11 controladores (1.700+ líneas)
│   ├── R2Uploader.php         # Cloudflare R2 — imágenes persistentes
│   └── Mailer.php             # SMTP nativo — emails transaccionales
├── views/
│   └── app.php                # SPA completa Vue 3 (4.500+ líneas)
├── railway.json               # Configuración Railway deploy
├── nixpacks.toml              # Build config PHP 8.2
├── .env.example               # Variables de entorno
└── INFORME_PROYECTO.md        # Documentación técnica completa
```

### Patrón arquitectónico

```
Request → public/index.php → Router (Core.php)
       → AuthMiddleware / AdminMiddleware / RateLimitMiddleware
       → Controller@method → DB (PDO Singleton)
       → Response::json() | Response::view()
```

---

## 🗄️ Base de Datos — 22 Tablas

| Tabla | Descripción |
|---|---|
| `users` | Usuarios con roles buyer/seller/admin + RUT chileno verificado |
| `user_tokens` | Tokens auth/reset con TTL configurable |
| `user_addresses` | Direcciones de envío (16 regiones Chile) |
| `categories` | Categorías con subcategorías (self-join) |
| `products` | Catálogo con FULLTEXT search, slugs únicos, variantes |
| `product_images` | Galería múltiple (máx. 8) con orden y primaria |
| `product_attributes` | Atributos clave-valor por producto |
| `product_variants` | Variantes (talla, color, etc.) |
| `carts` | Carritos guest (cookie) + autenticado (user_id) |
| `cart_items` | Items del carrito |
| `orders` | Órdenes con IVA configurable, comisión 5%, neto vendedor |
| `order_items` | Líneas de orden con snapshot de precio |
| `order_tracking` | Historial de estados de la orden |
| `order_confirmations` | Protocolo de seguridad en 4 pasos |
| `order_disputes` | Reclamos y disputas comprador-vendedor |
| `order_messages` | Chat por orden |
| `payments` | Registro de transacciones (MP + Khipu) |
| `vendor_payment_accounts` | OAuth Mercado Pago por vendedor |
| `vendor_bank_accounts` | Cuenta bancaria + métodos + IVA configurable |
| `notifications` | Centro de notificaciones por usuario |
| `reviews` | Reseñas con moderación y actualización de rating |
| `audit_logs` | Log de auditoría de acciones admin |

---

## 🔌 API REST — 77 Endpoints

### Públicas
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/forgot-password
POST   /api/auth/reset-password
GET    /api/products              ?q=&category=&min_price=&max_price=&condition=&sort=&page=
GET    /api/products/{slug}
GET    /api/categories
GET    /api/cart
POST   /api/cart/items
PATCH  /api/cart/items/{id}
DELETE /api/cart/items/{id}
DELETE /api/cart
GET    /api/vendor/{vendor_id}/payment-methods
POST   /api/webhooks/mercadopago/ipn
POST   /api/webhooks/bank-transfer/confirm
GET    /api/admin/report          ?secret=REPORT_SECRET  (cron job reporte diario)
```

### Autenticadas (Bearer Token)
```
GET    /api/auth/me                           POST   /api/auth/logout
GET    /api/my/products                       POST   /api/products
PUT    /api/products/{id}                     DELETE /api/products/{id}
POST   /api/products/{id}/images              PATCH  /api/products/{id}/images/order
DELETE /api/products/images/{imageId}
GET    /api/orders                            GET    /api/orders/{id}
POST   /api/orders/checkout                   POST   /api/orders/{id}/confirm
POST   /api/orders/{id}/cancel                POST   /api/orders/{id}/dispute
GET    /api/orders/{id}/messages              POST   /api/orders/{id}/messages
GET    /api/vendor/orders                     GET    /api/vendor/orders/{id}
POST   /api/vendor/orders/{id}/accept         POST   /api/vendor/orders/{id}/dispatch
POST   /api/vendor/orders/{id}/cancel
GET    /api/vendor/mp/status                  POST   /api/payments/mercadopago/create
POST   /api/vendor/bank-account/connect       POST   /api/vendor/payment-methods/save
POST   /api/payments/bank-transfer/create
GET    /api/profile                           PATCH  /api/profile
POST   /api/profile/password                  GET    /api/profile/addresses
POST   /api/profile/addresses                 PUT    /api/profile/addresses/{id}
PATCH  /api/profile/addresses/{id}/default    DELETE /api/profile/addresses/{id}
POST   /api/profile/avatar                    DELETE /api/profile/avatar
GET    /api/notifications                     PATCH  /api/notifications/{id}/read
POST   /api/notifications/read-all
```

### Admin (auth + role admin)
```
GET    /api/admin/dashboard       GET    /api/admin/users
PATCH  /api/admin/users/{id}      GET    /api/admin/products
GET    /api/admin/orders          PATCH  /api/admin/orders/{id}/status
GET    /api/admin/audit-log       GET    /api/admin/daily-report
```

---

## 🛡️ Protocolo de Seguridad de Órdenes (tipo escrow)

```
PAGADO
  └→ [Vendedor acepta — 24h] → EN PROCESO
       └→ [Vendedor despacha + carrier + tracking] → DESPACHADO
            └→ [Comprador confirma O auto 7 días] → COMPLETADO
                 └→ Fondos liberados al vendedor ✅
```

**Estados:** `pending` → `paid` → `processing` → `dispatched` → `in_transit` → `delivered` → `completed` / `dispute` / `cancelled` / `refunded`

---

## 💰 Métodos de Pago

| Método | Implementación |
|---|---|
| **Mercado Pago** | OAuth por vendedor + link directo `link.mercadopago.cl` |
| **Transferencia bancaria** | Khipu API + datos bancarios manuales |
| **Billetera digital** | Mach, Tenpo, BICE, Copec Pay |
| **Texto libre** | Instrucciones personalizadas |

**Desglose financiero (IVA configurable por vendedor):**
```
Precio de venta:          $100.000
IVA (configurable):       $0 - $19.000  (0% exento / 10% reducido / 19% estándar)
Comisión plataforma 5%:  -  $5.000      (fijo para todos los proveedores)
──────────────────────────────────────
Neto vendedor:             $95.000 - $76.000
```

**Régimen tributario configurable en Perfil → Métodos de pago:**
- `0%` — Persona natural exenta
- `10%` — Tasa reducida
- `19%` — Empresa / Persona jurídica
- Personalizado — cualquier % libre

---

## 📧 Notificaciones Email (SMTP)

| Evento | Destinatario | Asunto |
|---|---|---|
| Pedido creado | Comprador | ✅ Pedido creado — MS-XXXXXX |
| Orden aceptada | Comprador | 📦 Tu pedido fue aceptado |
| Pago recibido | Vendedor | 💰 Pago recibido — MS-XXXXXX |
| Orden despachada | Comprador | 🚚 Tu pedido está en camino |
| Orden completada | Vendedor | 🎉 Venta completada — Fondos liberados |
| Nuevo mensaje chat | Comprador/Vendedor | 💬 Nuevo mensaje de X |
| Reporte diario | Admin | 📊 Reporte diario MercadoSordo |

**Proveedores SMTP compatibles:**
- Resend.com (activo — 3.000 emails/mes gratis)
- Gmail / Google Workspace
- SendGrid (100/día gratis)
- Servidor propio / Hosting / VPS

---

## 🖼️ Almacenamiento de Imágenes

**Cloudflare R2** (activo en producción — 10GB gratis):
- Imágenes de productos y avatares
- Persistente entre redeploys de Railway
- Fallback a sistema de archivos local en desarrollo

---

## 🎨 Identidad Visual — Comunidad Sorda

| Variable | Color | Uso |
|---|---|---|
| `--ms-blue` | `#1B4F8A` | Navbar, botones principales |
| `--ms-blue-dark` | `#0E3060` | Category nav, dropdowns |
| `--ms-yellow` | `#F4C430` | Acento dorado, botón búsqueda |
| Tipografía | `Helvetica Neue, Helvetica, Arial` | Todo el sitio |

**Navbar responsive:** muestra `MercadoSordo` en desktop y `MS·` en mobile (<576px)

---

## 📲 Compartir en RRSS

Cada producto incluye botones de compartir:
- 🟢 WhatsApp — título + precio + URL
- 🔵 Facebook — sharer URL
- 🔵 Telegram — título + URL
- ⚫ TikTok — URL
- 🔗 Copiar link — clipboard API

---

## 🔐 Seguridad

- ✅ Passwords `bcrypt` cost-12 | Tokens `random_bytes(32)` TTL configurable
- ✅ Prepared statements PDO — SQL injection imposible
- ✅ Validación MIME real en uploads | RUT chileno módulo 11 + inmutabilidad
- ✅ Rate limiting 60 req/min | AuthMiddleware + AdminMiddleware
- ✅ Vendedor no puede comprar sus propios productos
- ✅ Admin no puede realizar compras
- ✅ Audit log | Soft delete | Headers de seguridad

---

## ⚙️ Instalación Local

```bash
git clone https://github.com/NacAbarca/mercadosordo.git
cd mercadosordo

# Base de datos
mysql -u root -p -e "CREATE DATABASE mercadosordo CHARACTER SET utf8mb4;"
for f in schema migrate_v2 migrate_v3 migrate_v4 migrate_v5 migrate_v6; do
  mysql -u root -p mercadosordo < database/$f.sql
done

mkdir -p public/uploads/products public/uploads/avatars
php -S localhost:8080 -t public
```

---

## 🚂 Deploy Railway

**Variables de entorno requeridas:**
```env
# Base de datos
MYSQLHOST=mysql.railway.internal
MYSQLPORT=3306
MYSQLDATABASE=railway
MYSQLUSER=root
MYSQLPASSWORD=****

# App
APP_URL=https://mercadosordo-production.up.railway.app

# Cloudflare R2 — imágenes
R2_BUCKET=mercadosordo-uploads
R2_ACCESS_KEY=****
R2_SECRET_KEY=****
R2_ENDPOINT=https://ACCOUNT_ID.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://pub-XXXXX.r2.dev

# SMTP — emails
SMTP_HOST=smtp.resend.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=resend
SMTP_PASS=re_XXXXXXXXX
SMTP_FROM=noreply@mercadosordo.cl
SMTP_FROM_NAME=MercadoSordo
REPORT_SECRET=clave_secreta_cron

# Pagos
MP_LINK_PAGO=https://link.mercadopago.cl/mercadosordo
MP_COMMISSION=5.0
BANK_COMMISSION=5.0
```

**Reporte diario (cron job):**
```bash
curl "https://mercadosordo-production.up.railway.app/api/admin/report?secret=TU_REPORT_SECRET"
```

---

## 📊 Métricas v1.1.0

| Métrica | Valor |
|---|---|
| Líneas de código | 6.700+ |
| Controladores PHP | 11 clases |
| Rutas API REST | 77 endpoints |
| Tablas DB | 22 tablas |
| Migraciones | 6 versiones |
| Vistas SPA | 13 vistas |
| Dependencias externas | 0 (sin Composer) |
| Librerías CDN | Bootstrap 5.3, Bootstrap Icons 1.11, Vue 3.4 |

---

## 🗺️ Roadmap

- [x] Auth · Catálogo · Carrito · Checkout · Pagos duales
- [x] Protocolo seguridad 4 pasos (escrow)
- [x] Chat · Notificaciones 👋🏻 · Dashboard Admin
- [x] Reglas de negocio · Identidad Comunidad Sorda
- [x] Deploy producción Railway
- [x] **Cloudflare R2 — imágenes persistentes** ✅
- [x] **Email SMTP — 7 notificaciones automáticas** ✅
- [x] **IVA configurable por vendedor** ✅
- [x] **Botones compartir RRSS** ✅
- [x] **Navbar responsive mobile** ✅
- [x] **Indicador % descuento en formulario** ✅
- [ ] Dominio propio mercadosordo.cl
- [ ] Búsqueda avanzada (Meilisearch)
- [ ] PWA / Mobile app
- [ ] Tests unitarios PHPUnit

---

*© 2026 MercadoSordo — Hecho en Chile 🇨🇱 con identidad de la Comunidad Sorda*  
*Developed by [Nac Abarca](https://github.com/NacAbarca)*
