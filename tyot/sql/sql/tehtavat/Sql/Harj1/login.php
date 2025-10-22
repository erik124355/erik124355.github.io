<?php
session_start();
require_once 'db-default.php';

$viesti = '';

if (isset($_SESSION['user_id'])) {
    header('Location: omat_sivut.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("SELECT id, sahkoposti, salasana_db FROM kayttajat WHERE sahkoposti = :sahkoposti");
        
        $stmt->execute([':sahkoposti' => $_POST['sahkoposti']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && $_POST["salasana"] === $user['salasana_db']) {
            $_SESSION['user_id']=$user['id'];
            $_SESSION['username'] = $user['sahkoposti'];
            header("Location: omat_sivut.php");
            exit();
        } else {
            $viesti = "väärä kayttäjätunnus tai salasana";
        }
    } catch (PDOException $e) {
        $viesti = "Tietokantavirhe... " . $e;
    }
}
?>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Vastaanotto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="p-4">
        <div class="container" style="max-width: 600px;">
            <h1>Kirjaudu sisään</h1>
            <p>Täytäthän tietosi alla olevaan lomakkeesseen</p>

            <form method="post">
                <div class="mb-3">
                    <label for="sahkoposti" class="form-label">Sähköposti</label>
                    <input type="email" name="sahkoposti" id="sahkoposti" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="salasana" class="form-label">Salasana</label>
                    <input type="password" name="salasana" id="salasana" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Kirjaudu sisään</button>
            </form>
        </div>
    </div>
</body>
</html>