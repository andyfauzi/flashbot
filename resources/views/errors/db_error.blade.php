<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koneksi Database Terputus - Tenanta.id</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: hsl(262, 80%, 50%);
            --primary-light: hsl(262, 80%, 95%);
            --primary-dark: hsl(262, 80%, 35%);
            --bg: hsl(210, 20%, 98%);
            --card-bg: #ffffff;
            --text-main: hsl(220, 15%, 15%);
            --text-muted: hsl(220, 15%, 45%);
            --border: hsl(220, 15%, 90%);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .error-card {
            background: var(--card-bg);
            border-radius: 24px;
            max-width: 500px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 20px 45px -10px rgba(124, 58, 237, 0.12);
            border: 1px solid var(--border);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-container {
            width: 90px;
            height: 90px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            position: relative;
        }

        .database-icon {
            font-size: 42px;
            animation: pulse 2s infinite ease-in-out;
        }

        .warning-badge {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: #ef4444;
            color: #ffffff;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            border: 3px solid #ffffff;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }

        h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .btn-retry {
            display: inline-block;
            background: var(--primary);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 6px 18px rgba(124, 58, 237, 0.25);
            transition: var(--transition);
            cursor: pointer;
            border: none;
        }

        .btn-retry:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(124, 58, 237, 0.3);
        }

        .btn-retry:active {
            transform: translateY(0);
        }

        /* Collapsible Developer Details */
        .dev-section {
            margin-top: 32px;
            border-top: 1px solid var(--border);
            padding-top: 20px;
            text-align: left;
        }

        .dev-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            user-select: none;
        }

        .dev-details {
            display: none;
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 11px;
            color: #ef4444;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            white-space: pre-wrap;
        }

        .dev-section.active .dev-details {
            display: block;
        }
    </style>
</head>
<body>

    <div class="error-card">
        <div class="icon-container">
            <span class="database-icon">🖥️</span>
            <span class="warning-badge">!</span>
        </div>
        
        <h1>Koneksi Database Terputus</h1>
        <p>Layanan kami sedang tidak dapat terhubung ke server database saat ini. Jangan khawatir, tim teknis kami sedang menangani hal ini secara otomatis.</p>
        
        <button class="btn-retry" onclick="window.location.reload()">Coba Lagi</button>

        @if(config('app.debug'))
            <div class="dev-section" id="devSection">
                <div class="dev-title" onclick="toggleDevDetails()">
                    <span>INFORMASI DEBUG DEVELOPER</span>
                    <span id="toggleIcon">▼</span>
                </div>
                <div class="dev-details">{{ $message }}</div>
            </div>
        @endif
    </div>

    <script>
        function toggleDevDetails() {
            const section = document.getElementById('devSection');
            const icon = document.getElementById('toggleIcon');
            if (section.classList.contains('active')) {
                section.classList.remove('active');
                icon.textContent = '▼';
            } else {
                section.classList.add('active');
                icon.textContent = '▲';
            }
        }
    </script>
</body>
</html>
