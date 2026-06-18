<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $pesanan->nomor_order }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .info {
            width: 100%;
            margin-bottom: 20px;
        }
        .info td {
            padding: 5px 0;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-items th, .table-items td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table-items th {
            background-color: #f4f4f4;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .totals {
            width: 50%;
            float: right;
            border-collapse: collapse;
        }
        .totals td {
            padding: 5px;
            border-top: 1px solid #ddd;
        }
        .footer {
            clear: both;
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>INVOICE PESANAN</h1>
    <p>Terima kasih atas pesanan Anda</p>
</div>

<table class="info">
    <tr>
        <td width="20%"><strong>Nomor Order</strong></td>
        <td width="30%">: {{ $pesanan->nomor_order }}</td>
        <td width="20%"><strong>Tanggal Order</strong></td>
        <td width="30%">: {{ $pesanan->created_at->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td><strong>Nama Penerima</strong></td>
        <td>: {{ $pesanan->nama_penerima }}</td>
        <td><strong>No WhatsApp</strong></td>
        <td>: {{ $pesanan->nomor_wa }}</td>
    </tr>
    <tr>
        <td><strong>Tipe Pengiriman</strong></td>
        <td>: {{ ucwords(str_replace('_', ' ', $pesanan->tipe_pengiriman)) }}</td>
        <td><strong>Status</strong></td>
        <td>: {{ strtoupper($pesanan->status) }}</td>
    </tr>
</table>

<table class="table-items">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="45%">Produk</th>
            <th width="15%" class="text-center">Jumlah</th>
            <th width="15%" class="text-right">Harga Satuan</th>
            <th width="20%" class="text-right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pesanan->items as $index => $item)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                {{ $item->produk ? $item->produk->nama : 'Produk Umum' }}
                @if(!empty($item->addons))
                    <br>
                    <small style="color: #666;">
                        Tambahan: 
                        @php
                            $addonNames = array_column($item->addons, 'nama_addon');
                            echo implode(', ', $addonNames);
                        @endphp
                    </small>
                @endif
            </td>
            <td class="text-center">{{ $item->jumlah }}</td>
            <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td><strong>Subtotal Produk</strong></td>
        <td class="text-right">Rp {{ number_format($pesanan->biaya_barang, 0, ',', '.') }}</td>
    </tr>
    @if($pesanan->biaya_pengantaran > 0)
    <tr>
        <td><strong>Biaya Pengantaran</strong></td>
        <td class="text-right">Rp {{ number_format($pesanan->biaya_pengantaran, 0, ',', '.') }}</td>
    </tr>
    @endif
    <tr>
        <td><strong>Total Tagihan</strong></td>
        <td class="text-right"><strong>Rp {{ number_format($pesanan->total_biaya, 0, ',', '.') }}</strong></td>
    </tr>
    <tr>
        <td><strong>Sudah Dibayar</strong></td>
        <td class="text-right">Rp {{ number_format($pesanan->uang_muka, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td><strong>Sisa Pembayaran</strong></td>
        <td class="text-right"><strong>Rp {{ number_format($pesanan->sisa_pembayaran, 0, ',', '.') }}</strong></td>
    </tr>
</table>

<div class="footer">
    <p>Ini adalah bukti transaksi digital yang sah.</p>
    @if($pesanan->sisa_pembayaran > 0)
    <p><em>Anda dapat melakukan pelunasan sisa tagihan saat mengambil pesanan.</em></p>
    @endif
</div>

</body>
</html>
