<?php
header('HTTP/1.0 404 Not Found');
if(Router::is_ajax()){

}
else{
  Template::$meta_title = 'ERROR-404 | JV&D';
  Template::header_html();
  Template::include_view('message-404');
  Template::footer_html();
}
?>
