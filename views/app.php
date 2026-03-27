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
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --ms-yellow: #F4C430;
      --ms-yellow-dark: #C9991A;
      --ms-blue: #1B4F8A;
      --ms-blue-dark: #0E3060;
      --ms-green: #00A650;
      --ms-text: #0A1628;
      --ms-muted: #5A7099;
      --ms-border: #D6E4F0;
      --ms-bg: #EBF2FB;
      --ms-card: #ffffff;
      --ms-radius: 8px;
      --ms-shadow: 0 1px 4px rgba(11,30,60,.10);
      --ms-shadow-hover: 0 4px 16px rgba(11,30,60,.16);
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background: var(--ms-bg);
      color: var(--ms-text);
      min-height: 100vh;
    }

    /* ─── NAVBAR ─── */
    .navbar-ms {
      background: var(--ms-blue);
      padding: 10px 0;
      position: sticky; top: 0; z-index: 1040;
      box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }
    .navbar-ms .brand {
      font-family: 'Sora', sans-serif;
      font-weight: 800;
      font-size: 1.5rem;
      color: #ffffff;
      text-decoration: none;
      white-space: nowrap;
      flex-shrink: 0;
    }
    .navbar-ms .brand span { color: var(--ms-yellow); }
    @media (max-width: 576px) {
      .navbar-ms .brand {
        font-size: 1.15rem;
      }
      .navbar-ms .brand .brand-full { display: none; }
      .navbar-ms .brand .brand-short { display: inline; }
      .navbar-actions a span, .navbar-actions button span {
        font-size: .72rem;
      }
      .navbar-actions i { font-size: 1.1rem; }
      .search-bar input { font-size: .85rem; }
    }
    @media (min-width: 577px) {
      .navbar-ms .brand .brand-short { display: none; }
      .navbar-ms .brand .brand-full { display: inline; }
    }

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
      background: var(--ms-yellow);
      color: var(--ms-blue-dark);
      border: none;
      padding: 10px 18px;
      cursor: pointer;
      transition: background .2s;
      font-weight: 700;
    }
    .search-bar button:hover { background: var(--ms-yellow-dark); }

    .navbar-actions a, .navbar-actions button {
      color: rgba(255,255,255,.85);
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
    .navbar-actions a:hover, .navbar-actions button:hover { background: rgba(255,255,255,.12); }

    /* ─── DROPDOWN MENÚ ─── */
    .navbar-ms .dropdown-menu {
      background: var(--ms-blue-dark);
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 10px;
      box-shadow: 0 8px 24px rgba(0,0,0,.25);
      min-width: 220px;
      padding: 8px;
      margin-top: 8px !important;
    }
    .navbar-ms .dropdown-menu .dropdown-item {
      color: rgba(255,255,255,.85);
      border-radius: 6px;
      padding: 9px 14px;
      font-size: .88rem;
      font-weight: 600;
      transition: background .15s;
    }
    .navbar-ms .dropdown-menu .dropdown-item:hover {
      background: rgba(255,255,255,.12);
      color: var(--ms-yellow);
    }
    .navbar-ms .dropdown-menu .dropdown-item.text-danger {
      color: #ff6b6b !important;
    }
    .navbar-ms .dropdown-menu .dropdown-item.text-danger:hover {
      background: rgba(255,107,107,.15);
      color: #ff6b6b !important;
    }
    .navbar-ms .dropdown-menu .dropdown-divider {
      border-color: rgba(255,255,255,.15);
      margin: 6px 0;
    }
    /* Header del dropdown con nombre de usuario */
    .navbar-ms .dropdown-menu .dropdown-header {
      color: var(--ms-yellow);
      font-weight: 800;
      font-size: .85rem;
      padding: 6px 14px;
    }
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
      background: var(--ms-blue-dark);
      border-bottom: 2px solid var(--ms-yellow);
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
      font-size: .85rem; font-weight: 600; color: rgba(255,255,255,.7);
      text-decoration: none; border-bottom: 3px solid transparent;
      transition: all .15s;
    }
    .cat-nav a:hover, .cat-nav a.active {
      color: var(--ms-yellow);
      border-bottom-color: var(--ms-yellow);
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
    /* Precio tachado en detalle producto — rojo tachado */
    .detail-compare-price {
      font-size: 1rem;
      color: #e53935;
      text-decoration: line-through;
      font-weight: 500;
    }
    /* Precio actual en detalle producto — grande y bold */
    .detail-price {
      font-family: 'Sora', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      color: var(--ms-blue);
      margin: 0;
      line-height: 1.1;
    }
    @media (max-width: 576px) {
      .detail-price { font-size: 1.6rem; }
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

  /* ─── FOOTER ─── */
  .ms-footer {
    background: var(--ms-text);
    color: rgba(255,255,255,.5);
    text-align: center;
    padding: 14px 16px;
    font-size: .78rem;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    letter-spacing: .3px;
    margin-top: auto;
  }
  .ms-footer a {
    color: var(--ms-yellow);
    text-decoration: none;
    font-weight: 700;
  }
  .ms-footer a:hover { text-decoration: underline; }
  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  #app { flex: 1; display: flex; flex-direction: column; }

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
        <span class="brand-full">Mercado<span>Sordo</span></span>
        <span class="brand-short">MS<span style="color:var(--ms-yellow)">·</span></span>
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
              <li><h6 class="dropdown-header"><i class="bi bi-person-circle me-2"></i>{{ auth.user?.name }}</h6></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" @click.prevent="navigate('profile')"><i class="bi bi-person me-2"></i>Mi perfil</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="navigate('orders')"><i class="bi bi-box me-2"></i>Mis compras</a></li>
              <li><a class="dropdown-item" href="#" @click.prevent="navigate('my-products')"><i class="bi bi-grid me-2"></i>Mis ventas</a></li>
              <!-- Mis pedidos — solo seller y admin -->
              <li v-if="auth.user.role === 'seller' || auth.user.role === 'admin'">
                <a class="dropdown-item" href="#" @click.prevent="navigate('vendor-orders')">
                  <i class="bi bi-bag-check me-2"></i>Mis pedidos
                  <span class="badge bg-danger ms-1" v-if="vendorOrdersBadge>0">{{vendorOrdersBadge}}</span>
                </a>
              </li>
              <li v-if="auth.user.role === 'admin'"><a class="dropdown-item" href="#" @click.prevent="navigate('admin')"><i class="bi bi-shield me-2"></i>Admin Panel</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="#" @click.prevent="logout"><i class="bi bi-box-arrow-right me-2"></i>Salir</a></li>
            </ul>
          </div>
        </template>
        <!-- Notificaciones -->
        <a href="#" @click.prevent="navigate('notifications')" style="position:relative" v-if="auth.user">
          <i class="bi bi-bell"></i>
          <span>Avisos</span>
          <span v-if="unreadCount>0"
                style="position:absolute;top:-4px;right:-4px;background:#e53935;color:white;
                       font-size:.6rem;font-weight:800;border-radius:50%;width:18px;height:18px;
                       display:flex;align-items:center;justify-content:center;z-index:10">
            {{unreadCount>9?'9+':unreadCount}}
          </span>
        </a>
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
      <h3 class="section-title mb-4"><i class="bi bi-bag me-2 text-primary"></i>Mis compras</h3>
      <div v-if="ordersLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      <div v-else>

        <!-- Detalle de orden seleccionada -->
        <div v-if="selectedOrder" class="mb-4">
          <button class="btn btn-outline-secondary btn-sm mb-3" @click="selectedOrder=null">
            <i class="bi bi-arrow-left me-1"></i>Volver a mis compras
          </button>

          <!-- Comprobante -->
          <div class="bg-white rounded shadow-sm p-4 mb-3" id="comprobante">
            <div class="d-flex justify-content-between align-items-start mb-4">
              <div>
                <div class="brand fw-bold" style="font-family:Sora,sans-serif;font-size:1.3rem">
                  Mercado<span style="color:var(--ms-blue)">Sordo</span>
                </div>
                <div class="text-muted small mt-1">Comprobante de compra</div>
              </div>
              <div class="text-end">
                <div class="fw-bold fs-5">{{ selectedOrder.order_number }}</div>
                <div class="text-muted small">{{ formatDate(selectedOrder.created_at) }}</div>
                <span class="badge mt-1" :class="statusBadge(selectedOrder.status)">{{ statusLabel(selectedOrder.status) }}</span>
              </div>
            </div>

            <!-- Datos comprador -->
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <div class="bg-light rounded p-3">
                  <div class="fw-bold small text-muted mb-2"><i class="bi bi-person me-1"></i>COMPRADOR</div>
                  <div class="fw-bold">{{ auth.user?.name }}</div>
                  <div class="text-muted small">{{ auth.user?.email }}</div>
                  <div class="text-muted small" v-if="auth.user?.rut">RUT: {{ auth.user?.rut }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="bg-light rounded p-3">
                  <div class="fw-bold small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>DIRECCIÓN DE ENVÍO</div>
                  <div v-if="selectedOrder.address_snapshot">
                    <div class="fw-bold">{{ JSON.parse(selectedOrder.address_snapshot).full_name }}</div>
                    <div class="text-muted small">{{ JSON.parse(selectedOrder.address_snapshot).address }}</div>
                    <div class="text-muted small">{{ JSON.parse(selectedOrder.address_snapshot).city }}, {{ JSON.parse(selectedOrder.address_snapshot).region }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Productos -->
            <h6 class="fw-bold mb-3">Productos</h6>
            <div v-if="selectedOrder.items" class="mb-3">
              <div v-for="item in selectedOrder.items" :key="item.id"
                   class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div class="d-flex gap-3 align-items-center">
                  <div>
                    <div class="fw-bold small">{{ item.title }}</div>
                    <div class="text-muted small">SKU: {{ item.sku || '—' }} · Cant: {{ item.quantity }}</div>
                  </div>
                </div>
                <div class="text-end">
                  <div class="fw-bold">{{ formatCLP(item.subtotal) }}</div>
                  <div class="text-muted small">{{ formatCLP(item.price) }} c/u</div>
                </div>
              </div>
            </div>

            <!-- Totales -->
            <div class="d-flex justify-content-end">
              <div style="min-width:240px">
                <div class="d-flex justify-content-between mb-1 small">
                  <span class="text-muted">Subtotal</span>
                  <span>{{ formatCLP(selectedOrder.subtotal) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1 small text-success">
                  <span>Envío</span><span>Gratis</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold">
                  <span>Total pagado</span>
                  <span class="text-primary">{{ formatCLP(selectedOrder.total) }}</span>
                </div>
              </div>
            </div>

            <!-- Método de pago -->
            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
              <div class="small text-muted">
                <i class="bi bi-credit-card me-1"></i>
                Método: <strong>{{ selectedOrder.payment_method === 'mercadopago' ? 'Mercado Pago' : selectedOrder.payment_method === 'bank_transfer' ? 'Transferencia bancaria' : selectedOrder.payment_method }}</strong>
              </div>
              <div class="d-flex gap-2">
                <button v-if="selectedOrder.status === 'pending'"
                        class="btn btn-primary btn-sm fw-bold"
                        @click="retryPayment(selectedOrder)">
                  <i class="bi bi-credit-card me-1"></i>Reintentar pago
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                  <i class="bi bi-printer me-1"></i>Imprimir
                </button>
              </div>
            </div>

            <!-- Botones acción comprador -->
            <div class="mt-3 d-flex gap-2" v-if="['dispatched','in_transit'].includes(selectedOrder.status)">
              <button class="btn btn-success fw-bold" @click="confirmOrderReceived(selectedOrder.id)">
                <i class="bi bi-check-circle me-1"></i>Confirmar recepción
              </button>
              <button class="btn btn-outline-warning btn-sm" @click="openDisputeModal(selectedOrder.id)">
                <i class="bi bi-shield-exclamation me-1"></i>Abrir disputa
              </button>
            </div>

            <!-- Tracking -->
            <div class="mt-4" v-if="selectedOrder.tracking?.length">
              <h6 class="fw-bold mb-3"><i class="bi bi-truck me-2 text-primary"></i>Seguimiento</h6>
              <div class="d-flex flex-column gap-2">
                <div v-for="t in [...(selectedOrder.tracking||[])].reverse()" :key="t.id"
                     class="d-flex gap-3 align-items-start">
                  <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0"
                       style="width:28px;height:28px;font-size:.7rem;color:white">
                    <i class="bi bi-check-lg"></i>
                  </div>
                  <div>
                    <div class="fw-bold small">{{ statusLabel(t.status) }}</div>
                    <div class="text-muted small">{{ t.description }}</div>
                    <div class="text-muted" style="font-size:.72rem">{{ formatDate(t.created_at) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista órdenes -->
        <div v-else>
          <div v-for="o in orders" :key="o.id"
               class="bg-white rounded p-4 mb-3 shadow-sm"
               style="cursor:pointer;transition:box-shadow .15s"
               @mouseenter="$event.currentTarget.style.boxShadow='0 4px 16px rgba(0,0,0,.12)'"
               @mouseleave="$event.currentTarget.style.boxShadow=''"
               @click="loadOrderDetail(o.id)">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="fw-bold">{{ o.order_number }}</span>
                <span class="text-muted small ms-2">{{ formatDate(o.created_at) }}</span>
              </div>
              <span class="badge" :class="statusBadge(o.status)">{{ statusLabel(o.status) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div class="text-muted small">{{ o.items_count }} producto(s)</div>
              <div class="fw-bold text-primary">{{ formatCLP(o.total) }}</div>
            </div>
            <div class="text-muted small mt-1">
              <i class="bi bi-eye me-1"></i>Click para ver comprobante
            </div>
          </div>
          <div v-if="orders.length === 0" class="text-center py-5 text-muted bg-white rounded shadow-sm">
            <i class="bi bi-bag fs-1"></i>
            <p class="mt-2 fw-bold">No tienes compras aún</p>
            <button class="btn btn-primary" @click="navigate('home')">Ver productos</button>
          </div>
        </div>

      </div>
    </template>


    <!-- ─── MIS VENTAS ─── -->
    <template v-if="currentView === 'my-products'">
      <!-- Si es buyer → mostrar modal vendedor en lugar de la vista -->
      <div v-if="auth.user?.role === 'buyer' && sellerModal.show && !appLoading" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
           style="background:rgba(10,22,40,0.82);z-index:9999;padding:16px">
        <div class="bg-white rounded-3 shadow-lg" style="max-width:460px;width:100%">
          <div class="text-center p-4" style="background:var(--ms-blue);border-radius:12px 12px 0 0">
            <div style="font-size:2rem">🛍️</div>
            <div style="font-family:Sora,sans-serif;font-size:1.4rem;font-weight:800;color:white">Quiero ser Vendedor</div>
            <div class="text-white-50 small">MercadoSordo</div>
          </div>
          <div class="p-4">
            <p class="text-muted small mb-3">Para publicar productos acepta estas condiciones:</p>
            <ul style="font-size:.88rem;line-height:1.9;color:#444;padding-left:1.2rem">
              <li>Comisión del <strong>5%</strong> sobre cada venta completada</li>
              <li>Despachar dentro del plazo acordado con el comprador</li>
              <li>Solo publicar productos legales y de tu propiedad</li>
              <li>Responder mensajes y disputas dentro de 48 horas</li>
            </ul>
            <div class="form-check p-3 rounded-3 mt-3" style="background:rgba(27,79,138,0.07)">
              <input class="form-check-input" type="checkbox" id="smAcept" v-model="sellerModal.accepted">
              <label class="form-check-label small fw-bold" for="smAcept">
                Acepto estas condiciones y quiero activar mi cuenta vendedor
              </label>
            </div>
          </div>
          <div class="p-4 pt-0 d-flex gap-2">
            <button class="btn btn-outline-secondary flex-fill" @click="navigate('home')">Cancelar</button>
            <button class="btn btn-primary fw-bold flex-fill" @click="doActivateSeller"
                    :disabled="!sellerModal.accepted || sellerModal.loading"
                    style="background:var(--ms-blue);border:none">
              <span v-if="sellerModal.loading" class="spinner-border spinner-border-sm me-1"></span>
              <i class="bi bi-shop me-1" v-else></i>Activar cuenta vendedor
            </button>
          </div>
        </div>
      </div>
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="section-title mb-0"><i class="bi bi-shop me-2 text-primary"></i>Mis ventas</h3>
        <button class="btn btn-primary btn-sm fw-bold px-3" @click="openProductForm(null)">
          <i class="bi bi-plus-lg me-1"></i>Publicar producto
        </button>
      </div>

      <!-- TABS -->
      <!-- Banner límite buyer -->
      <div v-if="auth.user?.role === 'buyer'" class="alert mb-3 d-flex justify-content-between align-items-center py-2"
           style="background:#FFF8E1;border:1px solid #F4C430;border-radius:8px">
        <span class="small">
          <i class="bi bi-info-circle me-1" style="color:#F4C430"></i>
          Cuenta comprador — puedes publicar hasta <strong>2 productos</strong>.
          Tienes <strong>{{ (myProducts.data||[]).filter(p=>p.status!=='deleted').length }}/2</strong> publicados.
        </span>
        <button class="btn btn-sm fw-bold ms-3 flex-shrink-0" @click="sellerModal.show=true"
                style="background:var(--ms-blue);color:white;border:none;font-size:.78rem;border-radius:6px">
          🛍️ Ser Vendedor
        </button>
      </div>

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
                    <img :src="p.primary_image || '/uploads/no-image.png'"
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

              <!-- Precio tachado con indicador % -->
              <div class="col-md-4">
                <label class="form-label fw-bold">Precio tachado <span class="text-muted fw-normal small">(opcional)</span></label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="number" class="form-control" v-model.number="productForm.compare_price"
                         placeholder="Precio antes del descuento" min="0">
                </div>
                <!-- Precio mayor = sube = rojo (precio subió) -->
                <div v-if="productForm.compare_price > 0 && productForm.price > 0">
                  <small v-if="productForm.compare_price > productForm.price" class="fw-bold text-success">
                    <i class="bi bi-tag-fill me-1"></i>
                    {{ Math.round((1 - productForm.price / productForm.compare_price) * 100) }}% de descuento
                  </small>
                  <small v-else-if="productForm.compare_price < productForm.price" class="fw-bold text-danger">
                    <i class="bi bi-graph-up-arrow me-1"></i>
                    {{ Math.round((productForm.price / productForm.compare_price - 1) * 100) }}% más caro que antes
                  </small>
                  <small v-else class="text-muted">Sin cambio de precio</small>
                </div>
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

            <!-- ── GALERÍA DE IMÁGENES ── -->
            <div class="col-12 mt-3">
              <label class="form-label fw-bold">
                <i class="bi bi-images me-1 text-primary"></i>
                Fotos del producto
                <span class="text-muted fw-normal small">(máx. 8 imágenes · JPG/PNG/WebP · 5MB c/u)</span>
              </label>

              <!-- Zona de drop / selección -->
              <div class="border-2 border-dashed rounded p-4 text-center mb-3"
                   style="border: 2px dashed #3483FA; background:#f8fbff; cursor:pointer; transition: background .2s"
                   @click="triggerImageUpload"
                   @dragover.prevent="imgDragOver=true"
                   @dragleave="imgDragOver=false"
                   @drop.prevent="onImageDrop"
                   :style="imgDragOver ? 'background:#e3f0ff' : ''">
                <i class="bi bi-cloud-arrow-up" style="font-size:2.5rem;color:#3483FA"></i>
                <p class="mb-1 fw-bold mt-2">Arrastra fotos aquí o haz clic para seleccionar</p>
                <p class="text-muted small mb-0">La primera imagen será la principal del anuncio</p>
                <input type="file" id="productImgInput" multiple accept="image/jpeg,image/png,image/webp"
                       style="display:none" @change="onImagesSelected">
              </div>

              <!-- Preview grid -->
              <div class="row g-2" v-if="productImages.length > 0">
                <div class="col-4 col-md-3 col-lg-2" v-for="(img, idx) in productImages" :key="img.id || idx">
                  <div class="position-relative rounded overflow-hidden border"
                       :class="idx===0 ? 'border-primary border-2' : 'border-light'"
                       style="aspect-ratio:1;background:#f8f8f8">
                    <img v-if="img.preview || (img.url && !img.isLegacy)"
                         :src="img.preview || img.url"
                         class="w-100 h-100"
                         style="object-fit:cover"
                         @error="img.loadError=true"
                         v-show="!img.loadError">
                    <div v-if="img.isLegacy && !img.preview"
                         class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center p-1"
                         style="font-size:.6rem;gap:2px;background:#fff3cd">
                      <i class="bi bi-exclamation-triangle text-warning" style="font-size:1rem"></i>
                      <span class="text-warning fw-bold">Imagen local</span>
                      <span class="text-muted">Reemplazar</span>
                    </div>
                    <div v-if="!img.preview && !img.isLegacy && (!img.url || img.loadError)"
                         class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted"
                         style="font-size:.65rem;gap:2px">
                      <i class="bi bi-image" style="font-size:1.2rem"></i>
                      <span>Sin imagen</span>
                    </div>
                    <!-- Badge principal -->
                    <span v-if="idx===0"
                          class="position-absolute top-0 start-0 badge bg-primary m-1"
                          style="font-size:.6rem">Principal</span>
                    <!-- Overlay acciones -->
                    <div class="position-absolute top-0 end-0 d-flex flex-column gap-1 m-1">
                      <button type="button" class="btn btn-sm btn-light p-1" style="width:24px;height:24px;font-size:.65rem"
                              @click.stop="setPrimaryImage(idx)" title="Hacer principal" v-if="idx!==0">
                        <i class="bi bi-star"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-danger p-1" style="width:24px;height:24px;font-size:.65rem"
                              @click.stop="removeImage(idx)" title="Eliminar">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>
                    <!-- Progreso upload -->
                    <div v-if="img.uploading"
                         class="position-absolute inset-0 d-flex align-items-center justify-content-center"
                         style="background:rgba(255,255,255,.8);inset:0">
                      <div class="spinner-border text-primary spinner-border-sm"></div>
                    </div>
                    <div v-if="img.error"
                         class="position-absolute bottom-0 start-0 end-0 bg-danger text-white text-center"
                         style="font-size:.65rem;padding:2px">Error</div>
                  </div>
                  <!-- Reordenar -->
                  <div class="d-flex gap-1 mt-1 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" style="font-size:.65rem"
                            @click="moveImage(idx,-1)" :disabled="idx===0">◀</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" style="font-size:.65rem"
                            @click="moveImage(idx,1)" :disabled="idx===productImages.length-1">▶</button>
                  </div>
                </div>

                <!-- Agregar más -->
                <div class="col-4 col-md-3 col-lg-2" v-if="productImages.length < 8">
                  <div class="rounded border d-flex align-items-center justify-content-center"
                       style="aspect-ratio:1;cursor:pointer;background:#f8f8f8;border-style:dashed!important"
                       @click="triggerImageUpload">
                    <i class="bi bi-plus-lg text-muted" style="font-size:1.5rem"></i>
                  </div>
                </div>
              </div>

              <div class="alert alert-danger mt-2 py-2 small" v-if="imgErrors.length">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <span v-for="e in imgErrors" :key="e">{{ e }} </span>
              </div>
            </div>

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

      <!-- ── PANEL MÉTODOS DE PAGO (vendedor) ── -->
      <div class="bg-white rounded shadow-sm p-3 mb-3"
           v-if="auth.user?.role === 'seller' || auth.user?.role === 'admin'">
        <div class="fw-bold small mb-2"><i class="bi bi-credit-card me-1 text-primary"></i>Métodos de cobro</div>
        <div class="row g-2">
          <!-- Mercado Pago -->
          <div class="col-md-6">
            <div class="p-2 rounded border d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <img src="https://www.mercadopago.com/org-img/MP3/home/logomp.png" style="height:18px">
                <div class="small" :class="mpStatus.connected?'text-success fw-bold':'text-muted'">
                  {{ mpStatus.connected ? '✓ ' + mpStatus.account?.email : 'Sin conectar' }}
                </div>
              </div>
              <button v-if="!mpStatus.connected" class="btn btn-primary btn-sm py-0 px-2" style="font-size:.75rem" @click="connectMercadoPago">Conectar</button>
              <button v-else class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size:.75rem" @click="disconnectMercadoPago">Desconectar</button>
            </div>
          </div>
          <!-- Cuenta Bancaria -->
          <div class="col-md-6">
            <div class="p-2 rounded border d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-bank2 text-primary"></i>
                <div class="small" :class="bankStatus.connected?'text-success fw-bold':'text-muted'">
                  {{ bankStatus.connected ? '✓ ' + bankStatus.account?.bank_name : 'Sin cuenta bancaria' }}
                </div>
              </div>
              <button class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size:.75rem" @click="openBankForm=true">
                {{ bankStatus.connected ? 'Editar' : 'Conectar' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal cuenta bancaria -->
      <div v-if="openBankForm"
           style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;display:flex;align-items:center;justify-content:center;padding:1rem"
           @click.self="openBankForm=false">
        <div class="bg-white rounded p-4 shadow w-100" style="max-width:480px">
          <h5 class="fw-bold mb-3"><i class="bi bi-bank2 me-2 text-primary"></i>Cuenta bancaria para cobros</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold small">Banco <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" v-model="bankForm.bank_name">
                <option value="">— Selecciona —</option>
                <option>Banco de Chile</option>
                <option>BancoEstado</option>
                <option>Santander</option>
                <option>BCI</option>
                <option>Itaú</option>
                <option>Scotiabank</option>
                <option>BICE</option>
                <option>Security</option>
                <option>Falabella</option>
                <option>Ripley</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small">Tipo de cuenta <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" v-model="bankForm.account_type">
                <option value="cuenta_corriente">Cuenta Corriente</option>
                <option value="cuenta_ahorro">Cuenta de Ahorro</option>
                <option value="cuenta_vista">Cuenta Vista</option>
                <option value="cuenta_rut">Cuenta RUT</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold small">Número de cuenta <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" v-model="bankForm.account_number" placeholder="0000000000">
            </div>
            <div class="col-12">
              <label class="form-label fw-bold small">Nombre titular <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" v-model="bankForm.account_name" placeholder="Nombre completo">
            </div>
            <div class="col-12">
              <label class="form-label fw-bold small">Email para notificaciones</label>
              <input type="email" class="form-control form-control-sm" v-model="bankForm.account_email">
            </div>
            <div class="col-12 p-2 bg-light rounded small text-muted">
              <i class="bi bi-info-circle me-1"></i>El RUT registrado en tu perfil (<strong>{{ auth.user?.rut }}</strong>) se usará como titular.
            </div>
          </div>
          <div class="alert alert-danger mt-2 small" v-if="bankFormError">{{ bankFormError }}</div>
          <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary fw-bold" @click="saveBankAccount" :disabled="bankFormLoading">
              <span v-if="bankFormLoading" class="spinner-border spinner-border-sm me-1"></span>
              Guardar cuenta
            </button>
            <button class="btn btn-outline-secondary" @click="openBankForm=false">Cancelar</button>
          </div>
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
            <!-- Avatar con upload -->
            <div class="position-relative d-inline-block mb-3">
              <div class="rounded-circle overflow-hidden border border-3 border-primary"
                   style="width:90px;height:90px;cursor:pointer"
                   @click="triggerAvatarUpload"
                   title="Cambiar foto">
                <img v-if="auth.user?.avatar || avatarPreview"
                     :src="avatarPreview || auth.user.avatar"
                     class="w-100 h-100"
                     style="object-fit:cover">
                <div v-else
                     class="w-100 h-100 bg-primary d-flex align-items-center justify-content-center"
                     style="font-size:2rem;color:white;font-weight:700">
                  {{ auth.user?.name?.charAt(0).toUpperCase() }}
                </div>
              </div>
              <!-- Botón cámara -->
              <button class="btn btn-primary btn-sm rounded-circle position-absolute"
                      style="width:28px;height:28px;bottom:0;right:0;padding:0;font-size:.75rem"
                      @click="triggerAvatarUpload" title="Cambiar foto">
                <i class="bi bi-camera-fill"></i>
              </button>
              <!-- Input file oculto -->
              <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp"
                     style="display:none" @change="onAvatarSelected">
            </div>

            <!-- Preview + acciones -->
            <div v-if="avatarPreview" class="mb-3">
              <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-success btn-sm fw-bold" @click="uploadAvatar" :disabled="avatarUploading">
                  <span v-if="avatarUploading" class="spinner-border spinner-border-sm me-1"></span>
                  <i class="bi bi-cloud-upload me-1" v-else></i>Guardar
                </button>
                <button class="btn btn-outline-secondary btn-sm" @click="cancelAvatar">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
              <small class="text-muted d-block mt-1">{{ avatarFileName }}</small>
            </div>

            <h5 class="fw-bold mb-0">{{ auth.user?.name }}</h5>
            <p class="text-muted small mb-1">{{ auth.user?.email }}</p>
            <span class="badge" :class="{'bg-danger':auth.user?.role==='admin','bg-info text-dark':auth.user?.role==='seller','bg-secondary':auth.user?.role==='buyer'}">
              {{ auth.user?.role }}
            </span>
            <div class="mt-3 pt-3 border-top text-start">
              <div class="small text-muted mb-1">
                <i class="bi bi-calendar me-1"></i>Miembro desde {{ formatDate(auth.user?.created_at) }}
              </div>
              <div class="small text-muted" v-if="auth.user?.rut">
                <i class="bi bi-shield-check text-success me-1"></i>RUT verificado
              </div>
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
            <a href="#" class="d-flex align-items-center gap-2 px-3 py-3 border-bottom text-decoration-none"
               :class="profileTab==='payments' ? 'bg-primary bg-opacity-10 text-primary fw-bold' : 'text-dark'"
               @click.prevent="profileTab='payments'">
              <i class="bi bi-wallet2"></i> Métodos de pago
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

                <!-- Fila 1: Nombre + RUT -->
                <div class="col-md-6">
                  <label class="form-label fw-bold">Nombre completo <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" :class="{'is-invalid': profileErrors.name}"
                         v-model="profileData.name" placeholder="Tu nombre completo">
                  <div class="invalid-feedback">{{ profileErrors.name }}</div>
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
                    Obligatorio — no puede modificarse una vez guardado.
                  </small>
                  <small class="text-muted" v-if="auth.user?.rut">
                    <i class="bi bi-lock me-1"></i>No puede modificarse por razones de seguridad.
                  </small>
                </div>

                <!-- Fila 2: Email + Teléfono -->
                <div class="col-md-6">
                  <label class="form-label fw-bold">Email</label>
                  <input type="email" class="form-control bg-light" :value="auth.user?.email" disabled>
                  <small class="text-muted">No puede modificarse. Contacta soporte si lo necesitas.</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Teléfono</label>
                  <div class="input-group">
                    <span class="input-group-text">+56</span>
                    <input type="tel" class="form-control" v-model="profileData.phone"
                           placeholder="9 1234 5678" maxlength="12">
                  </div>
                </div>

                <!-- Fila 3: Fecha de nacimiento -->
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

          <!-- ── TAB: MÉTODOS DE PAGO ── -->
          <div v-if="profileTab==='payments'" class="bg-white rounded shadow-sm p-4">
            <h5 class="fw-bold mb-1"><i class="bi bi-wallet2 me-2 text-primary"></i>Métodos de pago</h5>
            <p class="text-muted small mb-4">
              <span v-if="auth.user?.role === 'buyer'">Selecciona cómo prefieres pagar en MercadoSordo.</span>
              <span v-else>Configura qué métodos de pago aceptas como vendedor. Los compradores verán estas opciones al comprar tus productos.</span>
            </p>

            <!-- Lista de métodos -->
            <div class="d-flex flex-column gap-3 mb-4">

              <!-- Mercado Pago -->
              <div class="border rounded p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:#009ee3">
                      <img src="https://www.mercadopago.com/org-img/MP3/home/logomp.png"
                           style="height:20px;filter:brightness(10)">
                    </div>
                    <div>
                      <div class="fw-bold">Mercado Pago</div>
                      <div class="text-muted small">Tarjeta de crédito, débito y cuotas</div>
                    </div>
                  </div>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox"
                           v-model="paymentMethods.mercadopago.enabled"
                           style="width:2.5rem;height:1.25rem">
                  </div>
                </div>
                <div v-if="paymentMethods.mercadopago.enabled" class="mt-3 pt-3 border-top">
                  <label class="form-label fw-bold small">Link de pago <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                    <input type="url" class="form-control"
                           v-model="paymentMethods.mercadopago.link"
                           placeholder="https://link.mercadopago.cl/tu-usuario">
                  </div>
                  <small class="text-muted">Tu link de cobro de Mercado Pago</small>
                </div>
              </div>

              <!-- Billetera Digital personalizada -->
              <div class="border rounded p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:#6f42c1">
                      <i class="bi bi-phone-fill text-white" style="font-size:1.2rem"></i>
                    </div>
                    <div>
                      <div class="fw-bold">Billetera Digital</div>
                      <div class="text-muted small">Mach, BICE Vida, Tenpo, Fintual u otra</div>
                    </div>
                  </div>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox"
                           v-model="paymentMethods.wallet.enabled"
                           style="width:2.5rem;height:1.25rem">
                  </div>
                </div>
                <div v-if="paymentMethods.wallet.enabled" class="mt-3 pt-3 border-top">
                  <div class="row g-2">
                    <div class="col-md-6">
                      <label class="form-label fw-bold small">Nombre de la billetera</label>
                      <select class="form-select form-select-sm" v-model="paymentMethods.wallet.provider">
                        <option value="">— Selecciona —</option>
                        <option>Mach</option>
                        <option>Tenpo</option>
                        <option>BICE Vida</option>
                        <option>Fintual</option>
                        <option>Dale!</option>
                        <option>Copec Pay</option>
                        <option>Otra</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold small">Usuario / teléfono</label>
                      <input type="text" class="form-control form-control-sm"
                             v-model="paymentMethods.wallet.account"
                             placeholder="+56 9 1234 5678 o @usuario">
                    </div>
                    <div class="col-12">
                      <label class="form-label fw-bold small">Instrucciones para el comprador</label>
                      <input type="text" class="form-control form-control-sm"
                             v-model="paymentMethods.wallet.instructions"
                             placeholder="Ej: Enviar al +56912345678 con el número de orden en el comentario">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Transferencia Manual -->
              <div class="border rounded p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:#198754">
                      <i class="bi bi-bank2 text-white" style="font-size:1.2rem"></i>
                    </div>
                    <div>
                      <div class="fw-bold">Transferencia Bancaria Manual</div>
                      <div class="text-muted small">El comprador te transfiere directo</div>
                    </div>
                  </div>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox"
                           v-model="paymentMethods.transfer.enabled"
                           style="width:2.5rem;height:1.25rem">
                  </div>
                </div>
                <div v-if="paymentMethods.transfer.enabled" class="mt-3 pt-3 border-top">
                  <div class="row g-2">
                    <div class="col-md-6">
                      <label class="form-label fw-bold small">Banco</label>
                      <select class="form-select form-select-sm" v-model="paymentMethods.transfer.bank">
                        <option value="">— Selecciona —</option>
                        <option>Banco de Chile</option>
                        <option>BancoEstado</option>
                        <option>Santander</option>
                        <option>BCI</option>
                        <option>Itaú</option>
                        <option>Scotiabank</option>
                        <option>BICE</option>
                        <option>Security</option>
                        <option>Falabella</option>
                        <option>Ripley</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold small">Tipo de cuenta</label>
                      <select class="form-select form-select-sm" v-model="paymentMethods.transfer.account_type">
                        <option value="cuenta_corriente">Cuenta Corriente</option>
                        <option value="cuenta_ahorro">Cuenta de Ahorro</option>
                        <option value="cuenta_vista">Cuenta Vista</option>
                        <option value="cuenta_rut">Cuenta RUT</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold small">Número de cuenta</label>
                      <input type="text" class="form-control form-control-sm"
                             v-model="paymentMethods.transfer.account_number"
                             placeholder="0000000000">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold small">Nombre titular</label>
                      <input type="text" class="form-control form-control-sm"
                             v-model="paymentMethods.transfer.account_name"
                             :placeholder="auth.user?.name">
                    </div>
                    <div class="col-12">
                      <label class="form-label fw-bold small">RUT titular</label>
                      <input type="text" class="form-control form-control-sm bg-light"
                             :value="auth.user?.rut" disabled>
                      <small class="text-muted">Se usa el RUT de tu perfil</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Texto libre / Instrucciones personalizadas -->
              <div class="border rounded p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-3">
                    <div class="rounded d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:#fd7e14">
                      <i class="bi bi-chat-text-fill text-white" style="font-size:1.2rem"></i>
                    </div>
                    <div>
                      <div class="fw-bold">Instrucciones personalizadas</div>
                      <div class="text-muted small">Texto libre que verá el comprador al pagar</div>
                    </div>
                  </div>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox"
                           v-model="paymentMethods.custom.enabled"
                           style="width:2.5rem;height:1.25rem">
                  </div>
                </div>
                <div v-if="paymentMethods.custom.enabled" class="mt-3 pt-3 border-top">
                  <label class="form-label fw-bold small">Mensaje para el comprador</label>
                  <textarea class="form-control" rows="3"
                            v-model="paymentMethods.custom.text"
                            placeholder="Ej: Pagar mediante depósito en cualquier sucursal de BancoEstado a nombre de Juan Pérez, RUT 12.345.678-9, cuenta corriente N° 12345678. Enviar comprobante al +56912345678."
                            maxlength="500"></textarea>
                  <small class="text-muted d-flex justify-content-end">{{ paymentMethods.custom.text.length }}/500</small>
                </div>
              </div>

            </div>

            <!-- Resumen activos -->
            <div class="bg-light rounded p-3 mb-4" v-if="activePaymentMethods.length > 0">
              <div class="fw-bold small mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i>Métodos activos:</div>
              <div class="d-flex flex-wrap gap-2">
                <span v-for="m in activePaymentMethods" :key="m" class="badge bg-primary">{{ m }}</span>
              </div>
            </div>

            <!-- Alerta sin métodos -->
            <div class="alert alert-warning small" v-if="activePaymentMethods.length === 0">
              <i class="bi bi-exclamation-triangle me-1"></i>
              Sin métodos de pago activos. Los compradores no podrán completar el pago de tus productos.
            </div>

            <!-- Régimen tributario -->
            <div class="card border-0 rounded-3 p-3 mb-3" style="background:rgba(27,79,138,0.06)">
              <div class="fw-bold mb-2"><i class="bi bi-percent me-2 text-primary"></i>Régimen tributario (IVA)</div>
              <div class="row g-2 align-items-end">
                <div class="col-md-7">
                  <label class="form-label small fw-bold">Tasa IVA que aplicas a tus ventas</label>
                  <select class="form-select" v-model.number="paymentMethods.tax_rate">
                    <option :value="0">0% — Exento (Persona natural sin inicio de actividades)</option>
                    <option :value="10">10% — Tasa reducida</option>
                    <option :value="19">19% — Estándar (Empresa / Persona jurídica)</option>
                    <option :value="-1">Otro % — Personalizado</option>
                  </select>
                  <div class="mt-2" v-if="paymentMethods.tax_rate === -1">
                    <div class="input-group" style="max-width:200px">
                      <input type="number" class="form-control" v-model.number="paymentMethods.tax_rate_custom"
                             min="0" max="100" step="0.1" placeholder="0.0">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="p-2 rounded border" style="font-size:.82rem">
                    <div class="text-muted mb-1">Comisión plataforma</div>
                    <div class="fw-bold text-primary fs-5">5% fijo</div>
                    <div class="text-muted" style="font-size:.72rem">Aplica a todos los proveedores por igual</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Botón guardar -->
            <div class="alert alert-success small" v-if="paymentMethodsSaved">
              <i class="bi bi-check-circle me-1"></i>Métodos de pago guardados correctamente.
            </div>
            <button class="btn btn-primary fw-bold px-4" @click="savePaymentMethods" :disabled="profileLoading">
              <span v-if="profileLoading" class="spinner-border spinner-border-sm me-2"></span>
              <i class="bi bi-cloud-check me-1" v-else></i>Guardar métodos de pago
            </button>
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
            <img :src="activeImage || '/uploads/no-image.png'" :alt="selectedProduct.title" style="max-height:100%;object-fit:contain">
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
          <!-- Precio tachado — rojo pequeño -->
          <div v-if="selectedProduct.compare_price" class="detail-compare-price mb-1">
            {{ formatCLP(selectedProduct.compare_price) }}
          </div>
          <!-- Precio actual — grande azul -->
          <h2 class="detail-price mb-2">{{ formatCLP(selectedProduct.price) }}</h2>
          <!-- Badge descuento -->
          <div v-if="selectedProduct.compare_price" class="discount-badge d-inline-block mb-3">
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
          <button class="btn-buy-now" @click="buyNow(selectedProduct, detailQty)" :disabled="selectedProduct.stock === 0">Comprar ahora</button>
          <div class="mt-3 pt-3 border-top">
            <small class="text-muted d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-person-circle"></i>Vendido por <strong>{{ selectedProduct.seller_name }}</strong>
            </small>
            <small class="text-muted d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-shield-check text-success"></i>Compra protegida
            </small>
            <!-- Compartir en RRSS -->
            <div class="pt-2">
              <small class="text-muted fw-bold d-block mb-2"><i class="bi bi-share me-1"></i>Compartir</small>
              <div class="d-flex gap-2 flex-wrap">
                <a :href="'https://wa.me/?text=' + encodeURIComponent(selectedProduct.title + ' - ' + formatCLP(selectedProduct.price) + ' - ' + shareUrl)"
                   target="_blank" class="btn btn-sm d-flex align-items-center gap-1 fw-bold"
                   style="background:#25D366;color:white;border:none;border-radius:8px;font-size:.8rem">
                  <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
                <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl)"
                   target="_blank" class="btn btn-sm d-flex align-items-center gap-1 fw-bold"
                   style="background:#1877F2;color:white;border:none;border-radius:8px;font-size:.8rem">
                  <i class="bi bi-facebook"></i> Facebook
                </a>
                <a :href="'https://t.me/share/url?url=' + encodeURIComponent(shareUrl) + '&text=' + encodeURIComponent(selectedProduct.title)"
                   target="_blank" class="btn btn-sm d-flex align-items-center gap-1 fw-bold"
                   style="background:#229ED9;color:white;border:none;border-radius:8px;font-size:.8rem">
                  <i class="bi bi-telegram"></i> Telegram
                </a>
                <a :href="'https://www.tiktok.com/share?url=' + encodeURIComponent(shareUrl)"
                   target="_blank" class="btn btn-sm d-flex align-items-center gap-1 fw-bold"
                   style="background:#000;color:white;border:none;border-radius:8px;font-size:.8rem">
                  <i class="bi bi-tiktok"></i> TikTok
                </a>
                <button @click="copyShareLink" class="btn btn-sm d-flex align-items-center gap-1 fw-bold"
                        style="background:var(--ms-blue);color:white;border:none;border-radius:8px;font-size:.8rem">
                  <i class="bi bi-link-45deg"></i> Copiar link
                </button>
              </div>
            </div>
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


    <!-- ─── CHECKOUT ─── -->
    <template v-if="currentView === 'checkout'">
      <div class="container py-4" style="max-width:980px">
        <h3 class="section-title mb-4"><i class="bi bi-bag-check me-2 text-primary"></i>Finalizar compra</h3>

        <!-- Stepper -->
        <div class="d-flex align-items-center gap-2 mb-4">
          <template v-for="(step,i) in ['Resumen','Dirección','Pago']" :key="i">
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                   style="width:32px;height:32px;font-size:.85rem;transition:all .2s"
                   :class="checkoutStep>i?'bg-success text-white':checkoutStep===i?'bg-primary text-white':'bg-light text-muted border'">
                <i class="bi bi-check-lg" v-if="checkoutStep>i"></i>
                <span v-else>{{i+1}}</span>
              </div>
              <span class="fw-bold small d-none d-sm-inline"
                    :class="checkoutStep===i?'text-primary':'text-muted'">{{step}}</span>
            </div>
            <div v-if="i<2" class="flex-grow-1 border-top" style="max-width:60px"></div>
          </template>
        </div>

        <div class="row g-4">
          <div class="col-md-8">

            <!-- PASO 1: Resumen -->
            <div v-if="checkoutStep===0" class="bg-white rounded shadow-sm p-4">
              <h5 class="fw-bold mb-3">Resumen de tu compra</h5>
              <div v-for="item in cart.items" :key="item.id"
                   class="d-flex gap-3 align-items-center mb-3 pb-3 border-bottom">
                <img :src="item.image||'/uploads/no-image.png'"
                     style="width:60px;height:60px;object-fit:contain;background:#f8f8f8" class="rounded border">
                <div class="flex-grow-1">
                  <div class="fw-bold small">{{item.title}}</div>
                  <div class="text-muted small">Cant: {{item.quantity}} · {{formatCLP(item.price)}} c/u</div>
                </div>
                <div class="fw-bold">{{formatCLP(item.price*item.quantity)}}</div>
              </div>
              <button class="btn btn-primary fw-bold w-100 py-2" @click="checkoutStep=1">
                Continuar <i class="bi bi-arrow-right ms-1"></i>
              </button>
            </div>

            <!-- PASO 2: Dirección -->
            <div v-if="checkoutStep===1" class="bg-white rounded shadow-sm p-4">
              <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-primary"></i>Dirección de envío</h5>
              <div v-if="addressesLoading" class="text-center py-3"><div class="spinner-border text-primary"></div></div>
              <div v-else>
                <div v-if="addresses.length===0" class="text-center py-4">
                  <i class="bi bi-geo-alt text-muted" style="font-size:2.5rem"></i>
                  <p class="mt-2 text-muted">No tienes direcciones guardadas.</p>
                  <button class="btn btn-outline-primary btn-sm"
                          @click="profileTab='addresses'; navigate('profile')">
                    <i class="bi bi-plus-lg me-1"></i>Agregar dirección
                  </button>
                </div>
                <div v-for="addr in addresses" :key="addr.id"
                     class="p-3 rounded border mb-2" style="cursor:pointer;transition:all .15s"
                     :class="selectedAddressId===addr.id?'border-primary bg-primary bg-opacity-10':''"
                     @click="selectedAddressId=addr.id">
                  <div class="d-flex align-items-start gap-2">
                    <i class="bi mt-1"
                       :class="selectedAddressId===addr.id?'bi-record-circle-fill text-primary':'bi-circle text-muted'"></i>
                    <div class="flex-grow-1">
                      <div class="fw-bold small">{{addr.label}} — {{addr.full_name}}</div>
                      <div class="text-muted small">{{addr.address}}, {{addr.city}}, {{addr.region}}</div>
                    </div>
                    <span class="badge bg-success" v-if="addr.is_default">Principal</span>
                  </div>
                </div>
              </div>
              <div class="d-flex gap-2 mt-3">
                <button class="btn btn-outline-secondary" @click="checkoutStep=0">
                  <i class="bi bi-arrow-left me-1"></i>Volver
                </button>
                <button class="btn btn-primary fw-bold flex-grow-1" :disabled="!selectedAddressId"
                        @click="checkoutStep=2">
                  Continuar <i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>

            <!-- PASO 3: Método de pago -->
            <div v-if="checkoutStep===2" class="bg-white rounded shadow-sm p-4">
              <h5 class="fw-bold mb-4"><i class="bi bi-credit-card me-2 text-primary"></i>Método de pago</h5>

              <!-- Métodos del vendedor disponibles -->
              <div v-if="!checkoutOrderId" class="mb-4">
                <div v-if="vendorPaymentMethods.length === 0"
                     class="alert alert-info small">
                  <i class="bi bi-info-circle me-1"></i>
                  Cargando métodos de pago...
                </div>
                <div v-else class="d-flex flex-column gap-2">
                  <div v-for="method in vendorPaymentMethods" :key="method.key"
                       class="p-3 rounded border"
                       style="cursor:pointer;transition:all .15s"
                       :class="selectedPayMethod===method.key ? 'border-primary bg-primary bg-opacity-10' : ''"
                       @click="selectedPayMethod=method.key; selectedPayDetails=method">
                    <div class="d-flex align-items-center gap-3">
                      <i class="bi flex-shrink-0" style="font-size:1.3rem"
                         :class="selectedPayMethod===method.key ? 'bi-record-circle-fill text-primary' : 'bi-circle text-muted'"></i>
                      <!-- Ícono del método -->
                      <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                           :style="'width:38px;height:38px;background:'+method.color">
                        <img v-if="method.key==='mercadopago'"
                             src="https://www.mercadopago.com/org-img/MP3/home/logomp.png"
                             style="height:16px;filter:brightness(10)">
                        <i v-else :class="'bi '+method.icon+' text-white'" style="font-size:1rem"></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="fw-bold small">{{ method.label }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ method.desc }}</div>
                      </div>
                      <span v-if="method.recommended"
                            class="badge bg-success" style="font-size:.65rem">Recomendado</span>
                    </div>
                    <!-- Detalle si está seleccionado -->
                    <div v-if="selectedPayMethod===method.key && method.detail"
                         class="mt-2 ms-5 ps-1 small text-muted border-start border-primary ps-3">
                      {{ method.detail }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Botón pagar -->
              <div v-if="!checkoutOrderId && !mpInitPoint && !bankPayUrl">
                <div class="alert alert-info py-2 small mb-3" v-if="selectedPayMethod">
                  <i class="bi bi-info-circle me-1"></i>
                  <span v-if="selectedPayMethod==='mercadopago'">Serás redirigido a Mercado Pago para completar el pago.</span>
                  <span v-else>Serás redirigido a Khipu para realizar la transferencia bancaria.</span>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-outline-secondary" @click="checkoutStep=1">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                  </button>
                  <button class="btn btn-primary fw-bold flex-grow-1 py-2"
                          :disabled="!selectedPayMethod||checkoutLoading"
                          @click="doCheckout">
                    <span v-if="checkoutLoading" class="spinner-border spinner-border-sm me-2"></span>
                    <i class="bi bi-shield-lock me-1" v-else></i>
                    Pagar {{formatCLP(cart.total)}}
                  </button>
                </div>
              </div>

              <!-- Redirect MP -->
              <div v-if="mpInitPoint || (checkoutOrderId && selectedPayMethod==='mercadopago')" class="text-center py-3">
                <div class="mb-3">
                  <img src="https://www.mercadopago.com/org-img/MP3/home/logomp.png" style="height:36px">
                </div>
                <p class="text-muted small mb-3">Serás redirigido a Mercado Pago para completar el pago de forma segura.</p>
                <p class="text-muted small mb-4">
                  <i class="bi bi-info-circle me-1"></i>
                  En el campo "¿Para qué es?" escribe el número de orden:
                  <strong>{{ checkoutOrderNumber }}</strong>
                </p>
                <a :href="mpInitPoint || 'https://link.mercadopago.cl/mercadosordo'"
                   target="_blank"
                   class="btn fw-bold px-5 py-3 w-100 text-white"
                   style="background:#009ee3;border-color:#009ee3;font-size:1.05rem">
                  <i class="bi bi-shield-check me-2"></i>Pagar {{ formatCLP(cart.total || checkoutAmount) }} con Mercado Pago
                </a>
                <p class="text-muted mt-3" style="font-size:.75rem">
                  <i class="bi bi-lock me-1"></i>Pago 100% seguro · Tu orden: <strong>{{ checkoutOrderNumber }}</strong>
                </p>
              </div>

              <!-- Redirect Khipu -->
              <div v-if="bankPayUrl" class="text-center py-3">
                <i class="bi bi-bank2 text-primary" style="font-size:3rem"></i>
                <p class="fw-bold mt-2">Transferencia lista</p>
                <p class="text-muted small mb-4">Haz clic para realizar la transferencia bancaria vía Khipu.</p>
                <a :href="bankPayUrl" class="btn btn-success fw-bold px-5 py-3 w-100" style="font-size:1.05rem">
                  <i class="bi bi-bank2 me-2"></i>Transferir {{formatCLP(cart.total)}} vía Khipu
                </a>
              </div>

              <!-- Instrucciones manuales (wallet / transfer sin API / custom) -->
              <div v-if="checkoutOrderId && !mpInitPoint && !bankPayUrl && selectedPayDetails"
                   class="py-3">
                <div class="text-center mb-3">
                  <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                       :style="'width:60px;height:60px;background:'+selectedPayDetails.color">
                    <i :class="'bi '+selectedPayDetails.icon+' text-white'" style="font-size:1.5rem"></i>
                  </div>
                  <h5 class="fw-bold">{{ selectedPayDetails.label }}</h5>
                </div>
                <!-- Instrucciones del vendedor -->
                <div class="bg-light rounded p-3 mb-3 text-muted small" style="white-space:pre-wrap">
                  {{ selectedPayDetails.detail || 'Contacta al vendedor para coordinar el pago.' }}
                </div>
                <!-- Datos de la orden -->
                <div class="bg-primary bg-opacity-10 border border-primary rounded p-3 mb-3">
                  <div class="fw-bold small mb-1">Datos de tu orden:</div>
                  <div class="small">N° Orden: <strong>{{ checkoutOrderNumber }}</strong></div>
                  <div class="small">Total: <strong>{{ formatCLP(cart.total || checkoutAmount) }}</strong></div>
                  <div class="small text-muted mt-1">
                    Incluye el número de orden en el comentario/descripción del pago.
                  </div>
                </div>
                <div class="alert alert-success small">
                  <i class="bi bi-check-circle me-1"></i>
                  Tu orden fue creada. Una vez que el vendedor confirme el pago, recibirás la confirmación.
                </div>
              </div>

              <!-- Error -->
              <div class="alert alert-danger mt-3" v-if="checkoutError">
                <i class="bi bi-exclamation-triangle me-2"></i>{{checkoutError}}
              </div>
            </div>

            <!-- CONFIRMACIÓN -->
            <div v-if="checkoutStep===3" class="bg-white rounded shadow-sm p-4 text-center">
              <i class="bi bi-check-circle-fill text-success" style="font-size:4rem"></i>
              <h4 class="fw-bold mt-3">¡Pago recibido!</h4>
              <p class="text-muted">Orden <strong>{{checkoutOrderNumber}}</strong></p>
              <p class="text-muted small mb-4">Recibirás un email con el seguimiento de tu compra.</p>
              <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-outline-primary" @click="navigate('orders')">
                  <i class="bi bi-box me-1"></i>Mis compras
                </button>
                <button class="btn btn-primary" @click="navigate('home')">
                  Seguir comprando
                </button>
              </div>
            </div>

          </div>

          <!-- Sidebar resumen -->
          <div class="col-md-4">
            <div class="bg-white rounded shadow-sm p-4 sticky-top" style="top:80px">
              <h6 class="fw-bold mb-3">Tu pedido</h6>
              <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Subtotal ({{cart.count}} items)</span>
                <span>{{formatCLP(cart.total)}}</span>
              </div>
              <div class="d-flex justify-content-between mb-2 small text-success">
                <span>Envío</span><span>Gratis</span>
              </div>
              <div class="d-flex justify-content-between mb-2 small text-muted" v-if="selectedPayMethod">
                <span>Comisión plataforma</span>
                <span>Cargo al vendedor</span>
              </div>
              <hr>
              <div class="d-flex justify-content-between fw-bold">
                <span>Total a pagar</span>
                <span class="text-primary fs-5">{{formatCLP(cart.total)}}</span>
              </div>
              <div class="mt-3 pt-3 border-top d-flex flex-column gap-1">
                <div class="d-flex align-items-center gap-2 small text-muted">
                  <i class="bi bi-shield-check text-success"></i>Compra protegida
                </div>
                <div class="d-flex align-items-center gap-2 small text-muted">
                  <i class="bi bi-arrow-counterclockwise text-success"></i>Devolución 30 días
                </div>
                <div class="d-flex align-items-center gap-2 small text-muted">
                  <i class="bi bi-lock text-success"></i>Pago 100% seguro
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>


    <!-- ─── MIS PEDIDOS (VENDEDOR) ─── -->
    <template v-if="currentView === 'vendor-orders'">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="section-title mb-0"><i class="bi bi-bag-check me-2 text-primary"></i>Mis pedidos recibidos</h3>
        <select class="form-select form-select-sm w-auto" v-model="vendorOrderFilter" @change="loadVendorOrders()">
          <option value="">Todos los estados</option>
          <option value="paid">Pagados — acción requerida</option>
          <option value="processing">En proceso</option>
          <option value="dispatched">Despachados</option>
          <option value="completed">Completados</option>
          <option value="dispute">En disputa</option>
          <option value="cancelled">Cancelados</option>
        </select>
      </div>

      <!-- Detalle de orden -->
      <div v-if="selectedVendorOrder">
        <button class="btn btn-outline-secondary btn-sm mb-3" @click="selectedVendorOrder=null">
          <i class="bi bi-arrow-left me-1"></i>Volver a mis pedidos
        </button>
        <div class="row g-4">
          <!-- Info orden + protocolo -->
          <div class="col-md-8">
            <!-- Header orden -->
            <div class="bg-white rounded shadow-sm p-4 mb-3">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h5 class="fw-bold mb-1">{{selectedVendorOrder.order_number}}</h5>
                  <div class="text-muted small">{{formatDate(selectedVendorOrder.created_at)}}</div>
                </div>
                <span class="badge fs-6" :class="statusBadge(selectedVendorOrder.status)">
                  {{statusLabel(selectedVendorOrder.status)}}
                </span>
              </div>
              <!-- Datos comprador -->
              <div class="bg-light rounded p-3 mb-3">
                <div class="fw-bold small text-muted mb-2"><i class="bi bi-person me-1"></i>COMPRADOR</div>
                <div class="fw-bold">{{selectedVendorOrder.buyer_name}}</div>
                <div class="text-muted small">{{selectedVendorOrder.buyer_email}}</div>
                <div class="text-muted small" v-if="selectedVendorOrder.buyer_rut">RUT: {{selectedVendorOrder.buyer_rut}}</div>
                <div class="text-muted small" v-if="selectedVendorOrder.buyer_phone">Tel: {{selectedVendorOrder.buyer_phone}}</div>
              </div>
              <!-- Dirección -->
              <div class="bg-light rounded p-3 mb-3" v-if="selectedVendorOrder.address_snapshot">
                <div class="fw-bold small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i>DIRECCIÓN DE ENTREGA</div>
                <div class="fw-bold">{{JSON.parse(selectedVendorOrder.address_snapshot).full_name}}</div>
                <div class="text-muted small">{{JSON.parse(selectedVendorOrder.address_snapshot).address}}</div>
                <div class="text-muted small">{{JSON.parse(selectedVendorOrder.address_snapshot).city}}, {{JSON.parse(selectedVendorOrder.address_snapshot).region}}</div>
              </div>
              <!-- Productos -->
              <h6 class="fw-bold mb-2">Productos</h6>
              <div v-for="item in selectedVendorOrder.items" :key="item.id"
                   class="d-flex gap-3 align-items-center py-2 border-bottom">
                <img :src="item.image||'/uploads/no-image.png'" style="width:56px;height:56px;object-fit:contain;background:#f8f8f8" class="rounded border">
                <div class="flex-grow-1">
                  <div class="fw-bold small">{{item.title}}</div>
                  <div class="text-muted small">SKU: {{item.sku||'—'}} · Cant: {{item.quantity}}</div>
                </div>
                <div class="text-end">
                  <div class="fw-bold">{{formatCLP(item.subtotal)}}</div>
                  <div class="text-muted small">{{formatCLP(item.price)}} c/u</div>
                </div>
              </div>
            </div>

            <!-- ── PROTOCOLO DE SEGURIDAD ── -->
            <div class="bg-white rounded shadow-sm p-4 mb-3">
              <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2 text-primary"></i>Protocolo de seguridad</h6>
              <div class="d-flex flex-column gap-3">
                <!-- Paso 1 -->
                <div class="d-flex gap-3 align-items-start p-3 rounded"
                     :class="getProtocolStepClass(1, selectedVendorOrder.status)">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
                       style="width:36px;height:36px;font-size:.9rem"
                       :class="isStepDone(1,selectedVendorOrder.status)?'bg-success text-white':'bg-light text-muted border'">
                    <i class="bi bi-check-lg" v-if="isStepDone(1,selectedVendorOrder.status)"></i>
                    <span v-else>1</span>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-bold small">Aceptar orden</div>
                    <div class="text-muted" style="font-size:.78rem">Confirmas que tienes el producto disponible</div>
                    <div v-if="['paid','pending'].includes(selectedVendorOrder.status)" class="mt-2">
                      <button class="btn btn-success btn-sm fw-bold px-3" @click="vendorAcceptOrder(selectedVendorOrder.id)" :disabled="orderActionLoading">
                        <span v-if="orderActionLoading" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-check-circle me-1" v-else></i>Aceptar pedido
                      </button>
                    </div>
                  </div>
                  <span class="badge bg-success" v-if="isStepDone(1,selectedVendorOrder.status)">✓ Aceptado</span>
                </div>
                <!-- Paso 2 -->
                <div class="d-flex gap-3 align-items-start p-3 rounded"
                     :class="getProtocolStepClass(2, selectedVendorOrder.status)">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
                       style="width:36px;height:36px;font-size:.9rem"
                       :class="isStepDone(2,selectedVendorOrder.status)?'bg-success text-white':'bg-light text-muted border'">
                    <i class="bi bi-check-lg" v-if="isStepDone(2,selectedVendorOrder.status)"></i>
                    <span v-else>2</span>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-bold small">Confirmar despacho</div>
                    <div class="text-muted" style="font-size:.78rem">Ingresa número de seguimiento del envío</div>
                    <div v-if="selectedVendorOrder.status==='processing'" class="mt-2">
                      <div class="row g-2 mb-2">
                        <div class="col-md-6">
                          <select class="form-select form-select-sm" v-model="dispatchForm.carrier">
                            <option>Correos de Chile</option>
                            <option>Starken</option>
                            <option>Chilexpress</option>
                            <option>Blue Express</option>
                            <option>Shippify</option>
                            <option>Retiro en tienda</option>
                          </select>
                        </div>
                        <div class="col-md-6">
                          <input type="text" class="form-control form-control-sm" v-model="dispatchForm.tracking" placeholder="Nº seguimiento (opcional)">
                        </div>
                      </div>
                      <button class="btn btn-primary btn-sm fw-bold px-3" @click="vendorDispatchOrder(selectedVendorOrder.id)" :disabled="orderActionLoading">
                        <i class="bi bi-truck me-1"></i>Confirmar despacho
                      </button>
                    </div>
                    <div class="text-muted small mt-1" v-if="selectedVendorOrder.tracking_number">
                      <i class="bi bi-truck me-1"></i>{{selectedVendorOrder.tracking_carrier}} · {{selectedVendorOrder.tracking_number}}
                    </div>
                  </div>
                  <span class="badge bg-success" v-if="isStepDone(2,selectedVendorOrder.status)">✓ Despachado</span>
                </div>
                <!-- Paso 3 -->
                <div class="d-flex gap-3 align-items-start p-3 rounded"
                     :class="getProtocolStepClass(3, selectedVendorOrder.status)">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
                       style="width:36px;height:36px;font-size:.9rem"
                       :class="isStepDone(3,selectedVendorOrder.status)?'bg-success text-white':'bg-light text-muted border'">
                    <i class="bi bi-check-lg" v-if="isStepDone(3,selectedVendorOrder.status)"></i>
                    <span v-else>3</span>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-bold small">Comprador confirma recepción</div>
                    <div class="text-muted" style="font-size:.78rem">
                      El comprador confirma que recibió el pedido. Auto-confirma en 7 días.
                      <span v-if="selectedVendorOrder.auto_complete_at"> · Fecha límite: {{formatDate(selectedVendorOrder.auto_complete_at)}}</span>
                    </div>
                  </div>
                  <span class="badge bg-success" v-if="isStepDone(3,selectedVendorOrder.status)">✓ Recibido</span>
                  <span class="badge bg-warning text-dark" v-else-if="['dispatched','in_transit'].includes(selectedVendorOrder.status)">Esperando</span>
                </div>
                <!-- Paso 4 -->
                <div class="d-flex gap-3 align-items-start p-3 rounded"
                     :class="selectedVendorOrder.status==='completed'?'bg-success bg-opacity-10 border-success border':'bg-light'">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                       style="width:36px;height:36px"
                       :class="selectedVendorOrder.status==='completed'?'bg-success text-white':'bg-light text-muted border'">
                    <i class="bi bi-cash-coin" v-if="selectedVendorOrder.status==='completed'"></i>
                    <span v-else class="fw-bold" style="font-size:.9rem">4</span>
                  </div>
                  <div>
                    <div class="fw-bold small">Fondos liberados</div>
                    <div class="text-muted" style="font-size:.78rem">
                      Recibes {{formatCLP(selectedVendorOrder.financials?.vendor_net)}} neto en tu cuenta
                    </div>
                  </div>
                  <span class="badge bg-success ms-auto" v-if="selectedVendorOrder.status==='completed'">✓ Completado</span>
                </div>
              </div>

              <!-- Cancelar orden -->
              <div class="mt-3 pt-3 border-top" v-if="!['completed','cancelled','refunded'].includes(selectedVendorOrder.status)">
                <button class="btn btn-outline-danger btn-sm" @click="showCancelOrder=true">
                  <i class="bi bi-x-circle me-1"></i>Cancelar orden
                </button>
              </div>
            </div>

            <!-- Chat -->
            <div class="bg-white rounded shadow-sm p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-chat-dots me-2 text-primary"></i>Chat con el comprador</h6>
              <div class="d-flex flex-column gap-2 mb-3" style="max-height:300px;overflow-y:auto" id="chatBox">
                <div v-if="orderMessages.length===0" class="text-center text-muted small py-3">
                  Sin mensajes aún. Inicia la conversación.
                </div>
                <div v-for="msg in orderMessages" :key="msg.id"
                     class="d-flex gap-2" :class="msg.sender_id==auth.user?.id?'flex-row-reverse':''">
                  <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0"
                       style="width:32px;height:32px;color:white;font-size:.8rem">
                    {{msg.sender_name?.charAt(0)}}
                  </div>
                  <div class="p-2 rounded" style="max-width:70%"
                       :class="msg.sender_id==auth.user?.id?'bg-primary text-white':'bg-light'">
                    <div style="font-size:.85rem">{{msg.message}}</div>
                    <div style="font-size:.7rem;opacity:.7">{{formatDate(msg.created_at)}}</div>
                  </div>
                </div>
              </div>
              <div class="d-flex gap-2">
                <input type="text" class="form-control" v-model="chatMessage"
                       placeholder="Escribe un mensaje..." @keyup.enter="sendOrderMessage(selectedVendorOrder.id)">
                <button class="btn btn-primary" @click="sendOrderMessage(selectedVendorOrder.id)" :disabled="!chatMessage.trim()">
                  <i class="bi bi-send-fill"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Sidebar financiero -->
          <div class="col-md-4">
            <div class="bg-white rounded shadow-sm p-4 sticky-top" style="top:80px">
              <h6 class="fw-bold mb-3"><i class="bi bi-calculator me-2 text-primary"></i>Desglose financiero</h6>
              <div v-if="selectedVendorOrder.financials">
                <div class="d-flex justify-content-between mb-2 small">
                  <span class="text-muted">Precio de venta</span>
                  <span class="fw-bold">{{formatCLP(selectedVendorOrder.total)}}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                  <span>IVA incluido (19%)</span>
                  <span>{{formatCLP(selectedVendorOrder.financials.iva_amount)}}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                  <span>Subtotal neto</span>
                  <span>{{formatCLP(selectedVendorOrder.financials.subtotal_neto)}}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2 small text-danger">
                  <span>Comisión MercadoSordo (5%)</span>
                  <span>- {{formatCLP(selectedVendorOrder.financials.commission_amount)}}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                  <span>Recibes tú</span>
                  <span class="text-success fs-5">{{formatCLP(selectedVendorOrder.financials.vendor_net)}}</span>
                </div>
                <div class="mt-3 p-2 bg-light rounded small text-muted text-center">
                  IVA 19% incluido en el precio de venta
                </div>
              </div>
              <!-- Tracking -->
              <div class="mt-4" v-if="selectedVendorOrder.tracking?.length">
                <h6 class="fw-bold mb-2 small text-muted">HISTORIAL</h6>
                <div v-for="t in selectedVendorOrder.tracking" :key="t.id" class="d-flex gap-2 mb-2 small">
                  <i class="bi bi-circle-fill text-primary mt-1 flex-shrink-0" style="font-size:.5rem"></i>
                  <div>
                    <div class="fw-bold">{{statusLabel(t.status)}}</div>
                    <div class="text-muted">{{t.description}}</div>
                    <div class="text-muted" style="font-size:.7rem">{{formatDate(t.created_at)}}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal cancelar -->
        <div v-if="showCancelOrder" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9001;display:flex;align-items:center;justify-content:center;padding:1rem">
          <div class="bg-white rounded p-4 shadow" style="max-width:400px;width:100%">
            <h5 class="fw-bold text-danger mb-3"><i class="bi bi-x-circle me-2"></i>Cancelar orden</h5>
            <p class="text-muted small mb-3">¿Seguro que deseas cancelar la orden <strong>{{selectedVendorOrder.order_number}}</strong>? Se restaurará el stock.</p>
            <textarea class="form-control mb-3" rows="2" v-model="cancelReason" placeholder="Motivo de cancelación..."></textarea>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary" @click="showCancelOrder=false">Volver</button>
              <button class="btn btn-danger fw-bold" @click="doCancelOrder(selectedVendorOrder.id)">
                Sí, cancelar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de pedidos -->
      <div v-else>
        <div v-if="vendorOrdersLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else>
          <div v-if="vendorOrders.length===0" class="text-center py-5 bg-white rounded shadow-sm">
            <i class="bi bi-bag" style="font-size:3rem;color:#ccc"></i>
            <p class="mt-3 fw-bold">Sin pedidos aún</p>
            <p class="text-muted small">Cuando alguien compre tus productos aparecerán aquí.</p>
          </div>
          <div v-for="o in vendorOrders" :key="o.id"
               class="bg-white rounded shadow-sm p-4 mb-3"
               style="cursor:pointer;transition:box-shadow .15s"
               @mouseenter="$event.currentTarget.style.boxShadow='0 4px 16px rgba(0,0,0,.12)'"
               @mouseleave="$event.currentTarget.style.boxShadow=''"
               @click="loadVendorOrderDetail(o.id)">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <div class="fw-bold">{{o.order_number}}</div>
                <div class="text-muted small">{{formatDate(o.created_at)}} · {{o.items_count}} producto(s)</div>
                <div class="text-muted small"><i class="bi bi-person me-1"></i>{{o.buyer_name}}</div>
              </div>
              <div class="text-end">
                <span class="badge mb-1 d-block" :class="statusBadge(o.status)">{{statusLabel(o.status)}}</span>
                <div class="fw-bold text-primary">{{formatCLP(o.total)}}</div>
              </div>
            </div>
            <!-- Alerta acción requerida -->
            <div class="alert alert-warning py-1 px-2 mb-0 small" v-if="o.status==='paid'">
              <i class="bi bi-exclamation-triangle me-1"></i>
              Acción requerida — acepta o cancela este pedido
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ─── NOTIFICACIONES ─── -->
    <template v-if="currentView === 'notifications'">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="section-title mb-0"><i class="bi bi-bell me-2 text-primary"></i>Centro de notificaciones
          <span class="badge bg-danger ms-2" v-if="unreadCount>0">{{unreadCount}}</span>
        </h3>
        <button class="btn btn-outline-secondary btn-sm" @click="markAllNotifRead" v-if="unreadCount>0">
          <i class="bi bi-check2-all me-1"></i>Marcar todas como leídas
        </button>
      </div>
      <div v-if="notifsLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      <div v-else>
        <div v-if="notifications.length===0" class="text-center py-5 bg-white rounded shadow-sm">
          <i class="bi bi-bell-slash" style="font-size:3rem;color:#ccc"></i>
          <p class="mt-3 text-muted">Sin notificaciones</p>
        </div>
        <div v-for="n in notifications" :key="n.id"
             class="rounded p-3 mb-2 d-flex gap-3 align-items-start"
             :style="!n.read_at
               ? 'cursor:pointer;background:rgba(27,79,138,0.08);border-left:4px solid #1B4F8A;transition:background .2s'
               : 'cursor:pointer;background:white;border-left:4px solid transparent;transition:background .2s;opacity:.75'"
             @mouseenter="$event.currentTarget.style.background=!n.read_at?'rgba(27,79,138,0.14)':'rgba(0,0,0,0.03)'"
             @mouseleave="$event.currentTarget.style.background=!n.read_at?'rgba(27,79,138,0.08)':'white'"
             @click="readNotif(n)">
          <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
               style="width:42px;height:42px"
               :style="'background:var(--ms-'+n.color+',#1B4F8A)20'">
            <i :class="'bi '+n.icon" :style="'font-size:1.15rem;color:var(--ms-'+n.color+',#1B4F8A)'"></i>
          </div>
          <div class="flex-grow-1">
            <div class="small" :class="!n.read_at?'fw-bold':'text-muted'">{{n.title}}</div>
            <div class="text-muted small mt-1">{{n.body}}</div>
            <div class="text-muted mt-1 d-flex align-items-center gap-2" style="font-size:.72rem">
              <i class="bi bi-clock"></i>{{formatDate(n.created_at)}}
              <span v-if="n.entity_id" class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.65rem">
                Ver detalle →
              </span>
            </div>
          </div>
          <span style="font-size:1.2rem;line-height:1;flex-shrink:0" v-if="!n.read_at">👋🏻</span>
        </div>
      </div>
    </template>

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
          <img :src="item.image || '/uploads/no-image.png'" :alt="item.title">
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
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h2 class="section-title mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
          <span class="text-muted small"><i class="bi bi-clock me-1"></i>Actualizado ahora</span>
        </div>

        <!-- ── KPIs Fila 1: Ingresos ─────────────────────────────────── -->
        <div class="row g-3 mb-3">
          <div class="col-6 col-xl-3">
            <div class="stat-card green">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-muted small fw-bold">INGRESOS TOTALES</div>
                  <div class="stat-value">{{ formatCLP(adminDash.kpis?.revenue_total || 0) }}</div>
                  <small class="text-success">{{ formatCLP(adminDash.kpis?.revenue_month || 0) }} este mes</small>
                </div>
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                  <i class="bi bi-cash-stack text-success fs-5"></i>
                </div>
              </div>
              <div class="mt-2 pt-2 border-top d-flex gap-3">
                <div><div class="text-muted" style="font-size:.7rem">HOY</div><div class="fw-bold small text-success">{{ formatCLP(adminDash.kpis?.revenue_today || 0) }}</div></div>
                <div><div class="text-muted" style="font-size:.7rem">SEMANA</div><div class="fw-bold small">{{ formatCLP(adminDash.kpis?.revenue_week || 0) }}</div></div>
                <div><div class="text-muted" style="font-size:.7rem">COMISIÓN</div><div class="fw-bold small text-primary">{{ formatCLP(adminDash.kpis?.commission_total || 0) }}</div></div>
              </div>
            </div>
          </div>
          <div class="col-6 col-xl-3">
            <div class="stat-card">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-muted small fw-bold">ÓRDENES</div>
                  <div class="stat-value">{{ adminDash.kpis?.orders_total || 0 }}</div>
                  <small class="text-muted">{{ adminDash.kpis?.orders_week || 0 }} esta semana</small>
                </div>
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                  <i class="bi bi-bag-check text-primary fs-5"></i>
                </div>
              </div>
              <div class="mt-2 pt-2 border-top d-flex gap-3">
                <div><div class="text-muted" style="font-size:.7rem">HOY</div><div class="fw-bold small">{{ adminDash.kpis?.orders_today || 0 }}</div></div>
                <div><div class="text-muted" style="font-size:.7rem">PENDIENTES</div><div class="fw-bold small text-warning">{{ adminDash.kpis?.orders_pending || 0 }}</div></div>
                <div><div class="text-muted" style="font-size:.7rem">DISPUTAS</div><div class="fw-bold small text-danger">{{ adminDash.kpis?.orders_dispute || 0 }}</div></div>
              </div>
            </div>
          </div>
          <div class="col-6 col-xl-3">
            <div class="stat-card yellow">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-muted small fw-bold">USUARIOS</div>
                  <div class="stat-value">{{ adminDash.kpis?.users_total || 0 }}</div>
                  <small class="text-muted">{{ adminDash.kpis?.sellers_total || 0 }} vendedores</small>
                </div>
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                  <i class="bi bi-people text-warning fs-5"></i>
                </div>
              </div>
              <div class="mt-2 pt-2 border-top d-flex gap-3">
                <div><div class="text-muted" style="font-size:.7rem">HOY</div><div class="fw-bold small text-success">+{{ adminDash.kpis?.users_today || 0 }}</div></div>
                <div><div class="text-muted" style="font-size:.7rem">SEMANA</div><div class="fw-bold small">+{{ adminDash.kpis?.users_week || 0 }}</div></div>
              </div>
            </div>
          </div>
          <div class="col-6 col-xl-3">
            <div class="stat-card">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-muted small fw-bold">REPUTACIÓN</div>
                  <div class="stat-value d-flex align-items-center gap-2">
                    {{ adminDash.kpis?.avg_rating || '—' }}
                    <i class="bi bi-star-fill text-warning" style="font-size:1rem"></i>
                  </div>
                  <small class="text-muted">{{ adminDash.kpis?.total_reviews || 0 }} reseñas</small>
                </div>
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                  <i class="bi bi-star-half text-warning fs-5"></i>
                </div>
              </div>
              <!-- Rating dist -->
              <div class="mt-2 pt-2 border-top">
                <div v-for="r in (adminDash.rating_dist||[])" :key="r.rating" class="d-flex align-items-center gap-1 mb-1">
                  <span style="font-size:.65rem;width:8px">{{ r.rating }}</span>
                  <i class="bi bi-star-fill text-warning" style="font-size:.55rem"></i>
                  <div class="flex-grow-1 bg-light rounded" style="height:5px">
                    <div class="bg-warning rounded" :style="'height:5px;width:'+Math.min(100,(r.count/(adminDash.kpis?.total_reviews||1))*100)+'%'"></div>
                  </div>
                  <span style="font-size:.65rem;width:20px">{{ r.count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Gráfico + Estados órdenes ─────────────────────────────── -->
        <div class="row g-3 mb-3">
          <div class="col-md-8">
            <div class="bg-white rounded shadow-sm p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-graph-up me-2 text-primary"></i>Ingresos últimos 30 días</h6>
              <div v-if="!adminDash.revenue_chart?.length" class="text-center text-muted py-4 small">Sin datos suficientes aún</div>
              <div v-else style="height:160px;position:relative">
                <svg width="100%" height="160" :viewBox="'0 0 '+(adminDash.revenue_chart.length*30)+' 160'">
                  <defs>
                    <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#1B4F8A" stop-opacity="0.3"/>
                      <stop offset="100%" stop-color="#1B4F8A" stop-opacity="0"/>
                    </linearGradient>
                  </defs>
                  <template v-if="adminDash.revenue_chart?.length">
                    <polyline
                      :points="adminDash.revenue_chart.map((d,i) => (i*30+15)+','+(140 - (d.total / Math.max(...adminDash.revenue_chart.map(x=>x.total))) * 120)).join(' ')"
                      fill="none" stroke="#1B4F8A" stroke-width="2.5" stroke-linejoin="round"/>
                    <circle v-for="(d,i) in adminDash.revenue_chart" :key="i"
                      :cx="i*30+15" :cy="140 - (d.total / Math.max(...adminDash.revenue_chart.map(x=>x.total))) * 120"
                      r="3" fill="#1B4F8A"/>
                  </template>
                </svg>
              </div>
              <div class="d-flex justify-content-between mt-1">
                <span class="text-muted" style="font-size:.7rem">{{ adminDash.revenue_chart?.[0]?.date }}</span>
                <span class="text-muted" style="font-size:.7rem">{{ adminDash.revenue_chart?.[adminDash.revenue_chart.length-1]?.date }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white rounded shadow-sm p-4 h-100">
              <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2 text-primary"></i>Órdenes por estado</h6>
              <div v-for="s in (adminDash.orders_by_status||[])" :key="s.status" class="mb-2">
                <div class="d-flex justify-content-between mb-1">
                  <span class="badge" :class="statusBadge(s.status)">{{ statusLabel(s.status) }}</span>
                  <span class="fw-bold small">{{ s.count }}</span>
                </div>
                <div class="bg-light rounded" style="height:5px">
                  <div class="rounded" :class="'bg-'+statusBadge(s.status).replace('bg-','').split(' ')[0]"
                       :style="'height:5px;width:'+Math.min(100,(s.count/(adminDash.kpis?.orders_total||1))*100)+'%'"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Top Vendedores + Compradores ──────────────────────────── -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="bg-white rounded shadow-sm p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-trophy me-2 text-warning"></i>Mejores vendedores</h6>
              <div v-for="(s,i) in (adminDash.top_sellers||[])" :key="s.id" class="d-flex align-items-center gap-3 mb-3">
                <div class="fw-bold text-muted" style="width:20px;font-size:.85rem">#{{ i+1 }}</div>
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:38px;height:38px;color:white;font-weight:700;font-size:.9rem">
                  {{ s.name?.charAt(0) }}
                </div>
                <div class="flex-grow-1">
                  <div class="fw-bold small">{{ s.name }}</div>
                  <div class="text-muted" style="font-size:.73rem">
                    {{ s.products_count }} productos · {{ s.total_sales }} ventas
                  </div>
                </div>
                <div class="text-end">
                  <div class="fw-bold small text-success">{{ formatCLP(s.total_revenue) }}</div>
                  <div class="text-muted" style="font-size:.7rem">
                    <i class="bi bi-star-fill text-warning" style="font-size:.6rem"></i>
                    {{ s.avg_rating > 0 ? s.avg_rating : '—' }}
                  </div>
                </div>
              </div>
              <div v-if="!adminDash.top_sellers?.length" class="text-center text-muted small py-3">Sin datos aún</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white rounded shadow-sm p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-award me-2 text-primary"></i>Mejores compradores</h6>
              <div v-for="(b,i) in (adminDash.top_buyers||[])" :key="b.id" class="d-flex align-items-center gap-3 mb-3">
                <div class="fw-bold text-muted" style="width:20px;font-size:.85rem">#{{ i+1 }}</div>
                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:38px;height:38px;color:white;font-weight:700;font-size:.9rem">
                  {{ b.name?.charAt(0) }}
                </div>
                <div class="flex-grow-1">
                  <div class="fw-bold small">{{ b.name }}</div>
                  <div class="text-muted" style="font-size:.73rem">{{ b.orders_count }} órdenes</div>
                </div>
                <div class="fw-bold small text-primary">{{ formatCLP(b.total_spent) }}</div>
              </div>
              <div v-if="!adminDash.top_buyers?.length" class="text-center text-muted small py-3">Sin datos aún</div>
            </div>
          </div>
        </div>

        <!-- ── Top Productos más vendidos + más vistos ───────────────── -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="bg-white rounded shadow-sm p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-fire me-2 text-danger"></i>Productos más vendidos</h6>
              <div v-for="(p,i) in (adminDash.top_selling||[])" :key="p.id"
                   class="d-flex align-items-center gap-3 mb-2 pb-2 border-bottom">
                <span class="fw-bold text-muted" style="width:20px;font-size:.8rem">#{{i+1}}</span>
                <img :src="p.image||'/uploads/no-image.png'"
                     style="width:40px;height:40px;object-fit:contain;background:#f0f4fb;border-radius:6px">
                <div class="flex-grow-1 overflow-hidden">
                  <div class="fw-bold small text-truncate">{{ p.title }}</div>
                  <div class="text-muted" style="font-size:.72rem">
                    <i class="bi bi-shop me-1"></i>{{ p.seller_name }}
                    · <i class="bi bi-star-fill text-warning" style="font-size:.6rem"></i> {{ p.rating_avg > 0 ? Number(p.rating_avg).toFixed(1) : '—' }}
                  </div>
                </div>
                <div class="text-end flex-shrink-0">
                  <div class="fw-bold small text-primary">{{ formatCLP(p.price) }}</div>
                  <div class="badge bg-danger" style="font-size:.65rem">{{ p.sales_count }} ventas</div>
                </div>
              </div>
              <div v-if="!adminDash.top_selling?.length" class="text-center text-muted small py-3">Sin datos aún</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white rounded shadow-sm p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-eye me-2 text-info"></i>Productos más vistos</h6>
              <div v-for="(p,i) in (adminDash.top_viewed||[])" :key="p.id"
                   class="d-flex align-items-center gap-3 mb-2 pb-2 border-bottom">
                <span class="fw-bold text-muted" style="width:20px;font-size:.8rem">#{{i+1}}</span>
                <img :src="p.image||'/uploads/no-image.png'"
                     style="width:40px;height:40px;object-fit:contain;background:#f0f4fb;border-radius:6px">
                <div class="flex-grow-1 overflow-hidden">
                  <div class="fw-bold small text-truncate">{{ p.title }}</div>
                  <div class="text-muted" style="font-size:.72rem">
                    <i class="bi bi-shop me-1"></i>{{ p.seller_name }}
                  </div>
                </div>
                <div class="text-end flex-shrink-0">
                  <div class="fw-bold small text-primary">{{ formatCLP(p.price) }}</div>
                  <div class="badge bg-info text-white" style="font-size:.65rem">
                    <i class="bi bi-eye-fill me-1"></i>{{ p.views }}
                  </div>
                </div>
              </div>
              <div v-if="!adminDash.top_viewed?.length" class="text-center text-muted small py-3">Sin datos aún</div>
            </div>
          </div>
        </div>

        <!-- ── Favoritos + Categorías + Órdenes recientes ────────────── -->
        <div class="row g-3">
          <div class="col-md-4">
            <div class="bg-white rounded shadow-sm p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-heart me-2 text-danger"></i>Más agregados a favoritos</h6>
              <div v-for="(p,i) in (adminDash.top_favorites||[])" :key="p.id"
                   class="d-flex align-items-center gap-2 mb-2">
                <span class="fw-bold text-muted" style="width:16px;font-size:.75rem">#{{i+1}}</span>
                <img :src="p.image||'/uploads/no-image.png'"
                     style="width:34px;height:34px;object-fit:contain;background:#f0f4fb;border-radius:6px">
                <div class="flex-grow-1 overflow-hidden">
                  <div class="small text-truncate fw-bold">{{ p.title }}</div>
                  <div class="text-muted" style="font-size:.7rem">{{ formatCLP(p.price) }}</div>
                </div>
                <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:.65rem">
                  <i class="bi bi-heart-fill me-1"></i>{{ p.wishlist_count || 0 }}
                </span>
              </div>
              <div v-if="!adminDash.top_favorites?.length" class="text-center text-muted small py-3">Sin datos aún</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white rounded shadow-sm p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-tags me-2 text-primary"></i>Categorías más activas</h6>
              <div v-for="cat in (adminDash.top_categories||[])" :key="cat.name" class="mb-2">
                <div class="d-flex justify-content-between mb-1">
                  <span class="small fw-bold">{{ cat.name }}</span>
                  <span class="small text-muted">{{ cat.sales }} ventas</span>
                </div>
                <div class="bg-light rounded" style="height:6px">
                  <div class="bg-primary rounded" style="height:6px;transition:width .5s"
                       :style="'width:'+Math.min(100,(cat.sales/Math.max(...(adminDash.top_categories||[{sales:1}]).map(c=>c.sales||1)))*100)+'%'">
                  </div>
                </div>
              </div>
              <div v-if="!adminDash.top_categories?.length" class="text-center text-muted small py-3">Sin datos aún</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white rounded shadow-sm p-4">
              <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Actividad reciente</h6>
              <div v-for="o in (adminDash.recent_orders||[])" :key="o.order_number"
                   class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                <div>
                  <div class="fw-bold" style="font-size:.78rem">{{ o.order_number }}</div>
                  <div class="text-muted" style="font-size:.7rem">{{ o.buyer_name }}</div>
                </div>
                <div class="text-end">
                  <div class="fw-bold small">{{ formatCLP(o.total) }}</div>
                  <span class="badge" :class="statusBadge(o.status)" style="font-size:.6rem">
                    {{ statusLabel(o.status) }}
                  </span>
                </div>
              </div>
              <div v-if="!adminDash.recent_orders?.length" class="text-center text-muted small py-3">Sin órdenes aún</div>
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
        <img :src="product.primary_image || '/uploads/no-image.png'" :alt="product.title" loading="lazy">
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
    const registerForm = ref({
      name: '', email: '', password: '',
      consentTerms: false, consentPrivacy: false, consentMarketing: false
    });


    const categories = ref([]);
    const products = ref({ data: [], total: 0, current_page: 1, last_page: 1, loading: false });
    const filters = ref({ sort: 'created_at_desc', min_price: '', max_price: '', condition: '', free_shipping: false });
    const selectedProduct = ref(null);

    const cart = ref({ items: [], total: 0, count: 0 });
    const orders = ref([]);
    const ordersLoading  = ref(false);
    const selectedOrder  = ref(null);

    // ── Mis Ventas ─────────────────────────────────────────────────────────
    const sellerTab       = ref('list');
    // ── Galería imágenes producto ───────────────────────────────────────
    const productImages   = ref([]);   // [{id, url, preview, file, uploading, error, is_primary}]
    const imgErrors       = ref([]);
    const imgDragOver     = ref(false);

    function triggerImageUpload() {
      document.getElementById('productImgInput').click();
    }

    function validateImageFile(file) {
      const allowed = ['image/jpeg','image/png','image/webp'];
      if (!allowed.includes(file.type)) return `${file.name}: formato no permitido.`;
      if (file.size > 5 * 1024 * 1024) return `${file.name}: supera 5MB.`;
      return null;
    }

    function addImageFiles(files) {
      imgErrors.value = [];
      const remaining = 8 - productImages.value.length;
      const toAdd = Array.from(files).slice(0, remaining);
      if (files.length > remaining) {
        imgErrors.value.push(`Solo puedes agregar ${remaining} imagen(es) más.`);
      }
      toAdd.forEach(file => {
        const err = validateImageFile(file);
        if (err) { imgErrors.value.push(err); return; }
        const reader = new FileReader();
        const imgObj = { id: null, url: null, preview: null, file, uploading: false, error: false, is_primary: productImages.value.length === 0 };
        reader.onload = e => { imgObj.preview = e.target.result; };
        reader.readAsDataURL(file);
        productImages.value.push(imgObj);
      });
    }

    function onImagesSelected(e) { addImageFiles(e.target.files); e.target.value = ''; }
    function onImageDrop(e)      { imgDragOver.value = false; addImageFiles(e.dataTransfer.files); }

    function removeImage(idx) {
      if (productImages.value[idx].id) {
        deleteProductImage(productImages.value[idx].id);
      }
      productImages.value.splice(idx, 1);
      if (productImages.value.length > 0) productImages.value[0].is_primary = true;
    }

    function setPrimaryImage(idx) {
      productImages.value.forEach((img, i) => img.is_primary = i === idx);
      const [img] = productImages.value.splice(idx, 1);
      productImages.value.unshift(img);
    }

    function moveImage(idx, dir) {
      const newIdx = idx + dir;
      if (newIdx < 0 || newIdx >= productImages.value.length) return;
      const tmp = productImages.value[idx];
      productImages.value[idx]    = productImages.value[newIdx];
      productImages.value[newIdx] = tmp;
      productImages.value[0].is_primary = true;
    }

    async function uploadProductImages(productId) {
      const token   = localStorage.getItem('ms_token');
      const pending = productImages.value.filter(img => img.file && !img.id);
      for (let i = 0; i < pending.length; i++) {
        const img = pending[i];
        img.uploading = true;
        img.error = false;

        // Reintento automático hasta 3 veces (útil en mobile con conexión lenta)
        let lastError = null;
        for (let attempt = 1; attempt <= 3; attempt++) {
          try {
            const fd = new FormData();
            fd.append('image', img.file, img.file.name || `image_${i}.jpg`);
            fd.append('sort_order', productImages.value.indexOf(img));
            fd.append('is_primary', img.is_primary ? '1' : '0');
            // Timeout 30s para conexiones móviles lentas
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 30000);
            const res = await fetch(`/api/products/${productId}/images`, {
              method: 'POST',
              headers: { 'Authorization': `Bearer ${token}` },
              body: fd,
              signal: controller.signal
            });
            clearTimeout(timeout);
            const data = await res.json();
            if (!res.ok) throw data;
            img.id      = data.id;
            img.url     = data.url;
            img.error   = false;
            lastError   = null;
            break; // éxito — salir del loop de reintentos
          } catch (e) {
            lastError = e;
            if (attempt < 3) await new Promise(r => setTimeout(r, 1000 * attempt)); // esperar antes de reintentar
          }
        }

        if (lastError) {
          img.error = true;
          let msg = lastError?.error || lastError?.message || 'Error de conexión';
          if (msg === 'Failed to fetch' || msg.includes('fetch')) {
            msg = 'Sin conexión. Verifica tu WiFi o datos móviles e intenta de nuevo.';
          } else if (msg.includes('abort') || lastError?.name === 'AbortError') {
            msg = 'Tiempo agotado. Foto muy grande o conexión lenta. Intenta con otra foto.';
          }
          imgErrors.value.push(`Error imagen ${i + 1}: ${msg}`);
        }
        img.uploading = false;
      }
      // Actualizar sort_order de todas
      await syncImageOrder(productId);
    }

    async function syncImageOrder(productId) {
      const token = localStorage.getItem('ms_token');
      const order = productImages.value
        .filter(img => img.id)
        .map((img, i) => ({ id: img.id, sort_order: i, is_primary: i === 0 ? 1 : 0 }));
      if (!order.length) return;
      try {
        await fetch(`/api/products/${productId}/images/order`, {
          method: 'PATCH',
          headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
          body: JSON.stringify({ images: order })
        });
      } catch {}
    }

    async function deleteProductImage(imageId) {
      try { await api('DELETE', `/products/images/${imageId}`); } catch {}
    }

    function loadProductImages(product) {
      if (product.images && product.images.length) {
        productImages.value = product.images.map((img, i) => ({
          id: img.id,
          url: img.url,
          // Si la URL es relativa (/uploads/...) y estamos en producción Railway → no hay archivo
          preview: null,
          file: null, uploading: false, error: false, loadError: false,
          is_primary: img.is_primary || i === 0,
          isLegacy: img.url && img.url.startsWith('/uploads/') // imagen local antigua
        }));
      } else {
        productImages.value = [];
      }
    }
    const myProducts      = ref({ data: [], total: 0 });
    const sellerModal     = ref({ show: false, accepted: false, loading: false });
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
        loadProductImages(product);
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
        productImages.value = [];
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

    async function doActivateSeller() {
      sellerModal.value.loading = true;
      try {
        await api('PATCH', '/profile', { role: 'seller' });
        auth.value.user.role = 'seller';
        sellerModal.value.show = false;
        toast('¡Cuenta vendedor activada! Ya puedes publicar productos. 🛍️');
        sellerTab.value = 'list';
        await loadMyProducts(); await loadMpStatus(); await loadBankStatus();
        currentView.value = 'my-products';
      } catch (e) {
        toast(e.error || 'Error al activar.', 'error');
      } finally { sellerModal.value.loading = false; }
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

    const BUYER_PRODUCT_LIMIT = 2;

    async function submitProductForm() {
      if (!checkRut()) return;
      formErrors.value = {}; imgErrors.value = [];
      const errs = validateProductForm();
      if (Object.keys(errs).length) { formErrors.value = errs; return; }

      // Verificar límite de productos para buyer
      const isNewProduct = !productForm.value.id;
      if (isNewProduct && auth.value.user?.role === 'buyer') {
        const activeCount = (myProducts.value.data || []).filter(p => p.status !== 'deleted').length;
        if (activeCount >= BUYER_PRODUCT_LIMIT) {
          sellerModal.value = { show: true, accepted: false, loading: false };
          toast(`Como comprador puedes publicar hasta ${BUYER_PRODUCT_LIMIT} productos. Activa tu cuenta vendedor para publicar más.`, 'error');
          return;
        }
      }

      productFormLoading.value = true;
      try {
        const body = { ...productForm.value };
        if (!body.compare_price || body.compare_price <= 0) delete body.compare_price;
        if (!body.sku)           delete body.sku;
        if (!body.external_link) delete body.external_link;
        if (!body.weight_kg || body.weight_kg <= 0) delete body.weight_kg;

        let productId = body.id;
        if (body.id) {
          await api('PUT', `/products/${body.id}`, body);
          toast('Producto actualizado.');
        } else {
          const r = await api('POST', '/products', body);
          productId = r.id;
          toast('Producto publicado. Subiendo imágenes...');
        }
        // Subir imágenes pendientes
        if (productImages.value.some(img => img.file)) {
          await uploadProductImages(productId);
        }
        toast(body.id ? 'Producto actualizado correctamente ✓' : '¡Producto publicado con éxito! ✓');
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
    const avatarPreview   = ref(null);
    const avatarFile      = ref(null);
    const avatarFileName  = ref('');
    const avatarUploading = ref(false);

    function triggerAvatarUpload() {
      document.getElementById('avatarInput').click();
    }

    function onAvatarSelected(e) {
      const file = e.target.files[0];
      if (!file) return;
      // Validar tipo
      if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
        toast('Solo se permiten imágenes JPG, PNG o WebP.', 'error'); return;
      }
      // Validar tamaño (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        toast('La imagen no puede superar 2MB.', 'error'); return;
      }
      avatarFile.value    = file;
      avatarFileName.value = file.name;
      const reader = new FileReader();
      reader.onload = (ev) => { avatarPreview.value = ev.target.result; };
      reader.readAsDataURL(file);
    }

    async function uploadAvatar() {
      if (!avatarFile.value) return;
      avatarUploading.value = true;
      try {
        const formData = new FormData();
        formData.append('avatar', avatarFile.value);
        const token = localStorage.getItem('ms_token');
        const res = await fetch('/api/profile/avatar', {
          method: 'POST',
          headers: { 'Authorization': `Bearer ${token}` },
          body: formData
        });
        const data = await res.json();
        if (!res.ok) throw data;
        auth.value.user.avatar = data.avatar_url;
        avatarPreview.value  = null;
        avatarFile.value     = null;
        avatarFileName.value = '';
        document.getElementById('avatarInput').value = '';
        toast('Foto de perfil actualizada ✓');
      } catch (e) {
        toast(e.error || 'Error al subir la imagen.', 'error');
      } finally {
        avatarUploading.value = false;
      }
    }

    function cancelAvatar() {
      avatarPreview.value  = null;
      avatarFile.value     = null;
      avatarFileName.value = '';
      document.getElementById('avatarInput').value = '';
    }
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

    // ── Métodos de pago ─────────────────────────────────────────────────
    const paymentMethodsSaved  = ref(false);
    const selectedPayDetails   = ref(null);
    const vendorPaymentMethods = ref([]);   // métodos del vendedor visibles en checkout

    const paymentMethods = ref({
      mercadopago: { enabled: false, link: 'https://link.mercadopago.cl/mercadosordo' },
      wallet:      { enabled: false, provider: '', account: '', instructions: '' },
      transfer:    { enabled: false, bank: '', account_type: 'cuenta_corriente', account_number: '', account_name: '' },
      custom:      { enabled: false, text: '' },
      tax_rate:    0,
      tax_rate_custom: 0,
    });

    const activePaymentMethods = computed(() => {
      const names = { mercadopago: 'Mercado Pago', wallet: 'Billetera Digital', transfer: 'Transferencia', custom: 'Personalizado' };
      const keys  = ['mercadopago', 'wallet', 'transfer', 'custom'];
      return keys
        .filter(k => paymentMethods.value[k]?.enabled)
        .map(k => names[k]);
    });

    // Construir lista de métodos del vendedor para mostrar en checkout
    function buildVendorMethods(methods) {
      const list = [];
      if (methods.mercadopago?.enabled) {
        list.push({
          key: 'mercadopago', label: 'Mercado Pago',
          desc: 'Tarjeta de crédito, débito, cuotas',
          icon: 'bi-credit-card', color: '#009ee3',
          detail: methods.mercadopago.link ? 'Serás redirigido a: ' + methods.mercadopago.link : null,
          link: methods.mercadopago.link,
          recommended: true,
        });
      }
      if (methods.wallet?.enabled) {
        list.push({
          key: 'wallet', label: methods.wallet.provider || 'Billetera Digital',
          desc: methods.wallet.account || 'Pago por billetera digital',
          icon: 'bi-phone-fill', color: '#6f42c1',
          detail: methods.wallet.instructions,
        });
      }
      if (methods.transfer?.enabled) {
        list.push({
          key: 'bank_transfer', label: 'Transferencia Bancaria',
          desc: (methods.transfer.bank || '') + ' · ' + (methods.transfer.account_name || ''),
          icon: 'bi-bank2', color: '#198754',
          detail: methods.transfer.bank
            ? `${methods.transfer.bank} · ${({cuenta_corriente:'Cta. Corriente',cuenta_ahorro:'Cta. Ahorro',cuenta_vista:'Cta. Vista',cuenta_rut:'Cta. RUT'})[methods.transfer.account_type] || ''} N° ${methods.transfer.account_number}`
            : null,
        });
      }
      if (methods.custom?.enabled && methods.custom.text) {
        list.push({
          key: 'custom', label: 'Otro método de pago',
          desc: methods.custom.text.substring(0, 60) + (methods.custom.text.length > 60 ? '...' : ''),
          icon: 'bi-chat-text-fill', color: '#fd7e14',
          detail: methods.custom.text,
        });
      }
      return list;
    }

    async function savePaymentMethods() {
      profileLoading.value = true;
      try {
        const pmData = {
          ...paymentMethods.value,
          tax_rate: paymentMethods.value.tax_rate === -1
            ? (paymentMethods.value.tax_rate_custom || 0)
            : (paymentMethods.value.tax_rate || 0),
        };
        await api('POST', '/vendor/payment-methods/save', pmData);
        paymentMethodsSaved.value = true;
        setTimeout(() => paymentMethodsSaved.value = false, 3000);
        toast('Métodos de pago guardados ✓');
      } catch (e) {
        toast(e.error || 'Error al guardar.', 'error');
      } finally {
        profileLoading.value = false;
      }
    }

    async function loadPaymentMethods() {
      try {
        const r = await api('GET', '/vendor/bank/status');
        if (r.connected && r.account) {
          const a = r.account;
          paymentMethods.value = {
            mercadopago: { enabled: !!a.mp_enabled,      link: a.mp_link || 'https://link.mercadopago.cl/mercadosordo' },
            wallet:      { enabled: !!a.wallet_enabled,  provider: a.wallet_provider || '', account: a.wallet_account || '', instructions: a.wallet_instructions || '' },
            transfer:    { enabled: !!a.is_active,       bank: a.bank_name || '', account_type: a.account_type || 'cuenta_corriente', account_number: a.account_number || '', account_name: a.account_name || '' },
            custom:      { enabled: !!a.custom_enabled,  text: a.custom_text || '' },
            tax_rate:    parseFloat(a.tax_rate ?? 0),
            tax_rate_custom: parseFloat(a.tax_rate ?? 0),
          };
        }
      } catch {}
    }

    // Cargar métodos del vendedor de los items del carrito
    async function loadVendorPaymentMethods() {
      const defaultMP = [{
        key: 'mercadopago', label: 'Mercado Pago',
        desc: 'Tarjeta de crédito, débito, cuotas',
        icon: 'bi-credit-card', color: '#009ee3',
        link: 'https://link.mercadopago.cl/mercadosordo',
        recommended: true,
      }];

      if (!cart.value.items?.length) {
        vendorPaymentMethods.value = defaultMP;
        selectedPayMethod.value    = 'mercadopago';
        selectedPayDetails.value   = defaultMP[0];
        return;
      }

      const sellerId = cart.value.items[0]?.seller_id;
      if (!sellerId) {
        vendorPaymentMethods.value = defaultMP;
        selectedPayMethod.value    = 'mercadopago';
        selectedPayDetails.value   = defaultMP[0];
        return;
      }

      try {
        const r = await fetch(`/api/vendor/${sellerId}/payment-methods`);
        const data = await r.json();
        if (data.methods && data.methods.length > 0) {
          vendorPaymentMethods.value = data.methods;
          selectedPayMethod.value    = data.methods[0].key;
          selectedPayDetails.value   = data.methods[0];
          return;
        }
      } catch {}

      // Fallback MP
      vendorPaymentMethods.value = defaultMP;
      selectedPayMethod.value    = 'mercadopago';
      selectedPayDetails.value   = defaultMP[0];
    }

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
        profileData.value.name      = auth.value.user.name      || '';
        profileData.value.phone     = auth.value.user.phone     || '';
        profileData.value.rut       = auth.value.user.rut       || '';
        profileData.value.birthdate = auth.value.user.birthdate || '';
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
        // No enviar rut si ya está registrado (evita 403)
        const payload = { ...profileData.value };
        if (auth.value.user?.rut) delete payload.rut;
        await api('PATCH', '/profile', payload);
        const updated = { ...profileData.value };
        if (auth.value.user?.rut) delete updated.rut;
        auth.value.user = { ...auth.value.user, ...updated };
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

    // ── Checkout + Mercado Pago ────────────────────────────────────────────
    const checkoutStep        = ref(0);
    const selectedPayMethod   = ref('mercadopago');
    const bankPayUrl          = ref('');
    const checkoutLoading     = ref(false);
    const checkoutError       = ref('');
    const checkoutOrderId     = ref(null);
    const checkoutAmount      = ref(0);
    const checkoutOrderNumber = ref('');
    const mpInitPoint         = ref('');
    const selectedAddressId   = ref(null);

    // Conectar MP (para vendedores)
    const mpStatus   = ref({ connected: false, account: null });
    const bankStatus = ref({ connected: false, account: null });
    const openBankForm   = ref(false);
    const bankFormLoading = ref(false);
    const bankFormError   = ref('');
    const bankForm = ref({
      bank_name: '', account_type: 'cuenta_corriente',
      account_number: '', account_name: '', account_email: ''
    });

    async function loadMpStatus() {
      try { mpStatus.value = await api('GET', '/vendor/mp/status'); } catch {}
    }

    async function connectMercadoPago() {
      try {
        const r = await api('GET', '/vendor/mp/authorize');
        window.location.href = r.redirect_url;
      } catch (e) { toast(e.error || 'Error al conectar MP.', 'error'); }
    }

    async function disconnectMercadoPago() {
      try {
        await api('POST', '/vendor/mp/disconnect');
        mpStatus.value = { connected: false, account: null };
        toast('Cuenta Mercado Pago desconectada.');
      } catch { toast('Error al desconectar.', 'error'); }
    }

    async function loadBankStatus() {
      try { bankStatus.value = await api('GET', '/vendor/bank/status'); } catch {}
    }

    async function saveBankAccount() {
      if (!bankForm.value.bank_name || !bankForm.value.account_number || !bankForm.value.account_name) {
        bankFormError.value = 'Completa los campos obligatorios.'; return;
      }
      bankFormLoading.value = true; bankFormError.value = '';
      try {
        await api('POST', '/vendor/bank-account/connect', bankForm.value);
        await loadBankStatus();
        openBankForm.value = false;
        toast('Cuenta bancaria guardada correctamente ✓');
      } catch (e) {
        bankFormError.value = e.error || 'Error al guardar la cuenta.';
      } finally { bankFormLoading.value = false; }
    }

    async function doCheckout() {
      if (!checkRut()) return;
      if (!selectedAddressId.value) { toast('Selecciona una dirección de envío.', 'error'); return; }
      if (!selectedPayMethod.value)  { toast('Selecciona un método de pago.', 'error'); return; }
      checkoutLoading.value = true;
      checkoutError.value   = '';
      mpInitPoint.value     = '';
      bankPayUrl.value      = '';
      try {
        let currentOrderId = checkoutOrderId.value;

        // 1. Crear orden SOLO si no existe ya una
        if (!currentOrderId) {
          const orderRes = await api('POST', '/orders/checkout', {
            address_id:     selectedAddressId.value,
            payment_method: selectedPayMethod.value,
          });
          currentOrderId                = orderRes.order_id;
          checkoutOrderId.value         = orderRes.order_id;
          checkoutAmount.value          = orderRes.total || cart.value.total;
          checkoutOrderNumber.value     = orderRes.order_number;
        }

        if (!currentOrderId) throw { error: 'No se pudo crear la orden.' };

        if (selectedPayMethod.value === 'mercadopago') {
          // Usar link directo del vendedor (sin API MP)
          const vendorLink = selectedPayDetails.value?.link || 'https://link.mercadopago.cl/mercadosordo';
          mpInitPoint.value = vendorLink;
        } else if (selectedPayMethod.value === 'bank_transfer') {
          try {
            const bankRes = await api('POST', '/payments/bank-transfer/create', { order_id: currentOrderId });
            bankPayUrl.value = bankRes.payment_url;
          } catch {
            // Sin API Khipu — mostrar datos bancarios del vendedor
            bankPayUrl.value = null;
            checkoutError.value = '';
            // Mostrar instrucciones manuales
            mpInitPoint.value = null;
          }
        } else {
          // wallet o custom — mostrar instrucciones del vendedor directamente
          mpInitPoint.value = null;
          bankPayUrl.value  = null;
        }
      } catch (e) {
        // Si ya se creó la orden pero falló el pago, mostrar mensaje específico
        if (checkoutOrderId.value) {
          checkoutError.value = e.error || 'Orden creada pero hubo un error al iniciar el pago. Intenta desde Mis Compras.';
        } else {
          checkoutError.value = e.error || 'Error al procesar. Intenta nuevamente.';
        }
      } finally {
        checkoutLoading.value = false;
      }
    }

    function initCheckout() {
      checkoutStep.value      = 0;
      loadVendorPaymentMethods();
      checkoutError.value     = '';
      mpInitPoint.value       = '';
      checkoutAmount.value    = 0;
      bankPayUrl.value        = '';
      checkoutOrderId.value   = null;
      selectedPayMethod.value = 'mercadopago';
      selectedAddressId.value = addresses.value.find(a => a.is_default)?.id || null;
      // Detectar retorno desde MP
      const params = new URLSearchParams(window.location.search);
      if (params.get('order_id') && params.get('collection_status') === 'approved') {
        checkoutOrderNumber.value = params.get('external_reference') || '';
        checkoutStep.value = 3;
        loadCart();
        window.history.replaceState({}, '', '/checkout');
      }
    }

    // ── Gestión de órdenes vendedor ────────────────────────────────────────
    const vendorOrders       = ref([]);
    const vendorOrdersLoading = ref(false);
    const vendorOrderFilter  = ref('');
    const selectedVendorOrder = ref(null);
    const vendorOrdersBadge  = ref(0);
    const orderActionLoading = ref(false);
    const dispatchForm       = ref({ carrier: 'Correos de Chile', tracking: '' });
    const showCancelOrder    = ref(false);
    const cancelReason       = ref('');
    const orderMessages      = ref([]);
    const chatMessage        = ref('');

    // ── Notificaciones ──────────────────────────────────────────────────
    const notifications  = ref([]);
    const unreadCount    = ref(0);
    const notifsLoading  = ref(false);

    // Helpers protocolo
    const protocolSteps = {
      paid: 1, processing: 2, dispatched: 3,
      in_transit: 3, delivered: 3, completed: 4
    };
    function isStepDone(step, status) {
      return (protocolSteps[status] || 0) >= step;
    }
    function getProtocolStepClass(step, status) {
      const cur = protocolSteps[status] || 0;
      if (cur >= step) return 'bg-success bg-opacity-10 border border-success rounded';
      if (cur === step - 1) return 'border border-primary rounded';
      return 'bg-light rounded';
    }

    async function loadVendorOrders() {
      vendorOrdersLoading.value = true;
      try {
        const r = await api('GET', `/vendor/orders${vendorOrderFilter.value ? '?status=' + vendorOrderFilter.value : ''}`);
        vendorOrders.value      = r.data || [];
        vendorOrdersBadge.value = vendorOrders.value.filter(o => o.status === 'paid').length;
      } catch {}
      finally { vendorOrdersLoading.value = false; }
    }

    async function loadVendorOrderDetail(id) {
      try {
        selectedVendorOrder.value = await api('GET', `/vendor/orders/${id}`);
        orderMessages.value = selectedVendorOrder.value.messages || [];
        window.scrollTo(0, 0);
      } catch { toast('Error al cargar pedido.', 'error'); }
    }

    async function vendorAcceptOrder(id) {
      orderActionLoading.value = true;
      try {
        await api('POST', `/vendor/orders/${id}/accept`);
        await loadVendorOrderDetail(id);
        toast('✅ Orden aceptada. Comprador notificado.');
      } catch (e) { toast(e.error || 'Error.', 'error'); }
      finally { orderActionLoading.value = false; }
    }

    async function vendorDispatchOrder(id) {
      orderActionLoading.value = true;
      try {
        await api('POST', `/vendor/orders/${id}/dispatch`, dispatchForm.value);
        await loadVendorOrderDetail(id);
        toast('🚚 Despacho confirmado. Comprador notificado.');
        dispatchForm.value = { carrier: 'Correos de Chile', tracking: '' };
      } catch (e) { toast(e.error || 'Error.', 'error'); }
      finally { orderActionLoading.value = false; }
    }

    async function doCancelOrder(id) {
      try {
        await api('POST', `/vendor/orders/${id}/cancel`, { reason: cancelReason.value });
        showCancelOrder.value = false;
        cancelReason.value    = '';
        selectedVendorOrder.value = null;
        await loadVendorOrders();
        toast('Orden cancelada.');
      } catch (e) { toast(e.error || 'Error.', 'error'); }
    }

    async function sendOrderMessage(orderId) {
      if (!chatMessage.value.trim()) return;
      try {
        const r = await api('POST', `/orders/${orderId}/messages`, { message: chatMessage.value });
        orderMessages.value.push(r.message);
        chatMessage.value = '';
        setTimeout(() => {
          const box = document.getElementById('chatBox');
          if (box) box.scrollTop = box.scrollHeight;
        }, 100);
      } catch { toast('Error al enviar mensaje.', 'error'); }
    }

    // ── Notificaciones ────────────────────────────────────────────────────
    async function loadNotifications() {
      notifsLoading.value = true;
      try {
        const r = await api('GET', '/notifications');
        notifications.value = r.data || [];
        unreadCount.value   = r.unread_count || 0;
      } catch {}
      finally { notifsLoading.value = false; }
    }

    async function loadUnreadCount() {
      try {
        const r = await api('GET', '/notifications?unread=1');
        unreadCount.value = r.unread_count || 0;
      } catch {}
    }

    async function readNotif(notif) {
      if (!notif.read_at) {
        await api('PATCH', `/notifications/${notif.id}/read`).catch(() => {});
        notif.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
      if (!notif.entity_id) return;

      // Navegar según tipo de entidad
      if (notif.entity_type === 'order') {
        // Detectar si es notificación para vendedor o comprador
        const vendorTypes = ['order_accepted','order_dispatched','order_completed','new_message','dispute_opened'];
        const isVendorNotif = vendorTypes.includes(notif.type);

        if (isVendorNotif) {
          // Ir a Mis Pedidos y abrir el detalle
          navigate('vendor-orders');
          await new Promise(r => setTimeout(r, 300));
          await loadVendorOrderDetail(notif.entity_id);
        } else {
          // Ir a Mis Compras y abrir el comprobante
          navigate('orders');
          await new Promise(r => setTimeout(r, 300));
          await loadOrderDetail(notif.entity_id);
        }
      }
    }

    async function markAllNotifRead() {
      await api('POST', '/notifications/read-all').catch(() => {});
      notifications.value.forEach(n => n.read_at = n.read_at || new Date().toISOString());
      unreadCount.value = 0;
      toast('Todas marcadas como leídas.');
    }

    // Comprador confirma recepción
    function openDisputeModal(orderId) {
      toast('Función de disputa próximamente.', 'warning');
    }

    async function buyNow(product, qty = 1) {
      if (auth.value.user?.role === 'admin') { toast('Los administradores no pueden realizar compras.', 'error'); return; }
      if (auth.value.user?.id && product.seller_id === auth.value.user.id) { toast('No puedes comprar tus propios productos.', 'error'); return; }
      if (!checkRut()) return;
      try {
        await api('POST', '/cart/items', { product_id: product.id, quantity: qty });
        await loadCart();
        navigate('checkout');
      } catch (e) { toast(e.error || 'Error al procesar.', 'error'); }
    }

    async function confirmOrderReceived(orderId) {
      try {
        await api('POST', `/orders/${orderId}/confirm`);
        toast('✅ Recepción confirmada. Fondos liberados al vendedor.');
        await loadOrders();
      } catch (e) { toast(e.error || 'Error.', 'error'); }
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

    // URL para compartir producto
    const shareUrl = Vue.computed(() => {
      if (!selectedProduct.value?.slug) return window.location.href;
      const base = window.location.origin;
      return base + '/products/' + selectedProduct.value.slug;
    });

    async function copyShareLink() {
      const url = shareUrl.value;
      try {
        await navigator.clipboard.writeText(url);
        toast('🔗 Link copiado al portapapeles');
      } catch {
        const el = document.createElement('input');
        el.value = url; document.body.appendChild(el);
        el.select(); document.execCommand('copy');
        document.body.removeChild(el);
        toast('🔗 Link copiado');
      }
    }

    function formatDate(d) {
      return d ? new Date(d).toLocaleDateString('es-CL') : '';
    }

    function statusBadge(s) {
      const map = {
        pending:     'bg-warning text-dark',
        paid:        'bg-info text-white',
        processing:  'bg-primary',
        dispatched:  'bg-primary',
        in_transit:  'bg-info text-white',
        delivered:   'bg-success',
        completed:   'bg-success',
        dispute:     'bg-danger',
        cancelled:   'bg-secondary',
        refunded:    'bg-warning text-dark',
      };
      return map[s] || 'bg-secondary';
    }
    function statusLabel(s) {
      const map = {
        pending:    'Pendiente',
        paid:       'Pago recibido',
        processing: 'Aceptado — preparando',
        dispatched: 'Despachado',
        in_transit: 'En camino',
        delivered:  'Entregado',
        completed:  'Completado',
        dispute:    'En disputa',
        cancelled:  'Cancelado',
        refunded:   'Reembolsado',
      };
      return map[s] || s;
    }

    function navigate(view) {
      currentView.value = view;
      window.scrollTo(0, 0);
      if (view === 'home') loadProducts(true);
      if (view === 'products') loadProducts();
      // Limpiar URL al salir del detalle de producto
      if (view !== 'product-detail' && window.location.pathname.startsWith('/products/')) {
        window.history.replaceState({}, '', '/');
      }
      if (view === 'cart') loadCart();
      if (view === 'orders') { selectedOrder.value = null; loadOrders(); }
      if (view === 'my-products') {
        const token = localStorage.getItem('ms_token');
        if (!token) { navigate('login'); return; }
        sellerModal.value = { show: false, accepted: false, loading: false }; // reset modal
        sellerTab.value = 'list'; loadMyProducts(); loadMpStatus(); loadBankStatus();
      }
      if (view === 'vendor-orders')  { selectedVendorOrder.value = null; loadVendorOrders(); }
      if (view === 'notifications')  { loadNotifications(); }
      if (view === 'profile')  { profileTab.value = 'data'; initProfileData(); loadAddresses(); loadPaymentMethods(); }
      if (view === 'checkout') { initCheckout(); loadAddresses(); }
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
        loadUnreadCount();
        navigate('home');
        toast('¡Bienvenido/a a MercadoSordo! 🤟');
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
      // Actualizar URL para que sea compartible
      window.history.pushState({}, '', `/products/${p.slug}`);
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
      // Bloquear admin
      if (auth.value.user?.role === 'admin') {
        toast('Los administradores no pueden realizar compras.', 'error'); return;
      }
      // Bloquear vendedor comprando sus propios productos
      if (auth.value.user?.id && product.seller_id === auth.value.user.id) {
        toast('No puedes comprar tus propios productos.', 'error'); return;
      }
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
      selectedOrder.value = null;
      try { orders.value = (await api('GET', '/orders')).data; } catch {}
      finally { ordersLoading.value = false; }
    }

    async function retryPayment(order) {
      // Prellenar checkout con la orden existente
      checkoutOrderId.value     = order.id;
      checkoutOrderNumber.value = order.order_number;
      checkoutStep.value        = 2;
      selectedPayMethod.value   = order.payment_method || 'mercadopago';
      navigate('checkout');
    }

    async function loadOrderDetail(id) {
      try {
        const r = await api('GET', `/orders/${id}`);
        // Asegurar que tracking existe
        if (!r.tracking) r.tracking = [];
        selectedOrder.value = r;
      } catch { toast('Error al cargar el detalle.', 'error'); }
    }

    // ─── Admin ────────────────────────────────────────────
    async function loadDashboard() {
      try {
        const r = await api('GET', '/admin/dashboard');
        adminDash.value = r;
      } catch {}
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
      if (auth.value.user) loadUnreadCount();

      // Polling cada 30s para refrescar badge notificaciones
      setInterval(() => {
        if (auth.value.user) loadUnreadCount();
      }, 30000);

      // Detectar URL directa /products/{slug} — para links compartidos en RRSS
      const path = window.location.pathname;
      const slugMatch = path.match(/^\/products\/([^\/]+)$/);
      if (slugMatch) {
        const slug = slugMatch[1];
        try {
          const product = await api('GET', `/products/${slug}`);
          if (product && product.id) {
            selectedProduct.value = product;
            activeImage.value = product.images?.[0]?.url || product.primary_image || null;
            detailQty.value = 1;
            currentView.value = 'product-detail';
            // Actualizar URL sin recargar
            window.history.replaceState({}, '', `/products/${slug}`);
          }
        } catch { navigate('home'); }
      }
    });

    return {
      currentView, appLoading, isAdminRoute, adminView, adminSearch, adminOrderFilter,
      searchQuery, activeCategory, detailQty, activeImage,
      auth, loginForm, registerForm, sellerModal,
      categories, products, filters, selectedProduct,
      cart, orders, ordersLoading,
      adminDash, adminUsers, adminProducts, adminOrders,
      toasts,
      navigate, doLogin, doRegister, logout, doActivateSeller,
      loadProducts, viewProduct, filterByCategory, doSearch, applyFilters, resetFilters, changePage,
      addToCart, updateCartItem, removeCartItem, toggleWishlist,
      loadOrders, loadOrderDetail, selectedOrder, retryPayment, loadDashboard, loadAdminUsers, loadAdminProducts, loadAdminOrders,
      profileTab, profileLoading, profileSuccess, profileErrors, profileData,
      pwdForm, pwdErrors, pwdSuccess, pwdStrength, showPwd,
      addresses, addressesLoading, addressForm, addressErrors,
      notifPrefs, privacyPrefs,
      confirmDeleteAccount, deleteAccountConfirmText,
      saveProfileData, savePassword, loadAddresses, formatRutInput, isValidRut, checkRut,
      avatarPreview, avatarFileName, avatarUploading,
      triggerAvatarUpload, onAvatarSelected, uploadAvatar, cancelAvatar,
      openAddressForm, saveAddress, setDefaultAddress, deleteAddress,
      savePrefs, doDeleteAccount, initProfileData,
      paymentMethods, activePaymentMethods, paymentMethodsSaved,
      savePaymentMethods, loadPaymentMethods, loadVendorPaymentMethods,
      vendorPaymentMethods, selectedPayDetails, buildVendorMethods,
      sellerTab, myProducts, myProductsLoading, myProductSearch, myProductStatusFilter,
      productForm, productFormLoading, formErrors, filteredMyProducts, deleteConfirm,
      openProductForm, loadMyProducts, submitProductForm, toggleProductStatus,
      productImages, imgErrors, imgDragOver,
      triggerImageUpload, onImagesSelected, onImageDrop,
      removeImage, setPrimaryImage, moveImage, loadProductImages,
      confirmDeleteProduct, doDeleteProduct, getCategoryName,
      updateAdminUser, updateOrderStatus,
      formatCLP, formatDate, statusBadge, statusLabel, buyNow,
        shareUrl, copyShareLink,
      vendorOrders, vendorOrdersLoading, vendorOrderFilter, selectedVendorOrder,
      vendorOrdersBadge, orderActionLoading, dispatchForm, showCancelOrder, cancelReason,
      orderMessages, chatMessage,
      loadVendorOrders, loadVendorOrderDetail, vendorAcceptOrder,
      vendorDispatchOrder, doCancelOrder, sendOrderMessage,
      isStepDone, getProtocolStepClass, confirmOrderReceived,
      notifications, unreadCount, notifsLoading,
      loadNotifications, readNotif, markAllNotifRead,
      checkoutStep, checkoutLoading, checkoutError, checkoutOrderId, checkoutAmount,
      checkoutOrderNumber, mpInitPoint, bankPayUrl, selectedAddressId, selectedPayMethod,
      mpStatus, connectMercadoPago, disconnectMercadoPago,
      bankStatus, openBankForm, bankForm, bankFormLoading, bankFormError,
      loadBankStatus, saveBankAccount,
      doCheckout, initCheckout, loadMpStatus,
    };
  }
});

app.mount('#app');
</script>
  <!-- FOOTER -->
  <footer class="ms-footer" v-if="!isAdminRoute">
    <span>© 2026 <strong style="color:rgba(255,255,255,.8)">MercadoSordo</strong></span>
    <span class="mx-2">·</span>
    <span>Developed by <a href="https://github.com/NacAbarca" target="_blank">Nac Abarca</a></span>
    <span class="mx-2">·</span>
    <span>PHP 8.2 · Vue 3 · Bootstrap 5</span>
  </footer>

</body>
</html>
