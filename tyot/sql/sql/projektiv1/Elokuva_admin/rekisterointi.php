<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "elokuva";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Yhteys epäonnistui: " . $conn->connect_error);
}

    $virheet = [];
    $onnistui = false;

if (isset($_POST['rekisteroi'])) {

    $nimi = trim($_POST['nimi']);
    $osoite = trim($_POST['osoite']);
    $liittymis = $_POST['liittymispvm'];
    $syntymavuosi = $_POST['syntymavuosi'];
    $tunnus = trim($_POST['kayttajatunnus']);
    $salasana = trim($_POST['salasana']);


    if (empty($nimi) || empty($osoite) || empty($tunnus) || empty($salasana)) {
        $virheet[] = "Kaikki kentät ovat pakollisia.";
    }

    if (!preg_match("/^\d{4}$/", $syntymavuosi) || $syntymavuosi < 1900 || $syntymavuosi > date("Y")) {
        $virheet[] = "Syntymävuosi ei ole kelvollinen.";
    }

    if (!strtotime($liittymis)) {
        $virheet[] = "Liittymispäivämäärä ei ole kelvollinen.";
    }

    $stmt_check = $conn->prepare("SELECT JasenID FROM Jasen WHERE Kayttajatunnus = ?");
    $stmt_check->bind_param("s", $tunnus);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $virheet[] = "Käyttäjätunnus on jo käytössä. Valitse toinen";
    }

    if (empty($virheet)) {
        $hash = password_hash($salasana, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO Jasen (Nimi, Osoite, LiittymisPVM, Syntymavuosi, Kayttajatunnus, SalasanaHash) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $nimi, $osoite, $liittymis, $syntymavuosi, $tunnus, $hash);

        $stmt->execute();
        $onnistui = true;
    }
}
?>
<!doctype html>
<html lang="fi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rekisteröinti</title>
<link rel="stylesheet" href="jasentyyli.css">
</head>
<body>
    <div class="page-container">
    <div class="message-container">
    <?php
    if ($onnistui) {
        echo "<div class='message success'>✅ Rekisteröinti onnistui!</div>";
    }
    foreach ($virheet as $virhe) {
        echo "<div class='message error'>❌ " . htmlspecialchars($virhe) . "</div>";
    }
    ?>
    </div>

    <h2>Rekisteröidy jäseneksi</h2>
    <form method="post" class="form-container">
        <label>Nimi:</label>
        <input type="text" name="nimi" required>

        <label>Osoite:</label>
        <input type="text" name="osoite" required>

        <label>Liittymispäivämäärä:</label>
        <input type="date" name="liittymispvm" required>

        <label>Syntymävuosi:</label>
        <input type="text" name="syntymavuosi" required>

        <label>Kayttajatunnus:</label>
        <input type="text" name="kayttajatunnus" required>

        <label>Salasana:</label>
        <input type="password" name="salasana" required>

        <input type="submit" name="rekisteroi" value="Rekisteröidy">

    <p> <a href="kirjautuminen.php">Siirry kirjautumaan &raquo;</a></p>
    </form>
</div>
</body>
</html>