# 📋 Informe de Proyecto — MercadoSordo
**Plataforma de e-commerce para la Comunidad Sorda de Chile**

> **Autor:** Nac Abarca  
> **Repositorio:** [github.com/NacAbarca/mercadosordo](https://github.com/NacAbarca/mercadosordo)  
> **Fecha:** Marzo 2026  
> **Branch activa:** `feat/order-management`

---

## 1. Resumen Ejecutivo

MercadoSordo es una plataforma de comercio electrónico tipo marketplace desarrollada con identidad visual de la Comunidad Sorda de Chile. Permite a vendedores publicar productos y a compradores adquirirlos de forma segura, con un protocolo de confirmación de transacciones en múltiples pasos, métodos de pago configurables por vendedor y un sistema completo de gestión de órdenes con seguimiento en tiempo real.

---

## 2. Stack Tecnológico

| Capa | Tecnología |
|---|---|
| **Backend** | PHP 8.2 — sin Composer, sin frameworks, arquitectura propia |
| **Frontend** | Vue 3 (CDN) + Bootstrap 5.3 + Bootstrap Icons 1.11 |
| **Base de datos** | MySQL 8.0+ |
| **Servidor web** | Apache (`.htaccess`) / PHP Built-in / Nginx |
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
│   └── Controllers.php      # 11 controladores (1.587 líneas)
├── routes/
│   └── api.php              # 76 rutas REST
├── views/
│   └── app.php              # SPA completa Vue 3 (4.326 líneas)
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   ├── schema.sql           # Schema inicial (18 tablas)
│   ├── migrate_v2.sql       # delivery_type, external_link, short_desc
│   ├── migrate_v3.sql       # rut, rut_verified, birthdate
│   ├── migrate_v4.sql       # vendor_payment_accounts, vendor_bank_accounts, payments
│   └── migrate_v5.sql       # order_confirmations, order_disputes, notifications, order_messages
└── .env                     # Variables de entorno
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

### 4.1 Tablas del sistema (22 tablas totales)

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

---

## 5. Controladores y Funcionalidades

### 5.1 Mapa de controladores (11 clases)

#### `AuthController`
| Método | Función |
|---|---|
| `register()` | Registro con validación, hash bcrypt cost-12 |
| `login()` | Login + merge carrito guest al autenticarse |
| `logout()` | Revocación de token |
| `me()` | Perfil del usuario autenticado |
| `forgotPassword()` | Solicitud de reset con token TTL 2h |
| `resetPassword()` | Cambio de contraseña con token |

#### `ProductController`
| Método | Función |
|---|---|
| `index()` | Listado paginado con filtros, búsqueda FULLTEXT, ordenamiento |
| `show()` | Detalle con imágenes, atributos, variantes, reseñas |
| `store()` | Crear producto con cast de tipos MySQL, slug único |
| `update()` | Editar con control de ownership |
| `destroy()` | Soft delete (status = deleted) |
| `myProducts()` | Productos del vendedor autenticado |

#### `CartController`
| Método | Función |
|---|---|
| `index()` | Ver carrito (guest por cookie o autenticado) |
| `addItem()` | Agregar con validación de stock |
| `updateItem()` | Cambiar cantidad |
| `removeItem()` | Eliminar item |
| `clear()` | Vaciar carrito |

#### `OrderController`
| Método | Función |
|---|---|
| `index()` | Mis órdenes como comprador |
| `show()` | Detalle con items y tracking |
| `checkout()` | Crear orden atómica: valida stock, BEGIN TRANSACTION, INSERT, COMMIT, vacía carrito. Guarda seller_id, IVA 19%, comisión 5%, neto vendedor |

#### `OrderManagementController`
| Método | Función |
|---|---|
| `vendorOrders()` | Pedidos recibidos del vendedor con desglose financiero |
| `vendorOrderDetail()` | Detalle completo: comprador, RUT, dirección, productos, chat, historial |
| `vendorAccept()` | Protocolo paso 1 — vendedor acepta, notifica comprador |
| `vendorDispatch()` | Protocolo paso 2 — despacho con carrier + tracking, auto-complete en 7 días |
| `buyerConfirm()` | Protocolo paso 3 — comprador confirma, fondos liberados |
| `cancelOrder()` | Cancelación con restauración de stock |
| `openDispute()` | Abrir reclamo formal |
| `getNotifications()` | Centro de notificaciones con conteo no leídas |
| `markNotificationRead()` | Marcar como leída |
| `markAllRead()` | Marcar todas como leídas |
| `getMessages()` | Chat por orden |
| `sendMessage()` | Enviar mensaje con notificación al receptor |

#### `ProfileController`
| Método | Función |
|---|---|
| `show()` | Datos del perfil incluyendo RUT y birthdate |
| `update()` | Actualizar datos + validación RUT chileno (módulo 11) + inmutabilidad |
| `changePassword()` | Cambio con verificación actual + revocación de otros tokens |
| `uploadAvatar()` | Upload JPG/PNG/WebP máx. 2MB, elimina anterior |
| `deleteAvatar()` | Eliminar avatar |
| `getAddresses()` | Listar direcciones |
| `storeAddress()` | Crear dirección |
| `updateAddress()` | Editar dirección |
| `setDefault()` | Establecer como principal |
| `deleteAddress()` | Eliminar dirección |
| `deleteAccount()` | Soft delete de cuenta |

#### `ProductImageController`
| Método | Función |
|---|---|
| `store()` | Upload JPG/PNG/WebP máx. 5MB, valida MIME real, carpeta `/uploads/products/` |
| `updateOrder()` | Reordenar imágenes + cambiar primaria |
| `destroy()` | Eliminar imagen del disco + DB, reasigna primaria |

#### `MercadoPagoController`
| Método | Función |
|---|---|
| `authorize()` | Redirect OAuth a Mercado Pago |
| `oauthCallback()` | Recibe code, obtiene access_token, guarda en DB |
| `createPreference()` | Crea preferencia con `marketplace_fee` 5% |
| `webhookIPN()` | Confirma pago, actualiza orden |
| `accountStatus()` | Estado de conexión del vendedor |
| `disconnect()` | Desconectar cuenta MP |

#### `BankTransferController`
| Método | Función |
|---|---|
| `connectBankAccount()` | Registrar cuenta bancaria del vendedor |
| `savePaymentMethods()` | Guardar métodos habilitados (MP, billetera, transferencia, custom) |
| `getVendorPaymentMethods()` | Endpoint público — métodos del vendedor para checkout |
| `bankAccountStatus()` | Estado de conexión bancaria |
| `createPayment()` | Crear pago Khipu |
| `webhookConfirm()` | Confirmar transferencia bancaria |

#### `AdminController`
| Método | Función |
|---|---|
| `dashboard()` | KPIs: usuarios, ingresos, órdenes, top productos, chart 30 días |
| `users()` | Lista paginada con búsqueda |
| `updateUser()` | Cambiar rol/estado |
| `products()` | Lista paginada de todos los productos |
| `orders()` | Lista paginada con filtro por estado |
| `updateOrderStatus()` | Cambiar estado + agregar tracking |
| `auditLog()` | Log de auditoría paginado |

#### `ReviewController`
| Método | Función |
|---|---|
| `store()` | Crear reseña + actualizar `rating_avg` y `rating_count` del producto |

---

## 6. API REST — 76 Rutas

### Públicas (sin autenticación)
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/forgot-password
POST   /api/auth/reset-password
GET    /api/products
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
GET    /api/auth/me
POST   /api/auth/logout
GET    /api/my/products
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
POST   /api/products/{id}/images
PATCH  /api/products/{id}/images/order
DELETE /api/products/images/{imageId}
GET    /api/orders
GET    /api/orders/{id}
POST   /api/orders/checkout
POST   /api/orders/{id}/confirm
POST   /api/orders/{id}/cancel
POST   /api/orders/{id}/dispute
GET    /api/orders/{id}/messages
POST   /api/orders/{id}/messages
GET    /api/vendor/orders
GET    /api/vendor/orders/{id}
POST   /api/vendor/orders/{id}/accept
POST   /api/vendor/orders/{id}/dispatch
POST   /api/vendor/orders/{id}/cancel
GET    /api/vendor/mp/status
GET    /api/vendor/mp/authorize
GET    /api/vendor/mp/callback
POST   /api/vendor/mp/disconnect
POST   /api/payments/mercadopago/create
GET    /api/vendor/bank/status
POST   /api/vendor/bank-account/connect
POST   /api/vendor/payment-methods/save
POST   /api/payments/bank-transfer/create
POST   /api/reviews
GET    /api/profile
PATCH  /api/profile
POST   /api/profile/password
DELETE /api/profile
GET    /api/profile/addresses
POST   /api/profile/addresses
PUT    /api/profile/addresses/{id}
PATCH  /api/profile/addresses/{id}/default
DELETE /api/profile/addresses/{id}
POST   /api/profile/avatar
DELETE /api/profile/avatar
GET    /api/notifications
PATCH  /api/notifications/{id}/read
POST   /api/notifications/read-all
```

### Admin (auth + role admin)
```
GET    /api/admin/dashboard
GET    /api/admin/users
PATCH  /api/admin/users/{id}
GET    /api/admin/products
GET    /api/admin/orders
PATCH  /api/admin/orders/{id}/status
GET    /api/admin/audit-log
```

---

## 7. Módulos del Frontend (Vue 3 SPA)

### 7.1 Vistas implementadas

| Vista | Ruta interna | Descripción |
|---|---|---|
| Home | `home` | Hero banner + productos destacados |
| Catálogo | `products` | Grid con filtros sidebar + paginación |
| Detalle producto | `product-detail` | Galería + buy box + reseñas + descripción breve |
| Carrito | `cart` | Items + resumen + continuar compra |
| Checkout | `checkout` | 3 pasos: resumen → dirección → pago |
| Login | `login` | Formulario con validación |
| Registro | `register` | Formulario con validación |
| Mis compras | `orders` | Lista clickeable + comprobante completo |
| Mis ventas | `my-products` | Tabs: publicaciones / formulario / estadísticas |
| Mis pedidos | `vendor-orders` | Panel vendedor con protocolo de seguridad |
| Perfil | `profile` | 5 tabs: datos / contraseña / direcciones / métodos de pago / preferencias |
| Notificaciones | `notifications` | Centro con badge contador en navbar |
| Admin | `admin` | Dashboard / usuarios / productos / órdenes |

### 7.2 Componentes Vue reutilizables
- `<product-card>` — tarjeta con wishlist, imagen, precio, badge descuento
- `<ms-toast>` — notificaciones tipo toast
- Modales inline (eliminación, cancelación, formulario dirección)

---

## 8. Seguridad

### 8.1 Autenticación y autorización
- Tokens `random_bytes(32)` hex-encoded, 64 caracteres, guardados en DB con TTL
- Passwords `bcrypt` cost-12
- Revocación de tokens en logout y cambio de contraseña
- Middlewares: `AuthMiddleware`, `AdminMiddleware`, `RateLimitMiddleware` (60 req/min por IP)
- Verificación de ownership en todas las operaciones sensibles

### 8.2 Validación y sanitización
- Prepared statements PDO en todas las queries — SQL injection imposible
- Cast explícito de tipos antes de INSERT (int, float, TINYINT)
- Validación MIME real con `mime_content_type()` en uploads — no confía en extensión
- Validación RUT chileno con algoritmo módulo 11
- RUT inmutable una vez registrado
- Sanitización XSS en inputs

### 8.3 Protocolo de seguridad de órdenes (tipo escrow)
```
PAID → [vendedor acepta 24h] → PROCESSING
     → [vendedor despacha] → DISPATCHED
     → [comprador confirma O auto 7 días] → COMPLETED
     → fondos liberados al vendedor
```
- Cancelación restaura stock automáticamente
- Sistema de disputas formal con estados de resolución
- Auto-complete a los 7 días si comprador no confirma

### 8.4 Headers de seguridad
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Access-Control-Allow-Origin: [configurado en .env]
```

### 8.5 Otros controles
- Soft delete en productos, usuarios y órdenes (sin borrado físico)
- Audit log de todas las acciones admin
- `.env` excluido del repositorio vía `.gitignore`
- Carpeta `uploads/` excluida del repositorio
- `.DS_Store` excluido del repositorio

---

## 9. Usabilidad

### 9.1 Experiencia de compra
- Carrito persistente para usuarios guest (cookie `ms_cart`) y autenticados
- Merge automático del carrito guest al hacer login
- Checkout en 3 pasos lineales con stepper visual
- Métodos de pago configurados por el vendedor, visibles para el comprador
- Comprobante de compra imprimible con todos los datos

### 9.2 Experiencia del vendedor
- Formulario de publicación completo con galería drag & drop
- Panel de estadísticas (vistas, ventas, conversión)
- Protocolo de despacho con selector de carrier
- Chat directo con el comprador por orden
- Desglose financiero claro: precio → IVA → comisión → neto

### 9.3 Notificaciones
- Badge en navbar con contador de no leídas
- Notificaciones automáticas en cada cambio de estado de orden
- Notificación al recibir mensajes nuevos

### 9.4 Accesibilidad
- Identidad visual de la Comunidad Sorda de Chile
- Paleta azul `#1B4F8A` + dorado `#F4C430` de alta legibilidad
- Tipografía Helvetica Neue — sans-serif de máxima legibilidad
- Contraste WCAG AA en navbar y elementos principales

---

## 10. Métodos de Pago

### 10.1 Por el vendedor (configurable en perfil)

| Método | Implementación |
|---|---|
| Mercado Pago | OAuth por vendedor + link directo `link.mercadopago.cl` + API preferencias |
| Transferencia bancaria | Khipu API + datos bancarios manuales |
| Billetera digital | Mach, Tenpo, BICE, Copec Pay + instrucciones personalizadas |
| Texto libre | Instrucciones personalizadas 500 chars |

### 10.2 Desglose financiero por venta
```
Precio de venta:          $100.000
IVA 19% (incluido):      - $15.966
Subtotal neto:             $84.034
Comisión MercadoSordo 5%: - $5.000
──────────────────────────────────
Neto vendedor:             $95.000
```

---

## 11. Identidad Visual

| Elemento | Valor |
|---|---|
| Color primario | `#1B4F8A` — Azul profundo |
| Color secundario | `#0E3060` — Azul oscuro |
| Color acento | `#F4C430` — Dorado |
| Color acento oscuro | `#C9991A` — Dorado oscuro |
| Color de fondo | `#EBF2FB` — Azul muy suave |
| Color oscuro | `#0A1628` — Casi negro azulado |
| Tipografía | Helvetica Neue, Helvetica, Arial |
| Tipografía display | Sora (marca) |
| Radio de bordes | 8px estándar |
| Inspiración | Bandera de la Comunidad Sorda — paleta propia sin reproducir elementos gráficos |

---

## 12. Instalación y Configuración

### Requisitos
- PHP 8.1+ con extensiones: PDO, PDO_MySQL, fileinfo, curl
- MySQL 8.0+
- Apache con `mod_rewrite` / Nginx / PHP built-in server

### Pasos
```bash
# 1. Clonar repositorio
git clone https://github.com/NacAbarca/mercadosordo.git
cd mercadosordo

# 2. Configurar entorno
cp .env.example .env
# Editar .env con credenciales DB y claves de pago

# 3. Base de datos
mysql -u root -p -e "CREATE DATABASE mercadosordo CHARACTER SET utf8mb4;"
mysql -u root -p mercadosordo < database/schema.sql
mysql -u root -p mercadosordo < database/migrate_v2.sql
mysql -u root -p mercadosordo < database/migrate_v3.sql
mysql -u root -p mercadosordo < database/migrate_v4.sql
mysql -u root -p mercadosordo < database/migrate_v5.sql

# 4. Crear carpetas de uploads
mkdir -p public/uploads/products
mkdir -p public/uploads/avatars

# 5. Levantar servidor
php -S localhost:8080 -t public

# Producción (Nginx)
# Document root → /public
# try_files $uri $uri/ /index.php?$query_string;
```

### Variables de entorno requeridas
```env
APP_URL=https://mercadosordo.cl
DB_HOST=127.0.0.1
DB_NAME=mercadosordo
DB_USER=root
DB_PASS=

MP_CLIENT_ID=
MP_CLIENT_SECRET=
MP_APP_ID=
MP_LINK_PAGO=https://link.mercadopago.cl/mercadosordo

KHIPU_RECEIVER_ID=
KHIPU_SECRET=
```

---

## 13. Métricas del Proyecto

| Métrica | Valor |
|---|---|
| Líneas de código total | 6.409 líneas |
| Controladores PHP | 11 clases |
| Rutas API REST | 76 endpoints |
| Tablas de base de datos | 22 tablas |
| Migraciones | 5 versiones |
| Vistas SPA | 13 vistas |
| Archivos del proyecto | 15 archivos fuente |
| Dependencias externas | 0 (sin Composer) |
| Librerías CDN frontend | Bootstrap 5.3, Bootstrap Icons 1.11, Vue 3.4 |

---

## 14. Roadmap

### Pendiente de implementación
- [ ] Notificaciones email transaccionales (SMTP)
- [ ] WebSockets para chat en tiempo real
- [ ] Búsqueda avanzada con Meilisearch
- [ ] Sistema de cupones y descuentos
- [ ] App mobile (PWA)
- [ ] Deploy producción (VPS / Railway)
- [ ] Tests unitarios PHPUnit

---

## 15. Licencia y Créditos

**Desarrollado por:** Nac Abarca  
**GitHub:** [github.com/NacAbarca](https://github.com/NacAbarca)  
**Año:** 2026  
**Stack:** PHP 8.2 · Vue 3 · Bootstrap 5 · MySQL 8  

> *MercadoSordo — Hecho en Chile 🇨🇱 con identidad de la Comunidad Sorda*

---

*Este documento fue generado el 21 de Marzo de 2026.*
