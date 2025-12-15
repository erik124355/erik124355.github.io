<?php
session_start();
require '../db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $kayttajanimi = trim($_POST["kayttajanimi"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT adminid, kayttajanimi, salasana FROM vkauppa_admin WHERE kayttajanimi = ?");
    $stmt->bind_param("s", $kayttajanimi);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin) {
        $message = "❌ Väärä käyttäjänimi tai salasana.";
    } elseif (!password_verify($password, $admin["salasana"])) {
        $message = "❌ Väärä käyttäjänimi tai salasana.";
    } else {
        $_SESSION["admin_id"] = $admin["adminid"];
        session_regenerate_id(true);
        header("Location: paneeli.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kirjautuminen</title>

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
                <div>
                    <h1 class="tauri-regular text-color-1">Admin</h1>
                    <br>
                    <div style="padding-right:15px; padding-left:15px;">
                        <?php if(!empty($message)): ?>
                            <div class="alert alert-danger alert-dismissible" style="position: relative;">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <form method="POST" action="">
                        <p class="zalando-sans text-color-1 left">Käyttäjänimi</p>
                        <input class="custom-input" type="text" name="kayttajanimi" required>
                        <br><br>
                        <p class="zalando-sans text-color-1 left">Salasana</p>
                        <input class="custom-input" type="password" name="password" required>
                        <br><br>
                        <button type="submit" class="zalando-sans button-1">Kirjaudu sisään</button>
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
