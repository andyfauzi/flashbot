@php
    $imageUrl = $produk->foto ? (str_starts_with($produk->foto, 'http') ? $produk->foto : asset($produk->foto)) : null;
    $fallbackImage = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=500&q=80'; // Cake placeholder
    $finalImage = $imageUrl ?: $fallbackImage;

    // Hitung rating
    $rating = $produk->average_rating > 0 ? $produk->average_rating : 4.8;
    $reviews = $produk->review_count > 0 ? $produk->review_count : rand(6, 24);

    // Ambil harga dasar & stok awal
    $basePrice = $produk->harga;
    $isMadeToOrder = $produk->is_made_to_order ? 1 : 0;
    
    $initialStock = $produk->stok;
    $displayPrice = $basePrice;

    if ($produk->varians->count() > 0) {
        $firstVarian = $produk->varians->first();
        $displayPrice = $firstVarian->harga > 0 ? $firstVarian->harga : $basePrice;
        $initialStock = $firstVarian->stok;
    }

    // Tentukan apakah awalnya habis
    $isOutOfStock = (!$isMadeToOrder && $initialStock <= 0);
@endphp

<div class="product-card {{ $catId }}" data-id="{{ $produk->id }}" data-made-to-order="{{ $isMadeToOrder }}" data-base-stok="{{ $produk->stok }}" data-base-price="{{ $basePrice }}">
    <div class="product-image-container">
        <img class="product-image" src="{{ $finalImage }}" alt="{{ $produk->nama }}" loading="lazy">
        <span class="product-badge">{{ $produk->kategori->nama ?? 'Menu' }}</span>
        @if($isOutOfStock)
            <span class="stock-badge-out" style="position: absolute; top: 12px; right: 12px; background: #ef4444; color: #fff; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; box-shadow: var(--shadow);">Habis</span>
        @endif
    </div>

    <div class="product-content">
        <div class="product-rating">
            <span>⭐</span>
            <span>{{ number_format($rating, 1) }}</span>
            <span class="review-count">({{ $reviews }} ulasan)</span>
        </div>

        <h3 class="product-title">{{ $produk->nama }}</h3>
        
        <p class="product-desc">{{ Str::limit($produk->deskripsi ?? 'Menu lezat buatan koki ahli kami dengan bahan pilihan terbaik.', 80) }}</p>

        @if($produk->varians->count() > 0)
            <div class="variant-selector-container">
                <div class="variant-label">Pilih Ukuran / Varian:</div>
                <select class="variant-select" onchange="updateProductPriceDisplay(this.closest('.product-card'))">
                    @foreach($produk->varians as $var)
                        <option value="{{ $var->id }}" data-price="{{ $var->harga }}" data-stok="{{ $var->stok }}">
                            {{ $var->nama_varian }} (Rp {{ number_format($var->harga ?: $basePrice, 0, ',', '.') }})
                            @if(!$isMadeToOrder && $var->stok <= 0)
                                [Habis]
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($produk->addons->where('aktif', true)->count() > 0)
            <div class="addons-selector-container" style="margin-top: 12px; margin-bottom: 12px; text-align: left;">
                <div class="variant-label" style="margin-bottom: 8px;">Tambahan (Add-ons):</div>
                <div class="addons-list" style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($produk->addons->where('aktif', true) as $addon)
                        <label class="addon-item-label" style="display: flex; align-items: flex-start; gap: 8px; font-size: 12px; cursor: pointer; font-weight: 500; color: var(--text-main);">
                            <input type="checkbox" class="addon-checkbox" value="{{ $addon->id }}" data-price="{{ $addon->harga }}" data-name="{{ $addon->nama_addon }}" data-needs-text="{{ $addon->butuh_teks ? 1 : 0 }}" onchange="updateProductPriceDisplay(this.closest('.product-card'))" style="margin-top: 3px; cursor: pointer;">
                            <div style="flex: 1;">
                                <span>{{ $addon->nama_addon }} (+Rp {{ number_format($addon->harga, 0, ',', '.') }})</span>
                                @if($addon->butuh_teks)
                                    <input type="text" class="addon-text-input form-control" placeholder="Tuliskan ucapan/pesan..." style="display: none; margin-top: 6px; padding: 6px 10px; font-size: 11px; width: 100%; border: 1px solid var(--border); border-radius: 6px;" onclick="event.stopPropagation()">
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="product-footer">
            <div class="product-price">
                <span class="price-label">Harga</span>
                <span class="price-value">Rp {{ number_format($displayPrice, 0, ',', '.') }}</span>
            </div>
            @if($isOutOfStock)
                <button class="add-to-cart-btn btn-disabled" disabled style="background: #cbd5e1; color: #94a3b8; box-shadow: none; cursor: not-allowed; width: auto; border-radius: 100px; padding: 0 16px; font-size: 12px;">Habis</button>
            @else
                <button class="add-to-cart-btn" onclick="addToCart({{ $produk->id }}, '{{ addslashes($produk->nama) }}', {{ $basePrice }}, '{{ $finalImage }}')">+</button>
            @endif
        </div>
    </div>
</div>
