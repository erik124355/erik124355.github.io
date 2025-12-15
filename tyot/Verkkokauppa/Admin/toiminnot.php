<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_id'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---------------------- ADD ----------------------
    if (isset($_POST['add_type'])) {
        $type = $_POST['add_type'];

        // Add worker
        if ($type === 'worker') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role = trim($_POST['role'] ?? '');

            if ($username !== '' && $password !== '' && $role !== '') {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO vkauppa_tyontekija (kayttajanimi, salasana, rooli) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashedPassword, $role);
                $stmt->execute();
            }

            $redirect_tab = 'workers';
        }

        // Add product
        elseif ($type === 'product') {
            $name = trim($_POST['nimi'] ?? '');
            $price = floatval($_POST['hinta'] ?? 0);
            $description = trim($_POST['kuvaus'] ?? '');
            $image = trim($_POST['kuva'] ?? '');
            $stock = intval($_POST['varastossa'] ?? 0);
            $category = trim($_POST['luokka'] ?? '');
            $active = intval($_POST['aktiivinen'] ?? 1);
            $discount = floatval($_POST['alennus'] ?? 0);
            $popular = intval($_POST['suosittu'] ?? 0);

            if ($name !== '') { // Only require name
                $stmt = $conn->prepare("
                    INSERT INTO vkauppa_tuotteet 
                        (nimi, hinta, kuvaus, kuva, varastossa, luokka, aktiivinen, alennus, suosittu) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param(
                    "sdssisidi", 
                    $name, $price, $description, $image, $stock, $category, $active, $discount, $popular
                );
                $stmt->execute();
            }

            $redirect_tab = 'products';
        }
    }

    // ---------------------- EDIT ----------------------
    elseif (isset($_POST['edit_type'], $_POST['edit_id'])) {
        $type = $_POST['edit_type'];
        $id = intval($_POST['edit_id']);

        // Edit worker
        if ($type === 'worker') {
            $username = trim($_POST['username'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($password !== '') {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE vkauppa_tyontekija SET kayttajanimi=?, salasana=?, rooli=? WHERE tyontekijaid=?");
                $stmt->bind_param("sssi", $username, $hashedPassword, $role, $id);
            } else {
                $stmt = $conn->prepare("UPDATE vkauppa_tyontekija SET kayttajanimi=?, rooli=? WHERE tyontekijaid=?");
                $stmt->bind_param("ssi", $username, $role, $id);
            }
            $stmt->execute();
        }

        // Edit product
        elseif ($type === 'product') {
            $name = trim($_POST['name'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $image = trim($_POST['image'] ?? '');
            $stock = intval($_POST['stock'] ?? 0);
            $category = trim($_POST['category'] ?? '');
            $active = intval($_POST['active'] ?? 1);
            $discount = floatval($_POST['discount'] ?? 0);
            $popular = intval($_POST['popular'] ?? 0);

            if ($name !== '') { // Only require name
                $stmt = $conn->prepare("
                    UPDATE vkauppa_tuotteet SET 
                        nimi=?, hinta=?, kuvaus=?, kuva=?, varastossa=?, luokka=?, aktiivinen=?, alennus=?, suosittu=? 
                    WHERE tuoteid=?
                ");
                $stmt->bind_param(
                    "sdssisidii", 
                    $name, $price, $description, $image, $stock, $category, $active, $discount, $popular, $id
                );
                $stmt->execute();
            }
        }

        $redirect_tab = $_POST['redirect_tab'] ?? 'users';
    }

    header("Location: paneeli.php?tab=" . urlencode($redirect_tab));
    exit;
}
?>
