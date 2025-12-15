<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

// Check if logged in
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Kirjaudu sisään ensin.']);
    exit;
}

$action = $_POST['action'] ?? '';
$productId = (int)($_POST['productId'] ?? 0);

if ($productId <= 0 || !in_array($action, ['add', 'remove'])) {
    echo json_encode(['success' => false, 'message' => 'Virheellinen toiminto.']);
    exit;
}

// ------------------------------------------------------------------
// 1. FIND OR CREATE ACTIVE CART
// ------------------------------------------------------------------
$stmt = $conn->prepare("SELECT ostoskoriid FROM vkauppa_ostoskori WHERE asiakasid = ? AND tila = 'kaytossa'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

if (!$cart) {
    $stmt = $conn->prepare("INSERT INTO vkauppa_ostoskori (asiakasid, tila, viimeksi_paivitetty) VALUES (?, 'kaytossa', NOW())");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cartId = $conn->insert_id;
} else {
    $cartId = $cart['ostoskoriid'];
}

// ------------------------------------------------------------------
// 2. GET PRODUCT INFO (stock + price)
// ------------------------------------------------------------------
$stmt = $conn->prepare("SELECT varastossa, hinta, alennus FROM vkauppa_tuotteet WHERE tuoteid = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Tuotetta ei löydy.']);
    exit;
}

$stock = (int)$product['varastossa'];
$price = $product['hinta'];
if ($product['alennus'] > 0) {
    $price = ceil($price * (1 - $product['alennus'] / 100) * 100) / 100;
}

// ------------------------------------------------------------------
// 3. CHECK IF PRODUCT ALREADY IN CART
// ------------------------------------------------------------------
$stmt = $conn->prepare("SELECT kori_tuotteetid, maara FROM vkauppa_kori_tuotteet WHERE ostoskoriid = ? AND tuoteid = ?");
$stmt->bind_param("ii", $cartId, $productId);
$stmt->execute();
$cartItem = $stmt->get_result()->fetch_assoc();
$currentQty = $cartItem['maara'] ?? 0;

// ------------------------------------------------------------------
// 4. HANDLE ACTION
// ------------------------------------------------------------------
if ($action === 'add') {
    if ($currentQty >= $stock) {
        echo json_encode(['success' => false, 'message' => 'Varastoraja saavutettu!', 'qty' => $currentQty]);
        exit;
    }

    if ($cartItem) {
        $stmt = $conn->prepare("UPDATE vkauppa_kori_tuotteet SET maara = maara + 1 WHERE kori_tuotteetid = ?");
        $stmt->bind_param("i", $cartItem['kori_tuotteetid']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO vkauppa_kori_tuotteet (ostoskoriid, tuoteid, maara, hinta) VALUES (?, ?, 1, ?)");
        $stmt->bind_param("iid", $cartId, $productId, $price);
        $stmt->execute();
    }
    $currentQty++;

} elseif ($action === 'remove') {
    if ($cartItem) {
        if ($currentQty <= 1) {
            $stmt = $conn->prepare("DELETE FROM vkauppa_kori_tuotteet WHERE kori_tuotteetid = ?");
            $stmt->bind_param("i", $cartItem['kori_tuotteetid']);
            $stmt->execute();
            $currentQty = 0;
        } else {
            $stmt = $conn->prepare("UPDATE vkauppa_kori_tuotteet SET maara = maara - 1 WHERE kori_tuotteetid = ?");
            $stmt->bind_param("i", $cartItem['kori_tuotteetid']);
            $stmt->execute();
            $currentQty--;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ei tuotteita poistettavaksi.', 'qty' => 0]);
        exit;
    }
}

// ------------------------------------------------------------------
// 5. UPDATE CART TIMESTAMP
// ------------------------------------------------------------------
$stmt = $conn->prepare("UPDATE vkauppa_ostoskori SET viimeksi_paivitetty = NOW() WHERE ostoskoriid = ?");
$stmt->bind_param("i", $cartId);
$stmt->execute();

// ------------------------------------------------------------------
// 6. RETURN UPDATED QUANTITY
// ------------------------------------------------------------------
echo json_encode([
    'success' => true,
    'qty' => (int)$currentQty
]);
exit;
