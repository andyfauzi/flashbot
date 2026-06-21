<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $pesanan->nomor_order }}</title>
    <style>
        /* Desain Khusus Thermal Printer (Lebar 58mm/80mm) */
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 10px;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; }
        .border-bottom { border-bottom: 1px dashed #000; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .my-2 { margin-top: 10px; margin-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px 0; }
        
        /* Hilangkan elemen yang tidak perlu saat di-print beneran */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; max-width: 100%; }
        }
        
        .print-btn {
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
            font-family: Arial, sans-serif;
            width: 100%;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-bluetooth { background-color: #10b981; }
        .btn-browser { background-color: #3b82f6; }
        .btn-close { background-color: #6c757d; }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="print-btn btn-bluetooth" onclick="printRawBT()">🖨️ Cetak via Bluetooth (RawBT)</button>
        <button class="print-btn btn-browser" onclick="window.print()">🖨️ Cetak Biasa (Browser/PC)</button>
        <button class="print-btn btn-close" onclick="if(window.opener || window.name !== '') { window.close(); } if(window.parent && typeof window.parent.closePrintModal === 'function') { window.parent.closePrintModal(); }">❌ Tutup</button>
        <hr style="margin-bottom: 20px; border-top: 1px solid #ccc;">
    </div>

    <div class="text-center mb-2">
        @if(isset($identitasToko) && $identitasToko->logo_path)
            <img src="{{ asset('storage/' . $identitasToko->logo_path) }}" alt="Logo" style="max-height: 80px; filter: grayscale(100%) contrast(200%); margin-bottom: 5px;">
        @endif
        <h2 class="fw-bold mb-1" style="font-size: 16px; margin-top:0;">{{ strtoupper($identitasToko->nama_toko ?? 'TOKO NINSKY') }}</h2>
        <div>{{ $identitasToko->alamat_toko ?? 'Jl. Contoh Alamat No. 123, Kota Anda' }}</div>
        <div>Telp: {{ $identitasToko->nomor_telepon ?? '081234567890' }}</div>
    </div>

    <div class="border-top border-bottom my-2" style="padding: 5px 0;">
        <table style="font-size: 11px;">
            <tr>
                <td width="35%" class="fw-bold" style="font-size: 14px;">Antrian</td>
                <td width="5%" class="fw-bold" style="font-size: 14px;">:</td>
                <td class="fw-bold" style="font-size: 14px;">{{ $pesanan->nomor_antrian ?? '-' }}</td>
            </tr>
            <tr>
                <td>No. Order</td>
                <td>:</td>
                <td>{{ $pesanan->nomor_order }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ $pesanan->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td>:</td>
                <td>{{ $pesanan->nama_penerima }}</td>
            </tr>
        </table>
    </div>

    <div class="mb-2">
        <table style="font-size: 11px;">
            @foreach($pesanan->items as $item)
            <tr>
                <td colspan="3" class="fw-bold">
                    {{ $item->produk->nama }}
                    @if($item->produkVarian)
                        <span style="font-size: 10px; font-weight: normal;"> - {{ $item->produkVarian->nama_varian }}</span>
                    @endif
                    @if(!empty($item->addons))
                        <br>
                        <span style="font-size: 10px; font-weight: normal;">
                            + 
                            @php
                                $addonNames = array_column($item->addons, 'nama_addon');
                                echo implode(', ', $addonNames);
                            @endphp
                        </span>
                    @endif
                </td>
            </tr>
            <tr>
                <td width="20%">{{ $item->jumlah }}x</td>
                <td width="40%">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                <td width="40%" class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="border-top my-2" style="padding-top: 5px;">
        <table style="font-size: 11px;">
            <tr>
                <td width="60%">Total Belanja</td>
                <td width="40%" class="text-right">Rp {{ number_format($pesanan->biaya_barang, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Ongkir</td>
                <td class="text-right">Rp {{ number_format($pesanan->biaya_pengantaran, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="fw-bold" style="font-size: 12px;">TOTAL BAYAR</td>
                <td class="fw-bold text-right" style="font-size: 12px;">Rp {{ number_format($pesanan->total_biaya, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pembayaran</td>
                <td class="text-right">{{ strtoupper($pesanan->metode_pembayaran) }}</td>
            </tr>
        </table>
    </div>

    <div class="text-center border-top my-2" style="padding-top: 10px; font-size:11px;">
        <div>{!! nl2br(e($identitasToko->pesan_footer ?? "Terima kasih atas kunjungan Anda!\nBarang yang dibeli tidak dapat ditukar/dikembalikan.")) !!}</div>
        <div style="margin-top: 10px;">--- {{ $identitasToko->nama_toko ?? 'Tenanta.id POS' }} ---</div>
    </div>

    <script>
        function centerText(text, length) {
            if(!text) return "";
            text = text.substring(0, length).trim();
            let pad = Math.floor((length - text.length) / 2);
            if(pad < 0) pad = 0;
            return " ".repeat(pad) + text + "\n";
        }

        function printRawBT() {
            let text = "";
            text += centerText("{{ strtoupper($identitasToko->nama_toko ?? 'TOKO NINSKY') }}", 32);
            text += centerText("{{ substr($identitasToko->alamat_toko ?? 'Jl. Contoh Alamat No.123', 0, 32) }}", 32);
            text += centerText("Telp: {{ $identitasToko->nomor_telepon ?? '081234567890' }}", 32);
            text += "--------------------------------\n";
            text += "Antrian  : {{ $pesanan->nomor_antrian ?? '-' }}\n";
            text += "No. Order: {{ $pesanan->nomor_order }}\n";
            text += "Tanggal  : {{ $pesanan->created_at->format('d/m/Y H:i') }}\n";
            text += "Pelanggan: {{ substr($pesanan->nama_penerima, 0, 20) }}\n";
            text += "--------------------------------\n";
            
            @foreach($pesanan->items as $item)
            text += "{{ substr(str_replace('"', '\"', $item->produk->nama) . ($item->produkVarian ? ' - '.str_replace('"', '\"', $item->produkVarian->nama_varian) : ''), 0, 32) }}\n";
            let qtyStr = "{{ $item->jumlah }}x {{ number_format($item->harga_satuan, 0, ',', '.') }}";
            let subStr = "{{ number_format($item->subtotal, 0, ',', '.') }}";
            let spaceCount = 32 - qtyStr.length - subStr.length;
            if(spaceCount < 1) spaceCount = 1;
            text += qtyStr + " ".repeat(spaceCount) + subStr + "\n";
            @endforeach

            text += "--------------------------------\n";
            let totalBelanja = "Total Belanja:";
            let totalBelanjaVal = "{{ number_format($pesanan->biaya_barang, 0, ',', '.') }}";
            text += totalBelanja + " ".repeat(32 - totalBelanja.length - totalBelanjaVal.length) + totalBelanjaVal + "\n";
            
            let ongkir = "Ongkir:";
            let ongkirVal = "{{ number_format($pesanan->biaya_pengantaran, 0, ',', '.') }}";
            text += ongkir + " ".repeat(32 - ongkir.length - ongkirVal.length) + ongkirVal + "\n";
            
            text += "--------------------------------\n";
            let grand = "TOTAL BAYAR:";
            let grandVal = "{{ number_format($pesanan->total_biaya, 0, ',', '.') }}";
            text += grand + " ".repeat(32 - grand.length - grandVal.length) + grandVal + "\n";
            
            text += "Pembayaran : {{ strtoupper($pesanan->metode_pembayaran) }}\n";
            text += "--------------------------------\n";
            @php
                $footerLines = explode("\n", wordwrap($identitasToko->pesan_footer ?? "Terima kasih atas kunjungan Anda", 32, "\n", true));
            @endphp
            @foreach($footerLines as $line)
            text += centerText("{{ trim($line) }}", 32);
            @endforeach
            text += centerText("--- {{ $identitasToko->nama_toko ?? 'Tenanta.id POS' }} ---", 32);
            text += "\n\n\n";

            // Btoa is standard base64 encoding in JS
            let encodedString = btoa(unescape(encodeURIComponent(text)));
            
            // RawBT intent structure
            let intentUrl = "intent:" + encodedString + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
            
            // Launch Intent
            window.location.href = intentUrl;
        }
    </script>
</body>
</html>
