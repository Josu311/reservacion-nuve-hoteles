<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>{{ $title }}</title>
  <meta name="description" content="{{ $description }}">

  <link rel="canonical" href="{{ $canonical }}">

  <meta property="og:title" content="{{ $title }}">
  <meta property="og:description" content="{{ $description }}">
  <meta property="og:url" content="{{ $url }}">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Nuve Hotel">
  <meta property="og:image" content="{{ $image }}">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $title }}">
  <meta name="twitter:description" content="{{ $description }}">
  <meta name="twitter:image" content="{{ $image }}">
</head>
<body>
  <noscript>
    <a href="{{ $canonical }}">Abrir página</a>
  </noscript>
</body>
</html>
