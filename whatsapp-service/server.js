// Polyfill global crypto untuk Node.js versi lama (seperti Node 18)
const cryptoPolyfill = require('crypto');
if (!global.crypto) {
    global.crypto = cryptoPolyfill;
}

// Global Error Handlers untuk mencegah server crash
process.on('uncaughtException', function (err) {
    console.error('❌ Uncaught Exception:', err);
});
process.on('unhandledRejection', function (reason, promise) {
    console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
});

const {
    default: makeWASocket,
    DisconnectReason,
    useMultiFileAuthState,
    delay,
    fetchLatestBaileysVersion,
    downloadContentFromMessage
} = require('@whiskeysockets/baileys');
const pino = require('pino');
const express = require('express');
const cors = require('cors');
const axios = require('axios');
const fs = require('fs');
const path = require('path');
const QRCode = require('qrcode');

// Load environment variables dari Laravel .env
const dotenv = require('dotenv');
dotenv.config({ path: path.join(__dirname, '../.env'), override: true });

const PORT = process.env.BAILEYS_PORT || 3000;
const LARAVEL_URL = process.env.APP_URL || 'http://localhost';

let webhookUrl = process.env.LARAVEL_WEBHOOK_URL;
if (!webhookUrl) {
    if (LARAVEL_URL === 'http://localhost') {
        webhookUrl = 'http://localhost/male_boot/public/webhook/whatsapp';
    } else {
        webhookUrl = `${LARAVEL_URL}/webhook/whatsapp`;
    }
}

console.log(`📡 Laravel Webhook Target: ${webhookUrl}`);

const WEBHOOK_SECRET = process.env.WEBHOOK_SECRET || 'FlashbotSecretKey2026';

// ALLOWED GROUPS CACHE
let allowedGroups = new Set();
if (process.env.WHATSAPP_GROUP_ID_SELLER) {
    allowedGroups.add(process.env.WHATSAPP_GROUP_ID_SELLER);
}

// Function to fetch allowed groups from Laravel
async function syncWhitelist() {
    try {
        const apiUrl = LARAVEL_URL === 'http://localhost' 
            ? 'http://localhost/male_boot/public/api/whatsapp/whitelist'
            : `${LARAVEL_URL}/api/whatsapp/whitelist`;
            
        const response = await axios.get(apiUrl, {
            headers: { 'x-api-key': WEBHOOK_SECRET }
        });
        
        if (response.data && response.data.status === 'success') {
            allowedGroups = new Set(response.data.data);
            console.log(`✅ [Whitelist] Tersinkronisasi. Total grup diizinkan: ${allowedGroups.size}`);
        }
    } catch (err) {
        console.error(`❌ [Whitelist] Gagal sinkronisasi dari Laravel: ${err.message}`);
    }
}

// Lakukan sinkronisasi awal
syncWhitelist();


const app = express();
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// SESSIONS MAP
// format: sessions.set(sessionId, { sock, status: '...', user: {...}, qr: '...', retries: 0 })
const sessions = new Map();

// Pastikan folder public/images ada untuk menyimpan QR Code
const imagesDir = path.join(__dirname, '../public/images');
if (!fs.existsSync(imagesDir)) {
    fs.mkdirSync(imagesDir, { recursive: true });
}

