<?php
class Router{

  //Pour rediriger et mettre les noms dans url

  const URL_TO_CONTROLLER = [
    'accueil' => 'c_home',
    'login' => 'c_login',
    'forum' => 'c_forum'
  ];

  protected static $current_url = '';
  protected static $current_controller = '';
  
  protected function __construct(){
  }

  public static function init(){
    $url = parse_url($_SERVER['REQUEST_URI']);
    $url = str_replace(BASE_URL , '' , $url['path']);
    
    if($url == ''){
      $url = 'c_home';
    }
    self::$current_url = $url;

    $controller = self::get_controller($url);

    if(!file_exists(ROOT_PATH.'/controllers/'.$controller.'.php')){
        $controller = '404';
    }

    self::$current_controller = $controller;

    require ROOT_PATH.'/controllers/'.$controller.'.php';
  }

  public static function get_current_controller(){
    return self::$current_controller;
  }

  public static function get_current_url(){
    return self::$current_url;
  }
  
  public static function get_controller(string $url){
    if(isset(self::URL_TO_CONTROLLER[$url])){
      return self::URL_TO_CONTROLLER[$url];
    }
    return $url;
  }

  public static function get_url(string $controller){
    if(in_array($controller , self::URL_TO_CONTROLLER)){
      $url = '';
      foreach (self::URL_TO_CONTROLLER as $key => $value) {
        if($value == $controller){
          $url = $key;
        }
      }
      return $url;
    }
    return $controller;
  }

  public static function is_ajax(){
    return false;
  }

  public function redirect(string $controller){
    $url = abs_url(self::get_url($controller));
    header("Location: $url");
    exit;
  }
}

?>
