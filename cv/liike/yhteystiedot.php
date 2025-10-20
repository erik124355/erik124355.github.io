<html>
    <meta charset="utf-8">
    <head>
        <style>
            body{
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                width: 100vh;
                margin: 0;
                font-family: Arial, sans-serif;
                flex-direction: column;
            }

            h1 {
                font-size: 36px;
                margin: 0;
                padding-bottom: 10px
            }

            .container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 20px;
            }

            .teksti {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            input {
                font-size: 20px;
                width: 200px;
                height: 40px;
            }

        </style>
    </head>
    <body>
        Nimi: <?php echo $_POST["nimi"]; ?><br>
        Sähköposti: <?php echo $_POST["sposti"]; ?> <br>
        Viest: <?php echo $_POST["viesti"]; ?> 
        <a href="yhteystiedot.html">Takaisin</a>
    </body>
</html>