// Inisiasi WhatsApp Socket Connection
async function connectToWhatsApp(sessionId) {
    const authFolder = path.join(__dirname, 'auth_info_baileys', sessionId);
    const qrImagePath = path.join(imagesDir, `whatsapp-qr-${sessionId}.png`);
    
    const { state, saveCreds } = await useMultiFileAuthState(authFolder);
    
    let version = [2, 3000, 1017531287];
    try {
        const { version: latestVersion } = await fetchLatestBaileysVersion();
        version = latestVersion;
    } catch (err) {}

    const sock = makeWASocket({
        version,
        logger: pino({ level: 'silent' }),
        auth: state,
        printQRInTerminal: true
    });

    sessions.set(sessionId, { sock, status: 'connecting', user: null, qr: null, retries: 0 });

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;
        const sessionData = sessions.get(sessionId) || {};
        
        if (qr) {
            sessionData.status = 'qr';
            sessionData.qr = qr;
            console.log(`⚡ [${sessionId}] QR Code generated!`);
            try {
                await QRCode.toFile(qrImagePath, qr, { width: 300, margin: 2 });
            } catch (err) {}
        }

        if (connection === 'close') {
            const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
            console.log(`🔌 [${sessionId}] Terputus, Reconnect: ${shouldReconnect}`);
            
            try { if (fs.existsSync(qrImagePath)) fs.unlinkSync(qrImagePath); } catch(e){ console.error('Error deleting QR:', e); }

            if (shouldReconnect) {
                sessionData.status = 'disconnected';
                sessionData.user = null;
                setTimeout(() => connectToWhatsApp(sessionId), 5000);
            } else {
                sessions.delete(sessionId);
                try { if (fs.existsSync(authFolder)) fs.rmSync(authFolder, { recursive: true, force: true }); } catch(e){ console.error('Error deleting auth folder:', e); }
            }
        } else if (connection === 'open') {
            console.log(`✅ [${sessionId}] Connection OPEN!`);
            sessionData.status = 'connected';
            const user = sock.user;
            sessionData.user = {
                id: user.id,
                name: user.name || 'Bot Device',
                number: user.id.split(':')[0]
            };
            if (fs.existsSync(qrImagePath)) fs.unlinkSync(qrImagePath);
        }
        sessions.set(sessionId, sessionData);
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('messages.upsert', async (m) => {
        if (m.type !== 'notify') return;

        for (const msg of m.messages) {
            if (msg.key.fromMe) continue;

            const sender = msg.key.remoteJid;
            const isGroup = sender.endsWith('@g.us');
            const memberNumber = isGroup 
                ? (msg.key.participant ? msg.key.participant.split('@')[0].split(':')[0] : '') 
                : sender.split('@')[0].split(':')[0];
            const senderNumber = isGroup ? sender : sender.split('@')[0].split(':')[0];
            const pushName = msg.pushName || 'User';
            
            let messageText = '';
            let mediaUrl = null;
            let mediaType = null;

            if (msg.message) {
                // Handle button response
                if (msg.message.templateButtonReplyMessage) {
                    messageText = msg.message.templateButtonReplyMessage.selectedId;
                } else if (msg.message.listResponseMessage) {
                    messageText = msg.message.listResponseMessage.singleSelectReply.selectedRowId;
                } else if (msg.message.buttonsResponseMessage) {
                    messageText = msg.message.buttonsResponseMessage.selectedButtonId;
                } else if (msg.message.imageMessage) {
                    messageText = msg.message.imageMessage.caption || '(gambar)';
                    try {
                        const stream = await downloadContentFromMessage(msg.message.imageMessage, 'image');
                        let buffer = Buffer.from([]);
                        for await(const chunk of stream) {
                            buffer = Buffer.concat([buffer, chunk]);
                        }
                        const uploadsDir = path.join(__dirname, '../public/uploads');
                        if (!fs.existsSync(uploadsDir)) {
                            fs.mkdirSync(uploadsDir, { recursive: true });
                        }
                        const filename = `proof_${Date.now()}.jpg`;
                        const filepath = path.join(uploadsDir, filename);
                        fs.writeFileSync(filepath, buffer);
                        
                        mediaUrl = `/uploads/${filename}`;
                        mediaType = 'image';
                    } catch (e) {
                        console.error('❌ Gagal mendownload gambar Baileys:', e);
                    }
                } else {
                    messageText = msg.message.conversation || 
                                  msg.message.extendedTextMessage?.text || 
                                  '';
                }
            }

            // Jika tidak ada text atau mediaUrl, abaikan pesan
            if (!messageText && !mediaUrl) continue;
            
            let groupName = null;
            let isSenderAdmin = false;
            if (isGroup) {
                // [WHITELIST FILTER]
                if (!allowedGroups.has(sender)) {
                    const txtLower = messageText ? messageText.trim().toLowerCase() : '';
                    if (txtLower === '!aktifkan-bot' || txtLower === '!daftarkan-grup') {
                        console.log(`[!] Pendaftaran grup baru diterima: ${sender}`);
                    } else {
                        console.log(`[!] Mengabaikan pesan dari grup tak terdaftar: ${sender}`);
                        continue; // Stop pemrosesan (Privasi Aman)
                    }
                }
                
                try {
                    const metadata = await sock.groupMetadata(sender);
                    groupName = metadata.subject;
                    
                    // Deteksi admin grup
                    const participantJid = msg.key.participant || '';
                    if (metadata.participants && participantJid) {
                        const participant = metadata.participants.find(p => p.id.split('@')[0] === participantJid.split('@')[0]);
                        if (participant && (participant.admin === 'admin' || participant.admin === 'superadmin')) {
                            isSenderAdmin = true;
                        }
                    }
                } catch (e) { groupName = 'Group'; }
                
                console.log(`📩 [GRUP: ${groupName}] dari ${pushName} (Admin: ${isSenderAdmin}): ${messageText || '[Media]'}`);
            } else {
                console.log(`📩 [PERSONAL] dari ${pushName} (${senderNumber}): ${messageText || '[Media]'}`);
            }

            try {
                const webhookPayload = {
                    message_id: msg.key.id,
                    message: messageText,
                    device: sessionId, // Gunakan sessionId sebagai device
                    isgroup: isGroup ? 'true' : 'false',
                    sender: isGroup ? senderNumber : sender,
                    member: memberNumber,
                    name: isGroup ? groupName : pushName,
                    mediaUrl: mediaUrl,
                    mediaType: mediaType,
                    is_admin: isSenderAdmin ? 'true' : 'false'
                };

                const sendWebhook = async (retries = 3) => {
                    for (let i = 0; i < retries; i++) {
                        try {
                            await axios.post(webhookUrl, webhookPayload, {
                                headers: { 'x-api-key': process.env.WEBHOOK_SECRET || 'FlashbotSecretKey2026' },
                                timeout: 10000 // 10s timeout
                            });
                            return; // Sukses, keluar dari loop
                        } catch (err) {
                            if (i === retries - 1) {
                                console.error(`❌ Webhook gagal setelah ${retries} percobaan:`, err.message);
                            } else {
                                // Exponential backoff sederhana (2s, 4s, 6s)
                                await new Promise(res => setTimeout(res, 2000 * (i + 1)));
                            }
                        }
                    }
                };
                sendWebhook();
            } catch (err) {}
        }
    });
}

