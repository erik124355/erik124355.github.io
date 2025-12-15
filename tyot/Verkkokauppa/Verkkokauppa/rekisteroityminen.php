<?php
require '../db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $etunimi = trim($_POST["etunimi"]);
    $sukunimi = trim($_POST["sukunimi"]);
    $puhelinnumero = trim($_POST["puhelinnumero"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (strlen($password) < 6) {
    $message = " <div class='alert alert-danger alert-dismissible'>
                    <a href='#' class='close' data-dismiss='alert'>&times;</a>
                    ❌ Salasanan täytyy olla vähintään 6 merkkiä!
                </div>";
    } else {
        $checkStmt = $conn->prepare("SELECT asiakasid FROM vkauppa_asiakas WHERE sahkoposti = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "<div class='alert alert-danger alert-dismissible'>
                            <a href='#' class='close' data-dismiss='alert'>&times;</a>
                            ❌ Sähköposti on jo käytössä!
                        </div>";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO vkauppa_asiakas (etunimi, sukunimi, puhelinnumero, sahkoposti, salasana) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $etunimi, $sukunimi, $puhelinnumero, $email, $hash);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success alert-dismissible'>
                                <a href='#' class='close' data-dismiss='alert'>&times;</a>
                                ✅ Rekisteröityminen onnistui! Voit nyt kirjautua sisään.
                            </div>";
            } else {
                $message = "<div class='alert alert-danger alert-dismissible'>
                                <a href='#' class='close' data-dismiss='alert'>&times;</a>
                                ❌ Rekisteröityminen epäonnistui: " . $stmt->error . "
                            </div>";
            }
            $stmt->close();
        }

        $checkStmt->close();
    }

}

?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taitaja 2024 Semifinaali - Rekiströityminen</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/styles.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tauri&family=Zalando+Sans:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
</head>
    <body class="custom-body">
    <div class="glass-card-login">
        <div class="content">
            <div class="header-row-2" style="margin: 20px; padding: 20px;">
                <a href="verkkokauppa.php" class="button-back">
                    <span class="arrow">&#8592;</span> Verkkokauppa
                </a>
                <div>
                    <h1 class="tauri-regular text-color-1" style="margin-top:30px">Rekisteröityminen</h1>
                    <br>
                    <p class="zalando-sans text-color-1">Rekisteröidy, niin pääset kirjautumaan ja tilaamaan tuotteita.</p>
                    <br>
                    <div style="padding-right:15px; padding-left:15px;">
                        <?php if(!empty($message)): ?>
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <?php echo $message; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <form method="POST" action="">
                        <p class="zalando-sans text-color-1 left">Etunimi</p>
                        <input class="custom-input" type="text" name="etunimi" required>
                        <br><br>
                        <p class="zalando-sans text-color-1 left">Sukunimi</p>
                        <input class="custom-input" type="text" name="sukunimi" required>
                        <br><br>
                        <p class="zalando-sans text-color-1 left">Puhelinnumero</p>
                        <input class="custom-input" type="text" name="puhelinnumero" required>
                        <br><br>
                        <p class="zalando-sans text-color-1 left">Sähköposti</p>
                        <input class="custom-input" type="email" name="email" required>
                        <br><br>
                        <p class="zalando-sans text-color-1 left">Salasana</p>
                        <input class="custom-input" type="password" name="password" required>
                        <br><br>
                        <button type="submit" class="button-2">Rekisteröidy</button>
                        <br><br>
                        <div class="divider">TAI</div>
                        <br>
                        <p class="zalando-sans text-color-1">Kirjaudu sisään <a class="link" href="kirjautuminen.php">täältä.</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="myCarousel" class="carousel slide bg-carousel" data-ride="carousel" data-interval="5000" data-pause="false">
        <div class="carousel-inner">
            <div class="item active"><img src="../images/bg-1.jpeg" alt="liikkuva kuva 1"></div>
            <div class="item"><img src="../images/bg-2.jpeg" alt="liikkuva kuva 2"></div>
            <div class="item"><img src="../images/bg-3.jpeg" alt="liikkuva kuva 3"></div>
            <div class="item"><img src="../images/bg-4.jpeg" alt="liikkuva kuva 4"></div>
            <div class="item"><img src="../images/bg-5.jpeg" alt="liikkuva kuva 5"></div>
            <div class="item"><img src="../images/bg-6.jpeg" alt="liikkuva kuva 6"></div>
            <div class="item"><img src="../images/bg-7.jpeg" alt="liikkuva kuva 7"></div>
            <div class="item"><img src="../images/bg-8.jpeg" alt="liikkuva kuva 8"></div>
            <div class="item"><img src="../images/bg-9.jpeg" alt="liikkuva kuva 9"></div>
            <div class="item"><img src="../images/bg-10.jpeg" alt="liikkuva kuva 10"></div>
            <div class="item"><img src="../images/bg-11.jpeg" alt="liikkuva kuva 11"></div>
            <div class="item"><img src="../images/bg-12.jpeg" alt="liikkuva kuva 12"></div>
            <div class="item"><img src="../images/bg-13.jpeg" alt="liikkuva kuva 13"></div>
            <div class="item"><img src="../images/bg-14.jpeg" alt="liikkuva kuva 14"></div>
            <div class="item"><img src="../images/bg-15.jpeg" alt="liikkuva kuva 15"></div>
            <div class="item"><img src="../images/bg-16.jpeg" alt="liikkuva kuva 16"></div>
            <div class="item"><img src="../images/bg-17.jpeg" alt="liikkuva kuva 17"></div>
            <div class="item"><img src="../images/bg-18.jpeg" alt="liikkuva kuva 18"></div>
            <div class="item"><img src="../images/bg-19.jpeg" alt="liikkuva kuva 19"></div>
            <div class="item"><img src="../images/bg-20.jpeg" alt="liikkuva kuva 20"></div>
            <div class="item"><img src="../images/bg-21.jpeg" alt="liikkuva kuva 21"></div>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var $carousel = $('#myCarousel');
    var total = $('.carousel-inner .item').length;
    var lastIndex = 0;

    function showRandom() {
        var nextIndex;
        do {
            nextIndex = Math.floor(Math.random() * total);
        } while (nextIndex === lastIndex);

        lastIndex = nextIndex;
        $carousel.carousel(nextIndex);
    }
    setInterval(showRandom, 5000);
    showRandom();
});
</script>
</body>

</html>