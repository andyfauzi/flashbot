module.exports = {
  apps: [
    {
      name: "flashbot-baileys",
      script: "./server.js",
      instances: 1, // Harus 1 untuk Baileys agar tidak bentrok file auth
      exec_mode: "fork", // Jangan gunakan cluster untuk WhatsApp bot
      watch: false, // Matikan watch di production agar tidak restart jika ada file berubah
      max_memory_restart: "1G", // Otomatis restart proses jika menggunakan RAM lebih dari 1GB
      env: {
        NODE_ENV: "development",
      },
      env_production: {
        NODE_ENV: "production",
      },
      // Error handling dan logs
      error_file: "../storage/logs/baileys-error.log",
      out_file: "../storage/logs/baileys-out.log",
      merge_logs: true,
      time: true
    },
    {
      name: "flashbot-queue",
      script: "artisan",
      interpreter: "php",
      args: "queue:work database",
      cwd: "../",
      watch: false,
      error_file: "storage/logs/queue-error.log",
      out_file: "storage/logs/queue-out.log",
      merge_logs: true,
      time: true
    }
  ]
};