// Load existing sessions on startup
const authFolderRoot = path.join(__dirname, 'auth_info_baileys');
if (fs.existsSync(authFolderRoot)) {
    const folders = fs.readdirSync(authFolderRoot);
    for (const folder of folders) {
        if (fs.statSync(path.join(authFolderRoot, folder)).isDirectory()) {
            console.log(`🔄 Memulai kembali sesi: ${folder}`);
            connectToWhatsApp(folder);
        }
    }
}

// =============================================
// API ROUTES
// =============================================

app.post('/device/start', (req, res) => {
    const { sessionId } = req.body;
    if (!sessionId) return res.status(400).json({ error: 'sessionId required' });
    
    if (sessions.has(sessionId)) {
        return res.json({ status: 'success', message: 'Session already exists' });
    }
    
    connectToWhatsApp(sessionId);
    res.json({ status: 'success', message: 'Session starting' });
});

// Endpoint untuk di-*trigger* oleh Laravel ketika Whitelist berubah
app.post('/whitelist/refresh', async (req, res) => {
    const providedKey = req.headers['x-api-key'] || req.body?.api_key;
    if (providedKey !== WEBHOOK_SECRET) {
        return res.status(403).json({ error: 'Unauthorized' });
    }
    
    // Respond IMMEDIATELY to prevent deadlock in PHP artisan serve
    res.json({ status: 'success', message: 'Sinkronisasi berjalan di background' });
    
    // Run sync asynchronously with a delay so php artisan serve has time to close the previous socket
    setTimeout(syncWhitelist, 1500);
});

app.get('/device/status/:sessionId', (req, res) => {
    const { sessionId } = req.params;
    const session = sessions.get(sessionId);
    
    if (!session) return res.json({ status: 'not_found' });
    
    res.json({
        status: session.status,
        user: session.user,
        qrUrl: session.status === 'qr' ? `/images/whatsapp-qr-${sessionId}.png` : null
    });
});

