@extends('layouts.app')

@section('title', 'Kalkulator Bisnis & BEP')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="fa-solid fa-calculator me-2 text-primary"></i>Kalkulator Bisnis & BEP</h3>
    </div>

    <div class="row mb-4">
        <!-- Input Parameter -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold m-0 text-primary">Parameter Simulasi</h5>
                </div>
                <div class="card-body">
                    <form id="kalkulatorForm" class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Target Profit Bulanan (Rp)</label>
                            <input type="number" id="targetProfit" class="form-control form-control-lg" value="5000000" min="0">
                            <small class="text-muted">Berapa laba bersih yang ingin Anda capai bulan ini?</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Total Biaya Tetap (Fixed Cost) Bulanan</label>
                            <input type="number" id="fixedCost" class="form-control form-control-lg" value="2000000" min="0">
                            <small class="text-muted">Contoh: Sewa tempat, Gaji karyawan tetap, Wifi bulanan.</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Hasil Simulasi -->
        <div class="col-12">
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
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="fw-bold m-0 text-primary mb-2 mb-md-0"><i class="fa-solid fa-bullseye me-2"></i>Breakdown Target Penjualan</h5>
                    
                    <div class="d-flex align-items-center">
                        <label class="fw-bold me-2 text-muted small mb-0">Filter Aktual:</label>
                        <div class="input-group input-group-sm w-auto shadow-sm">
                            <span class="input-group-text bg-white border-end-0 text-primary"><i class="fa-regular fa-calendar"></i></span>
                            <input type="date" id="dateFilter" class="form-control border-start-0 fw-bold text-primary" onchange="triggerRecalculate()" value="">
                            <button class="btn btn-primary" type="button" onclick="document.getElementById('dateFilter').value=''; triggerRecalculate();">Total Bulan Ini</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableBreakdown">
                            <thead class="bg-light">
                                <tr>
                                    <th>Menu / Produk</th>
                                    <th>Margin/Porsi</th>
                                    <th class="text-center" style="width: 150px;">Target /Bulan</th>
                                    <th class="text-center">Target /Hari</th>
                                    <th class="text-center">Aktual Terjual</th>
                                    <th class="text-end">Potensi Omset</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Diisi via JS -->
                            </tbody>
                            <tfoot class="bg-light fw-bold" id="tableBreakdownFoot">
                                <tr>
                                    <td colspan="2" class="text-end">TOTAL KESELURUHAN:</td>
                                    <td class="text-center text-primary" id="tfootTotalBulan">0</td>
                                    <td class="text-center text-info" id="tfootTotalHari">0</td>
                                    <td class="text-center" id="tfootTotalAktual">0</td>
                                    <td class="text-end text-muted" id="tfootTotalOmset">Rp 0</td>
                                </tr>
                            </tfoot>
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
    const globalProduks = @json($produks ?? []);
    const salesDataByDate = @json($salesDataByDate ?? []);
    let manualTargets = {}; // { index: qty }

    function formatRp(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    function hitungSimulasi() {
        const profit = parseFloat(document.getElementById('targetProfit').value) || 0;
        const fixedCost = parseFloat(document.getElementById('fixedCost').value) || 0;
        
        let sumHarga = 0;
        let sumHpp = 0;
        let productCount = globalProduks.length;
        
        if (productCount === 0) {
            document.getElementById('resMarginPorsi').innerText = 'Tidak ada produk';
            return;
        }
        
        globalProduks.forEach(p => {
            sumHarga += parseFloat(p.harga);
            sumHpp += parseFloat(p.hpp);
        });
        
        const harga = sumHarga / productCount;
        const hpp = sumHpp / productCount;
        
        // 1. Margin rata-rata per porsi
        const margin = harga - hpp;
        
        if (margin <= 0) {
            document.getElementById('resMarginPorsi').innerText = 'Rugi!';
            document.getElementById('resMarginPorsi').className = 'fw-bold text-danger mb-0';
            return;
        }
        
        document.getElementById('resMarginPorsi').innerText = formatRp(margin) + ' (Rata-rata)';
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
        
        // Pass total margin required to the dynamic table function
        const totalRequiredMargin = fixedCost + profit;
        updateDynamicBreakdownTable(totalRequiredMargin);
    }
    
    function triggerRecalculate() {
        const profit = parseFloat(document.getElementById('targetProfit').value) || 0;
        const fixedCost = parseFloat(document.getElementById('fixedCost').value) || 0;
        updateDynamicBreakdownTable(fixedCost + profit);
    }

    function setManualTarget(index, value) {
        if (value === '' || isNaN(value)) {
            delete manualTargets[index];
        } else {
            manualTargets[index] = parseFloat(value);
            if (manualTargets[index] < 0) manualTargets[index] = 0;
        }
        triggerRecalculate();
    }
    
    function resetManualTarget(index) {
        delete manualTargets[index];
        triggerRecalculate();
    }
    
    function updateDynamicBreakdownTable(totalRequiredMargin) {
        const tbody = document.querySelector('#tableBreakdown tbody');
        tbody.innerHTML = '';
        
        if (globalProduks.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Belum ada data produk aktif.</td></tr>';
            return;
        }
        
        // 1. Hitung margin yang sudah dikunci oleh manual input
        let lockedMargin = 0;
        let lockedCount = 0;
        
        globalProduks.forEach((p, idx) => {
            if (manualTargets[idx] !== undefined) {
                lockedMargin += (manualTargets[idx] * p.margin);
                lockedCount++;
            }
        });
        
        // 2. Sisa margin yang harus dibagi ke produk yang belum dilock
        let remainingMargin = totalRequiredMargin - lockedMargin;
        if (remainingMargin < 0) remainingMargin = 0;
        
        const unlockedCount = globalProduks.length - lockedCount;
        
        // 3. Hitung target per produk yang unlocked
        let targetPerUnlocked = 0;
        if (unlockedCount > 0) {
            let sumUnlockedMargin = 0;
            globalProduks.forEach((p, idx) => {
                if (manualTargets[idx] === undefined) {
                    sumUnlockedMargin += p.margin;
                }
            });
            
            if (sumUnlockedMargin > 0) {
                targetPerUnlocked = Math.ceil(remainingMargin / sumUnlockedMargin);
            }
        }
        
        const filterDate = document.getElementById('dateFilter').value;
        const isDaily = filterDate !== '';
        
        let totalTargetBulan = 0;
        let totalTargetHari = 0;
        let totalAktual = 0;
        let totalPotensiOmset = 0;
        let totalMarginGained = 0;
        
        globalProduks.forEach((p, idx) => {
            const isLocked = manualTargets[idx] !== undefined;
            const targetBulan = isLocked ? manualTargets[idx] : targetPerUnlocked;
            const targetHarian = Math.ceil(targetBulan / 30);
            
            let aktual = 0;
            if (isDaily) {
                aktual = (salesDataByDate[filterDate] && salesDataByDate[filterDate][p.id]) ? salesDataByDate[filterDate][p.id] : 0;
            } else {
                aktual = p.terjual || 0;
            }
            
            const targetCompare = isDaily ? targetHarian : targetBulan;
            const potensiOmset = targetBulan * parseFloat(p.harga);
            
            totalTargetBulan += targetBulan;
            totalTargetHari += targetHarian;
            totalAktual += aktual;
            totalPotensiOmset += potensiOmset;
            totalMarginGained += (targetBulan * p.margin);
            
            const tr = document.createElement('tr');
            if (isLocked) {
                tr.className = 'table-primary bg-opacity-10'; // highlight if locked
            }
            
            let statusBadge = '';
            let targetInputClass = '';
            
            if (aktual >= targetCompare) {
                statusBadge = `<span class="badge bg-success w-100 p-2"><i class="fa-solid fa-circle-check me-1"></i> ${aktual} Porsi</span>`;
                targetInputClass = 'text-success'; 
            } else {
                statusBadge = `<span class="badge bg-danger w-100 p-2"><i class="fa-solid fa-circle-xmark me-1"></i> ${aktual} Porsi</span><div class="text-muted small mt-1">Kurang ${targetCompare - aktual}</div>`;
                targetInputClass = 'text-danger'; 
            }
            
            // Override input color if viewing daily, just keep it blue/gray since input is for monthly target
            if (isDaily) targetInputClass = isLocked ? 'text-primary' : 'text-muted';
            
            tr.innerHTML = `
                <td class="fw-bold align-middle">
                    ${p.nama}
                    ${isLocked ? '<span class="badge bg-primary ms-2" style="font-size:0.6rem"><i class="fa-solid fa-lock"></i> Fixed</span>' : ''}
                </td>
                <td class="text-success align-middle">${formatRp(p.margin)}</td>
                <td class="align-middle">
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control text-center fw-bold ${targetInputClass}" 
                               value="${targetBulan}" min="0" onchange="setManualTarget(${idx}, this.value)" onkeyup="if(event.key==='Enter') setManualTarget(${idx}, this.value)">
                        ${isLocked ? `<button class="btn btn-outline-danger" type="button" onclick="resetManualTarget(${idx})" title="Buka Kunci"><i class="fa-solid fa-unlock"></i></button>` : ''}
                    </div>
                </td>
                <td class="text-center align-middle fw-bold text-info">${targetHarian.toLocaleString('id-ID')}</td>
                <td class="text-center align-middle">${statusBadge}</td>
                <td class="text-end text-muted align-middle">${formatRp(potensiOmset)}</td>
            `;
            tbody.appendChild(tr);
        });
        
        const targetTotalCompare = isDaily ? totalTargetHari : totalTargetBulan;
        
        // Update footer totals
        document.getElementById('tfootTotalBulan').innerText = totalTargetBulan.toLocaleString('id-ID');
        document.getElementById('tfootTotalHari').innerText = totalTargetHari.toLocaleString('id-ID');
        document.getElementById('tfootTotalAktual').innerHTML = `<span class="${totalAktual >= targetTotalCompare ? 'text-success' : 'text-danger'}">${totalAktual.toLocaleString('id-ID')}</span>`;
        document.getElementById('tfootTotalOmset').innerText = formatRp(totalPotensiOmset);
        
        // Sync Top Cards to reflect manual targets!
        const fixedCost = parseFloat(document.getElementById('fixedCost').value) || 0;
        const projectedProfit = totalMarginGained - fixedCost;
        
        const resSumProfitEl = document.getElementById('resSumProfit');
        if (resSumProfitEl) resSumProfitEl.innerText = formatRp(projectedProfit);
        
        const resTargetUnitEl = document.getElementById('resTargetUnit');
        if (resTargetUnitEl) resTargetUnitEl.innerText = totalTargetBulan.toLocaleString('id-ID') + ' Porsi';
        
        const resSumTargetEl = document.getElementById('resSumTarget');
        if (resSumTargetEl) resSumTargetEl.innerText = totalTargetBulan.toLocaleString('id-ID');
        
        const resSumTargetDayEl = document.getElementById('resSumTargetDay');
        if (resSumTargetDayEl) resSumTargetDayEl.innerText = Math.ceil(totalTargetBulan / 30).toLocaleString('id-ID');
        
        const resOmsetEl = document.getElementById('resOmset');
        if (resOmsetEl) resOmsetEl.innerText = formatRp(totalPotensiOmset);
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
