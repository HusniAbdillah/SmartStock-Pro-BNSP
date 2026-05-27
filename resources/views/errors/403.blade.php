<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Akses Ditolak — SmartStock Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="h-full" style="background-color:#F8FAFC; font-family:'Inter',sans-serif; display:flex; align-items:center; justify-content:center; padding:24px;">
    <div style="text-align:center; max-width:400px;">
        <div style="width:56px; height:56px; background:#FEE2E2; border-radius:5px; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
            <svg style="width:28px; height:28px; color:#EF4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <p style="font-size:64px; font-weight:300; color:#EF4444; line-height:1; margin-bottom:16px;">403</p>
        <h1 style="font-size:26px; font-weight:300; color:#061B31; margin-bottom:8px;">Akses Ditolak</h1>
        <p style="font-size:14px; color:#64748D; line-height:1.6; margin-bottom:32px;">Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator jika ini adalah kesalahan.</p>
        <a href="{{ route('dashboard') }}"
           style="display:inline-flex; align-items:center; gap:8px; background:#533AFD; color:#FFFFFF; font-size:14px; font-weight:500; padding:10px 20px; border-radius:4px; text-decoration:none; transition:background-color 150ms ease;"
           onmouseover="this.style.backgroundColor='#4329E8';"
           onmouseout="this.style.backgroundColor='#533AFD';">
            <svg style="width:16px; height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
