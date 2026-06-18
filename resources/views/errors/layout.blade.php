<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Tenanta.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .error-code {
            font-size: 80px;
            font-weight: 800;
            color: #dc3545;
            line-height: 1;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 20px;
            color: #495057;
            margin-bottom: 30px;
        }
        .brand {
            color: #0d6efd;
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="brand">
            <i data-lucide="zap" style="color: #0d6efd;"></i> Tenanta.id
        </div>
        <div class="error-code">@yield('code')</div>
        <div class="error-message">@yield('message')</div>
        @hasSection('action')
            @yield('action')
        @else
            <a href="https://tenanta.id" class="btn btn-primary px-4 py-2 rounded-pill">Pelajari Lebih Lanjut</a>
        @endif
    </div>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      lucide.createIcons();
    </script>
</body>
</html>
