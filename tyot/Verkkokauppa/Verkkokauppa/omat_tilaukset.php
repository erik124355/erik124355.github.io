<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: kirjautuminen.php");
    exit;
}

$asiakasid = $_SESSION['user_id'];

// --- AJAX HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $tilausid = intval($_POST['tilausid']);
    header('Content-Type: application/json');

    $statusText = [
        'tilattu' => 'Tilattu',
        'pakkauksessa' => 'Pakkauksessa',
        'odottaa_kuljetusta' => 'Odottaa kuljetusta',
        'odottaa_noutamista' => 'Odottaa noutamista',
        'odottaa_keraamista' => 'Odottaa kuljettajan keräämistä',
        'kuljetuksessa' => 'Kuljetuksessa',
        'suoritettu' => 'Suoritettu',
        'peruttu' => 'Peruttu'
    ];

    $statusColor = [
        'tilattu' => 'bg-yellow-100 text-yellow-800',
        'pakkauksessa' => 'bg-blue-100 text-blue-800',
        'odottaa_kuljetusta' => 'bg-gray-100 text-gray-800',
        'odottaa_noutamista' => 'bg-emerald-100 text-emerald-800',
        'odottaa_keraamista' => 'bg-gray-100 text-gray-800',
        'kuljetuksessa' => 'bg-indigo-100 text-indigo-800',
        'suoritettu' => 'bg-green-100 text-green-800',
        'peruttu' => 'bg-red-100 text-red-800'
    ];

    if ($_POST['action'] === 'cancel') {
        $stmt = $conn->prepare("UPDATE vkauppa_tilaus SET tila='peruttu' WHERE tilausid=? AND tila NOT IN ('suoritettu','peruttu')");
        $stmt->bind_param("i", $tilausid);
        if ($stmt->execute()) {
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['success'=>false,'message'=>'Tietokantavirhe']);
        }
        exit;
    }

    if ($_POST['action'] === 'status') {
        $stmt = $conn->prepare("SELECT tila, cancel_deadline FROM vkauppa_tilaus WHERE tilausid=?");
        $stmt->bind_param("i", $tilausid);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res) {
            $tila = $res['tila'];
            $badgeClass = $statusColor[$tila] ?? 'bg-gray-100 text-gray-800';
            $displayStatus = $statusText[$tila] ?? 'Tuntematon';
            echo json_encode([
                'success'=>true,
                'tila'=>$tila,
                'badgeClass'=>$badgeClass,
                'displayStatus'=>$displayStatus,
                'cancel_deadline'=>$res['cancel_deadline']
            ]);
        } else {
            echo json_encode(['success'=>false]);
        }
        exit;
    }
}

// --- MAIN QUERY ---
$stmt = $conn->prepare("
    SELECT 
        t.tilausid,
        t.ostoskoriid,
        t.tila AS order_status,
        t.tilaus_tyyli,
        t.tilaus_hinta,
        t.tilattu_aika,
        t.cancel_deadline,
        n.nouto_koodi,
        k.katuosoite,
        k.tarkennus,
        kt.tuoteid,
        kt.maara,
        kt.hinta AS tuote_hinta,
        p.nimi AS tuotenimi
    FROM vkauppa_tilaus t
    INNER JOIN vkauppa_ostoskori o ON t.ostoskoriid = o.ostoskoriid
    INNER JOIN vkauppa_kori_tuotteet kt ON kt.ostoskoriid = o.ostoskoriid
    INNER JOIN vkauppa_tuotteet p ON p.tuoteid = kt.tuoteid
    LEFT JOIN vkauppa_nouto n ON n.tilausid = t.tilausid
    LEFT JOIN vkauppa_kuljetus k ON k.tilausid = t.tilausid
    WHERE o.asiakasid = ? AND t.tila NOT IN ('suoritettu','peruttu')
    ORDER BY t.tilattu_aika DESC, t.tilausid DESC
");
$stmt->bind_param("i", $asiakasid);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $tid = $row['tilausid'];
    if (!isset($orders[$tid])) {
        $orders[$tid] = [
            'tilausid' => $tid,
            'tilaus_hinta' => $row['tilaus_hinta'],
            'tila' => $row['order_status'],
            'tilaus_tyyli' => $row['tilaus_tyyli'],
            'tilattu_aika' => $row['tilattu_aika'],
            'cancel_deadline' => $row['cancel_deadline'] ?? null,
            'nouto_koodi' => $row['nouto_koodi'] ?? null,
            'katuosoite' => $row['katuosoite'] ?? null,
            'tarkennus' => $row['tarkennus'] ?? null,
            'items' => [],
        ];
    }
    $orders[$tid]['items'][] = [
        'tuotenimi' => $row['tuotenimi'],
        'maara' => $row['maara'],
        'hinta' => $row['tuote_hinta']
    ];
}

