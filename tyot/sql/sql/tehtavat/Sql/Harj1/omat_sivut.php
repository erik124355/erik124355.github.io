<?php
if(isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <title>Omat sivut</title>
</head>
<body>
    <div class="container" style="max-width: 600px;">
        <h1>Kirjautuminen onnistui</h1>
    </div>
</body>
</html>