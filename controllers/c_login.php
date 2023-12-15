<?
class LoginController {
    public static function index() {
        // Code pour la page d'accueil
        Template::header_html();
        Template::include_view('login');
        Template::footer_html();
    }
}
?>