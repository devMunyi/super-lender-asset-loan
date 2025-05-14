<?php
// Basic security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
// Content Security Policy tailored for your CDNs


$dynamic_img_src = $UPLOAD_BASE_URL ?? "";

$csp = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://7dbeefd5.sl-3mg.pages.dev https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com https://cdnjs.cloudflare.com https://d3js.org",
    "style-src 'self' 'unsafe-inline' https://7dbeefd5.sl-3mg.pages.dev https://fonts.googleapis.com https://cdn.jsdelivr.net",
    "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://7dbeefd5.sl-3mg.pages.dev",
    "img-src 'self' data: https://7dbeefd5.sl-3mg.pages.dev https://4f87d4cd.spke.pages.dev https://nwk-84y.pages.dev https://tenakata.superlender.co.ke $dynamic_img_src",
    "connect-src 'self' https://7dbeefd5.sl-3mg.pages.dev https://passkeys.spcl.one https://cdn.jsdelivr.net https://fonts.googleapis.com wss://webrtc.africastalking.com",
    "frame-src 'self' https://maps.google.com https://maps.apple.com https://www.google.com https://www.apple.com https://7dbeefd5.sl-3mg.pages.dev",
    "media-src 'self' https://superlender.co.ke",
    "object-src 'none'"
];

header("Content-Security-Policy: " . implode("; ", $csp));

// Only enable HSTS if you're using HTTPS exclusively
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}

?>



<meta name="robots" content="noindex, nofollow">
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

<link rel="manifest" href="favicons/site.webmanifest">

<?php if ($has_remote_cdn == 1) { ?>
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/bootstrap.min.css">

    <!-- Ionicons -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/ionicons.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/AdminLTE.min.css">

    <!-- _all-skins -->
    <!-- link goes here -->

    <!-- Fontawesome -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/font-awesome.min.css">

    <!-- morris.css -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/morris.css">

    <!-- jvectormap -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/jquery-jvectormap.css">

    <!-- Date Picker -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/bootstrap-datepicker.min.css">

    <!-- dataTables.bootstrap -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/dataTables.bootstrap.min.css">

    <!-- buttons.dataTables -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/buttons.dataTables.min.css">

    <!-- select2 -->
    <link rel="stylesheet" href="https://7dbeefd5.sl-3mg.pages.dev/select2.min.css" crossorigin="anonymous"
        referrerpolicy="no-referrer" />

<?php } else { ?>
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">

    <!-- Ionicons -->
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">

    <!-- _all-skins -->
    <!-- link goes here -->

    <!-- Fontawesome -->
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">

    <!-- morris.css -->
    <link rel="stylesheet" href="bower_components/morris.js/morris.css">

    <!-- jvectormap -->
    <link rel="stylesheet" href="bower_components/jvectormap/jquery-jvectormap.css">

    <!-- Date Picker -->
    <link rel="stylesheet" href="bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">

    <!-- dataTables.bootstrap -->
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">

    <!-- buttons.dataTables -->
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/buttons.dataTables.min.css">

    <!-- select2 -->
    <link href="bower_components/select2/select2.min.css" rel="stylesheet" />

<?php } ?>

<!-- _all-skins, kept default as may contain custom frequent changing CSS -->
<link rel="stylesheet" href="dist/css/skins/_all-skins.css?v=1015">
<link rel="stylesheet" href="themes/styles/custom.css?v=2025-April-25-09-06-AM">
<link rel="stylesheet" href="themes/styles/indicators.css?v=2024-09-19">

<!-- daterangepicker -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<!-- Google Font -->
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

<!-- notification placeholder -->
<div class="alert" id="standardnotif">

</div>