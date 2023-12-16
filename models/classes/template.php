<?php
class Template{

  public static $meta_title = 'Jeux Vidéos & Débats';
  public static $body_class = '';
  public static function header_html(){
    ?>
    <!DOCTYPE html>
      <html lang="fr">
      <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo self::$meta_title ?></title>
        <script>
          const Site = {
            absUrl : function(url){
              return "<?php echo ROOT_URL.BASE_URL?>" + url;
            },
          }
        </script>
      </head>
      <body class="<?php echo self::$body_class ?>">
    <?php
  }

  public static function footer_html(){
    ?>
      </body>
    </html>
    <?php
  }

  //DIRECTORY_SEPARATOR pour adapter slash cheminement
  public static function include_view($view) {
    //echo 'Including view: ' . $view . '<br>'; //Débogage
    include(ROOT_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php');
}


  public static function send_json($json = []){
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($json);
    exit;
  }

}
?>
