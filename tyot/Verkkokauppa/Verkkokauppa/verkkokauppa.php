<?php
session_start();
require '../db.php';

$userId = $_SESSION['user_id'] ?? null;
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$timeout_hours = 24;
$stmt = $conn->prepare("
    UPDATE vkauppa_ostoskori
    SET tila = 'luovutettu'
    WHERE tila = 'kaytossa'
      AND viimeksi_paivitetty < NOW() - INTERVAL ? HOUR
");
$stmt->bind_param("i", $timeout_hours);
$stmt->execute();

$perPage = (int)($_GET['perPage'] ?? 24);
$allowedPerPage = [6,12,24,33];
if (!in_array($perPage, $allowedPerPage)) $perPage = 24;

$page = (int)($_GET['page'] ?? 1);
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$isSearchActive = $search !== '' || $category !== '';

$categories = [];
$catResult = $conn->query("SELECT DISTINCT luokka FROM vkauppa_tuotteet ORDER BY luokka ASC");
while ($row = $catResult->fetch_assoc()) $categories[] = $row['luokka'];

$sql = "SELECT tuoteid, nimi, hinta, kuvaus, kuva, luokka, varastossa, alennus, aktiivinen, suosittu
        FROM vkauppa_tuotteet WHERE aktiivinen = 1";
$conditions = [];
$params = [];
$types = "";

if ($search !== '') {
    $conditions[] = "nimi LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if ($category !== '') {
    $conditions[] = "luokka = ?";
    $params[] = $category;
    $types .= "s";
}
if ($conditions) $sql .= " AND ".implode(" AND ", $conditions);

$count_sql = str_replace(
    "SELECT tuoteid, nimi, hinta, kuvaus, kuva, luokka, varastossa, alennus, aktiivinen, suosittu",
    "SELECT COUNT(*) AS total",
    $sql
);
$stmt = $conn->prepare($count_sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalProducts = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $perPage);

$start = ($page-1)*$perPage;
$sql .= " ORDER BY nimi ASC LIMIT ?, ?";
$params[] = $start;
$params[] = $perPage;
$types .= "ii";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$cartQuantities = [];
if ($userId) {
    $sqlCart = "SELECT tuoteid, maara FROM vkauppa_kori_tuotteet 
                INNER JOIN vkauppa_ostoskori USING (ostoskoriid)
                WHERE asiakasid=? AND tila='kaytossa'";
    $stmtCart = $conn->prepare($sqlCart);
    $stmtCart->bind_param("i",$userId);
    $stmtCart->execute();
    $cartResult = $stmtCart->get_result();
    while ($row = $cartResult->fetch_assoc()) $cartQuantities[$row['tuoteid']] = (int)$row['maara'];
}

$popular = $conn->query("SELECT * FROM vkauppa_tuotteet WHERE suosittu=1 AND aktiivinen = 1 ORDER BY nimi ASC")->fetch_all(MYSQLI_ASSOC);
$discounted = $conn->query("SELECT * FROM vkauppa_tuotteet WHERE alennus>0 AND aktiivinen = 1 ORDER BY nimi ASC")->fetch_all(MYSQLI_ASSOC);

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function renderProductsRow($products, $cartQuantities) {
    $html = '';
    foreach ($products as $product) {
        $qty = $cartQuantities[$product['tuoteid']] ?? 0;

        $html .= '<div class="col-sm-4 col-md-4 col-lg-4" style="margin-bottom:20px;">
            <div class="product-card" style="height:480px;"> <!-- fixed height -->
                <div class="product-image">';
        
        if (!empty($product['kuva'])) {
            $html .= '<img src="../'.htmlspecialchars($product['kuva']).'" alt="'.htmlspecialchars($product['nimi']).'">';
        } else {
            $html .= '<div class="no-image">Ei kuvaa</div>';
        }

        $html .= '</div>
                <div class="product-info">
                    <h3 class="product-name">'.htmlspecialchars($product['nimi']).'</h3>
                    <p class="product-desc">'.htmlspecialchars($product['kuvaus']).'</p>
                    <p class="product-price">';
        
        if ($product['alennus'] > 0) {
            $html .= '<b>Hinta: </b><span class="original-price" style="text-decoration:line-through;color:#888;">'
                     .number_format($product['hinta'],2).' €</span> ';
            $html .= '<br><b>Alennettu: </b><span class="discounted-price" style="color:red;font-weight:bold;">'
                     .number_format($product['hinta']*(1-$product['alennus']/100),2).' €</span>';
        } else {
            $html .= number_format($product['hinta'],2).' €';
        }

        $html .= '</p>
                    <p class="product-category">'.htmlspecialchars($product['luokka']).'</p>
                </div>
                <div class="cart-controls text-center flex items-center justify-center gap-2" style="margin-top:10px;">
                    <!-- Minus button -->
                    <button class="btn minus" data-id="'.$product['tuoteid'].'" aria-label="Decrease quantity">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-dash" viewBox="0 0 16 16">
                            <path d="M3.5 8a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5z"/>
                        </svg>
                    </button>

                    <!-- Quantity -->
                    <span class="quantity px-2" data-id="'.$product['tuoteid'].'">'.$qty.'</span>

                    <!-- Plus button -->
                    <button class="btn plus" data-id="'.$product['tuoteid'].'" data-stock="'.$product['varastossa'].'" '.($qty >= $product['varastossa'] ? 'disabled' : '').' aria-label="Increase quantity">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>';
    }
    return $html;
}


if ($isAjax) {
    echo json_encode([
        'html' => renderProductsRow($products, $cartQuantities),
        'totalPages' => $totalPages
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Etusivu</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tauri&family=Zalando+Sans:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../css/styles.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
</head>
<style>

</style>
</head>
<body>
<section class="hero-section">
    <div class="leaf-decoration leaf-1"></div>
    <div class="leaf-decoration leaf-2"></div>
    <div class="leaf-decoration leaf-3"></div>
    
    <div class="hero-image">
        <div class="image-frame">
            <img src="../images/bg-6.jpeg" alt="Tuoreita mansikoita">
        </div>
    </div>
    
    <div class="hero-content">
        <div class="hero-logo">
            <img src="../images/logo.png" alt="Fresh organic produce" style="width:70px">
            <span class="logo-text">Makupolku</span>
        </div>
        
        <h1 class="hero-title">Ruoka ei ole vain tarve, se on elämäntapa</h1>
        
        <p class="hero-description">
            Tuoreimmat ja laadukkaimmat tuotteet suoraan luonnosta kotiisi. 
            Nautimme jokaisesta hetkestä kun toimitamme sinulle parasta mitä luonto tarjoaa.
        </p>
        
        <p class="hero-description">
            Valitse laadukkaista tuotteistamme ja koe ero. Jokainen tuote on valittu huolellisesti 
            varmistaaksemme parhaan mahdollisen maun ja raikkouden.
        </p>
        
        <div class="hero-cta">
            <a href="#products" class="cta-button" id="all-products-btn">Tutustu Tuotteisiin</a>
        </div>
        
        <div class="hero-features">
            <div class="feature-item">
                <div class="feature-icon">✓</div>
                <span>Tuoreet Tuotteet</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">✓</div>
                <span>Nopea Toimitus</span>
            </div>
            <div class="feature-item">
                <div class="feature-icon">✓</div>
                <span>Laadukkaat Valinnat</span>
            </div>
        </div>
    </div>
</section>

<nav class="navbar">

    <!-- LEFT -->
    <div class="nav-left">
        <span class="nav-title"><a href="verkkokauppa.php" style="text-decoration: none; color:black; margin-left: 10px">Makupolku</a></span>
    </div>

    <!-- CENTER -->
    <div class="nav-center">
        <form id="search-form" class="form-inline">
            <div class="search-wrapper">
                <i class="fa fa-search"></i>
                <input type="text" name="search" placeholder="Hae tuotetta" value="<?= htmlspecialchars($search) ?>">
            </div>

            <div class="custom-select-wrapper">
                <div class="custom-select">
                    <span class="selected">Valitse Luokka</span>
                    <div class="dropdown-arrow"></div>
                    <ul class="custom-options">
                        <li data-value="#">Valitse Luokka</li>
                        <li data-value="">Kaikki Tuotteet</li>
                        <?php foreach ($categories as $cat): ?>
                            <li data-value="<?= $cat ?>"><?= ucfirst(str_replace('-', ' ', $cat)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <input type="hidden" name="category" value="#">
            </div>
            <button type="button" class="btn btn-default" id="reset-filters">Nollaa</button>
        </form>
    </div>

    <!-- RIGHT -->
    <div class="nav-right">

        <a class="side-link nav-link" href="verkkokauppa.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </a>

        <a class="side-link nav-link" href="omat_tilaukset.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </a>

        <a class="side-link nav-link" href="ostoskori.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </a>
    </div>

</nav>

<!-- Menu Toggle Button -->
<button class="menu-toggle-btn" onclick="toggleMenu()">
    <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
</button>

<!-- Overlay -->
<div class="menu-overlay" onclick="toggleMenu()"></div>

<!-- Side Menu -->
<div id="sideMenu" class="side-menu">
    <div class="menu-header">
        <h1>Makupolku</h1>
        <button class="x-btn" onclick="toggleMenu()">×</button>
    </div>

    <div class="menu-content">
        <a class="side-link" href="index.html">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Intro
        </a>
        
        <a class="side-link" href="verkkokauppa.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Etusivu
        </a>
        
        <a class="side-link" href="omat_tilaukset.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Omat tilaukset
        </a>
        
        <a class="side-link" href="asetukset.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Asetukset
        </a>
        
        <a class="side-link" href="ostoskori.php">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Ostoskori
        </a>
        
        <div class="menu-divider"></div>
        
        <?php if ($userId): ?>
            <a class="side-link" href="kirjaudu_ulos.php">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Kirjaudu ulos
            </a>
        <?php else: ?>
            <a class="side-link" href="kirjautuminen.php">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Kirjaudu sisään
            </a>
        <?php endif; ?>
    </div>
</div>

<div id="menuBackdrop" class="menu-backdrop" onclick="toggleMenu()"></div>

<div class="container" style="padding-top: 20px;">
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
</div>
<div class="container" id="home-view">

    <h2 class="tauri-regular">Suosituimmat tuotteet</h2>
    <div class="horizontal-scroll" id="popular-products">
        <?php if(!empty($popular)): ?>
            <?php foreach($popular as $product): ?>
                <div class="scroll-item">
                    <div class="panel panel-default">
                        <div class="panel-body text-center">
                            <?php if (!empty($product['kuva'])): ?>
                                <img src="../<?= htmlspecialchars($product['kuva']) ?>" class="product-img">
                            <?php else: ?><span>Ei kuvaa</span><?php endif; ?>
                            <p><strong><?= htmlspecialchars($product['nimi']) ?></strong></p>
                            <p class="zalando-sans">
                                <strong>Hinta:</strong>
                                <?php if($product['alennus']>0): ?>
                                    <span style="text-decoration:line-through;color:#888;"><?= number_format($product['hinta'],2) ?> €</span>
                                    <?php 
                                    $discountedPrice = ceil($product['hinta'] * (1 - $product['alennus']/100) * 100) / 100;  
                                    ?>
                                    <br><b>Alennettu: </b><span style="color:red;font-weight:bold;margin-left:5px;"><?= number_format($discountedPrice, 2) ?> €</span>

                                <?php else: ?><?= number_format($product['hinta'],2) ?> €<?php endif; ?>
                            </p>
                                <div class="cart-controls text-center flex items-center justify-center gap-2">
                                    <!-- Minus button -->
                                    <button class="btn btn-default btn-sm minus" data-id="<?= $product['tuoteid'] ?>" aria-label="Decrease quantity">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-dash" viewBox="0 0 16 16">
                                            <path d="M3.5 8a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5z"/>
                                        </svg>
                                    </button>

                                    <!-- Quantity display -->
                                    <span class="quantity px-2" data-id="<?= $product['tuoteid'] ?>"><?= $cartQuantities[$product['tuoteid']] ?? 0 ?></span>

                                    <!-- Plus button -->
                                    <button class="btn btn-default btn-sm plus" data-id="<?= $product['tuoteid'] ?>" data-stock="<?= $product['varastossa'] ?>" <?= (($cartQuantities[$product['tuoteid']] ?? 0) >= $product['varastossa'] ? 'disabled':'') ?> aria-label="Increase quantity">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                        </svg>
                                    </button>
                                </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?><div class="no-items-message">Ei suosittuja tuotteita saatavilla.</div><?php endif; ?>
    </div>

    <h2 class="tauri-regular" style="margin-top:30px;">Alennetut tuotteet</h2>
    <div class="horizontal-scroll" id="discounted-products">
        <?php if(!empty($discounted)): ?>
            <?php foreach($discounted as $product): ?>
                <div class="scroll-item">
                    <div class="panel panel-default">
                        <div class="panel-body text-center">
                            <?php if (!empty($product['kuva'])): ?>
                                <img src="../<?= htmlspecialchars($product['kuva']) ?>" class="product-img">
                            <?php else: ?><span>Ei kuvaa</span><?php endif; ?>
                            <p><strong><?= htmlspecialchars($product['nimi']) ?></strong></p>
                            <p class="zalando-sans">
                                <strong>Hinta:</strong>
                                <span style="text-decoration:line-through;color:#888;"><?= number_format($product['hinta'],2) ?> €</span>
                                <?php 
                                $discountedPrice = ceil($product['hinta'] * (1 - $product['alennus']/100) * 100) / 100;  
                                ?>
                                <br><b>Alennettu: </b><span style="color:red;font-weight:bold;margin-left:5px;"><?= number_format($discountedPrice, 2) ?> €</span>

                            </p>
                                <div class="cart-controls text-center flex items-center justify-center gap-2">
                                    <!-- Minus button -->
                                    <button class="btn btn-default btn-sm minus" data-id="<?= $product['tuoteid'] ?>" aria-label="Decrease quantity">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-dash" viewBox="0 0 16 16">
                                            <path d="M3.5 8a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5z"/>
                                        </svg>
                                    </button>

                                    <!-- Quantity display -->
                                    <span class="quantity px-2" data-id="<?= $product['tuoteid'] ?>"><?= $cartQuantities[$product['tuoteid']] ?? 0 ?></span>

                                    <!-- Plus button -->
                                    <button class="btn btn-default btn-sm plus" data-id="<?= $product['tuoteid'] ?>" data-stock="<?= $product['varastossa'] ?>" <?= (($cartQuantities[$product['tuoteid']] ?? 0) >= $product['varastossa'] ? 'disabled':'') ?> aria-label="Increase quantity">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                        </svg>
                                    </button>
                                </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?><div class="no-items-message">Ei alennettuja tuotteita saatavilla.</div><?php endif; ?>
    </div>

</div>

<div class="container" id="search-view" style="<?= $isSearchActive ? '' : 'display:none;' ?>">
    <div class="row"></div>
</div>

<div id="signin-modal" class="modal">
    <div class="modal-content">
        <h4 class="zalando-sans">Kirjaudu sisään</h4>
        <p class="zalando-sans">Lisätäksesi tuotteita ostoskoriin sinun täytyy kirjautua sisään.</p>

        <div class="modal-actions">
            <a href="kirjautuminen.php" class="zalando-sans btn-dark">Kirjaudu sisään</a>
            <a href="rekisteroityminen.php" class="zalando-sans btn-light">Luo tili</a>
        </div>

        <button onclick="closeModal()" class="zalando-sans close-btn">Sulje</button>
    </div>
</div>
<div class="container" id="pagination-container" style="text-align:center; margin-top:20px;">
    <nav data-pagination id="pagination">
        <a href="#" class="prev disabled"></a>
        <ul></ul>
        <a href="#" class="next"></a>
    </nav>
</div>
<div class="responsive-spacer"></div>
<script>
let userId = <?= $userId ? $userId : 'null' ?>;

function showModal() {
    document.getElementById('signin-modal').style.display = 'block';
}
function closeModal() {
    document.getElementById('signin-modal').style.display = 'none';
}

function syncProductCards(productId, qty, stock) {
    document.querySelectorAll(`.quantity[data-id="${productId}"]`).forEach(el => el.innerText = qty);
    document.querySelectorAll(`.plus[data-id="${productId}"]`).forEach(btn => btn.disabled = qty >= stock);
    document.querySelectorAll(`.minus[data-id="${productId}"]`).forEach(btn => btn.disabled = qty <= 0);
}

function cartAction(action, productId, stock) {
    return fetch('kori.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=${action}&productId=${productId}`
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        let qty = parseInt(data.qty) || 0;
        syncProductCards(productId, qty, stock);
    });
}

function attachCartListeners() {
    document.querySelectorAll('.plus').forEach(btn => {
        btn.onclick = function() {
            if (!userId) { showModal(); return; }
            cartAction('add', this.dataset.id, parseInt(this.dataset.stock));
        };
    });
    document.querySelectorAll('.minus').forEach(btn => {
        btn.onclick = function() {
            if (!userId) return;
            cartAction('remove', this.dataset.id, parseInt(this.dataset.stock));
        };
    });
}

let totalPages = <?= $totalPages ?>;
let currentPage = <?= $page ?>;

function fetchProducts(page = 1) {
    currentPage = page;

    const categoryInput = document.querySelector('input[name="category"]');
    let cat = categoryInput ? categoryInput.value : (document.querySelector('select[name="category"]')?.value ?? '');
    const searchInput = document.querySelector('input[name="search"]');
    let search = searchInput.value.trim();
    const perPage = document.getElementById('perPage') ? document.getElementById('perPage').value : <?= $perPage ?>;

    const isFrontPage = cat === '#';

    if (isFrontPage && search) {
        cat = '';
        const categoryWrapper = document.querySelector('.custom-select-wrapper input[name="category"]');
        const categorySelected = document.querySelector('.custom-select-wrapper .selected');
        if (categoryWrapper && categorySelected) {
            categoryWrapper.value = '';
            categorySelected.textContent = 'Kaikki Tuotteet';
        }
    }

    if (isFrontPage && search) {
        cat = '';
        const categoryWrapper = document.querySelector('.custom-select-wrapper input[name="category"]');
        const categorySelected = document.querySelector('.custom-select-wrapper .selected');
        if (categoryWrapper && categorySelected) {
            categoryWrapper.value = '';
            categorySelected.textContent = 'Kaikki Tuotteet';
        }
    }

    const params = new URLSearchParams({page, category: cat, search, perPage});
    fetch('<?= basename($_SERVER['PHP_SELF']) ?>?' + params, {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.json())
.then(data => {
    const searchRow = document.querySelector('#search-view .row');
    searchRow.innerHTML = data.html;

    totalPages = data.totalPages;

    if (!(isFrontPage && !search) && !data.html.trim()) {
        searchRow.innerHTML = '<div class="no-items-message" style="padding:20px; text-align:center;">Ei tuotteita hakuehdoilla löytynyt.</div>';
    }

    const homeView = document.getElementById('home-view');
    const searchView = document.getElementById('search-view');

    if (!search && isFrontPage) {
        homeView.style.display = 'block';
        searchView.style.display = 'none';
    } else {
        searchView.style.display = 'block';
        homeView.style.display = 'none';
    }

    renderPagination();
    attachCartListeners();
    disableMinusButtonsOnLoad();

    renderPerPageSelector();
    document.getElementById('pagination').style.display = totalPages > 1 ? 'inline-block' : 'none';
});
}


function updateView() {
    const searchRow = document.querySelector('#search-view .row');
    const homeView = document.getElementById('home-view');

    if (searchRow && searchRow.children.length > 0) {
        document.getElementById('search-view').style.display = 'block';
        homeView.style.display = 'none';
    } else {
        document.getElementById('search-view').style.display = 'none';
        homeView.style.display = 'block';
    }
}

function resetFilters() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) searchInput.value = '';

    const categoryInput = document.querySelector('.custom-select-wrapper input[name="category"]');
    const selectedSpan = document.querySelector('.custom-select-wrapper .selected');
    if (categoryInput && selectedSpan) {
        categoryInput.value = '#';
        selectedSpan.textContent = 'Valitse Luokka';
    }

    const perPageContainer = document.querySelector('.per-page-selector');
    if (perPageContainer) perPageContainer.remove();

    fetchProducts(1);
}

function renderPerPageSelector() {
    const catInput = document.querySelector('input[name="category"]');
    const cat = catInput ? catInput.value : '#';
    let container = document.querySelector('.per-page-selector');

    if (cat !== '#') {
        if (!container) {
            container = document.createElement('div');
            container.className = 'per-page-selector';
            container.style.marginBottom = '15px';

            container.innerHTML = `
                <label class="zalando-sans">Näytä tuotteita per sivu: </label>
                <div class="custom-select-wrapper" style="display:inline-block; position:relative; width:100px; width: 75px;">
                    <div class="custom-select" id="perPage-select">
                        <span class="selected">24</span>
                        <div class="dropdown-arrow"></div>
                        <ul class="custom-options">
                            ${[6,12,24,33].map(p => `<li data-value="${p}">${p}</li>`).join('')}
                        </ul>
                    </div>
                    <input type="hidden" id="perPage" name="perPage" value="24">
                </div>
            `;
            document.getElementById('home-view').insertAdjacentElement('beforebegin', container);

            // Add dropdown behavior
            const wrapper = container.querySelector('.custom-select-wrapper');
            const selectBox = wrapper.querySelector('.custom-select');
            const hiddenInput = wrapper.querySelector('input[name="perPage"]');
            const selected = wrapper.querySelector('.selected');
            const options = wrapper.querySelectorAll('.custom-options li');

            selectBox.addEventListener('click', (e) => {
                e.stopPropagation();
                selectBox.classList.toggle('active');
            });

            options.forEach(option => {
                option.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selected.textContent = option.textContent;
                    hiddenInput.value = option.dataset.value;
                    selectBox.classList.remove('active');

                    fetchProducts(1);
                });
            });

            document.addEventListener('click', () => selectBox.classList.remove('active'));
        }
    } else if (container) {
        container.remove();
    }
}



document.addEventListener('DOMContentLoaded', () => {
    attachCartListeners();
    disableMinusButtonsOnLoad();
    updateView();
    renderPerPageSelector();
    renderPagination();

    document.getElementById('reset-filters').onclick = resetFilters;
    document.querySelector('form#search-form').onsubmit = e => { e.preventDefault(); fetchProducts(); };
    const categorySelect = document.querySelector('select[name="category"]');
    if (categorySelect) {
        categorySelect.onchange = () => {
            renderPerPageSelector();
            fetchProducts();
        };
    }
});

function renderPagination() {
    const container = document.querySelector('#pagination ul');
    const nav = document.getElementById('pagination');
    if (!container || totalPages <= 1) {
        nav.style.display = 'none';
        return;
    }
    nav.style.display = 'inline-block';
    container.innerHTML = '';

    const prev = nav.querySelector('.prev');
    prev.classList.toggle('disabled', currentPage === 1);
    prev.onclick = e => {
        e.preventDefault();
        if (currentPage > 1) fetchProducts(currentPage - 1);
    };

    const maxPagesToShow = 10;
    let start = Math.max(1, currentPage - Math.floor(maxPagesToShow/2));
    let end = Math.min(totalPages, start + maxPagesToShow - 1);
    if (end - start < maxPagesToShow - 1) start = Math.max(1, end - maxPagesToShow + 1);

    if (start > 1) {
        const li = document.createElement('li');
        li.innerHTML = `<a href="#">1</a>`;
        li.onclick = e => { e.preventDefault(); fetchProducts(1); };
        container.appendChild(li);

        if (start > 2) {
            const liDots = document.createElement('li');
            liDots.innerHTML = `<a href="#">…</a>`;
            liDots.classList.add('disabled');
            container.appendChild(liDots);
        }
    }

    for (let i = start; i <= end; i++) {
        const li = document.createElement('li');
        li.className = i === currentPage ? 'current' : '';
        li.innerHTML = `<a href="#">${i}</a>`;
        li.onclick = e => { e.preventDefault(); fetchProducts(i); };
        container.appendChild(li);
    }

    if (end < totalPages) {
        if (end < totalPages - 1) {
            const liDots = document.createElement('li');
            liDots.innerHTML = `<a href="#">…</a>`;
            liDots.classList.add('disabled');
            container.appendChild(liDots);
        }

        const liLast = document.createElement('li');
        liLast.innerHTML = `<a href="#">${totalPages}</a>`;
        liLast.onclick = e => { e.preventDefault(); fetchProducts(totalPages); };
        container.appendChild(liLast);
    }

    const next = nav.querySelector('.next');
    next.classList.toggle('disabled', currentPage === totalPages);
    next.onclick = e => {
        e.preventDefault();
        if (currentPage < totalPages) fetchProducts(currentPage + 1);
    };
}

function disableMinusButtonsOnLoad() {
    document.querySelectorAll('.quantity').forEach(qtyEl => {
        const qty = parseInt(qtyEl.innerText) || 0;
        const id = qtyEl.dataset.id;
        document.querySelectorAll(`.minus[data-id="${id}"]`)
            .forEach(btn => btn.disabled = qty <= 0);
    });
}

document.getElementById('all-products-btn').addEventListener('click', function(e) {
    e.preventDefault();

    const categoryInput = document.querySelector('.custom-select-wrapper input[name="category"]');
    const selectedText = document.querySelector('.custom-select-wrapper .selected');
    if (categoryInput && selectedText) {
        categoryInput.value = '';
        selectedText.textContent = 'Kaikki Tuotteet';
    }

    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) searchInput.value = '';

    currentPage = 1;

    renderPerPageSelector();

    const paginationNav = document.getElementById('pagination');
    if (paginationNav) paginationNav.style.display = 'inline-block';

    fetchProducts(1);
});

