<?php
session_start();
require '../db.php';

if (!isset($_SESSION['nouto_id'])) {
    header("Location: kirjautuminen.php");
    exit;
}

$pakkaajaId = $_SESSION['nouto_id'];
$order = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nouto_koodi'])) {
    $nouto_koodi = trim($_POST['nouto_koodi']);
    if ($nouto_koodi !== '') {
        $stmt = $conn->prepare("
            SELECT t.tilausid, t.tila, t.tilaus_hinta, t.tilattu_aika,
                o.asiakasid, u.etunimi, u.sukunimi,
                kt.tuoteid, kt.maara, kt.hinta AS tuote_hinta,
                p.nimi AS tuotenimi
            FROM vkauppa_nouto n
            INNER JOIN vkauppa_tilaus t ON t.tilausid = n.tilausid
            INNER JOIN vkauppa_ostoskori o ON o.ostoskoriid = t.ostoskoriid
            INNER JOIN vkauppa_kori_tuotteet kt ON kt.ostoskoriid = o.ostoskoriid
            INNER JOIN vkauppa_tuotteet p ON p.tuoteid = kt.tuoteid
            INNER JOIN vkauppa_asiakas u ON u.asiakasid = o.asiakasid
            WHERE n.nouto_koodi = ? AND t.tila != 'suoritettu'
        ");
        $stmt->bind_param("s", $nouto_koodi);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (!empty($rows)) {
            $order = [
                'tilausid' => $rows[0]['tilausid'],
                'tila' => $rows[0]['tila'],
                'tilaus_hinta' => $rows[0]['tilaus_hinta'],
                'tilattu_aika' => $rows[0]['tilattu_aika'],
                'customer_name' => $rows[0]['etunimi'].' '.$rows[0]['sukunimi'],
                'items' => []
            ];
            foreach ($rows as $row) {
                $order['items'][] = [
                    'tuotenimi' => $row['tuotenimi'],
                    'maara' => $row['maara'],
                    'hinta' => $row['tuote_hinta']
                ];
            }
        } else {
            $error = "Koodilla ei löyytynyt tilauksia.";
        }
    } else {
        $error = "Syötä noutokoodi.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_done_id'])) {
    $tilausid = intval($_POST['mark_done_id']);
    $update = $conn->prepare("UPDATE vkauppa_tilaus SET tila='suoritettu' WHERE tilausid=?");
    $update->bind_param("i", $tilausid);
    $success = $update->execute();
    echo json_encode(['success' => $success]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Noutokoodi Tarkistus</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<style>
body { display: flex; min-height: 100vh; }
.sidebar { width: 220px; background: #f8f9fa; padding: 20px; border-right: 1px solid #ddd; }
.sidebar h3 { margin-top: 0; }
.sidebar a { display: block; padding: 10px 0; color: #333; text-decoration: none; }
.sidebar a.active { font-weight: bold; color: #007bff; }
.content { flex-grow: 1; padding: 20px; }
</style>
</head>
<body>

<div class="sidebar">
    <h3>Nouto tarkistus</h3>
    <a href="#" class="nav-link active" id="tab_nouto">Noutokoodi tarkistus</a>
    <a href="kirjaudu_ulos.php"><span class="glyphicon glyphicon-log-out"></span> Kirjaudu ulos</a>
</div>

<div class="content">
    <div id="nouto_tab">
        <h2>Noutokoodi Tarkistus</h2>

        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="nouto_koodi" class="form-label">Syötä noutokoodi:</label>
                <input type="text" class="form-control" id="nouto_koodi" name="nouto_koodi" required>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Hae tilaus</button>
        </form>

        <?php if ($error): ?>
            <br>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
            <br>
            <div class="panel panel-default" id="orderCard">
                <div class="panel-heading">
                    <strong>Tilaus ID:</strong> <?= $order['tilausid'] ?> | 
                    <strong>Asiakas:</strong> <?= htmlspecialchars($order['customer_name']) ?>
                </div>
                <div class="panel-body">
                    <p><strong>Tilattu:</strong> <?= $order['tilattu_aika'] ?></p>
                    <p><strong>Hinta:</strong> €<?= number_format($order['tilaus_hinta'],2) ?></p>
                    <ul class="list-group mb-3">
                        <?php foreach ($order['items'] as $item): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <?= htmlspecialchars($item['tuotenimi']) ?> x<?= $item['maara'] ?>
                                <span>€<?= number_format($item['hinta'],2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button class="btn btn-success mark-done-btn" data-tilausid="<?= $order['tilausid'] ?>">Merkitse suoritettu</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function(){

    $(document).on('click', '.mark-done-btn', function(){
        var tilausid = $(this).data('tilausid');
        $.post('', { mark_done_id: tilausid }, function(response){
            if(response.success){
                $('#orderCard').remove();
                $('<div class="alert alert-success">Tilaus #' + tilausid + ' merkitty suoritetuksi.</div>')
                    .appendTo('.content')
                    .delay(3000).fadeOut(500, function(){ $(this).remove(); });
            } else {
                alert('Virhe tilan päivittämisessä.');
            }
        }, 'json');
    });

    setTimeout(function() { $(".alert").fadeOut(1000); }, 10000);
});
</script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
