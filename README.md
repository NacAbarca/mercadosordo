# 🔶 MercadoSordo — E-commerce Platform

> Stack: **PHP 8.2** · **Vue 3** · **Bootstrap 5.3** · **MySQL 8**  
> Arquitectura: **SPA + REST API** | Sin frameworks pesados | Nivel avanzado

---

## 📁 Estructura del Proyecto

```
mercadosordo/
├── config/
│   └── app.php              # Configuración general
├── database/
│   └── schema.sql           # Schema completo MySQL
├── public/                  # Document root del servidor
│   ├── index.php            # Entry point
│   ├── .htaccess            # Rewrite rules + seguridad
│   ├── css/                 # Assets compilados
│   ├── js/
│   └── uploads/             # Imágenes de productos
├── routes/
│   └── api.php              # Definición de todas las rutas
├── src/
│   ├── Core.php             # DB, Router, Request, Response, Auth, Middlewares
│   └── Controllers.php      # AuthController, ProductController, CartController,
│                            # OrderController, AdminController, ReviewController
├── views/
│   └── app.php              # SPA shell (Bootstrap 5 + Vue 3)
├── .env.example             # Plantilla de configuración
└── README.md
```

---

## 🗄️ Base de Datos (MySQL 8)

| Tabla              | Descripción                          |
|--------------------|--------------------------------------|
| `users`            | Usuarios (buyer / seller / admin)    |
| `user_tokens`      | Tokens JWT-style (auth, reset, API)  |
| `user_addresses`   | Direcciones de envío                 |
| `categories`       | Categorías con subcategorías (self-join) |
| `products`         | Catálogo de productos (FULLTEXT idx) |
| `product_images`   | Multi-imagen por producto            |
| `product_attributes` | Atributos clave-valor              |
| `product_variants` | Variantes (talla, color, etc.)       |
| `orders`           | Órdenes de compra                    |
| `order_items`      | Líneas de orden (snapshot de precio) |
| `order_tracking`   | Historial de estados                 |
| `carts`            | Carritos (sesión + usuario)          |
| `cart_items`       | Items del carrito                    |
| `reviews`          | Reseñas con moderación               |
| `wishlists`        | Favoritos por usuario                |
| `coupons`          | Cupones de descuento                 |
| `conversations`    | Mensajería comprador-vendedor        |
| `messages`         | Mensajes individuales                |
| `audit_logs`       | Log de auditoría                     |

---

## 🔌 API REST — Endpoints

### Auth `/api/auth`
| Método | Ruta                        | Auth | Descripción             |
|--------|-----------------------------|------|-------------------------|
| POST   | `/auth/register`            | ✗    | Registro de usuario     |
| POST   | `/auth/login`               | ✗    | Login → JWT token       |
| POST   | `/auth/logout`              | ✓    | Revocar token           |
| GET    | `/auth/me`                  | ✓    | Perfil del usuario      |
| POST   | `/auth/forgot-password`     | ✗    | Solicitar reset         |
| POST   | `/auth/reset-password`      | ✗    | Resetear contraseña     |

### Products `/api/products`
| Método | Ruta                        | Auth | Descripción                    |
|--------|-----------------------------|------|--------------------------------|
| GET    | `/products`                 | ✗    | Lista paginada + filtros       |
| GET    | `/products/{slug}`          | ✗    | Detalle con imágenes/reseñas   |
| POST   | `/products`                 | ✓    | Crear producto (seller)        |
| PUT    | `/products/{id}`            | ✓    | Editar producto (owner/admin)  |
| DELETE | `/products/{id}`            | ✓    | Soft delete                    |
| GET    | `/my/products`              | ✓    | Mis productos como vendedor    |

**Query params de `/products`:**
```
?q=iphone          → Búsqueda FULLTEXT
&category=electronica
&min_price=50000
&max_price=500000
&condition=new|used|refurbished
&sort=price_asc|price_desc|rating|sales|created_at_desc
&page=1
&per_page=20
```

### Cart `/api/cart`
| Método | Ruta                | Auth    | Descripción             |
|--------|---------------------|---------|-------------------------|
| GET    | `/cart`             | Guest✓  | Ver carrito             |
| POST   | `/cart/items`       | Guest✓  | Agregar item            |
| PATCH  | `/cart/items/{id}`  | Guest✓  | Cambiar cantidad        |
| DELETE | `/cart/items/{id}`  | Guest✓  | Eliminar item           |
| DELETE | `/cart`             | Guest✓  | Vaciar carrito          |

