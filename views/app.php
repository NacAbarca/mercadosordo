<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="MercadoSordo — Compra y vende en Chile">
  <title>MercadoSordo</title>

  <!-- Bootstrap 5 (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --ms-yellow: #FFE600;
      --ms-yellow-dark: #F0D800;
      --ms-blue: #3483FA;
      --ms-blue-dark: #2968C8;
      --ms-green: #00A650;
      --ms-text: #1A1A2E;
      --ms-muted: #666;
      --ms-border: #e8e8e8;
      --ms-bg: #EDEDED;
      --ms-card: #ffffff;
      --ms-radius: 8px;
      --ms-shadow: 0 1px 4px rgba(0,0,0,.12);
      --ms-shadow-hover: 0 4px 16px rgba(0,0,0,.16);
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'Nunito', sans-serif;
      background: var(--ms-bg);
      color: var(--ms-text);
      min-height: 100vh;
    }

    /* ─── NAVBAR ─── */
    .navbar-ms {
      background: var(--ms-yellow);
      padding: 10px 0;
      position: sticky; top: 0; z-index: 1040;
      box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }
    .navbar-ms .brand {
      font-family: 'Sora', sans-serif;
      font-weight: 800;
      font-size: 1.5rem;
      color: var(--ms-text);
      text-decoration: none;
    }
    .navbar-ms .brand span { color: var(--ms-blue); }

    .search-bar { flex: 1; max-width: 600px; position: relative; }
    .search-bar input {
      border-radius: 4px 0 0 4px;
      border: none;
      padding: 10px 16px;
      font-size: .95rem;
      width: 100%;
      outline: none;
      box-shadow: inset 0 1px 3px rgba(0,0,0,.1);
    }
    .search-bar button {
      border-radius: 0 4px 4px 0;
      background: var(--ms-blue);
      color: white;
      border: none;
      padding: 10px 18px;
      cursor: pointer;
      transition: background .2s;
    }
    .search-bar button:hover { background: var(--ms-blue-dark); }

    .navbar-actions a, .navbar-actions button {
      color: var(--ms-text);
      text-decoration: none;
      font-size: .85rem;
      font-weight: 600;
      display: flex; flex-direction: column; align-items: center;
      gap: 2px;
      background: none; border: none; cursor: pointer;
      padding: 4px 10px;
      border-radius: 4px;
      transition: background .15s;
    }
    .navbar-actions a:hover, .navbar-actions button:hover { background: rgba(0,0,0,.07); }
    .navbar-actions i { font-size: 1.3rem; }
    .cart-badge {
      background: var(--ms-blue); color: white;
      font-size: .65rem; font-weight: 800;
      border-radius: 50%; width: 18px; height: 18px;
      display: flex; align-items: center; justify-content: center;
      position: absolute; top: -4px; right: -4px;
    }

    /* ─── CATEGORIES NAV ─── */
    .cat-nav {
      background: white;
      border-bottom: 1px solid var(--ms-border);
      overflow-x: auto;
    }
    .cat-nav::-webkit-scrollbar { height: 0; }
    .cat-nav ul {
      display: flex; gap: 0; list-style: none;
      margin: 0; padding: 0; white-space: nowrap;
    }
    .cat-nav a {
      display: flex; align-items: center; gap: 6px;
      padding: 10px 16px;
      font-size: .85rem; font-weight: 600; color: var(--ms-text);
      text-decoration: none; border-bottom: 2px solid transparent;
      transition: all .15s;
    }
    .cat-nav a:hover, .cat-nav a.active {
      color: var(--ms-blue);
      border-bottom-color: var(--ms-blue);
    }

    /* ─── HERO BANNER ─── */
    .hero-banner {
      background: linear-gradient(135deg, var(--ms-yellow) 0%, #FFD000 50%, var(--ms-blue) 100%);
      border-radius: var(--ms-radius);
      overflow: hidden;
      min-height: 300px;
      display: flex; align-items: center;
      position: relative;
    }
    .hero-banner::after {
      content: '';
      position: absolute; right: 0; top: 0; bottom: 0; width: 45%;
      background: url('/public/uploads/hero-products.png') center/contain no-repeat;
    }

    /* ─── PRODUCT CARD ─── */
    .product-card {
      background: var(--ms-card);
      border-radius: var(--ms-radius);
      box-shadow: var(--ms-shadow);
      transition: box-shadow .2s, transform .2s;
      overflow: hidden;
      cursor: pointer;
      position: relative;
    }
    .product-card:hover {
      box-shadow: var(--ms-shadow-hover);
      transform: translateY(-2px);
    }
    .product-card .img-wrap {
      background: #f8f8f8;
      display: flex; align-items: center; justify-content: center;
      height: 200px; overflow: hidden;
    }
    .product-card img { max-height: 100%; object-fit: contain; transition: transform .3s; }
    .product-card:hover img { transform: scale(1.04); }
    .product-card .card-body { padding: 14px; }
    .product-card .price {
      font-family: 'Sora', sans-serif;
      font-size: 1.35rem; font-weight: 700; color: var(--ms-text);
    }
    .product-card .compare-price {
      font-size: .82rem; color: var(--ms-muted); text-decoration: line-through;
    }
    .product-card .discount-badge {
      background: #E8F5E9; color: var(--ms-green);
      font-size: .75rem; font-weight: 700;
      padding: 2px 7px; border-radius: 3px;
    }
    .product-card .title {
      font-size: .88rem; color: #333;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
      overflow: hidden; line-height: 1.4; margin: 6px 0;
    }
    .product-card .free-ship {
      font-size: .78rem; color: var(--ms-green); font-weight: 700;
    }
    .product-card .rating { font-size: .78rem; color: #999; }
    .product-card .wish-btn {
      position: absolute; top: 10px; right: 10px;
      background: white; border: none; border-radius: 50%;
      width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
      box-shadow: 0 1px 4px rgba(0,0,0,.2); cursor: pointer;
      color: #ccc; font-size: 1rem; transition: color .2s;
    }
    .product-card .wish-btn.active, .product-card .wish-btn:hover { color: #e53935; }

    /* ─── PRODUCT DETAIL ─── */
    .product-images .main-img {
      border: 1px solid var(--ms-border); border-radius: var(--ms-radius);
      display: flex; align-items: center; justify-content: center;
      height: 400px; overflow: hidden; background: #f8f8f8;
    }
    .product-images .thumbs { display: flex; gap: 8px; margin-top: 10px; }
    .product-images .thumb {
      width: 72px; height: 72px; object-fit: contain;
      border: 2px solid transparent; border-radius: 4px;
      cursor: pointer; background: #f8f8f8;
    }
    .product-images .thumb.active { border-color: var(--ms-blue); }

    .buy-box {
      background: var(--ms-card); border-radius: var(--ms-radius);
      padding: 24px; box-shadow: var(--ms-shadow);
    }
    .btn-add-cart {
      background: var(--ms-blue); color: white; border: none;
      border-radius: 6px; padding: 14px; font-weight: 700; font-size: 1rem;
      width: 100%; transition: background .2s;
    }
    .btn-add-cart:hover { background: var(--ms-blue-dark); }
    .btn-buy-now {
      background: white; color: var(--ms-blue);
      border: 2px solid var(--ms-blue);
      border-radius: 6px; padding: 13px; font-weight: 700; font-size: 1rem;
      width: 100%; margin-top: 10px; transition: all .2s;
    }
    .btn-buy-now:hover { background: var(--ms-blue); color: white; }

    /* ─── CART ─── */
    .cart-item {
      background: white; border-radius: var(--ms-radius);
      padding: 16px; margin-bottom: 12px; box-shadow: var(--ms-shadow);
      display: flex; gap: 16px; align-items: flex-start;
    }
    .cart-item img { width: 80px; height: 80px; object-fit: contain; }
    .qty-ctrl { display: flex; align-items: center; gap: 10px; }
    .qty-ctrl button {
      width: 30px; height: 30px; border-radius: 50%;
      border: 1px solid var(--ms-border); background: white;
      font-weight: 700; cursor: pointer;
    }

    /* ─── ADMIN ─── */
    .admin-sidebar {
      width: 240px; min-height: 100vh;
      background: var(--ms-text); color: white;
      padding: 24px 0; flex-shrink: 0;
    }
    .admin-sidebar .brand {
      padding: 0 24px 24px;
      font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1.2rem;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }
    .admin-sidebar a {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 24px; color: rgba(255,255,255,.7);
      text-decoration: none; font-weight: 600; font-size: .9rem;
      transition: all .15s;
    }
    .admin-sidebar a:hover, .admin-sidebar a.active {
      background: rgba(255,255,255,.1); color: white;
      border-left: 3px solid var(--ms-yellow);
    }
    .stat-card {
      background: white; border-radius: var(--ms-radius);
      padding: 20px; box-shadow: var(--ms-shadow);
      border-left: 4px solid var(--ms-blue);
    }
    .stat-card.green  { border-color: var(--ms-green); }
    .stat-card.yellow { border-color: var(--ms-yellow-dark); }
    .stat-card.red    { border-color: #e53935; }
    .stat-value { font-size: 2rem; font-weight: 800; font-family: 'Sora', sans-serif; }

    /* ─── AUTH ─── */
    .auth-card {
      max-width: 420px; margin: 60px auto;
      background: white; border-radius: 12px;
      padding: 40px; box-shadow: 0 4px 24px rgba(0,0,0,.1);
    }
    .auth-card .logo {
      font-family: 'Sora', sans-serif; font-weight: 800; font-size: 1.8rem;
      color: var(--ms-text); margin-bottom: 6px;
    }
    .auth-card .logo span { color: var(--ms-blue); }
    .form-control:focus {
      border-color: var(--ms-blue);
      box-shadow: 0 0 0 3px rgba(52,131,250,.2);
    }
    .btn-primary-ms {
      background: var(--ms-blue); color: white; border: none;
      border-radius: 6px; padding: 12px; font-weight: 700; width: 100%;
      transition: background .2s;
    }
    .btn-primary-ms:hover { background: var(--ms-blue-dark); }

    /* ─── TOAST ─── */
    .ms-toast-wrap {
      position: fixed; bottom: 24px; right: 24px; z-index: 9999;
      display: flex; flex-direction: column; gap: 10px;
    }
    .ms-toast {
      background: #333; color: white; padding: 12px 20px;
      border-radius: 8px; font-size: .9rem; font-weight: 600;
      box-shadow: 0 4px 12px rgba(0,0,0,.3);
      animation: slideIn .3s ease;
    }
    .ms-toast.success { background: var(--ms-green); }
    .ms-toast.error   { background: #e53935; }
    @keyframes slideIn { from { transform: translateX(120%); opacity:0; } to { transform: translateX(0); opacity:1; } }

    /* ─── UTILITIES ─── */
    .section-title { font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.2rem; }
    .stars { color: #f5a623; letter-spacing: 1px; }
    .badge-condition { font-size: .72rem; font-weight: 700; padding: 2px 8px; border-radius: 3px; }
    .badge-new  { background: #E3F2FD; color: #1565C0; }
    .badge-used { background: #FFF3E0; color: #E65100; }
    .page-loader {
      position: fixed; inset: 0; background: var(--ms-yellow);
      display: flex; align-items: center; justify-content: center;
      z-index: 9999; transition: opacity .3s;
    }
    [v-cloak] { display: none; }

    @media (max-width: 768px) {
      .admin-sidebar { display: none; }
      .search-bar input { font-size: .85rem; }
      .product-card .price { font-size: 1.1rem; }
    }
  </style>
</head>
<body>
<div id="app" v-cloak>
  <!-- Page Loader -->
  <div class="page-loader" v-if="appLoading">
    <div class="text-center">
      <div class="brand" style="font-family:Sora,sans-serif;font-weight:800;font-size:2rem;">
        Mercado<span style="color:#3483FA">Sordo</span>
      </div>
      <div class="mt-3">
        <div class="spinner-border" style="color:#3483FA"></div>
      </div>
    </div>
  </div>

  <!-- ─── NAVBAR ─── -->
  <nav class="navbar-ms" v-if="!isAdminRoute">
    <div class="container-fluid px-3 d-flex align-items-center gap-3">
      <a href="#" class="brand" @click.prevent="navigate('home')">
        Mercado<span>Sordo</span>
      </a>
      <div class="search-bar d-none d-md-flex">
        <input type="text" :placeholder="'Buscar en ' + (activeCategory || 'todo MercadoSordo')"
               v-model="searchQuery" @keyup.enter="doSearch">
        <button @click="doSearch"><i class="bi bi-search"></i></button>
      </div>
      <div class="navbar-actions d-flex align-items-center ms-auto gap-1">
        <template v-if="!auth.user">
          <a href="#" @click.prevent="navigate('login')">
            <i class="bi bi-person"></i><span>Ingresar</span>
          </a>
          <a href="#" @click.prevent="navigate('register')">
            <i class="bi bi-person-plus"></i><span>Registrarse</span>
          </a>
        </template>
        <template v-else>
          <div class="dropdown">
            <button class="navbar-actions d-flex flex-column align-items-center" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i>
              <span>{{ auth.user.name.split(' ')[0] }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#" @click.prevent="navigate('profile')"><i class="bi bi-person me-2"></i>Mi perfil</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="navigate('orders')"><i class="bi bi-box me-2"></i>Mis compras</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="navigate('my-products')"><i class="bi bi-grid me-2"></i>Mis ventas</a></li>
              <li v-if="auth.user.role === 'admin'"><a class="dropdown-item" href="#" @click.prevent="navigate('admin')"><i class="bi bi-shield me-2"></i>Admin Panel</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="#" @click.prevent="logout"><i class="bi bi-box-arrow-right me-2"></i>Salir</a></li>
            </ul>
          </div>
        </template>
        <a href="#" @click.prevent="navigate('cart')" style="position:relative">
          <i class="bi bi-cart3"></i>
          <span>Carrito</span>
          <span class="cart-badge" v-if="cart.count > 0">{{ cart.count }}</span>
        </a>
      </div>
    </div>
    <!-- Mobile search -->
    <div class="container-fluid px-3 mt-2 d-md-none">
      <div class="search-bar d-flex">
        <input type="text" placeholder="Buscar productos..." v-model="searchQuery" @keyup.enter="doSearch">
        <button @click="doSearch"><i class="bi bi-search"></i></button>
      </div>
    </div>
  </nav>

  <!-- BANNER ALERTA RUT -->
  <div v-if="auth.user && !auth.user.rut && !isAdminRoute"
       style="background:#e53935;color:white;padding:8px 16px;font-size:.85rem;font-weight:700;text-align:center;cursor:pointer"
       @click="profileTab='data'; navigate('profile')"
       class="d-flex align-items-center justify-content-center gap-2">
    <i class="bi bi-exclamation-triangle-fill"></i>
    Debes completar tu RUT chileno para operar en MercadoSordo — Haz clic aquí para completarlo
    <i class="bi bi-arrow-right-circle-fill"></i>
  </div>

  <!-- CATEGORIES NAV -->
  <nav class="cat-nav" v-if="!isAdminRoute">
    <div class="container-fluid">
      <ul>
        <li><a href="#" :class="{active: !activeCategory}" @click.prevent="filterByCategory(null)">Todos</a></li>
        <li v-for="cat in categories" :key="cat.id">
          <a href="#" :class="{active: activeCategory === cat.slug}" @click.prevent="filterByCategory(cat.slug)">
            <i :class="'bi ' + cat.icon"></i> {{ cat.name }}
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- ─── VIEWS ─── -->
  <div class="container-fluid py-3" v-if="!isAdminRoute && currentView !== 'product-detail' && currentView !== 'cart'">

    <!-- HOME -->
    <template v-if="currentView === 'home'">
      <!-- Hero Banner -->
      <div class="hero-banner p-4 mb-4">
        <div style="max-width:55%">
          <p class="text-muted mb-1" style="font-size:.9rem">Ofertas de hoy</p>
          <h1 class="fw-800 mb-2" style="font-family:Sora,sans-serif; font-size:2.2rem; line-height:1.2">
            Descuentos que <span style="color:var(--ms-blue)">no puedes</span> perderte
          </h1>
          <p class="mb-3">Envío gratis en miles de productos</p>
          <button class="btn btn-dark px-4 py-2 fw-bold" @click="navigate('products')">
            Ver ofertas <i class="bi bi-arrow-right ms-1"></i>
          </button>
        </div>
      </div>

      <!-- Featured sections -->
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="section-title mb-0">Más vendidos</h2>
        <a href="#" class="text-primary text-decoration-none small fw-bold" @click.prevent="navigate('products')">Ver todo <i class="bi bi-chevron-right"></i></a>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-6 col-sm-4 col-md-3 col-xl-2" v-for="p in (products.data || []).slice(0,12)" :key="p.id">
          <product-card :product="p" @add-cart="addToCart" @wishlist="toggleWishlist" @click="viewProduct(p)"></product-card>
        </div>
      </div>
      <div class="text-center" v-if="products.loading">
        <div class="spinner-border text-primary"></div>
      </div>
    </template>

    <!-- PRODUCTS LISTING -->
    <template v-if="currentView === 'products'">
      <div class="row g-3">
        <!-- Filters sidebar -->
        <div class="col-md-2">
          <div class="bg-white rounded p-3 shadow-sm">
            <h6 class="fw-bold">Filtros</h6>
            <div class="mb-3">
              <label class="form-label small fw-bold">Precio</label>
              <div class="d-flex gap-2">
                <input type="number" class="form-control form-control-sm" placeholder="Mín" v-model="filters.min_price">
                <input type="number" class="form-control form-control-sm" placeholder="Máx" v-model="filters.max_price">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Condición</label>
              <div v-for="c in ['new','used','refurbished']" :key="c" class="form-check">
                <input type="radio" class="form-check-input" :id="c" v-model="filters.condition" :value="c">
                <label class="form-check-label small" :for="c">{{ {new:'Nuevo',used:'Usado',refurbished:'Reacondicionado'}[c] }}</label>
              </div>
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="freeShip" v-model="filters.free_shipping">
                <label class="form-check-label small" for="freeShip">Envío gratis</label>
              </div>
            </div>
            <button class="btn btn-sm btn-primary w-100" @click="applyFilters">Aplicar</button>
            <button class="btn btn-sm btn-outline-secondary w-100 mt-2" @click="resetFilters">Limpiar</button>
          </div>
        </div>
        <!-- Results -->
        <div class="col-md-10">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted small">{{ products.total }} resultados</span>
            <select class="form-select form-select-sm w-auto" v-model="filters.sort" @change="applyFilters">
              <option value="created_at_desc">Más recientes</option>
              <option value="price_asc">Menor precio</option>
              <option value="price_desc">Mayor precio</option>
              <option value="rating">Mejor valorados</option>
              <option value="sales">Más vendidos</option>
            </select>
          </div>
          <div class="row g-3">
            <div class="col-6 col-sm-4 col-lg-3" v-for="p in (products.data || [])" :key="p.id">
              <product-card :product="p" @add-cart="addToCart" @wishlist="toggleWishlist" @click="viewProduct(p)"></product-card>
            </div>
          </div>
          <div class="d-flex justify-content-center mt-4">
            <nav>
              <ul class="pagination">
                <li class="page-item" :class="{disabled: products.current_page <= 1}">
                  <a class="page-link" href="#" @click.prevent="changePage(products.current_page-1)">‹</a>
                </li>
                <li class="page-item" v-for="p in (products.last_page || 1)" :key="p" :class="{active: p===products.current_page}">
                  <a class="page-link" href="#" @click.prevent="changePage(p)">{{p}}</a>
                </li>
                <li class="page-item" :class="{disabled: products.current_page >= products.last_page}">
                  <a class="page-link" href="#" @click.prevent="changePage(products.current_page+1)">›</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </template>

    <!-- AUTH VIEWS -->
    <template v-if="currentView === 'login'">
      <div class="auth-card">
        <div class="logo">Mercado<span>Sordo</span></div>
        <p class="text-muted mb-4">Ingresa a tu cuenta</p>
        <div class="mb-3">
          <label class="form-label fw-bold">Email</label>
          <input type="email" class="form-control" v-model="loginForm.email" @keyup.enter="doLogin">
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Contraseña</label>
          <input type="password" class="form-control" v-model="loginForm.password" @keyup.enter="doLogin">
        </div>
        <button class="btn-primary-ms mb-3" @click="doLogin" :disabled="auth.loading">
          <span v-if="auth.loading" class="spinner-border spinner-border-sm me-2"></span>
          Ingresar
        </button>
        <div class="text-center">
          <a href="#" @click.prevent="navigate('register')" class="text-primary text-decoration-none">¿No tienes cuenta? Regístrate</a>
        </div>
        <div v-if="auth.error" class="alert alert-danger mt-3 mb-0 small">{{ auth.error }}</div>
      </div>
    </template>

    <template v-if="currentView === 'register'">
      <div class="auth-card">
        <div class="logo">Mercado<span>Sordo</span></div>
        <p class="text-muted mb-4">Crea tu cuenta gratis</p>
        <div class="mb-3"><label class="form-label fw-bold">Nombre completo</label>
          <input type="text" class="form-control" v-model="registerForm.name"></div>
        <div class="mb-3"><label class="form-label fw-bold">Email</label>
          <input type="email" class="form-control" v-model="registerForm.email"></div>
        <div class="mb-3"><label class="form-label fw-bold">Contraseña</label>
          <input type="password" class="form-control" v-model="registerForm.password"></div>
        <button class="btn-primary-ms mb-3" @click="doRegister" :disabled="auth.loading">
          <span v-if="auth.loading" class="spinner-border spinner-border-sm me-2"></span>
          Registrarme
        </button>
        <div class="text-center">
          <a href="#" @click.prevent="navigate('login')" class="text-primary text-decoration-none">¿Ya tienes cuenta? Ingresa</a>
        </div>
        <div v-if="auth.error" class="alert alert-danger mt-3 mb-0 small">{{ auth.error }}</div>
      </div>
    </template>

    <!-- MY ORDERS -->
    <template v-if="currentView === 'orders'">
      <h3 class="section-title mb-4">Mis compras</h3>
      <div v-if="ordersLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      <div v-else>
        <div v-for="o in orders" :key="o.id" class="bg-white rounded p-4 mb-3 shadow-sm">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="fw-bold">{{ o.order_number }}</span>
              <span class="text-muted small ms-2">{{ formatDate(o.created_at) }}</span>
            </div>
            <span class="badge" :class="statusBadge(o.status)">{{ statusLabel(o.status) }}</span>
          </div>
          <div class="mt-2 text-muted small">{{ o.items_count }} productos · Total: {{ formatCLP(o.total) }}</div>
        </div>
        <div v-if="orders.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-box fs-1"></i><p class="mt-2">No tienes compras aún</p>
        </div>
      </div>
    </template>


    <!-- ─── MIS VENTAS ─── -->
    <template v-if="currentView === 'my-products'">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="section-title mb-0"><i class="bi bi-shop me-2 text-primary"></i>Mis ventas</h3>
        <button class="btn btn-primary btn-sm fw-bold px-3" @click="openProductForm(null)">
          <i class="bi bi-plus-lg me-1"></i>Publicar producto
        </button>
      </div>

      <!-- TABS -->
      <ul class="nav nav-tabs mb-3" id="sellerTabs">
        <li class="nav-item">
          <a class="nav-link" :class="{active: sellerTab==='list'}" href="#" @click.prevent="sellerTab='list'">
            <i class="bi bi-grid-3x3-gap me-1"></i>Mis publicaciones
            <span class="badge bg-primary ms-1" v-if="myProducts.data.length">{{ myProducts.data.length }}</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" :class="{active: sellerTab==='form'}" href="#" @click.prevent="openProductForm(null)">
            <i class="bi bi-plus-circle me-1"></i>{{ productForm.id ? 'Editar producto' : 'Nuevo producto' }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" :class="{active: sellerTab==='stats'}" href="#" @click.prevent="sellerTab='stats'">
            <i class="bi bi-bar-chart me-1"></i>Estadísticas
          </a>
        </li>
      </ul>

      <!-- ── TAB: LISTADO ── -->
      <div v-if="sellerTab === 'list'">
        <div v-if="myProductsLoading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2 text-muted small">Cargando tus publicaciones...</p>
        </div>
        <div v-else>
          <!-- Filtro rápido -->
          <div class="bg-white rounded p-3 mb-3 shadow-sm d-flex gap-2 flex-wrap align-items-center">
            <input type="text" class="form-control form-control-sm w-auto flex-grow-1"
                   placeholder="Buscar en mis productos..." v-model="myProductSearch">
            <select class="form-select form-select-sm w-auto" v-model="myProductStatusFilter">
              <option value="">Todos los estados</option>
              <option value="active">Activos</option>
              <option value="paused">Pausados</option>
              <option value="draft">Borrador</option>
              <option value="sold_out">Sin stock</option>
            </select>
            <span class="text-muted small">{{ filteredMyProducts.length }} productos</span>
          </div>

          <!-- Tabla -->
          <div class="bg-white rounded shadow-sm overflow-hidden" v-if="filteredMyProducts.length > 0">
            <table class="table table-striped table-hover align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th style="width:60px">Foto</th>
                  <th>Título</th>
                  <th class="d-none d-md-table-cell">Categoría</th>
                  <th>Precio</th>
                  <th class="d-none d-sm-table-cell">Stock</th>
                  <th class="d-none d-md-table-cell">Condición</th>
                  <th>Estado</th>
                  <th class="d-none d-lg-table-cell">Vistas</th>
                  <th class="d-none d-lg-table-cell">Ventas</th>
                  <th style="width:130px">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in filteredMyProducts" :key="p.id">
                  <td>
                    <img :src="p.primary_image || 'https://via.placeholder.com/48'"
                         class="rounded" style="width:48px;height:48px;object-fit:contain;background:#f8f8f8">
                  </td>
                  <td>
                    <div class="fw-bold" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" :title="p.title">{{ p.title }}</div>
                    <div class="text-muted" style="font-size:.75rem">SKU: {{ p.sku || '—' }}</div>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <span class="badge bg-light text-dark border">{{ getCategoryName(p.category_id) }}</span>
                  </td>
                  <td>
                    <div class="fw-bold text-primary">{{ formatCLP(p.price) }}</div>
                    <div class="text-muted text-decoration-line-through" style="font-size:.75rem" v-if="p.compare_price">{{ formatCLP(p.compare_price) }}</div>
                  </td>
                  <td class="d-none d-sm-table-cell">
                    <span :class="p.stock <= 5 ? 'text-danger fw-bold' : 'text-success fw-bold'">{{ p.stock }}</span>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <span class="badge" :class="{'bg-info text-dark': p.condition_type==='new', 'bg-warning text-dark': p.condition_type==='used', 'bg-secondary': p.condition_type==='refurbished'}">
                      {{ {new:'Nuevo', used:'Usado', refurbished:'Reacond.'}[p.condition_type] || p.condition_type }}
                    </span>
                  </td>
                  <td>
                    <div class="form-check form-switch mb-0" style="min-width:80px">
                      <input class="form-check-input" type="checkbox"
                             :checked="p.status === 'active'"
                             @change="toggleProductStatus(p)"
                             :id="'sw-'+p.id">
                      <label class="form-check-label small" :for="'sw-'+p.id">
                        {{ p.status === 'active' ? 'Activo' : p.status === 'paused' ? 'Pausado' : p.status }}
                      </label>
                    </div>
                  </td>
                  <td class="d-none d-lg-table-cell text-muted small">{{ p.views || 0 }}</td>
                  <td class="d-none d-lg-table-cell text-muted small">{{ p.sales_count || 0 }}</td>
                  <td>
                    <div class="d-flex gap-1">
                      <button class="btn btn-sm btn-outline-primary" title="Editar" @click="openProductForm(p)">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-info" title="Ver publicación" @click="viewProduct(p)">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" title="Eliminar" @click="confirmDeleteProduct(p)">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty state -->
          <div v-else class="text-center py-5 bg-white rounded shadow-sm">
            <i class="bi bi-bag-x" style="font-size:3rem;color:#ccc"></i>
            <p class="mt-3 fw-bold">No tienes publicaciones aún</p>
            <p class="text-muted small mb-3">Comienza a vender publicando tu primer producto</p>
            <button class="btn btn-primary" @click="openProductForm(null)">
              <i class="bi bi-plus-lg me-1"></i>Publicar ahora
            </button>
          </div>
        </div>
      </div>

      <!-- ── TAB: FORMULARIO ── -->
      <div v-if="sellerTab === 'form'">
        <div class="bg-white rounded shadow-sm p-4">
          <h5 class="fw-bold mb-4">
            <i class="bi bi-pencil-square me-2 text-primary"></i>
            {{ productForm.id ? 'Editar publicación' : 'Nueva publicación' }}
          </h5>

          <form @submit.prevent="submitProductForm" novalidate>
            <div class="row g-3">

              <!-- Título -->
              <div class="col-12">
                <label class="form-label fw-bold">Título <span class="text-danger">*</span></label>
                <input type="text" class="form-control" :class="{'is-invalid': formErrors.title}"
                       v-model="productForm.title" placeholder="Ej: iPhone 14 Pro 256GB Negro Espacial"
                       maxlength="255">
                <div class="d-flex justify-content-between">
                  <div class="invalid-feedback">{{ formErrors.title }}</div>
                  <small class="text-muted ms-auto">{{ productForm.title.length }}/255</small>
                </div>
              </div>

              <!-- Descripción breve -->
              <div class="col-12">
                <label class="form-label fw-bold">Descripción breve <span class="text-danger">*</span></label>
                <input type="text" class="form-control" :class="{'is-invalid': formErrors.short_desc}"
                       v-model="productForm.short_desc"
                       placeholder="Resumen en una línea para listados y resultados de búsqueda"
                       maxlength="160">
                <div class="d-flex justify-content-between">
                  <div class="invalid-feedback">{{ formErrors.short_desc }}</div>
                  <small class="text-muted ms-auto">{{ productForm.short_desc.length }}/160</small>
                </div>
              </div>

              <!-- Descripción completa -->
              <div class="col-12">
                <label class="form-label fw-bold">Descripción completa</label>
                <textarea class="form-control" v-model="productForm.description"
                          placeholder="Describe el producto en detalle: características, estado, accesorios incluidos, etc."
                          rows="5" maxlength="5000"></textarea>
                <small class="text-muted">{{ productForm.description.length }}/5000 caracteres</small>
              </div>

              <!-- Categoría -->
              <div class="col-md-6">
                <label class="form-label fw-bold">Categoría <span class="text-danger">*</span></label>
                <select class="form-select" :class="{'is-invalid': formErrors.category_id}"
                        v-model="productForm.category_id">
                  <option value="">— Selecciona una categoría —</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">
                    {{ c.name }}
                  </option>
                </select>
                <div class="invalid-feedback">{{ formErrors.category_id }}</div>
              </div>

              <!-- Condición -->
              <div class="col-md-6">
                <label class="form-label fw-bold">Condición <span class="text-danger">*</span></label>
                <div class="d-flex gap-2 mt-1">
                  <div v-for="cond in [{v:'new',l:'Nuevo',i:'bi-stars'},{v:'used',l:'Usado',i:'bi-recycle'},{v:'refurbished',l:'Reacondicionado',i:'bi-tools'}]"
                       :key="cond.v"
                       class="condition-opt flex-fill text-center p-2 rounded border"
                       :class="productForm.condition_type === cond.v ? 'border-primary bg-primary bg-opacity-10 fw-bold' : 'border-light-subtle'"
                       style="cursor:pointer"
                       @click="productForm.condition_type = cond.v">
                    <i :class="'bi ' + cond.i + ' d-block mb-1'" style="font-size:1.3rem"></i>
                    <small>{{ cond.l }}</small>
                  </div>
                </div>
              </div>

              <!-- Precio -->
              <div class="col-md-4">
                <label class="form-label fw-bold">Precio (CLP) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="number" class="form-control" :class="{'is-invalid': formErrors.price}"
                         v-model.number="productForm.price" placeholder="0" min="0">
                  <div class="invalid-feedback">{{ formErrors.price }}</div>
                </div>
                <small class="text-muted" v-if="productForm.price > 0">{{ formatCLP(productForm.price) }}</small>
              </div>

              <!-- Precio comparación -->
              <div class="col-md-4">
                <label class="form-label fw-bold">Precio tachado <span class="text-muted fw-normal small">(opcional)</span></label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="number" class="form-control" v-model.number="productForm.compare_price"
                         placeholder="Precio antes del descuento" min="0">
                </div>
                <small class="text-success fw-bold" v-if="productForm.compare_price > productForm.price && productForm.price > 0">
                  {{ Math.round((1 - productForm.price / productForm.compare_price) * 100) }}% de descuento
                </small>
              </div>

              <!-- Stock -->
              <div class="col-md-4">
                <label class="form-label fw-bold">Stock disponible <span class="text-danger">*</span></label>
                <input type="number" class="form-control" :class="{'is-invalid': formErrors.stock}"
                       v-model.number="productForm.stock" placeholder="0" min="0">
                <div class="invalid-feedback">{{ formErrors.stock }}</div>
              </div>

              <!-- Tipo de entrega -->
              <div class="col-md-6">
                <label class="form-label fw-bold">Tipo de entrega <span class="text-danger">*</span></label>
                <div class="d-flex flex-column gap-2 mt-1">
                  <div v-for="del in [
                    {v:'shipping', l:'Envío a domicilio', i:'bi-truck', d:'Despacho por courier'},
                    {v:'pickup',   l:'Retiro en persona', i:'bi-person-walking', d:'El comprador retira'},
                    {v:'both',     l:'Ambas opciones',    i:'bi-arrow-left-right', d:'Envío o retiro'}
                  ]" :key="del.v"
                    class="d-flex align-items-center gap-3 p-2 rounded border"
                    :class="productForm.delivery_type === del.v ? 'border-primary bg-primary bg-opacity-10' : ''"
                    style="cursor:pointer"
                    @click="productForm.delivery_type = del.v">
                    <i :class="'bi ' + del.i + ' text-primary'" style="font-size:1.4rem;width:24px"></i>
                    <div>
                      <div class="fw-bold small">{{ del.l }}</div>
                      <div class="text-muted" style="font-size:.75rem">{{ del.d }}</div>
                    </div>
                    <i class="bi bi-check-circle-fill text-primary ms-auto" v-if="productForm.delivery_type === del.v"></i>
                  </div>
                </div>
                <div class="mt-2">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="freeShip" v-model="productForm.free_shipping">
                    <label class="form-check-label small fw-bold text-success" for="freeShip">
                      <i class="bi bi-truck me-1"></i>Envío gratis
                    </label>
                  </div>
                </div>
              </div>

              <!-- SKU y Link externo -->
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">SKU / Código interno <span class="text-muted fw-normal small">(opcional)</span></label>
                  <input type="text" class="form-control" v-model="productForm.sku"
                         placeholder="Ej: APPL-IP14-256-BLK">
                </div>
                <div>
                  <label class="form-label fw-bold">Link externo / referencia <span class="text-muted fw-normal small">(opcional)</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                    <input type="url" class="form-control" :class="{'is-invalid': formErrors.external_link}"
                           v-model="productForm.external_link"
                           placeholder="https://...">
                    <div class="invalid-feedback">{{ formErrors.external_link }}</div>
                  </div>
                  <small class="text-muted">Ficha técnica, tienda oficial, MercadoLibre, etc.</small>
                </div>
              </div>

              <!-- Alertas y opciones extra -->
              <div class="col-12">
                <div class="bg-light rounded p-3">
                  <div class="row g-2">
                    <div class="col-md-4">
                      <label class="form-label small fw-bold">Alerta de stock mínimo</label>
                      <input type="number" class="form-control form-control-sm" v-model.number="productForm.stock_alert"
                             min="0" placeholder="5">
                      <small class="text-muted">Avísame cuando quede este stock</small>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label small fw-bold">Peso (kg)</label>
                      <input type="number" class="form-control form-control-sm" v-model.number="productForm.weight_kg"
                             min="0" step="0.1" placeholder="0.0">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label small fw-bold">Estado de publicación</label>
                      <select class="form-select form-select-sm" v-model="productForm.status">
                        <option value="active">Activo — visible en el catálogo</option>
                        <option value="draft">Borrador — solo yo lo veo</option>
                        <option value="paused">Pausado — oculto temporalmente</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

            </div><!-- /row -->

            <!-- Errores globales -->
            <div class="alert alert-danger mt-3 mb-0" v-if="formErrors._global">
              <i class="bi bi-exclamation-triangle me-2"></i>{{ formErrors._global }}
            </div>

            <!-- Botones -->
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
              <button type="submit" class="btn btn-primary fw-bold px-4" :disabled="productFormLoading">
                <span v-if="productFormLoading" class="spinner-border spinner-border-sm me-2"></span>
                <i class="bi bi-cloud-upload me-1" v-else></i>
                {{ productForm.id ? 'Guardar cambios' : 'Publicar producto' }}
              </button>
              <button type="button" class="btn btn-outline-secondary" @click="sellerTab='list'">
                <i class="bi bi-x-lg me-1"></i>Cancelar
              </button>
              <button type="button" class="btn btn-outline-warning ms-auto" v-if="productForm.id"
                      @click="productForm.status = (productForm.status==='active'?'paused':'active')">
                <i class="bi bi-pause-circle me-1"></i>
                {{ productForm.status === 'active' ? 'Pausar' : 'Activar' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── TAB: ESTADÍSTICAS ── -->
      <div v-if="sellerTab === 'stats'">
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-3">
            <div class="bg-white rounded p-3 shadow-sm text-center">
              <div class="fw-bold" style="font-size:1.8rem;color:var(--ms-blue)">{{ myProducts.data.length }}</div>
              <div class="text-muted small">Publicaciones</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="bg-white rounded p-3 shadow-sm text-center">
              <div class="fw-bold" style="font-size:1.8rem;color:var(--ms-green)">
                {{ myProducts.data.filter(p=>p.status==='active').length }}
              </div>
              <div class="text-muted small">Activos</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="bg-white rounded p-3 shadow-sm text-center">
              <div class="fw-bold" style="font-size:1.8rem;color:var(--ms-yellow-dark)">
                {{ myProducts.data.reduce((a,p)=>a+(p.views||0),0) }}
              </div>
              <div class="text-muted small">Vistas totales</div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="bg-white rounded p-3 shadow-sm text-center">
              <div class="fw-bold" style="font-size:1.8rem;color:var(--ms-green)">
                {{ myProducts.data.reduce((a,p)=>a+(p.sales_count||0),0) }}
              </div>
              <div class="text-muted small">Ventas totales</div>
            </div>
          </div>
        </div>
        <!-- Top 5 productos por ventas -->
        <div class="bg-white rounded shadow-sm p-4">
          <h6 class="fw-bold mb-3"><i class="bi bi-trophy me-2 text-warning"></i>Top productos por ventas</h6>
          <table class="table table-striped table-sm mb-0">
            <thead class="table-light">
              <tr><th>#</th><th>Producto</th><th>Precio</th><th>Ventas</th><th>Vistas</th><th>Conversión</th></tr>
            </thead>
            <tbody>
              <tr v-for="(p,i) in [...myProducts.data].sort((a,b)=>(b.sales_count||0)-(a.sales_count||0)).slice(0,5)" :key="p.id">
                <td><span class="badge" :class="['bg-warning text-dark','bg-secondary','bg-secondary','bg-secondary','bg-secondary'][i]">{{ i+1 }}</span></td>
                <td class="fw-bold">{{ p.title }}</td>
                <td>{{ formatCLP(p.price) }}</td>
                <td><span class="text-success fw-bold">{{ p.sales_count || 0 }}</span></td>
                <td>{{ p.views || 0 }}</td>
                <td>
                  <span class="small" v-if="p.views > 0">
                    {{ ((p.sales_count||0) / p.views * 100).toFixed(1) }}%
                  </span>
                  <span class="text-muted small" v-else>—</span>
                </td>
              </tr>
              <tr v-if="myProducts.data.length === 0">
                <td colspan="6" class="text-center text-muted py-3">Sin datos aún</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </template>


    <!-- ─── PERFIL DE USUARIO ─── -->
    <template v-if="currentView === 'profile'">
      <div class="row g-4">

        <!-- Sidebar perfil -->
        <div class="col-md-3">
          <div class="bg-white rounded shadow-sm p-4 text-center mb-3">
            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:80px;height:80px;font-size:2rem;color:white">
              {{ auth.user?.name?.charAt(0).toUpperCase() }}
            </div>
            <h5 class="fw-bold mb-0">{{ auth.user?.name }}</h5>
            <p class="text-muted small mb-1">{{ auth.user?.email }}</p>
            <span class="badge" :class="{'bg-danger':auth.user?.role==='admin','bg-info':auth.user?.role==='seller','bg-secondary':auth.user?.role==='buyer'}">
              {{ auth.user?.role }}
            </span>
            <div class="mt-3 pt-3 border-top text-start">
              <div class="small text-muted mb-1"><i class="bi bi-calendar me-1"></i>Miembro desde {{ formatDate(auth.user?.created_at) }}</div>
            </div>
          </div>
          <!-- Nav lateral -->
          <div class="bg-white rounded shadow-sm overflow-hidden">
            <a href="#" class="d-flex align-items-center gap-2 px-3 py-3 border-bottom text-decoration-none"
               :class="profileTab==='data' ? 'bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'"
               @click.prevent="profileTab='data'">
              <i class="bi bi-person-gear"></i> Mis datos
            </a>
            <a href="#" class="d-flex align-items-center gap-2 px-3 py-3 border-bottom text-decoration-none"
               :class="profileTab==='password' ? 'bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'"
               @click.prevent="profileTab='password'">
              <i class="bi bi-shield-lock"></i> Contraseña
            </a>
            <a href="#" class="d-flex align-items-center gap-2 px-3 py-3 border-bottom text-decoration-none"
               :class="profileTab==='addresses' ? 'bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'"
               @click.prevent="profileTab='addresses'">
              <i class="bi bi-geo-alt"></i> Direcciones
            </a>
            <a href="#" class="d-flex align-items-center gap-2 px-3 py-3 text-decoration-none"
               :class="profileTab==='prefs' ? 'bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'"
               @click.prevent="profileTab='prefs'">
              <i class="bi bi-sliders"></i> Preferencias
            </a>
          </div>
        </div>

        <!-- Contenido principal -->
        <div class="col-md-9">

          <!-- ── TAB: MIS DATOS ── -->
          <div v-if="profileTab==='data'" class="bg-white rounded shadow-sm p-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-person-gear me-2 text-primary"></i>Mis datos personales</h5>
            <form @submit.prevent="saveProfileData" novalidate>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Nombre completo <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" :class="{'is-invalid': profileErrors.name}"
                         v-model="profileData.name" placeholder="Tu nombre completo">
                  <div class="invalid-feedback">{{ profileErrors.name }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Teléfono</label>
                  <div class="input-group">
                    <span class="input-group-text">+56</span>
                    <input type="tel" class="form-control" v-model="profileData.phone"
                           placeholder="9 1234 5678" maxlength="12">
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold">Email</label>
                  <input type="email" class="form-control bg-light" :value="auth.user?.email" disabled>
                  <small class="text-muted">El email no puede modificarse. Contacta soporte si necesitas cambiarlo.</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">
                    RUT <span class="text-danger">*</span>
                    <span class="badge bg-success ms-1" v-if="auth.user?.rut">
                      <i class="bi bi-shield-check me-1"></i>Verificado
                    </span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                    <input type="text" class="form-control"
                           :class="{'is-invalid': profileErrors.rut, 'bg-light': auth.user?.rut}"
                           v-model="profileData.rut"
                           :disabled="!!auth.user?.rut"
                           :placeholder="auth.user?.rut ? auth.user.rut : '12.345.678-9'"
                           maxlength="12"
                           @input="formatRutInput"
                           @blur="formatRutInput">
                    <div class="invalid-feedback">{{ profileErrors.rut }}</div>
                  </div>
                  <small class="text-danger fw-bold" v-if="!auth.user?.rut">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Campo obligatorio — una vez guardado no puede modificarse.
                  </small>
                  <small class="text-muted" v-if="auth.user?.rut">
                    <i class="bi bi-lock me-1"></i>El RUT no puede modificarse por razones de seguridad.
                  </small>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Fecha de nacimiento <span class="text-muted fw-normal small">(opcional)</span></label>
                  <input type="date" class="form-control" v-model="profileData.birthdate">
                </div>
              </div>
              <div class="alert alert-danger mt-3" v-if="profileErrors._global">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ profileErrors._global }}
              </div>
              <div class="alert alert-success mt-3" v-if="profileSuccess">
                <i class="bi bi-check-circle me-2"></i>{{ profileSuccess }}
              </div>
              <div class="mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary fw-bold px-4" :disabled="profileLoading">
                  <span v-if="profileLoading" class="spinner-border spinner-border-sm me-2"></span>
                  <i class="bi bi-cloud-check me-1" v-else></i>Guardar cambios
                </button>
              </div>
            </form>
          </div>

          <!-- ── TAB: CONTRASEÑA ── -->
          <div v-if="profileTab==='password'" class="bg-white rounded shadow-sm p-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-shield-lock me-2 text-primary"></i>Cambiar contraseña</h5>
            <form @submit.prevent="savePassword" novalidate>
              <div class="row g-3" style="max-width:480px">
                <div class="col-12">
                  <label class="form-label fw-bold">Contraseña actual <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input :type="showPwd.current ? 'text' : 'password'" class="form-control"
                           :class="{'is-invalid': pwdErrors.current}" v-model="pwdForm.current">
                    <button type="button" class="btn btn-outline-secondary" @click="showPwd.current=!showPwd.current">
                      <i class="bi" :class="showPwd.current ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                    <div class="invalid-feedback">{{ pwdErrors.current }}</div>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold">Nueva contraseña <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input :type="showPwd.new ? 'text' : 'password'" class="form-control"
                           :class="{'is-invalid': pwdErrors.new}" v-model="pwdForm.new">
                    <button type="button" class="btn btn-outline-secondary" @click="showPwd.new=!showPwd.new">
                      <i class="bi" :class="showPwd.new ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                    <div class="invalid-feedback">{{ pwdErrors.new }}</div>
                  </div>
                  <!-- Indicador de fuerza -->
                  <div class="mt-2" v-if="pwdForm.new">
                    <div class="progress" style="height:4px">
                      <div class="progress-bar" :class="pwdStrength.color"
                           :style="{width: pwdStrength.pct + '%'}"></div>
                    </div>
                    <small :class="pwdStrength.textColor">{{ pwdStrength.label }}</small>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold">Confirmar nueva contraseña <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input :type="showPwd.confirm ? 'text' : 'password'" class="form-control"
                           :class="{'is-invalid': pwdErrors.confirm}" v-model="pwdForm.confirm">
                    <button type="button" class="btn btn-outline-secondary" @click="showPwd.confirm=!showPwd.confirm">
                      <i class="bi" :class="showPwd.confirm ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                    <div class="invalid-feedback">{{ pwdErrors.confirm }}</div>
                  </div>
                </div>
              </div>
              <div class="alert alert-danger mt-3" v-if="pwdErrors._global">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ pwdErrors._global }}
              </div>
              <div class="alert alert-success mt-3" v-if="pwdSuccess">
                <i class="bi bi-check-circle me-2"></i>{{ pwdSuccess }}
              </div>
              <div class="mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary fw-bold px-4" :disabled="profileLoading">
                  <span v-if="profileLoading" class="spinner-border spinner-border-sm me-2"></span>
                  <i class="bi bi-lock me-1" v-else></i>Cambiar contraseña
                </button>
              </div>
            </form>
          </div>

          <!-- ── TAB: DIRECCIONES ── -->
          <div v-if="profileTab==='addresses'">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2 text-primary"></i>Mis direcciones</h5>
              <button class="btn btn-primary btn-sm" @click="openAddressForm(null)">
                <i class="bi bi-plus-lg me-1"></i>Nueva dirección
              </button>
            </div>

            <!-- Lista de direcciones -->
            <div v-if="addressesLoading" class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            <div v-else>
              <div v-for="addr in addresses" :key="addr.id"
                   class="bg-white rounded shadow-sm p-3 mb-3 d-flex align-items-start gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px">
                  <i class="bi bi-house text-primary"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fw-bold">{{ addr.label }}</span>
                    <span class="badge bg-success" v-if="addr.is_default">Principal</span>
                  </div>
                  <div class="text-muted small">{{ addr.full_name }}</div>
                  <div class="text-muted small">{{ addr.address }}, {{ addr.city }}, {{ addr.region }}</div>
                  <div class="text-muted small" v-if="addr.zip_code">CP: {{ addr.zip_code }}</div>
                </div>
                <div class="d-flex flex-column gap-1">
                  <button class="btn btn-sm btn-outline-primary" @click="openAddressForm(addr)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-success" v-if="!addr.is_default"
                          @click="setDefaultAddress(addr.id)" title="Establecer como principal">
                    <i class="bi bi-star"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteAddress(addr.id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>

              <div v-if="addresses.length === 0" class="text-center py-5 bg-white rounded shadow-sm">
                <i class="bi bi-geo-alt" style="font-size:3rem;color:#ccc"></i>
                <p class="mt-3 fw-bold">Sin direcciones guardadas</p>
                <button class="btn btn-primary" @click="openAddressForm(null)">
                  <i class="bi bi-plus-lg me-1"></i>Agregar dirección
                </button>
              </div>
            </div>

            <!-- Formulario dirección (modal inline) -->
            <div v-if="addressForm.show"
                 style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem"
                 @click.self="addressForm.show=false">
              <div class="bg-white rounded p-4 shadow w-100" style="max-width:540px;max-height:90vh;overflow-y:auto">
                <h5 class="fw-bold mb-4">
                  <i class="bi bi-geo-alt me-2 text-primary"></i>
                  {{ addressForm.id ? 'Editar dirección' : 'Nueva dirección' }}
                </h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-bold">Etiqueta</label>
                    <select class="form-select" v-model="addressForm.label">
                      <option>Casa</option><option>Trabajo</option><option>Otro</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre receptor <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" :class="{'is-invalid': addressErrors.full_name}"
                           v-model="addressForm.full_name" placeholder="Nombre completo">
                    <div class="invalid-feedback">{{ addressErrors.full_name }}</div>
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-bold">Dirección <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" :class="{'is-invalid': addressErrors.address}"
                           v-model="addressForm.address" placeholder="Calle, número, depto/oficina">
                    <div class="invalid-feedback">{{ addressErrors.address }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-bold">Ciudad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" :class="{'is-invalid': addressErrors.city}"
                           v-model="addressForm.city" placeholder="Santiago">
                    <div class="invalid-feedback">{{ addressErrors.city }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-bold">Región <span class="text-danger">*</span></label>
                    <select class="form-select" :class="{'is-invalid': addressErrors.region}"
                            v-model="addressForm.region">
                      <option value="">— Selecciona —</option>
                      <option>Región Metropolitana</option>
                      <option>Región de Valparaíso</option>
                      <option>Región del Biobío</option>
                      <option>Región de La Araucanía</option>
                      <option>Región de Los Lagos</option>
                      <option>Región de Coquimbo</option>
                      <option>Región de Antofagasta</option>
                      <option>Región de Atacama</option>
                      <option>Región de Tarapacá</option>
                      <option>Región de Arica y Parinacota</option>
                      <option>Región de O'Higgins</option>
                      <option>Región del Maule</option>
                      <option>Región de Ñuble</option>
                      <option>Región de Los Ríos</option>
                      <option>Región de Aysén</option>
                      <option>Región de Magallanes</option>
                    </select>
                    <div class="invalid-feedback">{{ addressErrors.region }}</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-bold">Código postal <span class="text-muted fw-normal small">(opcional)</span></label>
                    <input type="text" class="form-control" v-model="addressForm.zip_code" placeholder="1234567">
                  </div>
                  <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="defaultAddr" v-model="addressForm.is_default">
                      <label class="form-check-label fw-bold" for="defaultAddr">
                        <i class="bi bi-star me-1 text-warning"></i>Dirección principal
                      </label>
                    </div>
                  </div>
                </div>
                <div class="alert alert-danger mt-3" v-if="addressErrors._global">{{ addressErrors._global }}</div>
                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                  <button class="btn btn-primary fw-bold" @click="saveAddress" :disabled="profileLoading">
                    <span v-if="profileLoading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ addressForm.id ? 'Guardar cambios' : 'Agregar dirección' }}
                  </button>
                  <button class="btn btn-outline-secondary" @click="addressForm.show=false">Cancelar</button>
                </div>
              </div>
            </div>
          </div>

          <!-- ── TAB: PREFERENCIAS ── -->
          <div v-if="profileTab==='prefs'" class="bg-white rounded shadow-sm p-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-sliders me-2 text-primary"></i>Preferencias</h5>
            <div class="row g-4">
              <div class="col-12">
                <h6 class="fw-bold text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.8px">Notificaciones</h6>
                <div class="d-flex flex-column gap-3 mt-2">
                  <div v-for="pref in notifPrefs" :key="pref.key"
                       class="d-flex align-items-center justify-content-between p-3 rounded border">
                    <div>
                      <div class="fw-bold small">{{ pref.label }}</div>
                      <div class="text-muted" style="font-size:.78rem">{{ pref.desc }}</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" v-model="pref.value"
                             style="width:2.5rem;height:1.25rem">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <h6 class="fw-bold text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.8px">Privacidad</h6>
                <div class="d-flex flex-column gap-3 mt-2">
                  <div v-for="pref in privacyPrefs" :key="pref.key"
                       class="d-flex align-items-center justify-content-between p-3 rounded border">
                    <div>
                      <div class="fw-bold small">{{ pref.label }}</div>
                      <div class="text-muted" style="font-size:.78rem">{{ pref.desc }}</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" v-model="pref.value"
                             style="width:2.5rem;height:1.25rem">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 pt-2 border-top">
                <button class="btn btn-primary fw-bold px-4" @click="savePrefs">
                  <i class="bi bi-cloud-check me-1"></i>Guardar preferencias
                </button>
              </div>
              <!-- Zona de peligro -->
              <div class="col-12">
                <h6 class="fw-bold text-danger text-uppercase" style="font-size:.75rem;letter-spacing:.8px">
                  <i class="bi bi-exclamation-triangle me-1"></i>Zona de peligro
                </h6>
                <div class="border border-danger rounded p-3 mt-2">
                  <p class="small text-muted mb-3">Eliminar tu cuenta borra permanentemente todos tus datos, productos y órdenes. Esta acción no puede deshacerse.</p>
                  <button class="btn btn-outline-danger btn-sm fw-bold" @click="confirmDeleteAccount=true">
                    <i class="bi bi-trash me-1"></i>Eliminar mi cuenta
                  </button>
                </div>
              </div>
            </div>
            <!-- Confirm delete account -->
            <div v-if="confirmDeleteAccount"
                 style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9001;display:flex;align-items:center;justify-content:center;padding:1rem">
              <div class="bg-white rounded p-4 shadow" style="max-width:380px">
                <h5 class="fw-bold text-danger mb-3"><i class="bi bi-trash me-2"></i>¿Eliminar cuenta?</h5>
                <p class="text-muted small mb-3">Esta acción es irreversible. Escribe <strong>ELIMINAR</strong> para confirmar.</p>
                <input type="text" class="form-control mb-3" v-model="deleteAccountConfirmText" placeholder="ELIMINAR">
                <div class="d-flex gap-2">
                  <button class="btn btn-outline-secondary" @click="confirmDeleteAccount=false;deleteAccountConfirmText=''">Cancelar</button>
                  <button class="btn btn-danger fw-bold" :disabled="deleteAccountConfirmText !== 'ELIMINAR'" @click="doDeleteAccount">
                    Sí, eliminar mi cuenta
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </template>


  </div>

  <!-- ─── PRODUCT DETAIL (full-width) ─── -->
  <div class="container py-4" v-if="currentView === 'product-detail' && selectedProduct">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#" @click.prevent="navigate('home')">Inicio</a></li>
        <li class="breadcrumb-item"><a href="#" @click.prevent="navigate('products')">{{ selectedProduct.category_name }}</a></li>
        <li class="breadcrumb-item active text-truncate" style="max-width:200px">{{ selectedProduct.title }}</li>
      </ol>
    </nav>
    <div class="row g-4">
      <!-- Images -->
      <div class="col-md-6">
        <div class="product-images">
          <div class="main-img">
            <img :src="activeImage || '/public/uploads/no-image.png'" :alt="selectedProduct.title" style="max-height:100%;object-fit:contain">
          </div>
          <div class="thumbs" v-if="selectedProduct.images?.length > 1">
            <img v-for="img in selectedProduct.images" :key="img.id" :src="img.url" class="thumb" :class="{active: activeImage===img.url}" @click="activeImage=img.url">
          </div>
        </div>
      </div>
      <!-- Info + Buy Box -->
      <div class="col-md-6">
        <div class="mb-1">
          <span class="badge-condition" :class="selectedProduct.condition_type === 'new' ? 'badge-new' : 'badge-used'">
            {{ selectedProduct.condition_type === 'new' ? 'Nuevo' : 'Usado' }}
          </span>
          <span class="text-muted small ms-2">{{ selectedProduct.sales_count }} vendidos</span>
        </div>
        <h1 class="fs-4 fw-bold mb-2">{{ selectedProduct.title }}</h1>
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="stars">{{ '★'.repeat(Math.round(selectedProduct.rating_avg)) }}{{ '☆'.repeat(5-Math.round(selectedProduct.rating_avg)) }}</span>
          <span class="text-muted small">({{ selectedProduct.rating_count }} reseñas)</span>
        </div>
        <div class="buy-box">
          <div v-if="selectedProduct.compare_price" class="compare-price mb-1">{{ formatCLP(selectedProduct.compare_price) }}</div>
          <div class="price mb-1">{{ formatCLP(selectedProduct.price) }}</div>
          <div v-if="selectedProduct.compare_price" class="discount-badge d-inline-block mb-2">
            {{ Math.round((1 - selectedProduct.price/selectedProduct.compare_price)*100) }}% OFF
          </div>
          <!-- Descripción breve -->
          <div v-if="selectedProduct.short_desc || selectedProduct.meta_desc"
               class="mb-3 pb-3 border-bottom text-muted"
               style="font-size:.93rem;line-height:1.5">
            {{ selectedProduct.short_desc || selectedProduct.meta_desc }}
          </div>
          <div v-if="selectedProduct.free_shipping" class="free-ship mb-3"><i class="bi bi-truck me-1"></i>Envío gratis</div>
          <div class="mb-3">
            <span class="fw-bold">Stock: </span>
            <span :class="selectedProduct.stock < 5 ? 'text-danger fw-bold' : 'text-success'">
              {{ selectedProduct.stock > 0 ? selectedProduct.stock + ' disponibles' : 'Sin stock' }}
            </span>
          </div>
          <div class="d-flex align-items-center gap-3 mb-3">
            <label class="fw-bold">Cantidad:</label>
            <div class="qty-ctrl">
              <button @click="detailQty = Math.max(1, detailQty-1)">−</button>
              <span class="fw-bold">{{ detailQty }}</span>
              <button @click="detailQty = Math.min(selectedProduct.stock, detailQty+1)">+</button>
            </div>
          </div>
          <button class="btn-add-cart" @click="addToCart(selectedProduct, detailQty)" :disabled="selectedProduct.stock === 0">
            <i class="bi bi-cart-plus me-2"></i>Agregar al carrito
          </button>
          <button class="btn-buy-now">Comprar ahora</button>
          <div class="mt-3 pt-3 border-top">
            <small class="text-muted d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-person-circle"></i>Vendido por <strong>{{ selectedProduct.seller_name }}</strong>
            </small>
            <small class="text-muted d-flex align-items-center gap-2">
              <i class="bi bi-shield-check text-success"></i>Compra protegida
            </small>
          </div>
        </div>
        <!-- Description -->
        <div class="mt-4">
          <h5 class="fw-bold">Descripción</h5>
          <p class="text-muted" style="white-space:pre-wrap">{{ selectedProduct.description }}</p>
        </div>
        <!-- Attributes -->
        <div v-if="selectedProduct.attributes?.length" class="mt-3">
          <h5 class="fw-bold">Características</h5>
          <table class="table table-sm table-striped">
            <tbody>
              <tr v-for="a in selectedProduct.attributes" :key="a.id">
                <td class="fw-bold text-muted" style="width:40%">{{ a.attr_key }}</td>
                <td>{{ a.attr_value }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- Reviews -->
    <div class="mt-5">
      <h4 class="fw-bold mb-3">Reseñas ({{ selectedProduct.rating_count }})</h4>
      <div v-for="r in selectedProduct.reviews" :key="r.id" class="bg-white rounded p-3 mb-3 shadow-sm">
        <div class="d-flex align-items-center gap-2 mb-1">
          <strong>{{ r.reviewer_name }}</strong>
          <span class="stars small">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5-r.rating) }}</span>
        </div>
        <p class="mb-0 text-muted small">{{ r.body }}</p>
      </div>
      <div v-if="!selectedProduct.reviews?.length" class="text-muted">Sin reseñas aún.</div>
    </div>
  </div>

  <!-- ─── CART VIEW ─── -->
  <div class="container py-4" v-if="currentView === 'cart'">
    <h2 class="section-title mb-4">Tu carrito ({{ cart.count }})</h2>
    <div class="row g-4">
      <div class="col-md-8">
        <div v-if="cart.items.length === 0" class="text-center py-5 text-muted bg-white rounded shadow-sm">
          <i class="bi bi-cart3 fs-1"></i><p class="mt-2">Tu carrito está vacío</p>
          <button class="btn btn-primary" @click="navigate('home')">Ver productos</button>
        </div>
        <div v-for="item in cart.items" :key="item.id" class="cart-item">
          <img :src="item.image || '/public/uploads/no-image.png'" :alt="item.title">
          <div class="flex-grow-1">
            <p class="fw-bold mb-1">{{ item.title }}</p>
            <p class="text-success small mb-2" v-if="item.free_shipping"><i class="bi bi-truck me-1"></i>Envío gratis</p>
            <div class="qty-ctrl">
              <button @click="updateCartItem(item.id, item.quantity - 1)">−</button>
              <span class="fw-bold">{{ item.quantity }}</span>
              <button @click="updateCartItem(item.id, item.quantity + 1)">+</button>
              <button class="ms-3 btn btn-sm btn-outline-danger" @click="removeCartItem(item.id)">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
          <div class="text-end">
            <div class="price">{{ formatCLP(item.price * item.quantity) }}</div>
            <small class="text-muted">{{ formatCLP(item.price) }} c/u</small>
          </div>
        </div>
      </div>
      <div class="col-md-4" v-if="cart.items.length > 0">
        <div class="buy-box">
          <h5 class="fw-bold mb-3">Resumen</h5>
          <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span><span>{{ formatCLP(cart.total) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-2 text-success">
            <span>Envío</span><span>Gratis</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
            <span>Total</span><span>{{ formatCLP(cart.total) }}</span>
          </div>
          <button class="btn-add-cart" @click="auth.user ? (checkRut() && navigate('checkout')) : navigate('login')">
            Continuar compra
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ─── ADMIN PANEL ─── -->
  <div class="d-flex" v-if="isAdminRoute">
    <div class="admin-sidebar">
      <div class="brand">🔶 MercadoSordo<br><small style="font-weight:400;font-size:.75rem;opacity:.6">Panel Admin</small></div>
      <nav class="mt-3">
        <a href="#" :class="{active: adminView==='dashboard'}" @click.prevent="adminView='dashboard'; loadDashboard()">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="#" :class="{active: adminView==='users'}" @click.prevent="adminView='users'; loadAdminUsers()">
          <i class="bi bi-people"></i> Usuarios
        </a>
        <a href="#" :class="{active: adminView==='products'}" @click.prevent="adminView='products'; loadAdminProducts()">
          <i class="bi bi-grid"></i> Productos
        </a>
        <a href="#" :class="{active: adminView==='orders'}" @click.prevent="adminView='orders'; loadAdminOrders()">
          <i class="bi bi-bag"></i> Órdenes
        </a>
        <a href="#" :class="{active: adminView==='categories'}" @click.prevent="adminView='categories'">
          <i class="bi bi-tags"></i> Categorías
        </a>
        <a href="#" @click.prevent="navigate('home')">
          <i class="bi bi-arrow-left-circle"></i> Volver al sitio
        </a>
        <a href="#" @click.prevent="logout" class="text-danger-emphasis">
          <i class="bi bi-box-arrow-right"></i> Salir
        </a>
      </nav>
    </div>
    <div class="flex-grow-1 p-4" style="background:var(--ms-bg); min-height:100vh">
      <!-- Dashboard -->
      <template v-if="adminView === 'dashboard'">
        <h2 class="section-title mb-4">Dashboard</h2>
        <div class="row g-3 mb-4">
          <div class="col-6 col-lg-3">
            <div class="stat-card">
              <div class="text-muted small">Usuarios totales</div>
              <div class="stat-value">{{ adminDash.users_total || 0 }}</div>
              <small class="text-success">+{{ adminDash.users_today || 0 }} hoy</small>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="stat-card green">
              <div class="text-muted small">Ingresos hoy</div>
              <div class="stat-value">{{ formatCLP(adminDash.revenue_today || 0) }}</div>
              <small class="text-muted">{{ formatCLP(adminDash.revenue_month || 0) }} este mes</small>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="stat-card yellow">
              <div class="text-muted small">Productos activos</div>
              <div class="stat-value">{{ adminDash.products_total || 0 }}</div>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="stat-card red">
              <div class="text-muted small">Órdenes hoy</div>
              <div class="stat-value">{{ adminDash.orders_today || 0 }}</div>
            </div>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-8">
            <div class="bg-white rounded p-4 shadow-sm">
              <h6 class="fw-bold mb-3">Órdenes recientes</h6>
              <table class="table table-sm">
                <thead><tr><th>#</th><th>Comprador</th><th>Total</th><th>Estado</th></tr></thead>
                <tbody>
                  <tr v-for="o in adminDash.recent_orders" :key="o.id">
                    <td class="fw-bold">{{ o.order_number }}</td>
                    <td>{{ o.buyer }}</td>
                    <td>{{ formatCLP(o.total) }}</td>
                    <td><span class="badge" :class="statusBadge(o.status)">{{ statusLabel(o.status) }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white rounded p-4 shadow-sm">
              <h6 class="fw-bold mb-3">Top Productos</h6>
              <div v-for="(p,i) in adminDash.top_products" :key="i" class="d-flex justify-content-between mb-2">
                <span class="small text-truncate" style="max-width:60%">{{ p.title }}</span>
                <span class="badge bg-primary">{{ p.sales_count }} ventas</span>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- ADMIN USERS -->
      <template v-if="adminView === 'users'">
        <h2 class="section-title mb-4">Usuarios</h2>
        <div class="bg-white rounded shadow-sm">
          <div class="p-3 border-bottom d-flex gap-2">
            <input type="text" class="form-control w-auto" placeholder="Buscar..." v-model="adminSearch" @keyup.enter="loadAdminUsers">
            <button class="btn btn-primary" @click="loadAdminUsers"><i class="bi bi-search"></i></button>
          </div>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
              <tbody>
                <tr v-for="u in adminUsers" :key="u.id">
                  <td>{{ u.id }}</td>
                  <td>{{ u.name }}</td>
                  <td>{{ u.email }}</td>
                  <td><span class="badge" :class="{'bg-danger':u.role==='admin','bg-info':u.role==='seller','bg-secondary':u.role==='buyer'}">{{ u.role }}</span></td>
                  <td><span class="badge" :class="{'bg-success':u.status==='active','bg-warning':u.status==='pending','bg-danger':u.status==='suspended'}">{{ u.status }}</span></td>
                  <td class="small text-muted">{{ formatDate(u.created_at) }}</td>
                  <td>
                    <select class="form-select form-select-sm w-auto d-inline" @change="updateAdminUser(u.id, {status: $event.target.value})">
                      <option value="active" :selected="u.status==='active'">Activo</option>
                      <option value="suspended" :selected="u.status==='suspended'">Suspender</option>
                    </select>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- ADMIN PRODUCTS -->
      <template v-if="adminView === 'products'">
        <h2 class="section-title mb-4">Productos</h2>
        <div class="bg-white rounded shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light"><tr><th>ID</th><th>Título</th><th>Vendedor</th><th>Precio</th><th>Stock</th><th>Estado</th></tr></thead>
              <tbody>
                <tr v-for="p in adminProducts" :key="p.id">
                  <td>{{ p.id }}</td>
                  <td class="fw-bold">{{ p.title }}</td>
                  <td>{{ p.seller }}</td>
                  <td>{{ formatCLP(p.price) }}</td>
                  <td>{{ p.stock }}</td>
                  <td><span class="badge" :class="{'bg-success':p.status==='active','bg-secondary':p.status==='draft','bg-warning':p.status==='paused'}">{{ p.status }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- ADMIN ORDERS -->
      <template v-if="adminView === 'orders'">
        <h2 class="section-title mb-4">Órdenes</h2>
        <div class="bg-white rounded shadow-sm">
          <div class="p-3 border-bottom">
            <select class="form-select w-auto" v-model="adminOrderFilter" @change="loadAdminOrders">
              <option value="">Todos los estados</option>
              <option value="pending">Pendiente</option>
              <option value="paid">Pagado</option>
              <option value="shipped">Enviado</option>
              <option value="delivered">Entregado</option>
              <option value="cancelled">Cancelado</option>
            </select>
          </div>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light"><tr><th>#</th><th>Comprador</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Cambiar estado</th></tr></thead>
              <tbody>
                <tr v-for="o in adminOrders" :key="o.id">
                  <td class="fw-bold">{{ o.order_number }}</td>
                  <td>{{ o.buyer }}</td>
                  <td>{{ formatCLP(o.total) }}</td>
                  <td><span class="badge" :class="statusBadge(o.status)">{{ statusLabel(o.status) }}</span></td>
                  <td class="small text-muted">{{ formatDate(o.created_at) }}</td>
                  <td>
                    <select class="form-select form-select-sm w-auto" @change="updateOrderStatus(o.id, $event.target.value)">
                      <option value="pending">Pendiente</option>
                      <option value="paid">Pagado</option>
                      <option value="processing">Procesando</option>
                      <option value="shipped">Enviado</option>
                      <option value="delivered">Entregado</option>
                      <option value="cancelled">Cancelado</option>
                    </select>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </div>


  <!-- Modal Confirmar Eliminación -->
  <div v-if="deleteConfirm.show" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;display:flex;align-items:center;justify-content:center" @click.self="deleteConfirm.show=false">
    <div class="bg-white rounded p-4 shadow" style="max-width:380px;width:90%;margin:1rem">
      <h5 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Eliminar producto</h5>
      <p class="text-muted mb-4">¿Estás seguro que quieres eliminar <strong>{{ deleteConfirm.product?.title }}</strong>? Esta acción no se puede deshacer.</p>
      <div class="d-flex gap-2 justify-content-end">
        <button class="btn btn-outline-secondary" @click="deleteConfirm.show=false">Cancelar</button>
        <button class="btn btn-danger fw-bold" @click="doDeleteProduct">
          <i class="bi bi-trash me-1"></i>Sí, eliminar
        </button>
      </div>
    </div>
  </div>

  <!-- TOASTS -->
  <div class="ms-toast-wrap">
    <div v-for="t in toasts" :key="t.id" class="ms-toast" :class="t.type">{{ t.msg }}</div>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Vue 3 -->
<script src="https://cdn.jsdelivr.net/npm/vue@3.4.21/dist/vue.global.prod.js"></script>

<script>
const { createApp, ref, computed, onMounted, watch } = Vue;

// ─── Product Card Component ───────────────────────────────
const ProductCard = {
  props: ['product'],
  emits: ['add-cart', 'wishlist', 'click'],
  template: `
    <div class="product-card" @click="$emit('click', product)">
      <button class="wish-btn" :class="{active: product.wishlisted}" @click.stop="$emit('wishlist', product)">
        <i class="bi" :class="product.wishlisted ? 'bi-heart-fill' : 'bi-heart'"></i>
      </button>
      <div class="img-wrap">
        <img :src="product.primary_image || 'https://via.placeholder.com/200'" :alt="product.title" loading="lazy">
      </div>
      <div class="card-body">
        <div v-if="product.compare_price" class="compare-price">{{ fmtCLP(product.compare_price) }}</div>
        <div class="d-flex align-items-center gap-2">
          <span class="price">{{ fmtCLP(product.price) }}</span>
          <span class="discount-badge" v-if="product.compare_price">
            {{ Math.round((1-product.price/product.compare_price)*100) }}% OFF
          </span>
        </div>
        <p class="title">{{ product.title }}</p>
        <div class="free-ship" v-if="product.free_shipping"><i class="bi bi-truck me-1"></i>Envío gratis</div>
        <div class="rating" v-if="product.rating_count > 0">
          <span style="color:#f5a623">★</span> {{ Number(product.rating_avg).toFixed(1) }} ({{ product.rating_count }})
        </div>
      </div>
    </div>
  `,
  methods: {
    fmtCLP(v) {
      return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(v);
    }
  }
};

// ─── Main App ─────────────────────────────────────────────
const app = createApp({
  components: { ProductCard },
  setup() {
    const API = '/api';
    const currentView = ref('home');
    const appLoading = ref(true);
    const searchQuery = ref('');
    const activeCategory = ref(null);
    const detailQty = ref(1);
    const activeImage = ref(null);
    const isAdminRoute = computed(() => currentView.value === 'admin' || currentView.value.startsWith('admin-'));
    const adminView = ref('dashboard');
    const adminSearch = ref('');
    const adminOrderFilter = ref('');

    const auth = ref({ user: null, loading: false, error: null });
    const loginForm = ref({ email: '', password: '' });
    const registerForm = ref({ name: '', email: '', password: '' });

    const categories = ref([]);
    const products = ref({ data: [], total: 0, current_page: 1, last_page: 1, loading: false });
    const filters = ref({ sort: 'created_at_desc', min_price: '', max_price: '', condition: '', free_shipping: false });
    const selectedProduct = ref(null);

    const cart = ref({ items: [], total: 0, count: 0 });
    const orders = ref([]);
    const ordersLoading = ref(false);

    // ── Mis Ventas ─────────────────────────────────────────────────────────
    const sellerTab       = ref('list');
    const myProducts      = ref({ data: [], total: 0 });
    const myProductsLoading = ref(false);
    const myProductSearch = ref('');
    const myProductStatusFilter = ref('');
    const productFormLoading = ref(false);
    const deleteConfirm   = ref({ show: false, product: null });
    const formErrors      = ref({});

    const productForm = ref({
      id: null, title: '', short_desc: '', description: '',
      category_id: '', condition_type: 'new', price: 0,
      compare_price: 0, stock: 0, stock_alert: 5,
      delivery_type: 'shipping', free_shipping: false,
      sku: '', external_link: '', weight_kg: 0, status: 'active'
    });

    const filteredMyProducts = computed(() => {
      let list = myProducts.value.data || [];
      if (myProductSearch.value) {
        const q = myProductSearch.value.toLowerCase();
        list = list.filter(p => p.title.toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q));
      }
      if (myProductStatusFilter.value) {
        list = list.filter(p => p.status === myProductStatusFilter.value);
      }
      return list;
    });

    function getCategoryName(id) {
      const cat = categories.value.find(c => c.id == id);
      return cat ? cat.name : '—';
    }

    function openProductForm(product) {
      formErrors.value = {};
      if (product) {
        productForm.value = {
          id: product.id, title: product.title || '',
          short_desc: product.short_desc || '', description: product.description || '',
          category_id: product.category_id || '', condition_type: product.condition_type || 'new',
          price: product.price || 0, compare_price: product.compare_price || 0,
          stock: product.stock || 0, stock_alert: product.stock_alert || 5,
          delivery_type: product.delivery_type || 'shipping',
          free_shipping: !!product.free_shipping,
          sku: product.sku || '', external_link: product.external_link || '',
          weight_kg: product.weight_kg || 0, status: product.status || 'active'
        };
      } else {
        productForm.value = {
          id: null, title: '', short_desc: '', description: '',
          category_id: '', condition_type: 'new', price: 0,
          compare_price: 0, stock: 0, stock_alert: 5,
          delivery_type: 'shipping', free_shipping: false,
          sku: '', external_link: '', weight_kg: 0, status: 'active'
        };
      }
      sellerTab.value = 'form';
    }

    async function loadMyProducts() {
      myProductsLoading.value = true;
      try {
        const r = await api('GET', '/my/products');
        myProducts.value = {
          data: Array.isArray(r.data) ? r.data : [],
          total: r.total || 0
        };
      } catch (e) {
        console.warn('loadMyProducts error:', e);
        myProducts.value = { data: [], total: 0 };
      } finally {
        myProductsLoading.value = false;
      }
    }

    function validateProductForm() {
      const errs = {};
      const f = productForm.value;
      if (!f.title || f.title.trim().length < 5) errs.title = 'El título debe tener al menos 5 caracteres.';
      if (!f.short_desc || f.short_desc.trim().length < 5) errs.short_desc = 'La descripción breve es requerida.';
      if (!f.category_id) errs.category_id = 'Selecciona una categoría.';
      if (!f.price || f.price <= 0) errs.price = 'El precio debe ser mayor a 0.';
      if (f.stock === '' || f.stock < 0) errs.stock = 'El stock no puede ser negativo.';
      if (f.external_link && !/^https?:\/\/.+/.test(f.external_link)) errs.external_link = 'URL inválida (debe empezar con http:// o https://)';
      return errs;
    }

    async function submitProductForm() {
      if (!checkRut()) return;
      formErrors.value = {};
      const errs = validateProductForm();
      if (Object.keys(errs).length) { formErrors.value = errs; return; }
      productFormLoading.value = true;
      try {
        const body = { ...productForm.value };
        if (body.compare_price <= 0) delete body.compare_price;
        if (!body.sku) delete body.sku;
        if (!body.external_link) delete body.external_link;
        if (body.weight_kg <= 0) delete body.weight_kg;
        if (body.id) {
          await api('PUT', `/products/${body.id}`, body);
          toast('Producto actualizado correctamente.');
        } else {
          await api('POST', '/products', body);
          toast('Producto publicado con éxito. ¡Ya está visible!');
        }
        await loadMyProducts();
        sellerTab.value = 'list';
      } catch (e) {
        formErrors.value = { _global: e.error || 'Error al guardar el producto.' };
      } finally {
        productFormLoading.value = false;
      }
    }

    async function toggleProductStatus(product) {
      const newStatus = product.status === 'active' ? 'paused' : 'active';
      try {
        await api('PUT', `/products/${product.id}`, { status: newStatus });
        product.status = newStatus;
        toast(newStatus === 'active' ? 'Producto activado.' : 'Producto pausado.');
      } catch (e) { toast('Error al cambiar estado.', 'error'); }
    }

    function confirmDeleteProduct(product) {
      deleteConfirm.value = { show: true, product };
    }

    async function doDeleteProduct() {
      const product = deleteConfirm.value.product;
      if (!product) return;
      try {
        await api('DELETE', `/products/${product.id}`);
        toast('Producto eliminado.');
        deleteConfirm.value = { show: false, product: null };
        await loadMyProducts();
        sellerTab.value = 'list';
      } catch (e) { toast('Error al eliminar.', 'error'); }
    }

    // ── Perfil de Usuario ──────────────────────────────────────────────────
    const profileTab    = ref('data');
    const profileLoading = ref(false);
    const profileSuccess = ref('');
    const profileErrors  = ref({});
    const pwdErrors      = ref({});
    const pwdSuccess     = ref('');
    const addressesLoading = ref(false);
    const addresses      = ref([]);
    const addressErrors  = ref({});
    const confirmDeleteAccount = ref(false);
    const deleteAccountConfirmText = ref('');

    const profileData = ref({
      name: '', phone: '', rut: '', birthdate: ''
    });

    const pwdForm = ref({ current: '', new: '', confirm: '' });
    const showPwd = ref({ current: false, new: false, confirm: false });

    const addressForm = ref({
      show: false, id: null, label: 'Casa', full_name: '',
      address: '', city: '', region: '', zip_code: '', is_default: false
    });

    const notifPrefs = ref([
      { key: 'email_orders',   label: 'Órdenes y compras',   desc: 'Confirmaciones, estados y actualizaciones de tus pedidos', value: true },
      { key: 'email_messages', label: 'Mensajes',             desc: 'Cuando recibes un mensaje de un comprador o vendedor', value: true },
      { key: 'email_offers',   label: 'Ofertas y descuentos', desc: 'Promociones y productos en oferta según tus intereses', value: false },
      { key: 'email_news',     label: 'Novedades',            desc: 'Nuevas funcionalidades y actualizaciones de MercadoSordo', value: false },
    ]);

    const privacyPrefs = ref([
      { key: 'show_phone',   label: 'Mostrar teléfono a compradores', desc: 'Los compradores pueden ver tu número de contacto', value: false },
      { key: 'public_sales', label: 'Perfil de ventas público',       desc: 'Cualquiera puede ver tus estadísticas de ventas', value: true },
    ]);

    const pwdStrength = computed(() => {
      const p = pwdForm.value.new;
      if (!p) return { pct: 0, label: '', color: '', textColor: '' };
      let score = 0;
      if (p.length >= 8)  score++;
      if (p.length >= 12) score++;
      if (/[A-Z]/.test(p)) score++;
      if (/[0-9]/.test(p)) score++;
      if (/[^A-Za-z0-9]/.test(p)) score++;
      const map = [
        { pct: 20,  label: 'Muy débil',  color: 'bg-danger',  textColor: 'text-danger' },
        { pct: 40,  label: 'Débil',      color: 'bg-warning', textColor: 'text-warning' },
        { pct: 60,  label: 'Regular',    color: 'bg-info',    textColor: 'text-info' },
        { pct: 80,  label: 'Fuerte',     color: 'bg-primary', textColor: 'text-primary' },
        { pct: 100, label: 'Muy fuerte', color: 'bg-success', textColor: 'text-success' },
      ];
      return map[Math.min(score, 4)];
    });

    // ── Utilidades RUT ─────────────────────────────────────
    function isValidRut(rut) {
      const clean = rut.replace(/[^0-9kK]/g, '');
      if (clean.length < 8 || clean.length > 9) return false;
      const body = clean.slice(0, -1);
      const dv   = clean.slice(-1).toLowerCase();
      let sum = 0, factor = 2;
      for (let i = body.length - 1; i >= 0; i--) {
        sum += parseInt(body[i]) * factor;
        factor = factor === 7 ? 2 : factor + 1;
      }
      const rem = 11 - (sum % 11);
      const expected = rem === 11 ? '0' : rem === 10 ? 'k' : String(rem);
      return dv === expected;
    }

    function formatRutInput(e) {
      if (auth.value.user?.rut) return; // bloqueado
      let val = e.target.value.replace(/[^0-9kK]/gi, '').toUpperCase();
      if (val.length > 1) {
        const dv   = val.slice(-1);
        const body = val.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        val = body + '-' + dv;
      }
      profileData.value.rut = val;
    }

    // ── Guard RUT — redirige a perfil si falta ─────────────
    function checkRut(action = null) {
      if (!auth.value.user?.rut) {
        profileTab.value = 'data';
        navigate('profile');
        setTimeout(() => {
          toast('⚠️ Debes ingresar tu RUT antes de continuar.', 'error');
          // Foco automático al campo RUT
          const rutInput = document.querySelector('input[placeholder="12.345.678-9"]');
          if (rutInput) { rutInput.focus(); rutInput.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        }, 300);
        return false;
      }
      return true;
    }

    function initProfileData() {
      if (auth.value.user) {
        profileData.value.name  = auth.value.user.name  || '';
        profileData.value.phone = auth.value.user.phone || '';
        profileData.value.rut   = auth.value.user.rut   || '';
      }
    }

    async function saveProfileData() {
      profileErrors.value = {}; profileSuccess.value = '';
      if (!profileData.value.name?.trim()) {
        profileErrors.value.name = 'El nombre es requerido.'; return;
      }
      // RUT obligatorio si aún no está guardado
      if (!auth.value.user?.rut) {
        if (!profileData.value.rut?.trim()) {
          profileErrors.value.rut = 'El RUT es obligatorio.'; return;
        }
        if (!isValidRut(profileData.value.rut)) {
          profileErrors.value.rut = 'RUT inválido. Verifica el dígito verificador.'; return;
        }
      }
      profileLoading.value = true;
      try {
        await api('PATCH', '/profile', profileData.value);
        auth.value.user = { ...auth.value.user, ...profileData.value };
        profileSuccess.value = 'Datos actualizados correctamente.';
        setTimeout(() => profileSuccess.value = '', 4000);
      } catch (e) {
        profileErrors.value._global = e.error || 'Error al guardar los datos.';
      } finally { profileLoading.value = false; }
    }

    async function savePassword() {
      pwdErrors.value = {}; pwdSuccess.value = '';
      if (!pwdForm.value.current) { pwdErrors.value.current = 'Ingresa tu contraseña actual.'; return; }
      if (!pwdForm.value.new || pwdForm.value.new.length < 8) { pwdErrors.value.new = 'Mínimo 8 caracteres.'; return; }
      if (pwdForm.value.new !== pwdForm.value.confirm) { pwdErrors.value.confirm = 'Las contraseñas no coinciden.'; return; }
      profileLoading.value = true;
      try {
        await api('POST', '/profile/password', { current: pwdForm.value.current, password: pwdForm.value.new });
        pwdSuccess.value = 'Contraseña actualizada correctamente.';
        pwdForm.value = { current: '', new: '', confirm: '' };
        setTimeout(() => pwdSuccess.value = '', 4000);
      } catch (e) {
        pwdErrors.value._global = e.error || 'Error al cambiar la contraseña.';
      } finally { profileLoading.value = false; }
    }

    async function loadAddresses() {
      addressesLoading.value = true;
      try { addresses.value = await api('GET', '/profile/addresses'); } catch {}
      finally { addressesLoading.value = false; }
    }

    function openAddressForm(addr) {
      addressErrors.value = {};
      if (addr) {
        addressForm.value = { show: true, id: addr.id, label: addr.label || 'Casa',
          full_name: addr.full_name, address: addr.address, city: addr.city,
          region: addr.region, zip_code: addr.zip_code || '', is_default: !!addr.is_default };
      } else {
        addressForm.value = { show: true, id: null, label: 'Casa', full_name: '',
          address: '', city: '', region: '', zip_code: '', is_default: false };
      }
    }

    async function saveAddress() {
      addressErrors.value = {};
      const f = addressForm.value;
      if (!f.full_name?.trim()) { addressErrors.value.full_name = 'El nombre es requerido.'; return; }
      if (!f.address?.trim())   { addressErrors.value.address   = 'La dirección es requerida.'; return; }
      if (!f.city?.trim())      { addressErrors.value.city      = 'La ciudad es requerida.'; return; }
      if (!f.region)            { addressErrors.value.region    = 'Selecciona una región.'; return; }
      profileLoading.value = true;
      try {
        if (f.id) {
          await api('PUT', `/profile/addresses/${f.id}`, f);
        } else {
          await api('POST', '/profile/addresses', f);
        }
        addressForm.value.show = false;
        await loadAddresses();
        toast(f.id ? 'Dirección actualizada.' : 'Dirección agregada.');
      } catch (e) {
        addressErrors.value._global = e.error || 'Error al guardar la dirección.';
      } finally { profileLoading.value = false; }
    }

    async function setDefaultAddress(id) {
      try {
        await api('PATCH', `/profile/addresses/${id}/default`);
        await loadAddresses();
        toast('Dirección principal actualizada.');
      } catch { toast('Error al actualizar.', 'error'); }
    }

    async function deleteAddress(id) {
      try {
        await api('DELETE', `/profile/addresses/${id}`);
        await loadAddresses();
        toast('Dirección eliminada.');
      } catch { toast('Error al eliminar.', 'error'); }
    }

    function savePrefs() {
      toast('Preferencias guardadas.');
    }

    async function doDeleteAccount() {
      if (deleteAccountConfirmText.value !== 'ELIMINAR') return;
      try {
        await api('DELETE', '/profile');
        await logout();
        toast('Cuenta eliminada. ¡Hasta pronto!');
      } catch { toast('Error al eliminar la cuenta.', 'error'); }
    }

    const adminDash = ref({});
    const adminUsers = ref([]);
    const adminProducts = ref([]);
    const adminOrders = ref([]);

    const toasts = ref([]);
    let toastId = 0;

    // ─── Helpers ───────────────────────────────────────────
    function getToken() { return localStorage.getItem('ms_token'); }

    async function api(method, path, body = null) {
      const opts = {
        method,
        headers: { 'Content-Type': 'application/json' },
      };
      if (getToken()) opts.headers['Authorization'] = `Bearer ${getToken()}`;
      if (body) opts.body = JSON.stringify(body);
      const res = await fetch(API + path, opts);
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw { status: res.status, ...data };
      return data;
    }

    function toast(msg, type = 'success') {
      const id = ++toastId;
      toasts.value.push({ id, msg, type });
      setTimeout(() => toasts.value = toasts.value.filter(t => t.id !== id), 3200);
    }

    function formatCLP(v) {
      return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(v || 0);
    }

    function formatDate(d) {
      return d ? new Date(d).toLocaleDateString('es-CL') : '';
    }

    function statusBadge(s) {
      return { 'bg-warning text-dark': s === 'pending', 'bg-info': s === 'paid', 'bg-primary': s === 'processing', 'bg-success': s === 'delivered', 'bg-danger': s === 'cancelled', 'bg-secondary': true }[s] || 'bg-secondary';
    }
    function statusLabel(s) {
      return { pending:'Pendiente', paid:'Pagado', processing:'En proceso', shipped:'Enviado', delivered:'Entregado', cancelled:'Cancelado', refunded:'Reembolsado' }[s] || s;
    }

    function navigate(view) {
      currentView.value = view;
      window.scrollTo(0, 0);
      if (view === 'home') loadProducts(true);
      if (view === 'products') loadProducts();
      if (view === 'cart') loadCart();
      if (view === 'orders') loadOrders();
      if (view === 'my-products') { sellerTab.value = 'list'; loadMyProducts(); }
      if (view === 'profile') { profileTab.value = 'data'; initProfileData(); loadAddresses(); }
      if (view === 'admin') { adminView.value = 'dashboard'; loadDashboard(); }
    }

    // ─── Auth ──────────────────────────────────────────────
    async function loadMe() {
      const token = getToken();
      if (!token) { appLoading.value = false; return; }
      try {
        const r = await api('GET', '/auth/me');
        auth.value.user = r.user;
      } catch { localStorage.removeItem('ms_token'); }
      finally { appLoading.value = false; }
    }

    async function doLogin() {
      auth.value.loading = true; auth.value.error = null;
      try {
        const r = await api('POST', '/auth/login', loginForm.value);
        localStorage.setItem('ms_token', r.token);
        auth.value.user = r.user;
        loginForm.value = { email: '', password: '' };
        navigate('home');
        toast('¡Bienvenido/a, ' + r.user.name.split(' ')[0] + '!');
        loadCart();
      } catch (e) { auth.value.error = e.error || 'Error al ingresar.'; }
      finally { auth.value.loading = false; }
    }

    async function doRegister() {
      auth.value.loading = true; auth.value.error = null;
      try {
        const r = await api('POST', '/auth/register', registerForm.value);
        localStorage.setItem('ms_token', r.token);
        auth.value.user = r.user;
        navigate('home');
        toast('¡Cuenta creada con éxito!');
      } catch (e) { auth.value.error = e.error || 'Error al registrarse.'; }
      finally { auth.value.loading = false; }
    }

    async function logout() {
      try { await api('POST', '/auth/logout'); } catch {}
      localStorage.removeItem('ms_token');
      auth.value.user = null;
      cart.value = { items: [], total: 0, count: 0 };
      navigate('home');
      toast('Sesión cerrada.');
    }

    // ─── Products ──────────────────────────────────────────
    async function loadProducts(featured = false) {
      products.value.loading = true;
      try {
        const params = new URLSearchParams({
          page: products.value.current_page,
          sort: filters.value.sort,
          ...(activeCategory.value && { category: activeCategory.value }),
          ...(searchQuery.value && { q: searchQuery.value }),
          ...(filters.value.min_price && { min_price: filters.value.min_price }),
          ...(filters.value.max_price && { max_price: filters.value.max_price }),
          ...(filters.value.condition && { condition: filters.value.condition }),
        });
        const r = await api('GET', `/products?${params}`);
        products.value = {
          data: Array.isArray(r.data) ? r.data : [],
          total: r.total || 0,
          current_page: r.current_page || 1,
          last_page: r.last_page || 1,
          loading: false
        };
      } catch (e) {
        console.warn('loadProducts error:', e);
        products.value = { data: [], total: 0, current_page: 1, last_page: 1, loading: false };
      }
    }

    async function loadCategories() {
      try { categories.value = await api('GET', '/categories'); } catch {}
    }

    async function viewProduct(p) {
      currentView.value = 'product-detail';
      window.scrollTo(0, 0);
      try {
        selectedProduct.value = await api('GET', `/products/${p.slug}`);
        activeImage.value = selectedProduct.value.images?.[0]?.url || null;
        detailQty.value = 1;
      } catch { toast('Error al cargar producto.', 'error'); }
    }

    function filterByCategory(slug) {
      activeCategory.value = slug;
      products.value.current_page = 1;
      loadProducts();
      if (currentView.value !== 'products' && currentView.value !== 'home') navigate('products');
    }

    function doSearch() {
      products.value.current_page = 1;
      navigate('products');
    }

    function applyFilters() { products.value.current_page = 1; loadProducts(); }
    function resetFilters() { filters.value = { sort: 'created_at_desc', min_price: '', max_price: '', condition: '', free_shipping: false }; loadProducts(); }
    function changePage(p) { products.value.current_page = p; loadProducts(); }

    // ─── Cart ──────────────────────────────────────────────
    async function loadCart() {
      try {
        const r = await api('GET', '/cart');
        cart.value = r;
      } catch {}
    }

    async function addToCart(product, qty = 1) {
      if (!checkRut()) return;
      try {
        await api('POST', '/cart/items', { product_id: product.id, quantity: qty });
        await loadCart();
        toast('Agregado al carrito ✓');
      } catch (e) { toast(e.error || 'Error al agregar.', 'error'); }
    }

    async function updateCartItem(id, qty) {
      try {
        await api('PATCH', `/cart/items/${id}`, { quantity: qty });
        await loadCart();
      } catch {}
    }

    async function removeCartItem(id) {
      try {
        await api('DELETE', `/cart/items/${id}`);
        await loadCart();
        toast('Item eliminado.');
      } catch {}
    }

    function toggleWishlist(product) {
      product.wishlisted = !product.wishlisted;
      toast(product.wishlisted ? 'Guardado en favoritos ♥' : 'Removido de favoritos');
    }

    // ─── Orders ───────────────────────────────────────────
    async function loadOrders() {
      ordersLoading.value = true;
      try { orders.value = (await api('GET', '/orders')).data; } catch {}
      finally { ordersLoading.value = false; }
    }

    // ─── Admin ────────────────────────────────────────────
    async function loadDashboard() {
      try { adminDash.value = await api('GET', '/admin/dashboard'); } catch {}
    }
    async function loadAdminUsers() {
      try { adminUsers.value = (await api('GET', `/admin/users?q=${adminSearch.value}`)).data; } catch {}
    }
    async function loadAdminProducts() {
      try { adminProducts.value = (await api('GET', '/admin/products')).data; } catch {}
    }
    async function loadAdminOrders() {
      try { adminOrders.value = (await api('GET', `/admin/orders?status=${adminOrderFilter.value}`)).data; } catch {}
    }
    async function updateAdminUser(id, data) {
      try { await api('PATCH', `/admin/users/${id}`, data); toast('Usuario actualizado.'); await loadAdminUsers(); } catch {}
    }
    async function updateOrderStatus(id, status) {
      try { await api('PATCH', `/admin/orders/${id}/status`, { status }); toast('Estado actualizado.'); await loadAdminOrders(); } catch {}
    }

    onMounted(async () => {
      await Promise.all([loadMe(), loadCategories(), loadProducts(true), loadCart()]);
    });

    return {
      currentView, appLoading, isAdminRoute, adminView, adminSearch, adminOrderFilter,
      searchQuery, activeCategory, detailQty, activeImage,
      auth, loginForm, registerForm,
      categories, products, filters, selectedProduct,
      cart, orders, ordersLoading,
      adminDash, adminUsers, adminProducts, adminOrders,
      toasts,
      navigate, doLogin, doRegister, logout,
      loadProducts, viewProduct, filterByCategory, doSearch, applyFilters, resetFilters, changePage,
      addToCart, updateCartItem, removeCartItem, toggleWishlist,
      loadOrders, loadDashboard, loadAdminUsers, loadAdminProducts, loadAdminOrders,
      profileTab, profileLoading, profileSuccess, profileErrors, profileData,
      pwdForm, pwdErrors, pwdSuccess, pwdStrength, showPwd,
      addresses, addressesLoading, addressForm, addressErrors,
      notifPrefs, privacyPrefs,
      confirmDeleteAccount, deleteAccountConfirmText,
      saveProfileData, savePassword, loadAddresses, formatRutInput, isValidRut, checkRut,
      openAddressForm, saveAddress, setDefaultAddress, deleteAddress,
      savePrefs, doDeleteAccount, initProfileData,
      sellerTab, myProducts, myProductsLoading, myProductSearch, myProductStatusFilter,
      productForm, productFormLoading, formErrors, filteredMyProducts, deleteConfirm,
      openProductForm, loadMyProducts, submitProductForm, toggleProductStatus,
      confirmDeleteProduct, doDeleteProduct, getCategoryName,
      updateAdminUser, updateOrderStatus,
      formatCLP, formatDate, statusBadge, statusLabel,
    };
  }
});

app.mount('#app');
</script>
</body>
</html>