document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
    const selectInput = wrapper.querySelector('input[name="category"]');
    const selectBox = wrapper.querySelector('.custom-select');
    const options = wrapper.querySelector('.custom-options');
    const selected = wrapper.querySelector('.selected');

    selectBox.addEventListener('click', (e) => {
        e.stopPropagation();
        selectBox.classList.toggle('active');
    });

    document.querySelectorAll('.custom-options li').forEach(option => {
        option.addEventListener('click', (e) => {
            e.stopPropagation();
            const wrapper = option.closest('.custom-select-wrapper');
            const selectInput = wrapper.querySelector('input[name="category"]');
            const selectBox = wrapper.querySelector('.custom-select');
            const selected = wrapper.querySelector('.selected');
            const searchInput = document.querySelector('input[name="search"]');

            selected.textContent = option.textContent;
            selectInput.value = option.dataset.value;

            if (option.dataset.value === '#') {
                if (searchInput) searchInput.value = '';
            }

            selectBox.classList.remove('active');

            renderPerPageSelector();
            fetchProducts(1);
        });
    });

        document.addEventListener('click', () => {
            selectBox.classList.remove('active');
        });
    });


function toggleMenu() {
    const menu = document.getElementById('sideMenu');
    const overlay = document.querySelector('.menu-overlay');
    const toggleBtn = document.querySelector('.menu-toggle-btn');
    
    menu.classList.toggle('open');
    overlay.classList.toggle('active');
    toggleBtn.classList.toggle('active');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('sideMenu');
    const toggleBtn = document.querySelector('.menu-toggle-btn');
    
    if (menu.classList.contains('open') && 
        !menu.contains(event.target) && 
        !toggleBtn.contains(event.target)) {
        toggleMenu();
    }
});
</script>

<?php include '../footer.php'; ?>
</body>
</html>