$statusText = [
    'tilattu' => 'Tilattu',
    'pakkauksessa' => 'Pakkauksessa',
    'odottaa_kuljetusta' => 'Odottaa kuljetusta',
    'odottaa_noutamista' => 'Odottaa noutamista',
    'odottaa_keraamista' => 'Odottaa kuljettajan keräämistä',
    'kuljetuksessa' => 'Kuljetuksessa',
    'suoritettu' => 'Suoritettu',
    'peruttu' => 'Peruttu'
];

$statusColor = [
    'tilattu' => 'bg-yellow-100 text-yellow-800',
    'pakkauksessa' => 'bg-blue-100 text-blue-800',
    'odottaa_kuljetusta' => 'bg-gray-100 text-gray-800',
    'odottaa_noutamista' => 'bg-emerald-100 text-emerald-800',
    'odottaa_keraamista' => 'bg-gray-100 text-gray-800',
    'kuljetuksessa' => 'bg-indigo-100 text-indigo-800',
    'suoritettu' => 'bg-green-100 text-green-800',
    'peruttu' => 'bg-red-100 text-red-800'
];
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omat tilaukset</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
        
        .cancel-btn {
            transition: all 0.2s ease;
        }
        
        .cancel-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .cancel-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen relative overflow-x-hidden">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <header class="bg-white border-b border-gray-100 relative z-10">
        <div class="mx-auto px-6 py-4 flex items-center justify-between">
            <a href="verkkokauppa.php" class="flex items-center space-x-3 text-gray-700 hover:text-emerald-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-medium">Takaisin</span>
            </a>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-semibold text-gray-800">Omat tilaukset</h1>
            </div>
        </div>
    </header>

    <main class="w-full px-4 py-12 relative z-10">
        <?php if (empty($orders)): ?>
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-lg">Sinulla ei ole aktiivisia tilauksia.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order):
                    $displayStatus = $statusText[$order['tila']] ?? 'Tuntematon';
                    $badgeClass = $statusColor[$order['tila']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow" 
                    data-tilausid="<?= $order['tilausid'] ?>" 
                    data-cancel-deadline="<?= $order['cancel_deadline'] ?? '' ?>">
                    
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-4 py-2 rounded-full text-sm font-medium <?= $badgeClass ?>" 
                                  data-tilausid="<?= $order['tilausid'] ?>">
                                <?= htmlspecialchars($displayStatus) ?>
                            </span>
                            <span class="text-sm text-gray-500">Tilaus #<?= $order['tilausid'] ?></span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <?php if($order['tila'] === 'odottaa_noutamista'): ?>
                                <div class="md:col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <p class="text-yellow-800">
                                        <strong>Automaattinen peruutus:</strong> 
                                        <span class="countdown" data-deadline="<?= $order['cancel_deadline'] ?>"></span>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div>
                                <span class="text-gray-500">Tilaustapa:</span>
                                <span class="ml-2 font-medium text-gray-900"><?= htmlspecialchars($order['tilaus_tyyli']) ?></span>
                            </div>
                            <div style="text-align: right;">
                                <span class="text-gray-500">Viimeisin aktiivisuus:</span>
                                <span class="ml-2 font-medium text-gray-900"><?= $order['tilattu_aika'] ?></span>
                            </div>
                            
                            <?php if ($order['tilaus_tyyli'] === 'nouto'): ?>
                                <div>
                                    <span class="text-gray-700" style="font-size: 20px;"><b>Noutokoodi:<b></span>
                                    <span class="ml-2 font-mono font-semibold text-emerald-600" style="font-size: 20px;"><?= htmlspecialchars($order['nouto_koodi'] ?? '-') ?></span>
                                </div>
                            <?php elseif ($order['tilaus_tyyli'] === 'kuljetus'): ?>
                                <div class="md:col-span-2">
                                    <span class="text-gray-500">Osoite:</span>
                                    <span class="ml-2 font-medium text-gray-900"><?= htmlspecialchars($order['katuosoite'] ?? '') ?> <?= htmlspecialchars($order['tarkennus'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="p-6" style="margin-bottom: 20px;">
                        <h3 class="font-medium text-gray-900 mb-3">Tuotteet</h3>
                        <div class="space-y-2 mb-4">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                    <span class="text-gray-700">
                                        <?= htmlspecialchars($item['tuotenimi']) ?> 
                                        <span class="text-gray-500">× <?= $item['maara'] ?></span>
                                    </span>
                                    <span class="font-medium text-gray-900"><?= number_format($item['hinta'], 2) ?> €</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="flex justify-between items-center pt-4 border-t border-gray-100 mb-4">
                            <span class="font-semibold text-gray-900">Yhteensä</span>
                            <span class="text-lg font-bold text-gray-900"><?= number_format($order['tilaus_hinta'], 2) ?> €</span>
                        </div>
                        
                        <button class="cancel-btn w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-3 rounded-xl transition-all cancel-order" 
                                data-tilausid="<?= $order['tilausid'] ?>">
                            Peru tilaus
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

<script>
$(document).ready(function() {
    // Cancel order button
    $('.cancel-order').click(function() {
        let tilausid = $(this).data('tilausid');
        if (!confirm('Haluatko varmasti perua tämän tilauksen?')) return;

        $.post('', { action: 'cancel', tilausid: tilausid }, function(data) {
            if (data.success) {
                // Update badge
                let card = $('.bg-white.rounded-2xl[data-tilausid="'+tilausid+'"]');
                card.find('span[data-tilausid="'+tilausid+'"]')
                    .removeClass()
                    .addClass('px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800')
                    .text('Peruttu');
                card.find('.cancel-btn').remove();
            } else {
                alert(data.message || 'Tilauksen peruminen epäonnistui.');
            }
        }, 'json').fail(function() {
            alert('Virhe tilauksen peruutuksessa.');
        });
    });

    // Countdown auto-cancel
    function cancelOrderSilent(tilausid, element) {
        $.post('', { action: 'cancel', tilausid: tilausid }, function(data) {
            if (data.success) {
                $(element).closest('.bg-white.rounded-2xl').fadeOut(500, function() {
                    $(this).remove();
                });
            }
        }, 'json');
    }

    function updateCountdowns() {
        $('.countdown').each(function() {
            let deadlineSec = $(this).data('deadline');
            if (!deadlineSec) return;

            let deadlineTime = parseInt(deadlineSec) * 1000;
            let now = Date.now();
            let distance = deadlineTime - now;
            let element = this;
            let tilausid = $(this).closest('[data-tilausid]').data('tilausid');

            if (distance <= 0) {
                cancelOrderSilent(tilausid, element);
                return;
            }

            let hours = Math.floor((distance % (1000*60*60*24)) / (1000*60*60));
            let minutes = Math.floor((distance % (1000*60*60)) / (1000*60));
            let seconds = Math.floor((distance % (1000*60)) / 1000);

            $(element).text(hours + 'h ' + minutes + 'm ' + seconds + 's');
        });
    }

    setInterval(updateCountdowns, 1000);
    updateCountdowns();

    // --- LIVE STATUS UPDATE ---
    function updateOrderStatus() {
        $('.bg-white.rounded-2xl').each(function() {
            let card = $(this);
            let tilausid = card.data('tilausid');

            $.post('', { action: 'status', tilausid: tilausid }, function(data) {
                if (data.success) {
                    let badge = card.find('span[data-tilausid="'+tilausid+'"]');

                    badge.removeClass().addClass('px-4 py-2 rounded-full text-sm font-medium '+data.badgeClass)
                         .text(data.displayStatus);

                    // Remove card if finished
                    if(data.tila === 'suoritettu' || data.tila === 'peruttu') {
                        card.fadeOut(500, function(){ $(this).remove(); });
                        return;
                    }

                    // Add countdown if needed
                    if(data.tila === 'odottaa_noutamista' && card.find('.countdown').length===0 && data.cancel_deadline) {
                        let countdownHtml = `<div class="md:col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p class="text-yellow-800"><strong>Automaattinen peruutus:</strong> 
                            <span class="countdown" data-deadline="${data.cancel_deadline}"></span></p></div>`;
                        card.find('.p-6.border-b').append(countdownHtml);
                    }
                }
            }, 'json');
        });
    }

    setInterval(updateOrderStatus, 5000);
});
</script>
</body>
</html>