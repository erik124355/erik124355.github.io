<!-- footer.php -->
<footer class="custom-footer pt-5 pb-5 text-center">
    <div class="container">

        <div class="row gy-4 justify-content-center">

            <!-- Meistä -->
            <div class="col-12 col-md-6 col-lg-4">
                <h5 class="fs-4">Meistä</h5>
                <p class="fs-5">
                    Olemme moderni verkkokauppa, joka tarjoaa laadukkaita tuotteita ja nopean toimituksen.
                </p>
            </div>

            <!-- Yhteystiedot -->
            <div class="col-12 col-md-6 col-lg-4">
                <h5 class="fs-4">Yhteystiedot</h5>
                <p class="fs-5 mb-1"><i class="bi bi-geo-alt-fill"></i> Esimerkkikatu 123, Kaupunki</p>
                <p class="fs-5 mb-1"><i class="bi bi-envelope-fill"></i> tuki@example.com</p>
                <p class="fs-5"><i class="bi bi-telephone-fill"></i> +358 123 4567</p>
                <div class="mt-2 fs-5">
                    <a href="#" class="social-icon me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon me-3"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="social-icon me-3"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

        </div>

        <hr class="border-dark mt-4">

        <div class="text-center fs-6 mt-3">
            &copy; <?= date('Y') ?> Makupolku. Kaikki oikeudet pidätetään.
        </div>

    </div>
</footer>

<!-- Styles -->
<style>
.custom-footer {
    font-size: 0.9rem;
}

.custom-footer h5 {
    font-size: 1.2rem;
}

.custom-footer p,
.custom-footer .social-icon,
.custom-footer .fs-6 {
    font-size: 0.95rem !important;
}

.custom-footer {
    position: relative;
    background-color: #dbeedc;
    color: #064635;
    box-shadow: inset 0 8px 15px rgba(0, 100, 50, 0.15);
    overflow: hidden;
}


.custom-footer::before,
.custom-footer::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    opacity: 0.2;
}

.custom-footer::before {
    width: 150px;
    height: 150px;
    background: linear-gradient(45deg, #2e7d32, #388e3c);
    top: -50px;
    left: -50px;
}

.custom-footer::after {
    width: 200px;
    height: 200px;
    background: linear-gradient(45deg, #2e7d32, #388e3c);
    bottom: -100px;
    right: -100px;
}

.social-icon {
    color: #064635;
    transition: transform 0.2s;
}

.social-icon:hover {
    transform: scale(1.2);
}
</style>

<!-- Bootstrap CSS & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">