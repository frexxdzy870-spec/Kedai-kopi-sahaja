<?php
/** <head>, meta SEO, CSS, dan navbar. Set $pageTitle / $pageDesc sebelum include. */
require_once __DIR__ . '/functions.php';
$pageTitle = isset($pageTitle) ? $pageTitle . ' · ' . SITE_NAME : SITE_NAME . ' · ' . SITE_TAGLINE;
$pageDesc  = $pageDesc ?? SITE_DESC;
$active    = $active ?? '';
$navItems  = ['menu' => 'Menu', 'event' => 'Event', 'komunitas' => 'Komunitas', 'lowongan' => 'Lowongan', 'tentang-kami' => 'Tentang Kami'];
$host      = preg_match('/^[a-z0-9.\-]+(:\d+)?$/i', $_SERVER['HTTP_HOST'] ?? '') ? $_SERVER['HTTP_HOST'] : 'localhost';
$origin    = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $host;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta name="theme-color" content="#A30303">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDesc) ?>">
  <meta property="og:image" content="<?= e($origin . BASE_URL . '/assets/images/hero/barista.jpg') ?>">
  <link rel="icon" type="image/png" href="<?= asset('assets/images/logo/favicon.png') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,600;1,800&family=Montserrat:wght@400;500;700&family=Chelsea+Market&display=swap">
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/responsive.css') ?>">
</head>
<body>
<a class="skip" href="#konten">Langsung ke konten</a>
<header class="navbar">
  <div class="container nav-inner">
    <a class="brand" href="<?= url('') ?>" aria-label="<?= e(SITE_NAME) ?> - beranda">
      <img src="<?= asset('assets/images/logo/bunga.png') ?>" alt="" class="brand-flower">
      <img src="<?= asset('assets/images/logo/logo-sahaja.png') ?>" alt="Sahaja" class="brand-word">
    </a>
    <button class="nav-toggle" aria-expanded="false" aria-controls="nav" aria-label="Buka menu navigasi"><span></span><span></span><span></span></button>
    <nav id="nav" class="nav-links">
      <?php foreach ($navItems as $slug => $label): ?>
        <a href="<?= url($slug) ?>"<?= $active === $slug ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <a class="btn btn-yellow" href="<?= url('reservasi') ?>">Reservasi</a>
    </nav>
  </div>
</header>
<main id="konten">