### Orders `/api/orders`
| Método | Ruta                    | Auth | Descripción         |
|--------|-------------------------|------|---------------------|
| GET    | `/orders`               | ✓    | Mis órdenes         |
| GET    | `/orders/{id}`          | ✓    | Detalle + tracking  |
| POST   | `/orders/checkout`      | ✓    | Crear orden         |

### Admin `/api/admin` (role: admin)
| Método | Ruta                           | Descripción              |
|--------|--------------------------------|--------------------------|
| GET    | `/admin/dashboard`             | KPIs + stats             |
| GET    | `/admin/users`                 | Lista usuarios           |
| PATCH  | `/admin/users/{id}`            | Editar usuario           |
| GET    | `/admin/products`              | Lista productos          |
| GET    | `/admin/orders`                | Lista órdenes            |
| PATCH  | `/admin/orders/{id}/status`    | Cambiar estado orden     |
| GET    | `/admin/audit-log`             | Log de auditoría         |

---

## 🔐 Autenticación

**Flujo:**
1. `POST /api/auth/login` → recibe `{ token, user }`
2. Guarda token en `localStorage` (SPA) o `HttpOnly Cookie`
3. Envía en cada request: `Authorization: Bearer {token}`
4. `AuthMiddleware` valida contra `user_tokens` en DB
5. Token revocado en logout → borrado de DB

**Roles:**
- `buyer` → comprar, reseñar, mensajear
- `seller` → buyer + publicar productos
- `admin` → acceso total al panel admin

---

## 🖥️ Frontend (Vue 3 SPA)

**Vistas incluidas:**
- 🏠 **Home** — Hero banner + productos destacados
- 🔍 **Productos** — Grid con filtros sidebar + paginación
- 📦 **Detalle de producto** — Galería + buy box + reseñas
- 🛒 **Carrito** — Items + resumen + checkout
- 🔑 **Login / Register** — Formularios con validación
- 📋 **Mis compras** — Historial de órdenes
- 🛡️ **Admin Panel** — Dashboard, usuarios, productos, órdenes

**Componentes:**
- `<product-card>` — Tarjeta reutilizable con wishlist
- Nav con búsqueda responsive
- Toast notifications
- Category nav con scroll horizontal

---

## ⚙️ Instalación

```bash
# 1. Configurar
cp .env.example .env
# Editar .env con tus credenciales DB

# 2. Base de datos
mysql -u root -p < database/schema.sql

# 3. Servidor web (Apache)
# Document root → /public
# O usar PHP built-in:
php -S localhost:8080 -t public

# 4. Producción (Nginx)
# Ver configuración abajo
```

### Nginx config
```nginx
server {
    listen 80;
    server_name mercadosordo.cl;
    root /var/www/mercadosordo/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~* \.(jpg|png|webp|css|js|woff2)$ { expires 1y; add_header Cache-Control "public"; }
}
```

---

## 🚀 Extensiones Sugeridas (próximos pasos)

- [ ] **WebPay Plus** (Transbank) — integración de pago real
- [ ] **File uploads** — S3 / local con resize de imágenes
- [ ] **Queue system** — emails asíncronos con Redis
- [ ] **WebSockets** — mensajería en tiempo real
- [ ] **Search** — Meilisearch/Elasticsearch para búsqueda avanzada
- [ ] **Cache** — Redis para productos y categorías
- [ ] **Tests** — PHPUnit para Controllers y servicios
- [ ] **CI/CD** — GitHub Actions + deploy automático

---

## 🔒 Seguridad implementada

- ✅ Passwords con `bcrypt` cost 12
- ✅ Tokens con `random_bytes(32)` hex-encoded
- ✅ Prepared statements en todas las queries (SQL injection ✗)
- ✅ Rate limiting por IP (60 req/min)
- ✅ CORS configurado
- ✅ Headers de seguridad (X-Frame, X-Content-Type, Referrer)
- ✅ Soft delete (no borrado real de productos/usuarios)
- ✅ Autorización por ownership en operaciones sensibles
- ✅ Audit log para cambios admin

---

*MercadoSordo — Hecho en Chile 🇨🇱*
