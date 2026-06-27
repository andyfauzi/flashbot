<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservasi;
use App\Models\Meja;

class ReservasiController extends Controller
{
    public function index()
    {
        $reservasis = Reservasi::with('meja')->orderBy('tanggal_waktu', 'desc')->paginate(20);
        $mejas = Meja::where('status', 'tersedia')->orderBy('nomor_meja')->get();
        return view('dashboard.reservasi.index', compact('reservasis', 'mejas'));
    }

    public function create()
    {
        // Not used, using modal in index
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meja_id' => 'required|exists:'.\App\Services\TenantManager::getTenantConnection().'.mejas,id',
            'nama_pelanggan' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:50',
            'tanggal_waktu' => 'required|date',
            'jumlah_orang' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
            'is_dp_required' => 'nullable|boolean',
            'nominal_dp' => 'nullable|numeric|min:0',
        ]);

        $validated['is_dp_required'] = $request->has('is_dp_required');
        
        $reservasi = Reservasi::create($validated);

        // Ubah status meja menjadi direservasi
        $meja = Meja::find($request->meja_id);
        if ($meja) {
            $meja->update(['status' => 'direservasi']);
        }

        return redirect()->route('dashboard.reservasi.index')->with('sukses', 'Reservasi berhasil dibuat.');
    }

    public function edit(Reservasi $reservasi)
    {
        // Not used, using modal in index
    }

    public function update(Request $request, Reservasi $reservasi)
    {
        $validated = $request->validate([
            'status' => 'required|in:menunggu,dikonfirmasi,selesai,batal',
            'status_pembayaran_dp' => 'nullable|in:belum_bayar,lunas',
        ]);

        $oldStatus = $reservasi->status_pembayaran_dp;
        $reservasi->update($validated);

        if ($oldStatus !== 'lunas' && isset($validated['status_pembayaran_dp']) && $validated['status_pembayaran_dp'] === 'lunas' && $reservasi->nominal_dp > 0) {
            $shift = \App\Models\Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
            if (config('flashbot.features.finance', true)) {
                \App\Models\CashFlow::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shift ? $shift->id : null,
                    'tanggal' => now()->toDateString(),
                    'tipe' => 'in',
                    'kategori' => 'DP Reservasi',
                    'nominal' => $reservasi->nominal_dp,
                    'keterangan' => 'Pembayaran DP Reservasi untuk pelanggan ' . $reservasi->nama_pelanggan,
                ]);
            }
            if ($shift) {
                $shift->increment('total_penjualan_tunai', $reservasi->nominal_dp);
            }
        }

        // Jika status selesai atau batal, bebaskan meja
        if (in_array($validated['status'], ['selesai', 'batal'])) {
            if ($reservasi->meja) {
                $reservasi->meja->update(['status' => 'tersedia']);
            }
        }

        return redirect()->route('dashboard.reservasi.index')->with('sukses', 'Status reservasi berhasil diperbarui.');
    }

    public function destroy(Reservasi $reservasi)
    {
        // Bebaskan meja jika reservasi dihapus dan belum selesai/batal
        if (in_array($reservasi->status, ['menunggu', 'dikonfirmasi'])) {
            if ($reservasi->meja) {
                $reservasi->meja->update(['status' => 'tersedia']);
            }
        }

        $reservasi->delete();

        return redirect()->route('dashboard.reservasi.index')->with('sukses', 'Reservasi berhasil dihapus.');
    }

    public function pengaturan()
    {
        $identitas = \App\Models\IdentitasToko::first();
        return view('dashboard.reservasi.pengaturan', compact('identitas'));
    }

    public function simpanPengaturan(Request $request)
    {
        $validated = $request->validate([
            'wajib_dp_reservasi' => 'nullable|boolean',
            'nominal_dp_reservasi' => 'nullable|numeric|min:0',
            'minimal_jam_reservasi' => 'nullable|integer|min:0|max:720',
            'hold_duration_hours' => 'nullable|integer|min:1|max:72',
            'max_pax_per_reservation' => 'nullable|integer|min:1|max:500',
        ]);

        $validated['wajib_dp_reservasi'] = $request->has('wajib_dp_reservasi');
        $validated['minimal_jam_reservasi'] = $request->minimal_jam_reservasi ?? 0;
        $validated['hold_duration_hours'] = $request->hold_duration_hours ?? 2;
        $validated['max_pax_per_reservation'] = $request->max_pax_per_reservation ?? 20;

        $identitas = \App\Models\IdentitasToko::first();
        if ($identitas) {
            $identitas->update($validated);
        } else {
            \App\Models\IdentitasToko::create($validated);
        }

        return redirect()->route('dashboard.reservasi.pengaturan')->with('sukses', 'Pengaturan reservasi berhasil disimpan.');
    }

    public function approve(Reservasi $reservasi)
    {
        $reservasi->update(['status' => 'dikonfirmasi']);
        return redirect()->route('dashboard.reservasi.index')->with('sukses', 'Reservasi berhasil disetujui.');
    }

    public function reject(Reservasi $reservasi)
    {
        $reservasi->update(['status' => 'batal']);
        if ($reservasi->meja) {
            $reservasi->meja->update(['status' => 'tersedia']);
        }
        return redirect()->route('dashboard.reservasi.index')->with('sukses', 'Reservasi berhasil ditolak.');
    }
}
