<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false, 'message'=>'Kirjaudu sisään']);
    exit;
}

$action = $_POST['action'] ?? '';
$cartitemid = (int)($_POST['cartitemid'] ?? 0);

/* -------------------------------
   1. Load cart item
--------------------------------*/
$stmt = $conn->prepare("
    SELECT 
        c.kori_tuotteetid,
        c.tuoteid,
        c.maara,
        c.hinta AS cart_price,
        c.ostoskoriid,
        t.hinta AS original_price,
        t.alennus
    FROM vkauppa_kori_tuotteet c
    JOIN vkauppa_tuotteet t ON c.tuoteid = t.tuoteid
    WHERE c.kori_tuotteetid=?
");
$stmt->bind_param("i", $cartitemid);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    echo json_encode(['success'=>false, 'message'=>'Tuotetta ei löytynyt']);
    exit;
}

/* -------------------------------
   2. Always calculate updated price
--------------------------------*/
$updatedPrice = $item['original_price'];

if ((float)$item['alennus'] > 0) {
    $updatedPrice = ceil($updatedPrice * (1 - $item['alennus'] / 100) * 100) / 100;
}

$updatedPrice = round($updatedPrice, 2);

/* -------------------------------
   3. Sync corrected price to cart
--------------------------------*/
$stmt = $conn->prepare("
    UPDATE vkauppa_kori_tuotteet 
    SET hinta = ? 
    WHERE kori_tuotteetid = ?
");
$stmt->bind_param("di", $updatedPrice, $cartitemid);
$stmt->execute();

$price = $updatedPrice;
$qty = (int)$item['maara'];
$cartId = $item['ostoskoriid'];

/* -------------------------------
   4. Add/remove item logic
--------------------------------*/
if ($action === 'add') {

    $stmt = $conn->prepare("SELECT varastossa FROM vkauppa_tuotteet WHERE tuoteid=?");
    $stmt->bind_param("i", $item['tuoteid']);
    $stmt->execute();
    $stock = (int)$stmt->get_result()->fetch_assoc()['varastossa'];

    if ($qty >= $stock) {
        echo json_encode([
            'success' => false,
            'message' => "Varastossa ei ole enää riittävästi tuotteita (vain $stock jäljellä).",
            'stock' => $stock
        ]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE vkauppa_kori_tuotteet SET maara = maara + 1 WHERE kori_tuotteetid=?");
    $stmt->bind_param("i", $cartitemid);
    $stmt->execute();
    $qty++;

} elseif ($action === 'remove') {

    if ($qty <= 1) {
        $stmt = $conn->prepare("DELETE FROM vkauppa_kori_tuotteet WHERE kori_tuotteetid=?");
        $stmt->bind_param("i", $cartitemid);
        $stmt->execute();
        $qty = 0;
    } else {
        $stmt = $conn->prepare("UPDATE vkauppa_kori_tuotteet SET maara = maara - 1 WHERE kori_tuotteetid=?");
        $stmt->bind_param("i", $cartitemid);
        $stmt->execute();
        $qty--;
    }
}

/* -------------------------------
   5. Update cart timestamp
--------------------------------*/
$stmt = $conn->prepare("UPDATE vkauppa_ostoskori SET viimeksi_paivitetty = NOW() WHERE ostoskoriid = ?");
$stmt->bind_param("i", $cartId);
$stmt->execute();

/* -------------------------------
   6. Return updated values
--------------------------------*/
echo json_encode([
    'success' => true,
    'price' => $price,
    'qty' => $qty
]);
exit;
