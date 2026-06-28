@extends('layouts.app')

@section('title', 'Kalkulator Bisnis & BEP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="fa-solid fa-calculator me-2 text-primary"></i>Kalkulator Bisnis & BEP</h3>
    </div>

    <div class="row g-4">
        <!-- Input Parameter -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold m-0 text-primary">Parameter Simulasi</h5>
                </div>
                <div class="card-body">
                    <form id="kalkulatorForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Target Profit Bulanan (Rp)</label>
                            <input type="number" id="targetProfit" class="form-control form-control-lg" value="5000000" min="0">
                            <small class="text-muted">Berapa laba bersih yang ingin Anda capai bulan ini?</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total Biaya Tetap (Fixed Cost) Bulanan</label>
                            <input type="number" id="fixedCost" class="form-control form-control-lg" value="2000000" min="0">
                            <small class="text-muted">Contoh: Sewa tempat, Gaji karyawan tetap, Wifi bulanan.</small>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Rata-rata Harga Jual per Porsi (Rp)</label>
                            <input type="number" id="avgHargaJual" class="form-control form-control-lg" value="{{ round($avgHargaJual) }}">
                            <small class="text-muted">Diambil dari rata-rata harga menu Anda.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Rata-rata Modal / HPP per Porsi (Rp)</label>
                            <input type="number" id="avgHpp" class="form-control form-control-lg text-danger" value="{{ round($avgHpp) }}">
                            <small class="text-muted">Diambil dari estimasi HPP resep bahan baku.</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Hasil Simulasi -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold m-0 text-success">Hasil Simulasi Target</h5>
                </div>
                <div class="card-body bg-light">
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <div class="card bg-white border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="text-muted small fw-bold mb-1">Margin Kotor (per Porsi)</div>
                                    <h4 class="fw-bold text-success mb-0" id="resMarginPorsi">Rp 0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-white border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="text-muted small fw-bold mb-1">BEP (Balik Modal)</div>
                                    <h4 class="fw-bold text-warning mb-0" id="resBepUnit">0 Porsi</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-white border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="text-muted small fw-bold mb-1">Target Penjualan</div>
                                    <h4 class="fw-bold text-primary mb-0" id="resTargetUnit">0 Porsi</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info border-0 shadow-sm mb-0">
                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-lightbulb me-2 text-warning"></i>Kesimpulan Target:</h6>
                        <ul class="mb-0 ps-3">
                            <li>Untuk **Balik Modal (BEP)** saja, Anda harus menjual <strong id="resSumBep">0</strong> porsi / bulan (atau sekitar <strong id="resSumBepDay">0</strong> porsi / hari).</li>
                            <li>Untuk mencapai laba bersih <strong id="resSumProfit">Rp 0</strong>, Anda harus menjual total <strong id="resSumTarget">0</strong> porsi / bulan (atau sekitar <strong id="resSumTargetDay">0</strong> porsi / hari).</li>
                            <li>Total **Omset Kotor** yang harus didapat bulan ini: <strong id="resOmset" class="text-primary fs-5">Rp 0</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <canvas id="bepChart" height="250"></canvas>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold m-0 text-primary"><i class="fa-solid fa-bullseye me-2"></i>Breakdown Target Penjualan (Asumsi Terjual Rata)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableBreakdown">
                            <thead class="bg-light">
                                <tr>
                                    <th>Menu / Produk</th>
                                    <th>Margin/Porsi</th>
                                    <th class="text-center">Target /Bulan</th>
                                    <th class="text-center">Target /Hari</th>
                                    <th class="text-end">Potensi Omset</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Diisi via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let bepChart;

    function formatRp(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    function hitungSimulasi() {
        const profit = parseFloat(document.getElementById('targetProfit').value) || 0;
        const fixedCost = parseFloat(document.getElementById('fixedCost').value) || 0;
        const harga = parseFloat(document.getElementById('avgHargaJual').value) || 0;
        const hpp = parseFloat(document.getElementById('avgHpp').value) || 0;
        
        // 1. Margin per porsi
        const margin = harga - hpp;
        
        if (margin <= 0) {
            document.getElementById('resMarginPorsi').innerText = 'Rugi!';
            document.getElementById('resMarginPorsi').className = 'fw-bold text-danger mb-0';
            return;
        }
        
        document.getElementById('resMarginPorsi').innerText = formatRp(margin);
        document.getElementById('resMarginPorsi').className = 'fw-bold text-success mb-0';
        
        // 2. BEP Unit (Balik Modal)
        const bepUnit = Math.ceil(fixedCost / margin);
        document.getElementById('resBepUnit').innerText = bepUnit.toLocaleString('id-ID') + ' Porsi';
        
        // 3. Target Unit (Untuk capai Profit)
        const targetUnit = Math.ceil((fixedCost + profit) / margin);
        document.getElementById('resTargetUnit').innerText = targetUnit.toLocaleString('id-ID') + ' Porsi';
        
        // 4. Kesimpulan Teks
        document.getElementById('resSumBep').innerText = bepUnit.toLocaleString('id-ID');
        document.getElementById('resSumBepDay').innerText = Math.ceil(bepUnit / 30).toLocaleString('id-ID');
        document.getElementById('resSumProfit').innerText = formatRp(profit);
        document.getElementById('resSumTarget').innerText = targetUnit.toLocaleString('id-ID');
        document.getElementById('resSumTargetDay').innerText = Math.ceil(targetUnit / 30).toLocaleString('id-ID');
        
        const totalOmset = targetUnit * harga;
        document.getElementById('resOmset').innerText = formatRp(totalOmset);
        
        updateChart(bepUnit, targetUnit, fixedCost, margin, harga);
        updateBreakdownTable(targetUnit);
    }
    
    function updateBreakdownTable(totalTargetUnit) {
        const produks = @json($produks ?? []);
        const tbody = document.querySelector('#tableBreakdown tbody');
        tbody.innerHTML = '';
        
        if (produks.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Belum ada data produk aktif.</td></tr>';
            return;
        }
        
        // Asumsi terjual rata (merata ke semua produk aktif)
        const targetPerProduk = Math.ceil(totalTargetUnit / produks.length);
        const targetHarian = Math.ceil(targetPerProduk / 30);
        
        produks.forEach(p => {
            const potensiOmset = targetPerProduk * parseFloat(p.harga);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="fw-bold">${p.nama}</td>
                <td class="text-success">${formatRp(p.margin)}</td>
                <td class="text-center fw-bold text-primary">${targetPerProduk.toLocaleString('id-ID')}</td>
                <td class="text-center">${targetHarian.toLocaleString('id-ID')}</td>
                <td class="text-end text-muted">${formatRp(potensiOmset)}</td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    function updateChart(bep, target, fc, margin, harga) {
        const ctx = document.getElementById('bepChart').getContext('2d');
        
        // Generate data points
        const points = [0, Math.floor(bep/2), bep, Math.floor(bep + (target-bep)/2), target, Math.floor(target * 1.2)];
        
        const dataPendapatan = points.map(x => x * harga);
        const dataBiaya = points.map(x => fc + (x * (harga - margin))); // Biaya = Fixed Cost + Variable Cost
        
        if (bepChart) {
            bepChart.destroy();
        }
        
        bepChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: points.map(p => p + ' Porsi'),
                datasets: [
                    {
                        label: 'Total Pendapatan',
                        data: dataPendapatan,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.1
                    },
                    {
                        label: 'Total Biaya (Fixed + HPP)',
                        data: dataBiaya,
                        borderColor: '#dc3545',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Grafik Break-Even Point (BEP)'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatRp(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value/1000000).toFixed(1) + ' Juta';
                            }
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('#kalkulatorForm input');
        inputs.forEach(input => {
            input.addEventListener('input', hitungSimulasi);
        });
        
        // Run once on load
        hitungSimulasi();
    });
</script>
@endsection
