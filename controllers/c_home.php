<?
class HomeController {
    public static function index() {
        // Code pour la page d'accueil
        Template::header_html();
        Template::include_view('home');
        Template::footer_html();
    }
}
?>