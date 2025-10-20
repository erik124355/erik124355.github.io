<?php
session_start();

if(!isset($_SESSION['JasenID'])) {
    header("Location: kirjautuminen.php");
    exit;
}

$host = "localhost";
$user = "root";
$password = "";
$database = "elokuva";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Yhteys epäonnistui: " . $conn->connect_error);
}

$jasenid = $_SESSION['JasenID'];

if (isset($_POST['vuokraa'])) {
    $elokuvaid = $_POST['elokuvaid'];
    $vuokrauspvm = $_POST['vuokrauspvm'];
    $palautuspvm = $_POST['palautuspvm'];

    $stmt = $conn->prepare("INSERT INTO vuokraus (JasenID, ElokuvaID, VuokrausPVM, PalautusPVM) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $jasenid, $elokuvaid, $vuokrauspvm, $palautuspvm);
    $stmt->execute();
}

if (isset($_GET['poista'])) {
    $elokuvaid =  $_GET['poista'];
    $conn->query("DELETE FROM vuokraus WHERE JasenID=$jasenid AND ElokuvaID=$elokuvaid");
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omat vokraukset</title>
    <link rel="stylesheet" href="jasentyyli.css">
</head>
<body>
<div class="page-container">
<h2>Omat vuokraukset</h2>
<a href="logout.php">↩ Kirjaudu ulos</a><br><br>

<table class="vuokraus-table">
<tr><th>Elokuva</th><th>Vuokrauspäivä</th><th>Palautuspäivä</th><th>Toiminnot</th></tr>
<?php
$result = $conn->query("SELECT V.ElokuvaID, E.Nimi, V.VuokrausPVM, V.PalautusPVM FROM vuokraus V JOIN Elokuva E ON V.ElokuvaID = E.ElokuvaID where V.JasenID = $jasenid");
if (!$result) {
    die("Query failed: " . $conn->error);
}
while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['Nimi']}</td>
            <td>{$row['VuokrausPVM']}</td>
            <td>{$row['PalautusPVM']}</td>
            <td><a href='omavuokraus.php?poista={$row['ElokuvaID']}'>Poista</a></td>
         </tr>";
}
?>
</table>
<h3>Vuokraa uusi elokuva</h3>
<form method="post" class="form-container">
    <label>Elokuva:</label>
    <div class="select-wrapper">
        <select name="elokuvaid">
            <?php
            $elokuvat = $conn->query("SELECT ElokuvaID, Nimi FROM Elokuva");
            while ($e = $elokuvat->fetch_assoc()) {
                echo "<option value='{$e['ElokuvaID']}'>{$e['Nimi']}</option>";
            }
            ?>
        </select>
    </div>
    <label>Vuokruspäivä:</label>
    <input type="date" name="vuokrauspvm" required>

    <label>Palautuspäivä:</label>
    <input type="date" name="palautuspvm" required>
    
    <input type="submit" name="vuokraa" value="vuokraa elokuva">
</form>
</div>    
</body>
</html>