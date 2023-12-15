<?php
  //Url de votre serveur
  define('ROOT_URL' , 'http://jved');

  //Dossier dans le lequel est situé le site, si le site est à la racine laisez un '/'
  define('BASE_URL' , '/');

  //Le nom de votre base de données
  //define('DB_NAME' , 'jved');

  //Le nom d'utilisateur pour se connecter à la base de données
  //define('DB_USER' , 'root');

  //Le mot de passe pour se connecter à la base de données
  //define('DB_PASSWORD' , '');

  //Nom du serveur ( localhost en général)
  //define('DB_SERVER' , 'localhost');

  //Activer le mode debug ou non
  define('DEBUG' , true);

  //Ne pas éditer
  define('ROOT_PATH' , __DIR__);
  define('ABS_URL' , ROOT_URL.BASE_URL);

  if(DEBUG){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
  }
?>
