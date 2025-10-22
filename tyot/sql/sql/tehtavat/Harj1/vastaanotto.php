<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Vastaanotto</title>
    <link rel="stylesheet" href="lomaketyyöi.css">
</head>
<body>
    <div class="kontti">
        <h1>Viestin on vastaanotettu</h1>

        <?php
        if( $_SERVER["REQUEST_METHOD"]=="POST") {
            $nimi = htmlspecialchars($_POST["nimi"]);
            $email = htmlspecialchars($_POST["email"]);
            $viesti = htmlspecialchars($_POST["viesti"]);

            echo "<p><strong>Nimi:</strong> $nimi</p>";
            echo "<p><strong>Sähköposti:</strong> $email</p>";
            echo "<p><strong>Viesti:</strong><br>" . $viesti. "</p>";
        }
        else{
            echo "<p>Lomaketta ei ole lähetetty oikein</p>";
        }
        ?>
    </div>
</body>
</html>