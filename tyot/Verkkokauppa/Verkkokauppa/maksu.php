<?php
session_start();
require "../db.php";

$asiakasid = $_SESSION['user_id'] ?? null;

if (!$asiakasid) {
    header("Location: kirjautuminen.php");
    exit;
}

function geocodeAddress($address) {
    $apiKey = "AIzaSyAL9EOTkgnrWOjotOyfVNATGUI7xouhpW4";
    $address = urlencode($address);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if($data['status'] === 'OK') {
        return $data['results'][0]['geometry']['location'];
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch cart
    $cart = $conn->prepare("SELECT ostoskoriid FROM vkauppa_ostoskori WHERE asiakasid = ? AND tila = 'kaytossa' LIMIT 1");
    $cart->bind_param("i", $asiakasid);
    $cart->execute();
    $result = $cart->get_result();
    if ($result->num_rows === 0) {
        header("Location: ostoskori.php?empty=1");
        exit;
    }
    $ostoskoriid = $result->fetch_assoc()['ostoskoriid'];

    // Calculate total
    $items = $conn->prepare("SELECT maara, hinta FROM vkauppa_kori_tuotteet WHERE ostoskoriid = ?");
    $items->bind_param("i", $ostoskoriid);
    $items->execute();
    $resultItems = $items->get_result();

    $total_price = 0;
    while ($row = $resultItems->fetch_assoc()) {
        $total_price += $row['maara'] * $row['hinta'];
    }

    if ($total_price <= 0) die("Virhe: Kokonaishintaa ei vastaanotettu.");

    $delivery_method = $_POST['toimitustapa'] ?? "nouto";

    $insert = $conn->prepare("INSERT INTO vkauppa_tilaus (ostoskoriid, tilaus_hinta, tila, tilaus_tyyli) VALUES (?, ?, 'tilattu', ?)");
    $insert->bind_param("ids", $ostoskoriid, $total_price, $delivery_method);
    $insert->execute();
    $tilausid = $insert->insert_id;

    // Update stock
    $items = $conn->prepare("SELECT tuoteid, maara FROM vkauppa_kori_tuotteet WHERE ostoskoriid = ?");
    $items->bind_param("i", $ostoskoriid);
    $items->execute();
    $resultItems = $items->get_result();
    while ($row = $resultItems->fetch_assoc()) {
        $updateStock = $conn->prepare("UPDATE vkauppa_tuotteet SET varastossa = varastossa - ? WHERE tuoteid = ?");
        $updateStock->bind_param("ii", $row['maara'], $row['tuoteid']);
        $updateStock->execute();
    }

    if ($delivery_method === "nouto") {
        function generateUniquePickupCode($conn, $length = 6) {
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            while (true) {
                $code = substr(str_shuffle($chars), 0, $length);
                $check = $conn->prepare("SELECT noutoid FROM vkauppa_nouto WHERE nouto_koodi = ?");
                $check->bind_param("s", $code);
                $check->execute();
                $exists = $check->get_result();
                if ($exists->num_rows === 0) return $code;
            }
        }
        $pickupCode = generateUniquePickupCode($conn);
        $stmt = $conn->prepare("INSERT INTO vkauppa_nouto (tilausid, nouto_koodi) VALUES (?, ?)");
        $stmt->bind_param("is", $tilausid, $pickupCode);
        $stmt->execute();
    }

    if ($delivery_method === "kuljetus") {
        $full_address = $_POST['osoite'] . ', ' . $_POST['kaupunki'] . ', ' . $_POST['maa'];
        $coords = geocodeAddress($full_address);
        $lat = $coords['lat'] ?? null;
        $lng = $coords['lng'] ?? null;

        $stmt = $conn->prepare("INSERT INTO vkauppa_kuljetus (tilausid, katuosoite, tarkennus, lat, lng) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issdd", $tilausid, $full_address, $_POST['tarkennus'], $lat, $lng);
        $stmt->execute();
    }

    $closeCart = $conn->prepare("UPDATE vkauppa_ostoskori SET tila = 'maksettu' WHERE ostoskoriid = ?");
    $closeCart->bind_param("i", $ostoskoriid);
    $closeCart->execute();

    header("Location: kiitos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Taitaja 2024 Semifinaali - Maksu</title>

<link rel="stylesheet" href="../css/styles.css">

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; }
    .shape { position: absolute; border-radius: 50%; opacity: 0.08; pointer-events: none; }
    .shape-1 { width: 300px; height: 300px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); top: -100px; right: -100px; }
    .shape-2 { width: 200px; height: 200px; background: linear-gradient(135deg, #34d399 0%, #10b981 100%); bottom: 100px; left: -50px; }
    .shape-3 { width: 150px; height: 150px; background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%); top: 50%; right: 10%; }

    .card { position: relative; background: rgba(255, 255, 255, 0.8); border-radius: 2rem; backdrop-filter: blur(10px); padding: 2rem; max-width: 400px; margin: 5rem auto; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .card input, .card select, .card button { width: 100%; margin-bottom: 1rem; padding: 0.5rem 1rem; border-radius: 0.75rem; border: 1px solid #ccc; font-size: 1rem; }
    .card button { background-color: #10b981; color: white; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .card button:hover { background-color: #059669; }
    .custom-select-wrapper {
    width: 100%;
}

.custom-select {
    width: 100%;
    margin-bottom: 1rem; /* spacing between fields */
    position: relative;
    cursor: pointer;
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
}

.custom-select .dropdown-arrow {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%) rotate(45deg);
    width: 8px;
    height: 8px;
    border: solid #555;
    border-width: 0 2px 2px 0;
    transition: transform 0.2s ease;
    pointer-events: none;
}

.custom-select.active .dropdown-arrow {
    transform: translateY(-30%) rotate(225deg);
}

.custom-select.active .custom-options {
    display: block;
}
</style>
</head>
<body class="bg-gray-50 min-h-screen relative overflow-x-hidden">

    <header class="bg-white border-b border-gray-100 relative z-10">
        <div class="mx-auto px-6 py-4 flex items-center justify-between">
            <a href="verkkokauppa.php" class="flex items-center space-x-3 text-gray-700 hover:text-emerald-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-medium">Takaisin</span>
            </a>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-semibold text-gray-800">Ostoskori</h1>
            </div>
        </div>
    </header>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="card">
        <form method="POST" action="">
            <h1 class="text-2xl font-semibold mb-6 text-gray-800 flex items-center gap-2">
                <img src="../images/logo.png" class="w-10 h-10"> Maksutiedot
            </h1>

            <input type="text" name="cardholder" placeholder="Kortinhaltijan nimi" required>
            <input type="text" name="card" placeholder="0000-0000-0000-0000" maxlength="19" inputmode="numeric" required>
            <div class="flex gap-4">
                <input type="text" name="exp" placeholder="KK/VV" maxlength="5" required>
                <input type="text" name="cvc" placeholder="CVC" maxlength="3" inputmode="numeric" required>
            </div>

            <div class="custom-select-wrapper mb-4">
                <div class="custom-select" id="toimitustapa-select">
                    <span class="selected">Valitse tilaus tyyli</span>
                    <div class="dropdown-arrow"></div>
                    <ul class="custom-options">
                        <li data-value="nouto">Nouto</li>
                        <li data-value="kuljetus">Kuljetus</li>
                    </ul>
                </div>
                <input type="hidden" name="toimitustapa" value="" required>
            </div>

            <div id="delivery-fields" style="display:none;" class="space-y-2">
                <input type="text" name="osoite" id="osoite" placeholder="Katu ja numero">
                <input type="text" style="margin-top: 0px; margin-bottom: 1rem;" name="tarkennus" id="tarkennus" placeholder="Asunto / Lisätieto (valinnainen)">

                <div class="custom-select-wrapper" style="margin-top:0px;">
                    <div class="custom-select" id="kaupunki-select">
                        <span class="selected">Valitse kaupunki</span>
                        <div class="dropdown-arrow"></div>
                        <ul class="custom-options">
                            <li data-value="Kuopio">Kuopio</li>
                        </ul>
                    </div>
                    <input type="hidden" name="kaupunki" value="" required>
                </div>

                <div class="custom-select-wrapper">
                    <div class="custom-select" id="maa-select">
                        <span class="selected">Valitse maa</span>
                        <div class="dropdown-arrow"></div>
                        <ul class="custom-options">
                            <li data-value="FI">Suomi</li>
                        </ul>
                    </div>
                    <input type="hidden" name="maa" value="" required>
                </div>
            </div>

            <button type="submit">Vahvista maksu</button>
        </form>
    </div>

<script>
document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
    const selectBox = wrapper.querySelector('.custom-select');
    const selected = selectBox.querySelector('.selected');
    const hiddenInput = wrapper.querySelector('input[type="hidden"]');
    const optionList = selectBox.querySelector('.custom-options');
    const options = optionList.querySelectorAll('li');

    selectBox.addEventListener('click', e => {
        e.stopPropagation();
        closeAllDropdowns();
        selectBox.classList.toggle('active');
    });

    options.forEach(option => {
        option.addEventListener('click', e => {
            e.stopPropagation();

            selected.textContent = option.textContent;
            hiddenInput.value = option.dataset.value;

            selectBox.classList.remove('active');

            if (hiddenInput.name === "toimitustapa") {
                const isDelivery = hiddenInput.value === "kuljetus";
                const deliveryFields = document.getElementById("delivery-fields");

                deliveryFields.style.display = isDelivery ? "block" : "none";

                document.getElementById("osoite").required = isDelivery;

                const cityHidden = document.querySelector('input[name="kaupunki"]');
                const countryHidden = document.querySelector('input[name="maa"]');

                cityHidden.required = isDelivery;
                countryHidden.required = isDelivery;

                if (isDelivery) {
                    cityHidden.required = true;
                    countryHidden.required = true;
                } else {
                    cityHidden.value = "";
                    countryHidden.value = "";

                    document.querySelector('#kaupunki-select .selected').textContent = "Valitse kaupunki";
                    document.querySelector('#maa-select .selected').textContent = "Valitse maa";
                }
            }
        });
    });
});


document.addEventListener('click', closeAllDropdowns);

function closeAllDropdowns() {
    document.querySelectorAll('.custom-select').forEach(select => {
        select.classList.remove('active');
    });
}

const form = document.querySelector('form');

form.addEventListener('submit', function(e) {
    const deliveryMethodInput = document.querySelector('input[name="toimitustapa"]');
    const deliveryMethod = deliveryMethodInput.value;

    if (!deliveryMethod) {
        e.preventDefault();
        alert("Valitse tilauksen tyyli ennen maksun vahvistamista.");
        return;
    }

    if (deliveryMethod === 'kuljetus') {
        const city = document.querySelector('input[name="kaupunki"]').value;
        const country = document.querySelector('input[name="maa"]').value;
        const address = document.querySelector('#osoite').value;

        if (!address || !city || !country) {
            e.preventDefault();
            alert("Täytä kaikki toimitustiedot ennen maksun vahvistamista.");
        }
    }
});
</script>

</body>

</html>
