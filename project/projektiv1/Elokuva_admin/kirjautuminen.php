<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "elokuva";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Yhteys epäonnistui: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tunnus = $_POST['kayttajatunnus'];
    $salasana = $_POST['salasana'];

    $stmt = $conn->prepare("SELECT JasenID, SalasanaHash, Rooli FROM Jasen WHERE Kayttajatunnus = ?");
    $stmt->bind_param("s", $tunnus);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($jasenID, $hash, $rooli);
        $stmt->fetch();

        if(password_verify($salasana, $hash)) {
            $_SESSION['JasenID'] = $jasenID;
            $_SESSION['Rooli'] = $rooli;
            
            if($rooli === 'admin') {
                header("Location: AdminLisaaJasen.php");
            } else {
                header("Location: omavuokraus.php");
            }
            exit;
        } else {
            $error = "❌ Väärä käyttäjätunnus tai salasana.";
        }
    } else {
        $error = "❌ Väärä käyttäjätunnus tai salasana.";
    }
    $stmt->close();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirjautuminen</title>
    <link rel="stylesheet" href="jasentyyli.css">
</head>
<body>
<div class="page-container">
    <h2>Kirjaudu sisään</h2>
    <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" class="form-container">
            <label>Käyttäjätunnus:</label>
            <input type="text" name="kayttajatunnus" required>

            <label>Salasana:</label>
            <input type="password" name="salasana" required>

            <input type="submit" value="Kirjaudu">

            <p> <a href="rekisterointi.php">&laquo; Tee uusi käyttäjä </a></p>
    </form>
</div>
</body>
</html>