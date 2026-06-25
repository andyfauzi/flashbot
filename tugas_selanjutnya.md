# Catatan Tugas Selanjutnya (Besok)

Berikut adalah daftar tugas dan topik yang akan kita kerjakan dan pelajari pada sesi berikutnya:

## 1. Alur Chatbot Saat Kuota Gemini Habis
- Mempelajari *behavior* (perilaku) dan *error handling* di `GeminiAiService.php` ketika Google AI Studio mengembalikan *error* 429 (Too Many Requests).
- Merancang mekanisme *Fallback* otomatis: Jika AI mati atau kuota habis, bot harus otomatis turun (fallback) menjadi **Mode Manual** (menggunakan `chatbot_menus` atau memunculkan pesan _"Mohon maaf, asisten AI kami sedang sibuk, silakan ketik *katalog* untuk memesan secara manual."_).

## 2. Strategi Menghemat Kuota (Token) Gemini AI
Kita akan mengevaluasi beberapa metode untuk menekan jumlah pemakaian *request* dan *token*:
- **Mempersingkat Prompt (System Instruction):** Mengurangi instruksi panjang yang memakan token.
- **Membatasi Riwayat Chat (History):** Saat ini bot membawa 15 pesan terakhir sebagai konteks. Kita bisa menurunkannya menjadi 5-7 pesan terakhir saja agar *request payload* lebih ringan.
- **Mengarahkan User Lebih Cepat:** Melatih AI agar di chat ke-2 atau ke-3 langsung menyodorkan katalog atau format pemesanan, sehingga pelanggan tidak terlalu lama mengobrol berbasa-basi yang menguras kuota.
- **Caching Produk:** Memastikan "Function Calling" (seperti `get_katalog_produk`) tidak ditarik berulang-ulang dalam satu percakapan jika datanya sama.

## 3. Pusat Bantuan Tenant
- Menambahkan dokumentasi dan penjelasan di "Pusat Bantuan Tenant" terkait perubahan-perubahan yang kita lakukan hari ini, khususnya terkait pengaturan Zona Waktu, Pemindahan Tema, dan Penggunaan Mode AI vs Mode Manual.
- Menambahkan penjelasan lengkap tentang cara menggunakan **Katalog Portal**, alur **Reservasi (Booking Tempat)**, serta alur **Pemesanan (Order)** lewat Portal Web.
- Menambahkan panduan tentang cara **mengedit tampilan/pengaturan Portal** di dashboard.

## 4. Uji Coba (Testing) Menyeluruh
- Melakukan pengujian (uji coba) terhadap semua fungsionalitas menu, baik di Mode Manual (perintah 'order', 'katalog', navigasi keranjang) maupun di Mode AI untuk memastikan semuanya berjalan lancar sebelum dirilis ke publik.

---
*Catatan ini disimpan untuk referensi saat sesi pemrograman dilanjutkan.*
