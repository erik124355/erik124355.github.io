<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: kirjautuminen.php");
    exit;
}
$userid = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT ostoskoriid FROM vkauppa_ostoskori WHERE asiakasid=? AND tila='kaytossa'");
$stmt->bind_param("i", $userid);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

$cartid = $cart['ostoskoriid'] ?? null;
$cartitems = [];


if ($cartid) {
    $stmt = $conn->prepare("
        SELECT kt.kori_tuotteetid, kt.tuoteid, kt.maara, kt.hinta, t.nimi, t.kuva, t.varastossa
        FROM vkauppa_kori_tuotteet kt
        JOIN vkauppa_tuotteet t ON kt.tuoteid = t.tuoteid
        WHERE kt.ostoskoriid=?
    ");
    $stmt->bind_param("i", $cartid);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cartitems[] = $row;
    }
}
$totalprice = 0;
foreach ($cartitems as $item) {
    $totalprice += $item['maara'] * $item['hinta'];
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ostoskori</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
        
        .btn-quantity {
            transition: all 0.2s ease;
        }
        
        .btn-quantity:hover {
            transform: scale(1.1);
        }
        
        .btn-quantity:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen relative overflow-x-hidden">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <header class="bg-white border-b border-gray-100 relative z-10">
        <div class="px-6 py-4 flex items-center justify-between">
            <a href="verkkokauppa.php" class="flex items-center space-x-3 text-gray-700 hover:text-emerald-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-medium">Takaisin</span>
            </a>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-semibold text-gray-800">Ostoskori</h1>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-12 relative z-10">
        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                <div id="cart-container">
                    <?php if (empty($cartitems)): ?>
                        <div class="bg-white rounded-2xl p-12 text-center shadow-sm empty-cart-message">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <p class="text-gray-500 text-lg">Ostoskorisi on tyhjä</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cartitems as $item): ?>
                            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow mb-3" id="row-<?= $item['kori_tuotteetid'] ?>">
                                <div class="flex items-center justify-between gap-6">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-medium text-gray-900 mb-1 truncate"><?= htmlspecialchars($item['nimi']) ?></h3>
                                        <p class="text-sm text-gray-500"><?= number_format($item['hinta'], 2) ?> € / kpl</p>
                                    </div>
                                    
                                    <div class="flex items-center gap-3 bg-gray-50 rounded-full px-4 py-2">
                                        <button class="btn-quantity w-7 h-7 rounded-full bg-white shadow-sm hover:shadow hover:bg-emerald-50 flex items-center justify-center text-gray-600 hover:text-emerald-600 minus" 
                                                data-id="<?= $item['kori_tuotteetid'] ?>">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </button>
                                        <span class="font-semibold text-gray-900 w-8 text-center" id="qty-<?= $item['kori_tuotteetid'] ?>"><?= $item['maara'] ?></span>
                                        <button class="btn-quantity w-7 h-7 rounded-full bg-white shadow-sm hover:shadow hover:bg-emerald-50 flex items-center justify-center text-gray-600 hover:text-emerald-600 plus" 
                                                data-id="<?= $item['kori_tuotteetid'] ?>"
                                                data-stock="<?= $item['varastossa'] ?>">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <div class="text-right min-w-[80px]">
                                        <p class="font-semibold text-gray-900"><span id="total-<?= $item['kori_tuotteetid'] ?>"><?= number_format($item['maara'] * $item['hinta'], 2) ?></span> €</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-8 shadow-sm sticky top-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Yhteenveto</h2>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Välisumma</span>
                            <span id="grand-total"><?= number_format($totalprice, 2) ?> €</span>
                        </div>
                        <div class="h-px bg-gray-100"></div>
                        <div class="flex justify-between text-lg font-semibold text-gray-900">
                            <span>Yhteensä</span>
                            <span id="grand-total-2"><?= number_format($totalprice, 2) ?> €</span>
                        </div>
                    </div>
                    
                    <a href="maksu.php">
                        <button id="checkout-btn" 
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-4 rounded-xl transition-colors shadow-sm hover:shadow disabled:bg-gray-300 disabled:cursor-not-allowed"
                                <?= empty($cartitems) ? 'disabled' : '' ?>>
                            Siirry kassalle
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
    function checkIfCartEmpty() {
        let rows = $('#cart-container > div[id^="row-"]').length;
        if (rows === 0) {
            $('#cart-container').html(`
                <div class="bg-white rounded-2xl p-12 text-center shadow-sm empty-cart-message">
                    <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg">Ostoskorisi on tyhjä</p>
                </div>
            `);
            $('#checkout-btn').prop('disabled', true);
        }
    }

    $(document).ready(function() {
        function updateTotals(cartitemid, newqty, price) {
            $('#qty-' + cartitemid).text(newqty);
            $('#total-' + cartitemid).text((newqty * price).toFixed(2));

            let grandtotal = 0;
            $('span[id^="total-"]').each(function() {
                grandtotal += parseFloat($(this).text());
            });
            $('#grand-total').text(grandtotal.toFixed(2) + ' €');
            $('#grand-total-2').text(grandtotal.toFixed(2) + ' €');
        }

        $('.plus').click(function() {
            let cartitemid = $(this).data('id');
            let stock = parseInt($(this).data('stock'));
            let qty = parseInt($('#qty-' + cartitemid).text());

            $.post('kori_paivitys.php', {action: 'add', cartitemid: cartitemid}, function(data) {
                if (data.success) {
                    qty = data.qty;
                    let price = parseFloat(data.price) || 0;
                    updateTotals(cartitemid, qty, price);
                } else {
                    alert(data.message);
                }
            }, 'json');
        });

        $('.minus').click(function() {
            let cartitemid = $(this).data('id');
            let qty = parseInt($('#qty-' + cartitemid).text());

            $.post('kori_paivitys.php', {action: 'remove', cartitemid: cartitemid}, function(data) {
                if (data.success) {
                    qty = data.qty;
                    let price = parseFloat(data.price) || 0;
                    updateTotals(cartitemid, qty, price);

                    if (qty === 0) {
                        $('#row-' + cartitemid).remove();
                        checkIfCartEmpty();
                    }
                } else {
                    alert(data.message);
                }
            }, 'json');
        });
    });
    </script>
</body>
</html>