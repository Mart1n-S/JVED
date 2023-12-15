<?php
require('config.php');

//require ROOT_PATH.'/models/classes/database.php';
require ROOT_PATH.'/models/classes/router.php';
require ROOT_PATH.'/models/classes/template.php';

define('IS_SHOWCASE' , in_array( BASE_URL , array('/') ) );

//Database::init(); quand on fera la classe database
Router::init();