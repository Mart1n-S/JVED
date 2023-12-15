<?
class ForumController {
    public static function index() {
        Template::header_html();
        Template::include_view('forum');
        Template::footer_html();
    }
}
?>