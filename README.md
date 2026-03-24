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
│   └── database.php         # Configuración DB (env vars Railway/local)
├── database/
│   ├── schema.sql            # Schema completo MySQL (22 tablas)
│   ├── migrate_v2.sql        # delivery_type, external_link, short_desc
│   ├── migrate_v3.sql        # rut, rut_verified, birthdate
│   ├── migrate_v4.sql        # vendor_payment_accounts, vendor_bank_accounts, payments
│   └── migrate_v5.sql        # order_confirmations, order_disputes, notifications, order_messages
├── public/                   # Document root
│   ├── index.php             # Entry point único
│   ├── .htaccess             # Rewrite rules + seguridad
│   └── uploads/              # Imágenes (avatars, productos)
├── routes/
│   └── api.php               # 76 rutas REST
├── src/
│   ├── Core.php              # DB, Router, Request, Response, Auth, Middlewares
│   └── Controllers.php       # 11 controladores (1.600+ líneas)
├── views/
│   └── app.php               # SPA completa Vue 3 (4.400+ líneas)
├── railway.json              # Configuración Railway deploy
├── nixpacks.toml             # Build config PHP 8.2
├── .env.example              # Variables de entorno
└── INFORME_PROYECTO.md       # Documentación técnica completa
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
| `orders` | Órdenes con IVA 19%, comisión 5%, neto vendedor |
| `order_items` | Líneas de orden con snapshot de precio |
| `order_tracking` | Historial de estados de la orden |
| `order_confirmations` | Protocolo de seguridad en 4 pasos |
| `order_disputes` | Reclamos y disputas comprador-vendedor |
| `order_messages` | Chat por orden |
| `payments` | Registro de transacciones (MP + Khipu) |
| `vendor_payment_accounts` | OAuth Mercado Pago por vendedor |
| `vendor_bank_accounts` | Cuenta bancaria + métodos habilitados |
| `notifications` | Centro de notificaciones por usuario |
| `reviews` | Reseñas con moderación y actualización de rating |
| `audit_logs` | Log de auditoría de acciones admin |

---

## 🔌 API REST — 76 Endpoints

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
GET    /api/admin/audit-log
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

**Desglose:** `$100.000` → IVA 19% `$15.966` → Comisión 5% `$5.000` → **Neto vendedor `$79.034`**

---

## 🎨 Identidad Visual — Comunidad Sorda

| Variable | Color | Uso |
|---|---|---|
| `--ms-blue` | `#1B4F8A` | Navbar, botones principales |
| `--ms-blue-dark` | `#0E3060` | Category nav, dropdowns |
| `--ms-yellow` | `#F4C430` | Acento dorado, botón búsqueda |
| Tipografía | `Helvetica Neue, Helvetica, Arial` | Todo el sitio |

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
git clone https://github.com/NacAbarca/mercadosordo.git && cd mercadosordo
mysql -u root -p -e "CREATE DATABASE mercadosordo CHARACTER SET utf8mb4;"
for f in schema migrate_v2 migrate_v3 migrate_v4 migrate_v5; do
  mysql -u root -p mercadosordo < database/$f.sql
done
mkdir -p public/uploads/products public/uploads/avatars
php -S localhost:8080 -t public
```

## 🚂 Deploy Railway

```env
MYSQLHOST=mysql.railway.internal
MYSQLPORT=3306
MYSQLDATABASE=railway
MYSQLUSER=root
MYSQLPASSWORD=****
APP_URL=https://tu-app.railway.app
MP_LINK_PAGO=https://link.mercadopago.cl/tu-usuario
MP_COMMISSION=5.0
BANK_COMMISSION=5.0
```

---

## 📊 Métricas v1.0.0

| Métrica | Valor |
|---|---|
| Líneas de código | 6.400+ |
| Controladores PHP | 11 clases |
| Rutas API REST | 76 endpoints |
| Tablas DB | 22 tablas |
| Migraciones | 5 versiones |
| Vistas SPA | 13 vistas |
| Dependencias externas | 0 (sin Composer) |

---

## 🗺️ Roadmap

- [x] Auth · Catálogo · Carrito · Checkout · Pagos duales
- [x] Protocolo seguridad 4 pasos (escrow)
- [x] Chat · Notificaciones · Dashboard Admin
- [x] Reglas de negocio · Identidad Comunidad Sorda
- [x] **Deploy producción Railway ✅**
- [ ] Email SMTP · Dominio mercadosordo.cl · Meilisearch · PWA

---

*© 2026 MercadoSordo — Hecho en Chile 🇨🇱 con identidad de la Comunidad Sorda*  
*Developed by [Nac Abarca](https://github.com/NacAbarca)*
