<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #1e293b;
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .lock-icon {
            width: 48px;
            height: 48px;
            background: rgba(79, 70, 229, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 22px;
        }

        h2 {
            color: #f1f5f9;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        p.subtitle {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 28px;
        }

        label {
            display: block;
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 6px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        input {
            width: 100%;
            background: #0f172a;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            padding: 12px 14px;
            color: #f1f5f9;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 18px;
        }

        input:focus {
            border-color: #4f46e5;
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 18px;
        }

        button[type="submit"] {
            width: 100%;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 13px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            margin-top: 4px;
        }

        button[type="submit"]:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        .footer-note {
            margin-top: 24px;
            text-align: center;
            color: #334155;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="lock-icon">🔐</div>
        <h2>Administrator Access</h2>
        <p class="subtitle">Restricted area. Authorized personnel only.</p>

        @if($errors->any())
        <div class="error-msg">
            {{ $errors->first() }}
        </div>
        @endif

        @if(session('error'))
        <div class="error-msg">
            {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="/sa-access">
            @csrf

            <label for="sa_email">Email</label>
            <input
                type="email"
                id="sa_email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="off"
                autofocus
                required
            >

            <label for="sa_password">Password</label>
            <input
                type="password"
                id="sa_password"
                name="password"
                autocomplete="off"
                required
            >

            <button type="submit">Masuk</button>
        </form>

        <div class="footer-note">
            Tenanta.id &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
