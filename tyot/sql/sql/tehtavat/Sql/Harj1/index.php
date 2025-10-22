<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Vastaanotto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="p-4">
        <div class="container" style="max-width: 600px;">
            <h1>Ilmoittautumislomake</h1>
            <p>Täytäthän tietosi alla olevaan lomakkeesseen</p>

            <form method="post" action=" db-default.php">
                <div class="mb-3">
                    <label for="etunimi" class="form-label">Etunimi</label>
                    <input type="text" name="etunimi" id="etunimi" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="sukunimi" class="form-label">Sukunimi</label>
                    <input type="text" name="sukunimi" id="sukunimi" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="sahkoposti" class="form-label">Sähköposti</label>
                    <input type="sahkoposti" name="sahkoposti" id="sahkoposti" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="salasana" class="form-label">Salasana</label>
                    <input type="password" name="salasana" id="salasana" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Ilmoittaudu</button>
            </form>
        </div>
    </div>
</body>
</html>