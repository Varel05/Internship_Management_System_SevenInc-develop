<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Masuk — Listmagang</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    * { font-family: 'Inter', sans-serif; }
    input:focus { outline: none; }
  </style>
</head>
<body style="background:#f8faf9; color:#1a1a1a; display:flex; justify-content:center; align-items:center; min-height:100vh;">

<div style="width:100%; max-width:440px; padding:20px;">
  <div style="text-align:center; margin-bottom:30px;">
    <a href="{{ route('home') }}" style="display:inline-flex; align-items:center; gap:10px; text-decoration:none;">
      <div style="width:32px; height:32px; border-radius:8px; background:#1a5c38; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:14px;">S</div>
      <span style="font-weight:700; color:#1a1a1a; font-size:18px;">Listmagang</span>
    </a>
  </div>

  <div style="background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
      <div style="width:40px;height:40px;border-radius:10px;background:#111827;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fas fa-sign-in-alt" style="color:#fff;font-size:14px;"></i>
      </div>
      <div>
        <div style="font-weight:700;color:#111;font-size:15px;">Masuk</div>
        <div style="color:#9ca3af;font-size:12px;">Login ke akun Anda</div>
      </div>
    </div>

    @if(session('error'))
      <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:13px;padding:10px 14px;border-radius:8px;margin-bottom:16px;">
        {{ session('error') }}
      </div>
    @endif
    @if(session('success'))
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-size:13px;padding:10px 14px;border-radius:8px;margin-bottom:16px;">
        {{ session('success') }}
      </div>
    @endif

    <form action="{{ route('user.login.submit') }}" method="POST">
      @csrf
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Email</label>
        <input type="email" name="email" required value="{{ old('email') }}" placeholder="email@contoh.com"
               style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;color:#111;background:#fff;box-sizing:border-box;"
               onfocus="this.style.borderColor='#374151'" onblur="this.style.borderColor='#e5e7eb'">
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Password</label>
        <input type="password" name="password" required placeholder="Password"
               style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;box-sizing:border-box;"
               onfocus="this.style.borderColor='#374151'" onblur="this.style.borderColor='#e5e7eb'">
      </div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
        <input type="checkbox" name="remember" id="remember" style="width:14px;height:14px;accent-color:#1a5c38;">
        <label for="remember" style="font-size:13px;color:#6b7280;cursor:pointer;">Ingat saya</label>
      </div>
      <button type="submit"
              style="width:100%;padding:13px;background:#111827;color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:700;cursor:pointer;transition:.2s;"
              onmouseover="this.style.background='#1f2937'" onmouseout="this.style.background='#111827'">
        <i class="fas fa-sign-in-alt" style="margin-right:8px;font-size:12px;"></i> Masuk ke Dashboard
      </button>
    </form>

    <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f3f4f6;text-align:center;">
      <p style="font-size:12px;color:#9ca3af;">Belum punya akun?
        <a href="{{ route('user.register') }}" style="color:#1a5c38;font-weight:600;text-decoration:none;">Daftar sekarang</a>
      </p>
    </div>
  </div>
</div>
</body>
</html>