app.post('/device/logout', async (req, res) => {
    const { sessionId } = req.body;
    const session = sessions.get(sessionId);
    
    if (session && session.sock) {
        try { await session.sock.logout(); } catch(e){}
    }
    
    sessions.delete(sessionId);
    const authDir = path.join(authFolderRoot, sessionId);
    if (fs.existsSync(authDir)) fs.rmSync(authDir, { recursive: true, force: true });
    
    const qrPath = path.join(imagesDir, `whatsapp-qr-${sessionId}.png`);
    if (fs.existsSync(qrPath)) fs.unlinkSync(qrPath);
    
    res.json({ status: 'success' });
});

app.post('/send-message', async (req, res) => {
    const { sessionId, number, message, mediaUrl, mediaType, interactiveType, interactiveOptions } = req.body;

    if (!sessionId || !number || (!message && !interactiveType)) {
        return res.status(400).json({ status: 'error', message: 'Parameter tidak lengkap!' });
    }

    const session = sessions.get(sessionId);
    if (!session || session.status !== 'connected') {
        return res.status(503).json({ status: 'error', message: 'WhatsApp belum terhubung/aktif!' });
    }

    try {
        let targetJid;
        if (number.includes('@')) targetJid = number;
        else if (number.includes('-')) targetJid = `${number}@g.us`;
        else targetJid = `${number}@s.whatsapp.net`;
        
        const sock = session.sock;
        
        // Simulasi "Sedang mengetik..."
        try {
            await sock.sendPresenceUpdate('composing', targetJid);
            await delay(1500); // jeda 1.5 detik
            await sock.sendPresenceUpdate('paused', targetJid);
        } catch (e) {}
        
        // INTERACTIVE MESSAGES (Button/List)
        // Catatan: WhatsApp sudah memblokir format button/list lama di Baileys.
        // Fallback ke format teks rapi dengan emoji angka.
        const EMOJI_NUMBERS = ['1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣','9️⃣','🔟','1️⃣1️⃣','1️⃣2️⃣','1️⃣3️⃣','1️⃣4️⃣','1️⃣5️⃣','1️⃣6️⃣','1️⃣7️⃣','1️⃣8️⃣','1️⃣9️⃣','2️⃣0️⃣'];

        if ((interactiveType === 'button' || interactiveType === 'list') && interactiveOptions) {
            const optionLines = interactiveOptions.map((opt, i) => {
                const emoji = EMOJI_NUMBERS[i] || `${i+1}.`;
                return `${emoji} ${opt.text}`;
            }).join('\n');

            const fullText = `${message || 'Silakan pilih:'}\n\n${optionLines}\n\n_Balas dengan angka pilihan Anda_`;

            let sendPayload = { text: fullText };

            if (mediaUrl) {
                // Kirim gambar + caption jika ada media
                const response = await sock.sendMessage(targetJid, {
                    image: { url: mediaUrl },
                    caption: fullText
                });
                return res.json({ status: 'success', data: response });
            }

            const response = await sock.sendMessage(targetJid, sendPayload);
            return res.json({ status: 'success', data: response });
        }

        // STANDARD MESSAGES
        if (mediaUrl) {
            const mimeType = mediaType === 'document' ? 'application/pdf' : 'image/jpeg';
            if (mediaType === 'document') {
                const response = await sock.sendMessage(targetJid, {
                    document: { url: mediaUrl },
                    mimetype: mimeType,
                    fileName: mediaUrl.split('/').pop() || 'document.pdf',
                    caption: message
                });
                return res.json({ status: 'success', data: response });
            } else {
                const response = await sock.sendMessage(targetJid, {
                    image: { url: mediaUrl },
                    caption: message
                });
                return res.json({ status: 'success', data: response });
            }
        } else {
            const response = await sock.sendMessage(targetJid, { text: message });
            return res.json({ status: 'success', data: response });
        }
    } catch (err) {
        console.error('❌ Gagal mengirim pesan:', err);
        res.status(500).json({ status: 'error', message: err.message });
    }
});

app.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 Baileys Service running on http://localhost:${PORT}`);
});
