<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Pesanan') - ASKARSA</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; color: #222; }
        .wrap { max-width: 800px; margin: 0 auto; padding: 24px 16px; }
        .card { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 12px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 12px; background: #e5e7eb; }
        .badge.selesai { background: #bbf7d0; }
        .badge.dibatalkan { background: #fecaca; }
        .badge.diproses { background: #fde68a; }
        .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; }
        .alert.ok { background: #dcfce7; }
        .alert.err { background: #fee2e2; }
        a { color: #2563eb; text-decoration: none; }
        .filter a { margin-right: 12px; }
        .filter a.aktif { font-weight: bold; color: #111; }
        textarea { width: 100%; box-sizing: border-box; }
        button { padding: 8px 16px; cursor: pointer; }
    </style>
</head>
<body>
<div class="wrap">
    @if (session('sukses'))
        <div class="alert ok">{{ session('sukses') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert err">
            @foreach ($errors->all() as $pesan)
                <div>{{ $pesan }}</div>
            @endforeach
        </div>
    @endif

    @yield('isi')
</div>
</body>
</html>