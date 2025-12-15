<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])){
    header("Location: kirjautuminen.php");
    exit;
}

$userid = $_SESSION['user_id'];
$message = "";

$nimi_stmt = $conn->prepare("SELECT etunimi FROM vkauppa_asiakas WHERE asiakasid=?");
$nimi_stmt->bind_param("i", $userid);
$nimi_stmt->execute();

$nimi_result = $nimi_stmt->get_result();
$row = $nimi_result->fetch_assoc();

$nimi = $row['etunimi'] ?? "Käyttäjä";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_password') {
        $newpass = $_POST['new_password'];

        if (strlen($newpass) < 6) {
            $message = "<div class='text-red-600 font-medium'>❌ Salasanan täytyy olla vähintään 6 merkkiä!</div>";
        } else {
            $hash = password_hash($newpass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE vkauppa_asiakas SET salasana=? WHERE asiakasid=?");
            $stmt->bind_param("si", $hash, $userid);
            $stmt->execute();

            $message = "<div class='text-green-600 font-medium'>✅ Salasana vaihdettu onnistuneesti!</div>";
        }
    }

    if ($action === "delete_user") {
        $confirmpass = $_POST['confirm_password'];

        $stmt = $conn->prepare("SELECT salasana FROM vkauppa_asiakas WHERE asiakasid=?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $hash = $stmt->get_result()->fetch_assoc()['salasana'];

        if (!password_verify($confirmpass, $hash)) {
            $message = "<div class='text-red-600 font-medium'>❌ Väärä salasana. Käyttäjää ei poistettu!</div>";
        } else {
            $stmt = $conn->prepare("DELETE FROM vkauppa_asiakas WHERE asiakasid=?");
            $stmt->bind_param("i", $userid);
            $stmt->execute();

            session_destroy();
            header("Location: verkkokauppa.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asetukset</title>
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

        .glass-card {
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2rem;
            padding: 2rem;
            max-width: 600px;
            margin: 4rem auto;
            position: relative;
            z-index: 10;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        input[type=password] {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            border: 1px solid #d1d5db;
            outline: none;
            margin-bottom: 1rem;
        }

        button {
            transition: all 0.2s ease;
        }

        button:hover {
            transform: scale(1.05);
        }

        button:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body class="bg-gray-50 relative overflow-x-hidden">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="glass-card">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900 flex items-center gap-2">
                Asetukset
            </h1>
            <a href="verkkokauppa.php" class="text-emerald-600 hover:text-emerald-700 font-medium">Takaisin</a>
        </div>

        <h2 class="text-lg font-medium text-gray-800 mb-4">Hei <?= htmlspecialchars($nimi) ?></h2>

        <?php if (!empty($message)): ?>
            <div class="mb-4"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" class="mb-6">
            <p class="text-gray-700 mb-2">Syötä uusi salasana</p>
            <input type="password" name="new_password" required>
            <input type="hidden" name="action" value="update_password">
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-3 rounded-xl">Vaihda</button>
        </form>

        <form method="POST">
            <p class="text-gray-700 mb-2">Poista käyttäjä</p>
            <input type="password" name="confirm_password" required placeholder="Vahvista salasanalla">
            <input type="hidden" name="action" value="delete_user">
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded-xl">Poista</button>
        </form>
    </div>
</body>
</html>
