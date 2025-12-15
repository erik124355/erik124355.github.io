<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: kirjautuminen.php");
    exit;
}

$asiakasid = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT t.tilausid, t.ostoskoriid, t.tilaus_hinta, t.tilaus_tyyli, t.tilattu_aika,
           n.nouto_koodi, k.katuosoite, k.tarkennus
    FROM vkauppa_tilaus t
    INNER JOIN vkauppa_ostoskori o ON t.ostoskoriid = o.ostoskoriid
    LEFT JOIN vkauppa_nouto n ON n.tilausid = t.tilausid
    LEFT JOIN vkauppa_kuljetus k ON k.tilausid = t.tilausid
    WHERE o.asiakasid = ?
    ORDER BY t.tilattu_aika DESC
    LIMIT 1
");
$stmt->bind_param("i", $asiakasid);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Tilauksen tietoja ei löytynyt.";
    exit;
}

$stmtItems = $conn->prepare("
    SELECT kt.tuoteid, kt.maara, kt.hinta, p.nimi
    FROM vkauppa_kori_tuotteet kt
    INNER JOIN vkauppa_tuotteet p ON kt.tuoteid = p.tuoteid
    WHERE kt.ostoskoriid = ?
");
$stmtItems->bind_param("i", $order['ostoskoriid']);
$stmtItems->execute();
$items = $stmtItems->get_result();
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiitos tilauksestasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
            pointer-events: none;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            top: -100px;
            right: -100px;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            bottom: 100px;
            left: -50px;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%);
            top: 50%;
            right: 10%;
        }
        
        @keyframes checkmark {
            0% {
                stroke-dashoffset: 100;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }
        
        @keyframes circle {
            0% {
                stroke-dashoffset: 300;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }
        
        .checkmark-circle {
            stroke-dasharray: 300;
            stroke-dashoffset: 300;
            animation: circle 0.6s ease-in-out forwards;
        }
        
        .checkmark-check {
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: checkmark 0.6s 0.3s ease-in-out forwards;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen relative overflow-x-hidden">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <main class="max-w-3xl mx-auto px-6 py-12 relative z-10">
        <!-- Success Icon -->
        <div class="text-center mb-8">
            <svg class="w-24 h-24 mx-auto mb-4" viewBox="0 0 100 100">
                <circle class="checkmark-circle" cx="50" cy="50" r="45" fill="none" stroke="#10b981" stroke-width="4"/>
                <path class="checkmark-check" d="M30 50 L45 65 L70 35" fill="none" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Kiitos tilauksestasi!</h1>
            <p class="text-gray-600">Tilauksesi on vastaanotettu ja käsittelyssä</p>
        </div>

        <!-- Order Details Card -->
        <div class="bg-white rounded-2xl shadow-sm p-8 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 pb-6 border-b border-gray-100">
                <div>
                    <span class="text-sm text-gray-500 block mb-1">Tilausnumero</span>
                    <span class="font-semibold text-gray-900">#<?= $order['tilausid'] ?></span>
                </div>
                <div>
                    <span class="text-sm text-gray-500 block mb-1">Tilaustapa</span>
                    <span class="font-semibold text-gray-900 capitalize"><?= htmlspecialchars($order['tilaus_tyyli']) ?></span>
                </div>
                <div>
                    <span class="text-sm text-gray-500 block mb-1">Tilausaika</span>
                    <span class="font-semibold text-gray-900"><?= $order['tilattu_aika'] ?></span>
                </div>
                
                <?php if ($order['tilaus_tyyli'] === 'nouto'): ?>
                    <div>
                        <span class="text-sm text-gray-500 block mb-1">Noutokoodi</span>
                        <span class="font-mono text-2xl font-bold text-emerald-600"><?= htmlspecialchars($order['nouto_koodi'] ?? '-') ?></span>
                    </div>
                    <div class="md:col-span-2 bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-sm text-amber-800">Tilaus perutaan automaattisesti 24 tunnin kuluttua, jos sitä ei käy hakemassa.</p>
                        </div>
                    </div>
                <?php elseif ($order['tilaus_tyyli'] === 'kuljetus'): ?>
                    <div class="md:col-span-2">
                        <span class="text-sm text-gray-500 block mb-1">Toimitusosoite</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($order['katuosoite'] ?? '') ?></span>
                        <?php if (!empty($order['tarkennus'])): ?>
                            <span class="block text-gray-600 mt-1"><?= htmlspecialchars($order['tarkennus']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Order Items -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Tilatut tuotteet</h2>
                <div class="space-y-2">
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <div class="flex justify-between items-center py-3 px-4 bg-gray-50 rounded-lg">
                            <span class="text-gray-700">
                                <?= htmlspecialchars($item['nimi']) ?> 
                                <span class="text-gray-500">× <?= $item['maara'] ?></span>
                            </span>
                            <span class="font-medium text-gray-900"><?= number_format($item['hinta'], 2) ?> €</span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Total -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-100">
                <span class="text-lg font-semibold text-gray-900">Yhteensä</span>
                <span class="text-2xl font-bold text-gray-900"><?= number_format($order['tilaus_hinta'], 2) ?> €</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="verkkokauppa.php" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-4 rounded-xl transition-colors shadow-sm hover:shadow text-center">
                Takaisin etusivulle
            </a>
            <a href="omat_tilaukset.php" class="flex-1 bg-white hover:bg-gray-50 text-gray-700 font-medium py-4 rounded-xl transition-colors shadow-sm hover:shadow border border-gray-200 text-center">
                Näytä tilaukseni
            </a>
        </div>
    </main>
</body>
</html>