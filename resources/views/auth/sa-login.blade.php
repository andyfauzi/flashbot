<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Access | Flashbot</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Ambient glowing orbs in background */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.5;
            animation: float 10s infinite alternate ease-in-out;
        }

        body::before {
            background: #4f46e5;
            top: -100px;
            left: -100px;
        }

        body::after {
            background: #e11d48;
            bottom: -100px;
            right: -100px;
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(30px, 30px); }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
            position: relative;
            z-index: 1;
        }

        .icon-container {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            font-size: 28px;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
            transform: rotate(-5deg);
        }

        h2 {
            color: #ffffff;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        p.subtitle {
            color: #94a3b8;
            font-size: 0.95rem;
            font-weight: 300;
            margin-bottom: 36px;
        }

        .input-group {
            position: relative;
            margin-bottom: 24px;
        }

        label {
            display: block;
            color: #cbd5e1;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px 16px;
            color: #ffffff;
            font-size: 1rem;
            font-family: 'Outfit', sans-serif;
            outline: none;
            transition: all 0.3s ease;
        }

        input:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
        }

        button[type="submit"] {
            width: 100%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 1.05rem;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(79, 70, 229, 0.3);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        .footer-note {
            margin-top: 32px;
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 300;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="icon-container">🛡️</div>
        <h2>Admin Portal</h2>
        <p class="subtitle">Secure access for authorized personnel.</p>

        @if($errors->any())
        <div class="error-msg">
            ⚠️ &nbsp; {{ $errors->first() }}
        </div>
        @endif

        @if(session('error'))
        <div class="error-msg">
            ⚠️ &nbsp; {{ session('error') }}
        </div>
        @endif

        @if(session('sukses'))
        <div class="error-msg" style="background: rgba(34, 197, 94, 0.15); border-color: rgba(34, 197, 94, 0.3); color: #86efac;">
            ✅ &nbsp; {{ session('sukses') }}
        </div>
        @endif

        <form method="POST" action="/sa-access">
            @csrf

            <div class="input-group">
                <label for="sa_email">Email Address</label>
                <input
                    type="email"
                    id="sa_email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="off"
                    autofocus
                    required
                    placeholder="Enter your email"
                >
            </div>

            <div class="input-group">
                <label for="sa_password">Password</label>
                <input
                    type="password"
                    id="sa_password"
                    name="password"
                    autocomplete="off"
                    required
                    placeholder="Enter your password"
                >
            </div>

            <button type="submit">Secure Login</button>
        </form>

        <div class="footer-note">
            Tenanta.id &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
