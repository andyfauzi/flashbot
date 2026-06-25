<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Pemesanan Mandiri - {{ $identitas->nama_toko ?? 'Toko NINSKY' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            @if(isset($identitas->tema_portal) && $identitas->tema_portal === 'warm')
                /* Warm: Energetic Orange & Yellow */
                --primary: hsl(24, 95%, 50%);
                --primary-light: hsl(24, 95%, 95%);
                --primary-dark: hsl(24, 95%, 38%);
                --secondary: hsl(45, 90%, 45%);
                --shadow: 0 10px 30px -10px rgba(245, 158, 11, 0.15);
            @elseif(isset($identitas->tema_portal) && $identitas->tema_portal === 'kalem')
                /* Kalem: Calm Mint & Sage Green */
                --primary: hsl(158, 55%, 40%);
                --primary-light: hsl(158, 55%, 95%);
                --primary-dark: hsl(158, 55%, 28%);
                --secondary: hsl(180, 50%, 40%);
                --shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.15);
            @else
                /* Cool: Harmoni Violet & Slate (Default) */
                --primary: hsl(262, 80%, 50%);
                --primary-light: hsl(262, 80%, 95%);
                --primary-dark: hsl(262, 80%, 35%);
                --secondary: hsl(190, 90%, 40%);
                --shadow: 0 10px 30px -10px rgba(124, 58, 237, 0.15);
            @endif
            --bg: hsl(210, 20%, 98%);
            --card-bg: #ffffff;
            --text-main: hsl(220, 15%, 15%);
            --text-muted: hsl(220, 15%, 45%);
            --border: hsl(220, 15%, 90%);
            --success: hsl(150, 80%, 40%);
            --warning: hsl(35, 90%, 50%);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 80px; /* Space for mobile cart float bar */
        }

        /* Offset scroll untuk sticky navbar (tinggi navbar ~58px) */
        [id] {
            scroll-margin-top: 68px;
        }

        /* Header Styling */
        header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        header p {
            font-size: 14px;
            opacity: 0.9;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Sticky Navbar Styling */
        .sticky-navbar {
            position: sticky;
            top: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 50;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid var(--border);
            padding: 12px 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
            transition: var(--transition);
            overflow-x: auto;
            white-space: nowrap;
        }

        .sticky-navbar a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 100px;
            transition: var(--transition);
        }

        .sticky-navbar a:hover, .sticky-navbar a.active {
            color: var(--primary-dark);
            background: var(--primary-light);
        }

        /* Hero Image Section */
        .hero-section {
            position: relative;
            height: 400px;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: #ffffff;
            overflow: hidden;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.7) 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            padding: 24px;
            max-width: 800px;
            width: 100%;
        }

        .hero-content h1 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 12px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            letter-spacing: -0.5px;
        }

        .hero-content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
            opacity: 0.9;
            text-shadow: 0 1px 5px rgba(0,0,0,0.3);
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-btn {
            padding: 12px 24px;
            border-radius: 100px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            border: none;
        }

        .hero-btn-primary {
            background: var(--primary);
            color: white;
        }

        .hero-btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            color: white;
        }

        .hero-btn-secondary {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            color: white;
            border: 1px solid rgba(255,255,255,0.4);
        }

        .hero-btn-secondary:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            color: white;
        }

        /* Gallery CSS */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        
        .gallery-item {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            aspect-ratio: 4/3;
            cursor: pointer;
            position: relative;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }
            backdrop-filter: blur(10px);
            z-index: 50;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid var(--border);
            padding: 12px 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
            transition: var(--transition);
            overflow-x: auto;
            white-space: nowrap;
        }

        .sticky-navbar a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 100px;
            transition: var(--transition);
        }

        .sticky-navbar a:hover, .sticky-navbar a.active {
            color: var(--primary-dark);
            background: var(--primary-light);
        }

        /* Section Styling */
        .page-section {
            padding-top: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--text-main);
            text-align: center;
        }

        .container {
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
            padding: 24px 16px;
        }

        /* Categories Grid (Awal) */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 10px;
        }

        .category-card {
            background: var(--card-bg);
            border-radius: 18px;
            padding: 24px 20px;
            text-align: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .category-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: 0 12px 24px rgba(31, 122, 92, 0.15);
        }

        .category-card-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: rgba(31, 122, 92, 0.1); /* Primary color low opacity */
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }

        .category-card-icon svg {
            width: 28px;
            height: 28px;
            stroke-width: 2px;
        }

        .category-card:hover .category-card-icon {
            transform: scale(1.1);
            background-color: var(--primary);
            color: #ffffff;
        }

        .category-card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .category-card-count {
            font-size: 12px;
            color: var(--text-muted);
        }

        .category-card.active {
            border-color: var(--primary);
            background-color: rgba(31, 122, 92, 0.05); /* Primary color low opacity */
        }

        .category-card.active .category-card-icon {
            background-color: var(--primary);
            color: #ffffff;
        }

        /* Products Grid (Default) */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 10px;
            transition: var(--transition);
        }

        /* Product Card Styling */
        .product-card {
            background: var(--card-bg);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.12);
            border-color: rgba(124, 58, 237, 0.2);
        }

        .product-image-container {
            width: 100%;
            height: 200px;
            background-color: #f3f4f6;
            position: relative;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(255, 255, 255, 0.95);
            padding: 4px 10px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            backdrop-filter: blur(4px);
        }

        .product-content {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #d97706;
        }

        .product-rating span.review-count {
            color: var(--text-muted);
            font-weight: 400;
            font-size: 11px;
        }

        .product-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-main);
            line-height: 1.4;
        }

        .product-desc {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 16px;
            flex: 1;
        }

        .variant-selector-container {
            margin-bottom: 16px;
        }

        .variant-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .variant-select {
            width: 100%;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            font-size: 12px;
            font-weight: 600;
            background: #f9fafb;
            color: var(--text-main);
            outline: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .variant-select:focus {
            border-color: var(--primary);
            background: #ffffff;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
        }

        .product-price {
            display: flex;
            flex-direction: column;
        }

        .price-label {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        .price-value {
            font-size: 18px;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .add-to-cart-btn {
            background: var(--primary);
            color: #ffffff;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
            transition: var(--transition);
        }

        .add-to-cart-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }

        /* products-list Layout overrides */
        .products-grid.products-list {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .products-grid.products-list .product-card {
            flex-direction: row;
            height: 140px;
            align-items: center;
        }

        .products-grid.products-list .product-image-container {
            width: 140px;
            height: 140px;
            flex-shrink: 0;
            border-top-right-radius: 0;
            border-bottom-left-radius: 18px;
        }

        .products-grid.products-list .product-content {
            padding: 16px 20px;
            height: 100%;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .products-grid.products-list .product-rating {
            margin-bottom: 4px;
        }

        .products-grid.products-list .product-desc {
            display: none;
        }

        .products-grid.products-list .product-footer {
            border-top: none;
            padding-top: 0;
            margin-top: 0;
            gap: 20px;
            flex-shrink: 0;
            align-items: center;
        }

        .products-grid.products-list .variant-selector-container {
            margin-bottom: 0;
            width: 180px;
            margin-right: 16px;
        }

        @media (max-width: 768px) {
            .products-grid.products-list .product-card {
                flex-direction: column;
                height: auto;
            }
            .products-grid.products-list .product-image-container {
                width: 100%;
                height: 180px;
            }
            .products-grid.products-list .product-content {
                flex-direction: column;
                align-items: stretch;
            }
            .products-grid.products-list .product-footer {
                margin-top: 12px;
                border-top: 1px solid #f3f4f6;
                padding-top: 12px;
            }
            .products-grid.products-list .variant-selector-container {
                width: 100%;
                margin-right: 0;
                margin-bottom: 12px;
            }
        }

        /* Cart Floating Bar */
        .cart-float-bar {
            position: fixed;
            bottom: 16px;
            left: 16px;
            right: 16px;
            background: #0f172a;
            color: #ffffff;
            padding: 14px 20px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
            z-index: 100;
            cursor: pointer;
            transform: translateY(150px);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .cart-float-bar.show {
            transform: translateY(0);
        }

        .cart-float-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-float-badge {
            background: var(--primary);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 100px;
        }

        .cart-float-price {
            font-size: 15px;
            font-weight: 700;
        }

        /* Cart Sidebar Drawer */
        .cart-drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 200;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .cart-drawer-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        .cart-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 450px;
            max-width: 100%;
            height: 100vh;
            background: #ffffff;
            z-index: 201;
            box-shadow: -10px 0 40px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
        }

        .cart-drawer.open {
            transform: translateX(0);
        }

        .cart-drawer-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-drawer-header h3 {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-main);
        }

        .close-drawer-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--text-muted);
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .close-drawer-btn:hover {
            background: #f1f5f9;
            color: var(--text-main);
        }

        .cart-items-list {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        .empty-cart-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 60%;
            color: var(--text-muted);
            text-align: center;
        }

        .empty-cart-state span {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .cart-item {
            display: flex;
            gap: 14px;
            padding-bottom: 16px;
            margin-bottom: 16px;
            border-bottom: 1px solid #f3f4f6;
            align-items: flex-start;
        }

        .cart-item-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background-color: #f3f4f6;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 3px;
        }

        .cart-item-varian {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 6px;
        }

        .cart-item-price {
            font-size: 13px;
            font-weight: 800;
            color: var(--text-main);
        }

        .qty-controls {
            display: flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 6px;
            width: fit-content;
            background: #f9fafb;
            overflow: hidden;
            margin-top: 8px;
        }

        .qty-btn {
            background: none;
            border: none;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
            cursor: pointer;
            transition: var(--transition);
        }

        .qty-btn:hover {
            background: #e2e8f0;
        }

        .qty-value {
            font-size: 12px;
            font-weight: 700;
            width: 32px;
            text-align: center;
        }

        .cart-drawer-footer {
            padding: 20px;
            border-top: 1px solid var(--border);
            background: #f8fafc;
        }

        .total-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .total-summary-label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .total-summary-value {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .checkout-trigger-btn {
            width: 100%;
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
            transition: var(--transition);
        }

        .checkout-trigger-btn:hover {
            background: var(--primary-dark);
        }

        /* Checkout Form Modal */
        .checkout-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 300;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .checkout-modal-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        .checkout-modal {
            background: #ffffff;
            width: 550px;
            max-width: 100%;
            max-height: 90vh;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .checkout-modal-overlay.open .checkout-modal {
            transform: scale(1);
        }

        .checkout-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .checkout-modal-header h3 {
            font-size: 18px;
            font-weight: 800;
        }

        .checkout-form {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 13px;
            color: var(--text-main);
            outline: none;
            background: #f9fafb;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* Delivery & Payment Badges Selector */
        .option-badges {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
        }

        .option-badge-input {
            display: none;
        }

        .option-badge {
            border: 1px solid var(--border);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            background: #f9fafb;
            color: var(--text-muted);
        }

        .option-badge:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .option-badge-input:checked + .option-badge {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.1);
        }

        .checkout-modal-footer {
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            background: #f8fafc;
            display: flex;
            gap: 12px;
        }

        .btn {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: var(--transition);
            text-align: center;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: var(--text-main);
        }

        .btn-cancel:hover {
            background: #cbd5e1;
        }

        .btn-primary {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        /* Success Confirmation Modal */
        .success-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(6px);
            z-index: 400;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .success-modal-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        .success-modal {
            background: #ffffff;
            width: 450px;
            max-width: 100%;
            border-radius: 24px;
            padding: 32px 24px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .success-modal-overlay.open .success-modal {
            transform: scale(1);
        }

        .success-icon {
            width: 70px;
            height: 70px;
            background: #ecfdf5;
            color: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 20px;
        }

        .success-modal h3 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .success-modal p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .order-number-box {
            background: #f8fafc;
            border: 2px dashed var(--primary);
            padding: 12px;
            border-radius: 12px;
            font-family: monospace;
            font-size: 18px;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 24px;
        }

        .wa-btn {
            background: #25d366;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2);
            transition: var(--transition);
        }

        .wa-btn:hover {
            background: #128c7e;
        }

        .close-success-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            header {
                padding: 30px 16px;
            }

            header h1 {
                font-size: 22px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <header id="beranda" class="hero-section" style="@if(isset($identitas->hero_image_path)) background-image: url('{{ asset('storage/' . $identitas->hero_image_path) }}'); @else background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); @endif">
        @if(isset($identitas->hero_image_path))
            <div class="hero-overlay"></div>
        @endif
        <div class="hero-content">
            <h1>{{ $identitas->nama_toko ?? 'Toko NINSKY' }}</h1>
            @if(isset($meja))
                <div style="background: rgba(255,255,255,0.2); display: inline-block; padding: 8px 20px; border-radius: 100px; font-weight: 700; margin-bottom: 16px; font-size: 15px; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px);">
                    🍽️ Pesan untuk Meja: {{ $meja->nomor_meja }}
                </div>
                <p>Pindai kode meja berhasil. Silakan pesan menu favorit Anda, pesanan akan langsung diproses ke meja ini.</p>
            @else
                <p>Silakan pesan menu favorit Anda secara mandiri di bawah ini. Cepat, mudah, dan langsung diproses!</p>
            @endif

            <div class="hero-buttons">
                @if(!isset($meja) && in_array($identitas->jenis_layanan ?? 'keduanya', ['dine_in', 'keduanya']))
                    <button onclick="openReservasiModal()" class="hero-btn hero-btn-primary">📅 Reservasi Meja</button>
                @endif
                <a href="#katalog" class="hero-btn hero-btn-secondary">Lihat Menu</a>
            </div>
        </div>
    </header>

    <!-- Sticky Navbar -->
    <nav class="sticky-navbar" id="mainNav">
        <a href="#beranda" class="nav-link active">Beranda</a>
        @if(isset($identitas->deskripsi_toko) || isset($identitas->galeri_paths))
            <a href="#tentang" class="nav-link">Tentang Kami</a>
        @endif
        <a href="#katalog" class="nav-link">Katalog</a>
        <a href="#kontak" class="nav-link">Kontak</a>
        <a href="#syarat_ketentuan" class="nav-link">S&K</a>
    </nav>

    <!-- Main Container -->
    <div class="container">
        
        @if(isset($identitas->deskripsi_toko) || !empty($identitas->galeri_paths))
        <section id="tentang" class="page-section">
            @if(isset($identitas->deskripsi_toko))
                <h2 class="section-title">Tentang {{ $identitas->nama_toko ?? 'Kami' }}</h2>
                <div style="background: var(--card-bg); padding: 24px; border-radius: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); font-size: 15px; line-height: 1.8; color: var(--text-muted); white-space: pre-line; margin-bottom: 32px; text-align: center;">
                    {{ $identitas->deskripsi_toko }}
                </div>
            @endif

            @if(!empty($identitas->galeri_paths) && is_array($identitas->galeri_paths) && count($identitas->galeri_paths) > 0)
                <h2 class="section-title">Galeri Suasana</h2>
                <div class="gallery-grid">
                    @foreach($identitas->galeri_paths as $imgPath)
                        <div class="gallery-item">
                            <img src="{{ asset('storage/' . $imgPath) }}" alt="Galeri {{ $identitas->nama_toko }}" loading="lazy">
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
        @endif

        


        <!-- Section Katalog -->
        <section id="katalog" class="page-section">
        <div id="sectionKategori">
            <h2 style="font-size: 20px; font-weight: 800; margin-bottom: 18px; color: var(--text-main); text-align: center;">Pilih Kategori Menu</h2>
            <div class="categories-grid">
                <div class="category-card" onclick="selectCategory('all', 'Semua Menu', this)">
                    <div class="category-card-icon"><i data-lucide="layout-grid"></i></div>
                    <div class="category-card-title">Semua Menu</div>
                    <div class="category-card-count">{{ $kategoris->sum(function($k){ return $k->produks->count(); }) + $produkTanpaKategori->count() }} Produk</div>
                </div>

                @php
                    function getLucideIcon($name) {
                        $n = strtolower($name);
                        if (str_contains($n, 'minum')) return 'coffee';
                        if (str_contains($n, 'makan')) return 'utensils';
                        if (str_contains($n, 'dessert') || str_contains($n, 'kue') || str_contains($n, 'cake') || str_contains($n, 'pastry') || str_contains($n, 'snack')) return 'cake-slice';
                        if (str_contains($n, 'paket') || str_contains($n, 'bundling')) return 'package';
                        if (str_contains($n, 'promo') || str_contains($n, 'diskon')) return 'tag';
                        if (str_contains($n, 'baru') || str_contains($n, 'new')) return 'sparkles';
                        if (str_contains($n, 'spesial') || str_contains($n, 'special')) return 'star';
                        return 'layout-grid'; // fallback
                    }
                @endphp

                @foreach($kategoris as $kat)
                    @if($kat->produks->count() > 0)
                        <div class="category-card" onclick="selectCategory('cat-{{ $kat->id }}', '{{ $kat->nama }}', this)">
                            <div class="category-card-icon"><i data-lucide="{{ getLucideIcon($kat->nama) }}"></i></div>
                            <div class="category-card-title">{{ $kat->nama }}</div>
                            <div class="category-card-count">{{ $kat->produks->count() }} Produk</div>
                        </div>
                    @endif
                @endforeach

                @if($produkTanpaKategori->count() > 0)
                    <div class="category-card" onclick="selectCategory('cat-uncategorized', 'Lainnya', this)">
                        <div class="category-card-icon"><i data-lucide="package"></i></div>
                        <div class="category-card-title">Lainnya</div>
                        <div class="category-card-count">{{ $produkTanpaKategori->count() }} Produk</div>
                    </div>
                @endif
            </div>

            @php
                $favoriteProducts = \App\Models\Produk::where('aktif', true)->where('is_favorite', true)->with(['varians', 'addons', 'kategori'])->get();
            @endphp

            @if($favoriteProducts->count() > 0)
            <div id="sectionFavorit" style="margin-top: 48px; margin-bottom: 24px;">
                <h2 style="font-size: 20px; font-weight: 800; margin-bottom: 18px; color: var(--text-main); text-align: center;">⭐ Menu Favorit Pilihan</h2>
                <div class="products-grid">
                    @foreach($favoriteProducts as $prod)
                        @include('chatbot.portal_product_card', ['produk' => $prod, 'catId' => 'cat-fav'])
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Section Produk (Disembunyikan di Awal) -->
        <div id="sectionProduk" style="display: none;">
            <div class="produk-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <button class="btn-back-kategori" onclick="showCategoriesSection()" style="background: #ffffff; border: 1px solid var(--border); padding: 10px 18px; border-radius: 100px; font-size: 13px; font-weight: 700; cursor: pointer; color: var(--text-main); display: flex; align-items: center; gap: 6px; box-shadow: var(--shadow); transition: var(--transition);">
                    ← Kembali ke Kategori
                </button>
                <h2 id="activeCategoryTitle" style="font-size: 22px; font-weight: 800; margin: 0; color: var(--text-main);">Semua Menu</h2>
                
                <!-- Layout Toggle (Grid / List) -->
                <div class="layout-toggle" style="display: flex; background: #e2e8f0; padding: 4px; border-radius: 10px; gap: 4px;">
                    <button id="btnLayoutGrid" class="active" onclick="changeLayout('grid')" style="border: none; background: #ffffff; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; transition: var(--transition); display: flex; align-items: center; gap: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        🔳 Grid
                    </button>
                    <button id="btnLayoutList" onclick="changeLayout('list')" style="border: none; background: transparent; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--text-muted); transition: var(--transition); display: flex; align-items: center; gap: 4px;">
                        ▤ List
                    </button>
                </div>
            </div>

            <!-- Catalog Grid/List Container -->
            <div class="products-grid" id="productsDisplayContainer">
                @foreach($kategoris as $kat)
                    @foreach($kat->produks as $prod)
                        @include('chatbot.portal_product_card', ['produk' => $prod, 'catId' => 'cat-'.$kat->id])
                    @endforeach
                @endforeach

                @foreach($produkTanpaKategori as $prod)
                    @include('chatbot.portal_product_card', ['produk' => $prod, 'catId' => 'cat-uncategorized'])
                @endforeach
            </div>
        </div>
        </section>

        <!-- Section Kontak -->
        <section id="kontak" class="page-section">
            <h2 class="section-title">Kontak & Informasi Toko</h2>
            <div style="background: var(--card-bg); padding: 24px; border-radius: 16px; box-shadow: var(--shadow); border: 1px solid var(--border);">
                @if(!empty($identitas->kontak_portal))
                    <div style="white-space: pre-line; line-height: 1.6; color: var(--text-main);">
                        {{ $identitas->kontak_portal }}
                    </div>
                @else
                    <div style="line-height: 1.6; color: var(--text-main);">
                        <strong>Jam Operasional:</strong><br>
                        {{ $identitas->jam_buka ? \Carbon\Carbon::parse($identitas->jam_buka)->format('H:i') : '-' }} s/d {{ $identitas->jam_tutup ? \Carbon\Carbon::parse($identitas->jam_tutup)->format('H:i') : '-' }}<br><br>
                        
                        <strong>Telepon / WhatsApp:</strong><br>
                        {{ $identitas->nomor_telepon ?? '-' }}<br><br>
                        
                        <strong>Alamat:</strong><br>
                        {{ $identitas->alamat_toko ?? '-' }}
                    </div>
                @endif
                
                @if(!empty($identitas->nomor_telepon))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $identitas->nomor_telepon) }}" target="_blank" style="display: inline-block; margin-top: 16px; background: #25D366; color: white; padding: 10px 20px; border-radius: 100px; text-decoration: none; font-weight: 700; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2);">
                        <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
                    </a>
                @endif
            </div>
        </section>

        <!-- Section Syarat & Ketentuan -->
        <section id="syarat_ketentuan" class="page-section">
            <h2 class="section-title">Syarat & Ketentuan</h2>
            <div style="background: var(--card-bg); padding: 24px; border-radius: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); font-size: 14px; color: var(--text-muted); line-height: 1.6; white-space: pre-line;">
                @if(!empty($identitas->syarat_ketentuan_portal))
                    {{ $identitas->syarat_ketentuan_portal }}
                @else
                    1. Pemesanan yang sudah dibayar tidak dapat dibatalkan.
                    2. Untuk reservasi dine-in, harap datang tepat waktu sesuai jadwal yang ditentukan.
                    3. Keterlambatan lebih dari 30 menit dapat menyebabkan reservasi dibatalkan otomatis tanpa pengembalian dana (jika ada DP).
                    4. Segala bentuk kerusakan fasilitas oleh pengunjung menjadi tanggung jawab pengunjung.
                    5. Syarat dan ketentuan dapat berubah sewaktu-waktu sesuai kebijakan toko.
                @endif
            </div>
        </section>

    </div>

    <!-- Mobile Float Cart Bar -->
    <div class="cart-float-bar" id="cartFloatBar" onclick="openCartDrawer()">
        <div class="cart-float-left">
            <span>🛒</span>
            <span class="cart-float-badge" id="cartBadgeQty">0</span>
            <span>Lihat Keranjang</span>
        </div>
        <div class="cart-float-price" id="cartFloatTotal">Rp 0</div>
    </div>

    <!-- Cart Drawer Modal Overlay -->
    <div class="cart-drawer-overlay" id="cartDrawerOverlay" onclick="closeCartDrawer()"></div>
    
    <!-- Cart Drawer -->
    <div class="cart-drawer" id="cartDrawer">
        <div class="cart-drawer-header">
            <h3>Keranjang Belanja</h3>
            <button class="close-drawer-btn" onclick="closeCartDrawer()">✕</button>
        </div>

        <div class="cart-items-list" id="cartItemsList">
            <div class="empty-cart-state">
                <span>🛒</span>
                <p>Keranjang belanja Anda kosong.<br>Pilih menu lezat kami di katalog!</p>
            </div>
        </div>

        <div class="cart-drawer-footer">
            <div class="total-summary-row">
                <span class="total-summary-label">Subtotal Barang</span>
                <span class="total-summary-value" id="cartSubtotal">Rp 0</span>
            </div>
            <button class="checkout-trigger-btn" onclick="openCheckoutModal()">Checkout Sekarang</button>
        </div>
    </div>

    <!-- Checkout Modal Overlay -->
    <div class="checkout-modal-overlay" id="checkoutOverlay">
        <div class="checkout-modal">
            <div class="checkout-modal-header">
                <h3>Formulir Pemesanan</h3>
                <button class="close-drawer-btn" onclick="closeCheckoutModal()">✕</button>
            </div>
            <form class="checkout-form" id="checkoutForm" onsubmit="submitCheckout(event)">
                <div class="form-group">
                    <label for="inputName">Nama Penerima / Pemesan</label>
                    <input type="text" class="form-control" id="inputName" required placeholder="Contoh: Budi Santoso">
                </div>

                <div class="form-group">
                    <label for="inputPhone">Nomor WhatsApp Aktif @if(isset($meja)) <span style="text-transform: none; font-weight: 400; font-size: 11px;">(Opsional)</span> @endif</label>
                    <input type="tel" class="form-control" id="inputPhone" {{ !isset($meja) ? 'required' : '' }} placeholder="Contoh: 08123456789">
                </div>

                @if(isset($meja))
                    <input type="hidden" name="meja_id" id="inputMejaId" value="{{ $meja->id }}">
                    <input type="hidden" name="tipe_pengiriman" value="dine_in" id="inputTypePengiriman">
                @else
                    <div class="form-group">
                        <label>Metode Pengiriman</label>
                        <div class="option-badges">
                            <label>
                                <input type="radio" name="tipe_pengiriman" value="ambil_sendiri" class="option-badge-input" checked onchange="toggleAlamatField(false)">
                                <div class="option-badge">🚶 Ambil Sendiri</div>
                            </label>
                            <label>
                                <input type="radio" name="tipe_pengiriman" value="kurir_toko" class="option-badge-input" onchange="toggleAlamatField(true)">
                                <div class="option-badge">🛵 Kurir Toko</div>
                            </label>
                            <label>
                                <input type="radio" name="tipe_pengiriman" value="kurir_customer" class="option-badge-input" onchange="toggleAlamatField(true)">
                                <div class="option-badge">🚗 Ojol (Grab/Gojek)</div>
                            </label>
                        </div>
                    </div>
                @endif

                <div class="form-group" id="alamatGroup" style="display: none;">
                    <label for="inputAlamat">Alamat Pengantaran Lengkap</label>
                    <textarea class="form-control" id="inputAlamat" placeholder="Tuliskan jalan, nomor rumah, RT/RW, dan patokan..."></textarea>
                </div>

                @if(!isset($meja))
                <div class="form-row">
                    <div class="form-group">
                        <label for="inputTanggal">Jadwal Pengambilan / Kirim</label>
                        <input type="datetime-local" class="form-control" id="inputTanggal" required min="{{ date('Y-m-d\TH:i') }}">
                    </div>
                @else
                <div class="form-row" style="grid-template-columns: 1fr;">
                @endif

                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <div class="option-badges">
                            <label>
                                <input type="radio" name="metode_pembayaran" value="cod" class="option-badge-input" checked>
                                <div class="option-badge">💵 Cash di Kasir</div>
                            </label>
                            <label>
                                <input type="radio" name="metode_pembayaran" value="manual" class="option-badge-input">
                                <div class="option-badge">🏦 Transfer Manual</div>
                            </label>
                            @if(isset($identitas) && $identitas->is_midtrans_active && $identitas->midtrans_server_key)
                            <label>
                                <input type="radio" name="metode_pembayaran" value="midtrans" class="option-badge-input">
                                <div class="option-badge">💳 Midtrans (Otomatis)</div>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
            <div class="checkout-modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeCheckoutModal()">Batal</button>
                <button type="submit" form="checkoutForm" class="btn btn-primary" id="btnSubmitOrder">Kirim Pesanan</button>
            </div>
        </div>
    </div>

    <!-- Reservasi Modal Overlay -->
    <div class="checkout-modal-overlay" id="reservasiOverlay">
        <div class="checkout-modal">
            <div class="checkout-modal-header">
                <h3>Formulir Reservasi Tempat</h3>
                <button class="close-drawer-btn" onclick="closeReservasiModal()">✕</button>
            </div>
            <form class="checkout-form" id="reservasiForm" onsubmit="submitReservasi(event)">
                <div class="form-group">
                    <label for="resName">Nama Pemesan</label>
                    <input type="text" class="form-control" id="resName" required placeholder="Contoh: Budi Santoso">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="resPhone">Nomor WhatsApp Aktif</label>
                        <input type="tel" class="form-control" id="resPhone" required placeholder="Contoh: 08123456789">
                    </div>
                    <div class="form-group">
                        <label for="resPax">Jumlah Orang (Pax)</label>
                        <input type="number" class="form-control" id="resPax" required min="1" value="2">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="resTanggal">Tanggal Reservasi</label>
                        <input type="date" class="form-control" id="resTanggal" required>
                    </div>
                    <div class="form-group">
                        <label for="resJam">Jam Reservasi</label>
                        <input type="time" class="form-control" id="resJam" required>
                    </div>
                </div>

                <!-- Tombol Cek Ketersediaan -->
                <div class="form-group">
                    <button type="button" id="btnCekMeja" onclick="checkTableAvailability()" style="
                        width: 100%;
                        padding: 12px;
                        background: var(--primary-light);
                        color: var(--primary-dark);
                        border: 1.5px dashed var(--primary);
                        border-radius: 12px;
                        font-weight: 700;
                        font-size: 14px;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                    ">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Cek Ketersediaan Meja
                    </button>
                </div>

                <!-- Table Availability Container -->
                <div class="form-group" id="tableAvailabilityContainer">
                    <label>Pilih Meja <span style="font-size:11px;font-weight:400;color:var(--text-muted);">(Opsional)</span></label>
                    <div id="ketersediaanMsg" style="margin-bottom: 8px; font-size: 12px; font-weight: 600; color: var(--primary);"></div>
                    <div id="tableGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 10px; margin-top: 8px;">
                        <!-- Placeholder state sebelum dicek -->
                        <div id="tablePlaceholder" style="grid-column: 1/-1; text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px; background: #f8fafc; border-radius: 10px; border: 1px dashed var(--border);">
                            <span style="font-size: 28px; display: block; margin-bottom: 6px;">🪑</span>
                            Klik tombol di atas untuk melihat ketersediaan meja
                        </div>
                    </div>
                    <input type="hidden" id="resMejaId">
                    <small class="text-muted" style="display: block; margin-top: 6px; font-size: 11px;">Jika butuh meja berdekatan, pilih salah satu lalu tambahkan catatan.</small>
                </div>

                <div class="form-group">
                    <label for="resCatatan">Catatan Tambahan</label>
                    <textarea class="form-control" id="resCatatan" placeholder="Contoh: Kursi bayi, dekat jendela, ulang tahun..."></textarea>
                </div>
            </form>
            <div class="checkout-modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeReservasiModal()">Batal</button>
                <button type="submit" form="reservasiForm" class="btn btn-primary" id="btnSubmitReservasi">Kirim Reservasi</button>
            </div>
        </div>
    </div>

    <!-- Success Confirmation Modal Overlay -->
    <div class="success-modal-overlay" id="successOverlay">
        <div class="success-modal">
            <div class="success-icon">✓</div>
            <h3>Pemesanan Berhasil!</h3>
            <p>Pesanan Anda telah tercatat di sistem kami dan notifikasi telah dikirim langsung ke grup toko.</p>
            <div class="order-number-box" id="successOrderNumber">ORD-XXXXXX</div>
            
            <a href="#" class="wa-btn" id="successWaLink" target="_blank">
                <span>💬</span> Hubungi Toko via WhatsApp
            </a>
            
            <button class="close-success-btn" onclick="resetAndCloseSuccess()">Tutup</button>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        let cart = [];

        // Category Screen Navigation
        function selectCategory(catClass, catName, element = null) {
            document.getElementById('activeCategoryTitle').textContent = catName;

            // Handle active class if element is provided
            if (element) {
                const allCards = document.querySelectorAll('.category-card');
                allCards.forEach(card => card.classList.remove('active'));
                element.classList.add('active');
            }

            // Toggle cards visibility
            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                if (catClass === 'all') {
                    card.style.display = 'flex';
                } else {
                    if (card.classList.contains(catClass)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });

            // Transition UI sections
            document.getElementById('sectionKategori').style.display = 'none';
            document.getElementById('sectionProduk').style.display = 'block';
            
            // Scroll ke bagian produk
            document.getElementById('katalog').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function showCategoriesSection() {
            document.getElementById('sectionProduk').style.display = 'none';
            document.getElementById('sectionKategori').style.display = 'block';
            document.getElementById('katalog').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Layout Toggle (Grid vs List)
        function changeLayout(mode) {
            const container = document.getElementById('productsDisplayContainer');
            const btnGrid = document.getElementById('btnLayoutGrid');
            const btnList = document.getElementById('btnLayoutList');

            if (mode === 'list') {
                container.classList.add('products-list');
                btnList.classList.add('active');
                btnList.style.background = '#ffffff';
                btnList.style.color = 'var(--text-main)';
                btnList.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';

                btnGrid.classList.remove('active');
                btnGrid.style.background = 'transparent';
                btnGrid.style.color = 'var(--text-muted)';
                btnGrid.style.boxShadow = 'none';
            } else {
                container.classList.remove('products-list');
                btnGrid.classList.add('active');
                btnGrid.style.background = '#ffffff';
                btnGrid.style.color = 'var(--text-main)';
                btnGrid.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)';

                btnList.classList.remove('active');
                btnList.style.background = 'transparent';
                btnList.style.color = 'var(--text-muted)';
                btnList.style.boxShadow = 'none';
            }
        }

        // Dynamic Price Display & Addon Toggling
        function updateProductPriceDisplay(productCard) {
            const basePrice = parseFloat(productCard.getAttribute('data-base-price')) || 0;
            const variantSelect = productCard.querySelector('.variant-select');
            
            let price = basePrice;
            let stock = parseInt(productCard.getAttribute('data-base-stok')) || 0;

            if (variantSelect) {
                const selectedOption = variantSelect.options[variantSelect.selectedIndex];
                price = parseFloat(selectedOption.getAttribute('data-price')) || basePrice;
                stock = parseInt(selectedOption.getAttribute('data-stok')) || 0;
            }

            // Calculate active addons
            let addonsPrice = 0;
            const checkboxes = productCard.querySelectorAll('.addon-checkbox');
            checkboxes.forEach(cb => {
                const textInput = cb.closest('.addon-item-label').querySelector('.addon-text-input');
                if (cb.checked) {
                    addonsPrice += parseFloat(cb.getAttribute('data-price')) || 0;
                    if (textInput) {
                        textInput.style.display = 'block';
                        textInput.setAttribute('required', 'required');
                    }
                } else {
                    if (textInput) {
                        textInput.style.display = 'none';
                        textInput.removeAttribute('required');
                    }
                }
            });

            // Update Price UI
            const priceValElement = productCard.querySelector('.price-value');
            if (priceValElement) {
                priceValElement.textContent = formatRupiah(price + addonsPrice);
            }

            // Validate Stock
            const isMadeToOrder = productCard.getAttribute('data-made-to-order') === '1';
            const actionBtn = productCard.querySelector('.add-to-cart-btn');

            if (actionBtn) {
                if (!isMadeToOrder && stock <= 0) {
                    actionBtn.textContent = 'Habis';
                    actionBtn.setAttribute('disabled', 'disabled');
                    actionBtn.className = 'add-to-cart-btn btn-disabled';
                    actionBtn.style.background = '#cbd5e1';
                    actionBtn.style.color = '#94a3b8';
                    actionBtn.style.boxShadow = 'none';
                    actionBtn.style.cursor = 'not-allowed';
                    actionBtn.style.width = 'auto';
                    actionBtn.style.borderRadius = '100px';
                    actionBtn.style.padding = '0 16px';
                    actionBtn.style.fontSize = '12px';
                } else {
                    actionBtn.textContent = '+';
                    actionBtn.removeAttribute('disabled');
                    actionBtn.className = 'add-to-cart-btn';
                    actionBtn.style.background = 'var(--primary)';
                    actionBtn.style.color = '#ffffff';
                    actionBtn.style.boxShadow = '0 4px 12px rgba(124, 58, 237, 0.2)';
                    actionBtn.style.cursor = 'pointer';
                    actionBtn.style.width = '38px';
                    actionBtn.style.height = '38px';
                    actionBtn.style.borderRadius = '50%';
                    actionBtn.style.padding = '0';
                    actionBtn.style.fontSize = '18px';
                }
            }
        }

        // Add to Cart Action
        function addToCart(productId, productName, basePrice, imageUrl) {
            const parentCard = event.target.closest('.product-card');
            const variantSelect = parentCard.querySelector('.variant-select');
            
            let varianId = null;
            let varianName = '';
            let price = basePrice;
            let stock = parseInt(parentCard.getAttribute('data-base-stok')) || 0;

            if (variantSelect) {
                const selectedOption = variantSelect.options[variantSelect.selectedIndex];
                varianId = selectedOption.value;
                varianName = selectedOption.textContent;
                price = parseFloat(selectedOption.getAttribute('data-price')) || basePrice;
                stock = parseInt(selectedOption.getAttribute('data-stok')) || 0;
            }

            const isMadeToOrder = parentCard.getAttribute('data-made-to-order') === '1';

            // Double check stock validation in JS
            if (!isMadeToOrder && stock <= 0) {
                alert('Maaf, produk ini sedang habis.');
                return;
            }

            // Get selected addons
            const selectedAddons = [];
            const checkboxes = parentCard.querySelectorAll('.addon-checkbox:checked');
            let addonsPrice = 0;
            let hasInvalidText = false;

            checkboxes.forEach(cb => {
                const textInput = cb.closest('.addon-item-label').querySelector('.addon-text-input');
                let teksVal = null;
                if (textInput) {
                    teksVal = textInput.value.trim();
                    if (!teksVal) {
                        hasInvalidText = true;
                        textInput.focus();
                    }
                }
                addonsPrice += parseFloat(cb.getAttribute('data-price')) || 0;
                selectedAddons.push({
                    id: cb.value,
                    nama: cb.getAttribute('data-name'),
                    harga: parseFloat(cb.getAttribute('data-price')) || 0,
                    teks: teksVal
                });
            });

            if (hasInvalidText) {
                alert('Silakan isi pesan ucapan/teks tambahan terlebih dahulu.');
                return;
            }

            // Unique key to distinguish items with different variant/addons
            const cartKey = productId + '_' + (varianId || 'none') + '_' + selectedAddons.map(a => a.id + ':' + (a.teks || '')).join('|');

            // Check if item already exists in cart
            const existingItem = cart.find(item => item.cartKey === cartKey);

            if (existingItem) {
                // If not made to order, ensure we don't exceed stock limit
                if (!isMadeToOrder && existingItem.qty + 1 > stock) {
                    alert(`Maaf, batas pembelian maksimal sesuai sisa stok (${stock} pcs).`);
                    return;
                }
                existingItem.qty += 1;
            } else {
                cart.push({
                    cartKey: cartKey,
                    id: productId,
                    nama: productName,
                    varian_id: varianId,
                    varian_nama: varianName,
                    harga: price,
                    addons: selectedAddons,
                    foto: imageUrl,
                    qty: 1
                });
            }

            updateCartUI();
            
            // Pop temporary effect to show cart updated
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = '✓';
            btn.style.background = 'var(--success)';
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.background = 'var(--primary)';
            }, 800);
        }

        // Update Cart Drawer and Mobile Badge
        function updateCartUI() {
            const cartItemsList = document.getElementById('cartItemsList');
            const cartBadgeQty = document.getElementById('cartBadgeQty');
            const cartFloatTotal = document.getElementById('cartFloatTotal');
            const cartSubtotal = document.getElementById('cartSubtotal');
            const cartFloatBar = document.getElementById('cartFloatBar');

            if (cart.length === 0) {
                cartItemsList.innerHTML = `
                    <div class="empty-cart-state">
                        <span>🛒</span>
                        <p>Keranjang belanja Anda kosong.<br>Pilih menu lezat kami di katalog!</p>
                    </div>
                `;
                cartBadgeQty.textContent = '0';
                cartFloatTotal.textContent = 'Rp 0';
                cartSubtotal.textContent = 'Rp 0';
                cartFloatBar.classList.remove('show');
                return;
            }

            // Show float bar
            cartFloatBar.classList.add('show');

            let totalQty = 0;
            let subtotal = 0;
            let itemsHTML = '';

            cart.forEach((item, index) => {
                totalQty += item.qty;
                
                let itemAddonsPrice = 0;
                let addonsDisplay = '';
                
                if (item.addons && item.addons.length > 0) {
                    addonsDisplay = '<div class="cart-item-addons" style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: flex; flex-direction: column; gap: 2px;">';
                    item.addons.forEach(a => {
                        itemAddonsPrice += a.harga;
                        const textSuffix = a.teks ? ` ("${a.teks}")` : '';
                        addonsDisplay += `<span>+ ${a.nama}${textSuffix} (+${formatRupiah(a.harga)})</span>`;
                    });
                    addonsDisplay += '</div>';
                }

                const itemPrice = item.harga + itemAddonsPrice;
                const itemTotal = itemPrice * item.qty;
                subtotal += itemTotal;

                itemsHTML += `
                    <div class="cart-item">
                        <img src="${item.foto ? item.foto : '/storage/default-product.png'}" class="cart-item-img" alt="${item.nama}">
                        <div class="cart-item-details">
                            <div class="cart-item-title">${item.nama}</div>
                            ${item.varian_nama ? `<div class="cart-item-varian">${item.varian_nama}</div>` : ''}
                            ${addonsDisplay}
                            <div class="cart-item-price">${formatRupiah(itemPrice)}</div>
                            <div class="qty-controls">
                                <button class="qty-btn" onclick="changeQty(${index}, -1)">-</button>
                                <span class="qty-value">${item.qty}</span>
                                <button class="qty-btn" onclick="changeQty(${index}, 1)">+</button>
                            </div>
                        </div>
                    </div>
                `;
            });

            cartItemsList.innerHTML = itemsHTML;
            cartBadgeQty.textContent = totalQty;
            cartFloatTotal.textContent = formatRupiah(subtotal);
            cartSubtotal.textContent = formatRupiah(subtotal);
        }

        // Change Item Qty inside Cart Drawer
        function changeQty(index, offset) {
            const item = cart[index];
            const parentCard = document.querySelector(`.product-card[data-id="${item.id}"]`);
            
            if (parentCard && offset > 0) {
                const isMadeToOrder = parentCard.getAttribute('data-made-to-order') === '1';
                
                // Get stock based on variant or base product
                let stock = parseInt(parentCard.getAttribute('data-base-stok')) || 0;
                const variantSelect = parentCard.querySelector('.variant-select');
                if (variantSelect && item.varian_id) {
                    const option = variantSelect.querySelector(`option[value="${item.varian_id}"]`);
                    if (option) {
                        stock = parseInt(option.getAttribute('data-stok')) || 0;
                    }
                }

                if (!isMadeToOrder && item.qty + offset > stock) {
                    alert(`Maaf, batas pembelian maksimal sesuai sisa stok (${stock} pcs).`);
                    return;
                }
            }

            item.qty += offset;
            if (item.qty <= 0) {
                cart.splice(index, 1);
            }
            updateCartUI();
        }

        // Show/Hide Address Field
        function toggleAlamatField(show) {
            const alamatGroup = document.getElementById('alamatGroup');
            const inputAlamat = document.getElementById('inputAlamat');
            if (show) {
                alamatGroup.style.display = 'block';
                inputAlamat.setAttribute('required', 'required');
            } else {
                alamatGroup.style.display = 'none';
                inputAlamat.removeAttribute('required');
            }
        }

        // Drawer Controls
        function openCartDrawer() {
            document.getElementById('cartDrawer').classList.add('open');
            document.getElementById('cartDrawerOverlay').classList.add('open');
        }

        function closeCartDrawer() {
            document.getElementById('cartDrawer').classList.remove('open');
            document.getElementById('cartDrawerOverlay').classList.remove('open');
        }

        function openCheckoutModal() {
            if (cart.length === 0) return;
            closeCartDrawer();
            document.getElementById('checkoutOverlay').classList.add('open');
        }

        function closeCheckoutModal() {
            document.getElementById('checkoutOverlay').classList.remove('open');
        }

        // Submit Checkout via AJAX
        function submitCheckout(e) {
            e.preventDefault();
            
            const btnSubmit = document.getElementById('btnSubmitOrder');
            const originalBtnText = btnSubmit.textContent;
            btnSubmit.textContent = 'Mengirim...';
            btnSubmit.setAttribute('disabled', 'disabled');

            const tipePengirimanInput = document.querySelector('input[name="tipe_pengiriman"]:checked') || document.getElementById('inputTypePengiriman');
            const tipePengiriman = tipePengirimanInput ? tipePengirimanInput.value : '';
            const metodePembayaran = document.querySelector('input[name="metode_pembayaran"]:checked').value;
            const mejaIdInput = document.getElementById('inputMejaId');
            const tanggalInput = document.getElementById('inputTanggal');
            const alamatInput = document.getElementById('inputAlamat');
            
            const payload = {
                nama_penerima: document.getElementById('inputName').value,
                nomor_wa: document.getElementById('inputPhone').value,
                tipe_pengiriman: tipePengiriman,
                alamat_penerima: tipePengiriman !== 'ambil_sendiri' && alamatInput ? alamatInput.value : null,
                tanggal_diambil: tanggalInput ? tanggalInput.value : null,
                metode_pembayaran: metodePembayaran,
                meja_id: mejaIdInput ? mejaIdInput.value : null,
                cart: cart
            };

            fetch('/portal/order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    closeCheckoutModal();
                    
                    if (data.snap_token) {
                        // Buka popup Midtrans
                        window.snap.pay(data.snap_token, {
                            onSuccess: function(result){
                                showSuccessMessage(data, payload);
                            },
                            onPending: function(result){
                                showSuccessMessage(data, payload);
                            },
                            onError: function(result){
                                alert('Pembayaran gagal. Silakan coba lagi atau bayar manual via WhatsApp.');
                                showSuccessMessage(data, payload);
                            },
                            onClose: function(){
                                alert('Anda menutup popup pembayaran sebelum menyelesaikan pembayaran. Anda dapat membayar manual via WhatsApp.');
                                showSuccessMessage(data, payload);
                            }
                        });
                    } else {
                        // Tidak pakai midtrans atau COD
                        showSuccessMessage(data, payload);
                    }
                }
            })
            .catch(error => {
                let errorMsg = error.message || 'Terjadi kesalahan sistem, silakan coba lagi.';
                if (error.errors) {
                    errorMsg += '\n\nDetail:\n';
                    for (const key in error.errors) {
                        errorMsg += `- ${error.errors[key][0]}\n`;
                    }
                }
                alert(errorMsg);
            })
            .finally(() => {
                btnSubmit.textContent = originalBtnText;
                btnSubmit.removeAttribute('disabled');
            });
        }

        function showSuccessMessage(data, payload) {
            // Show success confirmation
            document.getElementById('successOrderNumber').textContent = data.nomor_order;
            
            // Check outside operating hours
            let operasionalNote = '';
            if (data.is_luar_jam_operasional) {
                operasionalNote = `\n\n*Catatan:* Pesanan Anda masuk di luar jam operasional. Kami akan memproses pesanan Anda pada jam buka toko kami (${data.jam_buka}).\n`;
                // Add alert directly to DOM if needed
                const successContainer = document.querySelector('#successOverlay .success-modal');
                if (successContainer && !document.getElementById('opWarningAlert')) {
                    const alertHtml = `<div id="opWarningAlert" class="alert alert-warning mt-3" role="alert" style="font-size: 14px; background: #fff3cd; color: #856404; padding: 10px; border-radius: 8px; border: 1px solid #ffeeba;">
                        <i class="fa-solid fa-clock me-1"></i> Pesanan masuk di luar jam operasional dan akan diproses pada jam buka toko (${data.jam_buka}).
                    </div>`;
                    // Insert before the WA button
                    const waBtn = document.getElementById('successWaLink');
                    if (waBtn) {
                        waBtn.insertAdjacentHTML('beforebegin', alertHtml);
                    } else {
                        successContainer.insertAdjacentHTML('beforeend', alertHtml);
                    }
                }
            } else {
                const opAlert = document.getElementById('opWarningAlert');
                if (opAlert) opAlert.remove();
            }

            // Build WhatsApp message link for manual confirmation
            const waText = encodeURIComponent(
                `Halo, saya ingin mengonfirmasi pesanan saya dari Portal.\n\n` +
                `*Nomor Order:* ${data.nomor_order}\n` +
                `*Nama:* ${payload.nama_penerima}\n` +
                `*Total Belanja:* ${formatRupiah(data.total_biaya)}\n` +
                operasionalNote +
                `\nMohon segera diproses ya. Terima kasih! 🙏`
            );
            const waNumber = '{{ preg_replace("/[^0-9]/", "", $identitas->nomor_telepon ?? "6282123456789") }}';
            document.getElementById('successWaLink').href = `https://wa.me/${waNumber}?text=${waText}`;
            
            document.getElementById('successOverlay').classList.add('open');
        }

        // Reset Cart and Form after success Close
        function resetAndCloseSuccess() {
            cart = [];
            updateCartUI();
            document.getElementById('checkoutForm').reset();
            toggleAlamatField(false);
            document.getElementById('successOverlay').classList.remove('open');
            showCategoriesSection(); // Return to category selection page
        }

        function openReservasiModal() {
            document.getElementById('reservasiOverlay').classList.add('open');
        }

        function closeReservasiModal() {
            document.getElementById('reservasiOverlay').classList.remove('open');
        }

        function checkTableAvailability() {
            const tanggal = document.getElementById('resTanggal').value;
            const jam = document.getElementById('resJam').value;
            const pax = document.getElementById('resPax').value;

            if (!tanggal || !jam) {
                alert('Mohon isi tanggal dan jam reservasi terlebih dahulu.');
                return;
            }

            const btn = document.getElementById('btnCekMeja');
            btn.textContent = 'Memuat...';
            btn.disabled = true;

            const tanggalWaktu = tanggal + 'T' + jam;
            fetch('{{ route("portal.check_meja", ["nama_toko_slug" => request()->route("nama_toko_slug")]) }}?tanggal_waktu=' + encodeURIComponent(tanggalWaktu) + '&pax=' + pax, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg> Cek Ketersediaan Meja';
                btn.disabled = false;

                if(data.status === 'success') {
                    const grid = document.getElementById('tableGrid');
                    const msgContainer = document.getElementById('ketersediaanMsg');


                    grid.innerHTML = '';

                    if (data.ketersediaan && data.ketersediaan.rekomendasi_msg) {
                        msgContainer.textContent = data.ketersediaan.rekomendasi_msg;
                        msgContainer.style.display = 'block';
                    } else {
                        msgContainer.style.display = 'none';
                    }

                    if (!data.mejas || data.mejas.length === 0) {
                        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:20px;color:var(--text-muted);font-size:13px;background:#f8fafc;border-radius:10px;border:1px dashed var(--border);">Tidak ada data meja yang terdaftar.</div>';
                        return;
                    }

                    data.mejas.forEach(meja => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'table-select-btn';
                        btn.style.padding = '10px 6px';
                        btn.style.border = '1px solid var(--border)';
                        btn.style.borderRadius = '8px';

                        // Style for recommended vs available vs unavailable
                        if (meja.is_recommended) {
                            btn.style.background = '#fef08a';
                            btn.style.color = '#854d0e';
                            btn.style.border = '2px solid #eab308';
                            btn.style.cursor = 'pointer';
                        } else if (meja.is_available) {
                            btn.style.background = '#ffffff';
                            btn.style.color = 'var(--text-main)';
                            btn.style.cursor = 'pointer';
                        } else {
                            btn.style.background = '#f87171';
                            btn.style.color = '#ffffff';
                            btn.style.cursor = 'not-allowed';
                        }

                        btn.style.fontSize = '12px';
                        btn.style.fontWeight = '700';
                        btn.style.textAlign = 'center';
                        btn.style.lineHeight = '1.4';
                        btn.style.minWidth = '90px';
                        const namaMeja = meja.nomor_meja;
                        const deskripsi = meja.deskripsi ? `<br><span style="font-weight:400;font-size:10px;opacity:0.75;">${meja.deskripsi}</span>` : '';
                        btn.innerHTML = `<strong>${namaMeja}</strong><br><span style="font-weight:400;font-size:10px;">${meja.kapasitas} Pax</span>${deskripsi}`;

                        if (meja.is_available) {
                            btn.onclick = () => selectTable(meja.id, btn);
                        }

                        grid.appendChild(btn);
                    });

                    document.getElementById('resMejaId').value = ''; // Reset selection
                } else {
                    alert(data.message || 'Gagal mengecek ketersediaan meja.');
                }
            })
            .catch(err => {
                document.getElementById('btnCekMeja').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg> Cek Ketersediaan Meja';
                document.getElementById('btnCekMeja').disabled = false;
                console.error(err);
                alert('Gagal terhubung ke server atau terjadi kesalahan internal.');
            });
        }

        function selectTable(id, btnElement) {
            document.getElementById('resMejaId').value = id;
            document.querySelectorAll('.table-select-btn').forEach(btn => {
                if(btn.style.cursor === 'pointer') {
                    btn.style.background = '#ffffff';
                    btn.style.color = 'var(--text-main)';
                    btn.style.border = '1px solid var(--border)';
                }
            });
            btnElement.style.background = 'var(--primary)';
            btnElement.style.color = '#ffffff';
            btnElement.style.border = '1px solid var(--primary)';
        }

        function submitReservasi(event) {
            event.preventDefault();

            const btnSubmit = document.getElementById('btnSubmitReservasi');
            const originalBtnText = btnSubmit.textContent;
            btnSubmit.textContent = 'Memproses...';
            btnSubmit.setAttribute('disabled', 'true');

            const tanggal = document.getElementById('resTanggal').value;
            const jam = document.getElementById('resJam').value;
            const tanggalWaktu = tanggal && jam ? (tanggal + ' ' + jam + ':00') : '';

            if (!tanggalWaktu) {
                alert('Mohon lengkapi tanggal dan jam reservasi.');
                btnSubmit.textContent = originalBtnText;
                btnSubmit.removeAttribute('disabled');
                return;
            }

            const payload = {
                nama_pelanggan: document.getElementById('resName').value,
                nomor_telepon: document.getElementById('resPhone').value,
                tanggal_waktu: tanggalWaktu,
                jumlah_orang: document.getElementById('resPax').value,
                meja_id: document.getElementById('resMejaId').value || null,
                catatan: document.getElementById('resCatatan').value,
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            fetch('{{ route("portal.reservasi", ["nama_toko_slug" => request()->route("nama_toko_slug")]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': payload._token
                },
                body: JSON.stringify(payload)
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal mengirim reservasi.');
                }
                return data;
            })
            .then(data => {
                closeReservasiModal();
                alert(data.message + (data.wajib_dp ? '\n\nSilakan bayar DP sebesar Rp ' + data.nominal_dp + ' ke rekening: ' + data.rekening : ''));
                document.getElementById('reservasiForm').reset();
            })
            .catch(error => {
                alert(error.message);
            })
            .finally(() => {
                btnSubmit.textContent = originalBtnText;
                btnSubmit.removeAttribute('disabled');
            });
        }

        // Helper: Format Rupiah
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }
    </script>
    
    @if(isset($identitas) && $identitas->is_midtrans_active && $identitas->midtrans_client_key)
        <script src="{{ $identitas->midtrans_is_production ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $identitas->midtrans_client_key }}"></script>
    @endif
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <!-- =============================================
         SMOOTH SCROLL NAVIGATION
         ============================================= -->
    <script>
        (function () {
            // Tinggi sticky navbar (untuk offset agar section tidak tertutup)
            const NAV_OFFSET = 68;

            /**
             * Smooth scroll ke elemen target dengan offset navbar
             */
            function smoothScrollTo(targetId) {
                const target = document.getElementById(targetId);
                if (!target) return;

                const targetTop = target.getBoundingClientRect().top + window.scrollY - NAV_OFFSET;

                window.scrollTo({
                    top: targetTop,
                    behavior: 'smooth'
                });
            }

            /**
             * Update class active di navbar
             */
            function setActiveNavLink(href) {
                document.querySelectorAll('#mainNav .nav-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === href) {
                        link.classList.add('active');
                    }
                });
            }

            /**
             * Intercept semua klik anchor yang mengarah ke #section
             */
            document.addEventListener('click', function (e) {
                // Cari anchor terdekat dari element yang diklik
                const anchor = e.target.closest('a[href^="#"]');
                if (!anchor) return;

                const href = anchor.getAttribute('href');
                if (!href || href === '#') return;

                const targetId = href.slice(1); // hapus '#'
                const target = document.getElementById(targetId);
                if (!target) return;

                e.preventDefault();
                smoothScrollTo(targetId);
                setActiveNavLink(href);

                // Update URL hash tanpa trigger scroll
                history.pushState(null, null, href);
            });

            /**
             * IntersectionObserver: auto-highlight menu aktif saat user scroll manual
             */
            const sectionIds = ['beranda', 'tentang', 'katalog', 'kontak', 'syarat_ketentuan'];
            const observerOptions = {
                root: null,
                // Trigger saat section masuk 30% dari atas viewport
                rootMargin: `-${NAV_OFFSET}px 0px -60% 0px`,
                threshold: 0
            };

            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setActiveNavLink('#' + entry.target.id);
                    }
                });
            }, observerOptions);

            // Observe semua section yang ada
            sectionIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) observer.observe(el);
            });

            // Juga observe header#beranda
            const beranda = document.getElementById('beranda');
            if (beranda) observer.observe(beranda);
        })();
    </script>

</body>
</html>
