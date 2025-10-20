<html data-bs-theme="dark">
<head>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <link rel="stylesheet" href="cv.css">
    </head>
    <body>
        <div id="header">Työstä kuva</div>
        <nav class="navbar navbar-expand-sm bg-secondary justify-content-center">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="csharp.html">Takaisin</a>
            </li>
          </ul>
        </nav>
        <div class="parallax"></div>  
        <div style="font-size:36px; position: absolute; top: 450px; width: 100%;">
          <div class="container">
            <div class="row">
              <div class="col-lg-12">
              <?php
                if (isset($_GET['clicked']) && $_GET['clicked'] == 'true' && isset($_GET['box'])) {
                    $boxName = $_GET['box'];

                    if ($boxName == 'box1')
                    echo '<img src="pitsakuva.png" alt="pitsa ohjelman kuva" style="width: 100%;">';
                }
            ?>
            </div>
          </div>
        </div>
        <div class="parallax"><img src="koodi.jpg" alt="koodi kuva" style="width: 100%; position: absolute; top: 680px; right: 28%; z-index: -1;"></div>
        <div style="font-size:36px; position: absolute; top: 1300px; width: 100%;">
          
        </div>
        </div>
        <div class="parallax"></div>
        <script>
            window.onscroll = function() {scrollFunction()};
            
            function scrollFunction() {
              if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
                document.getElementById("header").style.fontSize = "30px";
              } else {
                document.getElementById("header").style.fontSize = "90px";
              }
            }
        </script>
    </body>
</html>