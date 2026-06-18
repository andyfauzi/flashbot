<?php

// File ini disimpan di: config/chatbot.php

return [
    /*
    |----------------------------------------------------------
    | Fonnte API
    | Isi nilai ini di file .env
    |----------------------------------------------------------
    */
    'fonnte_token' => env('FONNTE_TOKEN'),

    // Webhook & Integration Secrets
    'webhook_secret'       => env('WEBHOOK_SECRET'),
    'meta_verify_token'    => env('META_WEBHOOK_VERIFY_TOKEN', env('META_VERIFY_TOKEN')),
    'meta_access_token'    => env('META_ACCESS_TOKEN'),
    'baileys_secret'       => env('BAILEYS_SECRET'),
    
    // Portal & Gateway Configs
    'qris_image_url'       => env('QRIS_IMAGE_URL'),
    'ngrok_public_url'     => env('NGROK_PUBLIC_URL'),
    'bank_transfer_info'   => env('BANK_TRANSFER_INFO', "Bank BCA\nNo Rekening: 123456789\na/n Toko Flashbot"),
    'meta_phone_number_id' => env('META_PHONE_NUMBER_ID'),
    'meta_api_version'     => env('META_API_VERSION', 'v20.0'),
    'whatsapp_group_id_seller' => env('WHATSAPP_GROUP_ID_SELLER'),
    'baileys_api_url'      => env('BAILEYS_API_URL', 'http://127.0.0.1:3001'),
];