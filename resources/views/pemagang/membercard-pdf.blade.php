<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    @page {
      size: 85.6mm 53.98mm;
      margin: 0;
    }
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    html, body {
      width: 85.6mm;
      height: 53.98mm;
      overflow: hidden;
      background: #000;
      font-family: 'Inter', sans-serif;
    }
    .membercard-wrapper {
      width: 85.6mm !important;
      height: 53.98mm !important;
      border-radius: 0 !important;
      box-shadow: none !important;
    }
  </style>
</head>
<body>
  <x-membercard
      :brand="$brandObj ?? null"
      :name="$name"
      :code="$code"
      :angkatan="$angkatan"
      :instansi="$instansi"
      :divisi="$divisi ?? null"
      width="85.6mm"
      height="53.98mm"
      :forPdf="true"
  />
</body>
</html>
