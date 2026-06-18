<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatbotBroadcast;
use App\Models\ChatbotUser;
use App\Models\IdentitasToko;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    public function index()
    {
        $identitas = IdentitasToko::first();
        if (!$identitas || !$identitas->is_broadcast_approved) {
            return redirect()->route('chatbot.dashboard')
                ->with('error', 'Fitur Broadcast Promosi belum diaktifkan untuk akun Anda. Silakan hubungi Super Admin.');
        }

        $broadcasts = ChatbotBroadcast::orderBy('created_at', 'desc')->get();
        $isMeta = $identitas->whatsapp_gateway === 'meta_mandiri';

        return view('chatbot.broadcast.index', compact('broadcasts', 'isMeta'));
    }

    public function create()
    {
        $identitas = IdentitasToko::first();
        if (!$identitas || !$identitas->is_broadcast_approved) {
            return redirect()->route('chatbot.dashboard')
                ->with('error', 'Fitur Broadcast Promosi belum diaktifkan untuk akun Anda.');
        }

        $isMeta = $identitas->whatsapp_gateway === 'meta_mandiri';
        $totalUsers = ChatbotUser::count();

        return view('chatbot.broadcast.form', compact('isMeta', 'totalUsers'));
    }

    public function store(Request $request)
    {
        $identitas = IdentitasToko::first();
        if (!$identitas || !$identitas->is_broadcast_approved) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $isMeta = $identitas->whatsapp_gateway === 'meta_mandiri';

        // Validasi input
        $rules = [
            'judul' => 'required|string|max:255',
            'isi_pesan' => 'required|string',
            'target_filter' => 'required|in:all,interaksi_rendah',
        ];

        if (!$isMeta) {
            $rules['persetujuan_risiko'] = 'required|accepted';
        } else {
            $rules['meta_template_name'] = 'nullable|string'; // Jika mau pakai template
        }

        $request->validate($rules, [
            'persetujuan_risiko.required' => 'Anda harus menyetujui risiko pemblokiran WhatsApp jika tidak menggunakan Meta.',
            'persetujuan_risiko.accepted' => 'Anda harus menyetujui risiko pemblokiran WhatsApp jika tidak menggunakan Meta.',
        ]);

        // Query penerima
        $query = ChatbotUser::query();
        if ($request->target_filter === 'interaksi_rendah') {
            // Misal: hanya yang pesan terakhirnya sudah lebih dari 7 hari atau total pesan masuk sedikit
            // Di sini kita batasi 50 user terakhir saja untuk interaksi rendah sebagai contoh
            $query->orderBy('updated_at', 'asc')->limit(50);
        }
        
        $users = $query->get();
        $totalPenerima = $users->count();

        if ($totalPenerima === 0) {
            return redirect()->back()->with('error', 'Tidak ada pengguna yang cocok dengan kriteria filter.');
        }

        // Simpan riwayat
        $broadcast = ChatbotBroadcast::create([
            'judul' => $request->judul,
            'isi_pesan' => $request->isi_pesan,
            'status' => 'dikirim',
            'total_penerima' => $totalPenerima,
            'target_filter' => $request->target_filter,
            'meta_template_name' => $isMeta ? $request->meta_template_name : null,
        ]);

        // Kirim (Dispatch Jobs)
        foreach ($users as $user) {
            // Jika Meta dan ada template, format pesannya akan beda. Namun sementara kita kirim plain text / media normal.
            // Di WhatsAppService, fungsi kirimPesanSekarang bisa menerima parameter template. (Bisa disesuaikan nanti)
            dispatch(new SendWhatsAppMessageJob(
                $user->nomor_wa,
                $request->isi_pesan
            ));
        }

        return redirect()->route('chatbot.broadcast.index')
            ->with('success', "Broadcast berhasil dijadwalkan ke {$totalPenerima} penerima! Sistem akan mengirimkannya secara bertahap.");
    }
}
