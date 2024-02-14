<?php
class c_dashboardController
{
    private $twig;
    private $connexionDB;
    private $security;
    private $userSession;

    /**
     * Constructeur prenant une instance de TwigConfig en paramètre
     *
     * @param $twig Instance de TwigConfig
     * @param $connexionDB Instance de PDO pour la connexion à la base de données
     */
    public function __construct($twig, $connexionDB, $userSession)
    {
        $this->twig = $twig;
        $this->connexionDB = $connexionDB;
        $this->security = new Security($this->connexionDB);
        $this->userSession = $userSession;
    }

    /**
     * Méthode d'affichage de la page d'accueil
     *
     * @return void 
     */
    public function index(): void
    {
        $this->security->checkAutorisation();

        $affichage = new Affichage($this->connexionDB);
        // Chargement du template spécifique à la page d'accueil
        $template = $this->twig->getTwig()->load('dashboard/index.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'topTopics' => $affichage->getTopTopicsAccueil(),
            'user' =>  $this->userSession
        ]);
    }

    public function posts(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);
        $topics = $dashboard->getTopics();

        // Chargement du template spécifique à la page des sujets
        echo $this->twig->getTwig()->render('dashboard/posts.html.twig', [
            'topics' => $topics,
            'user' =>  $this->userSession
        ]);
    }


    public function posts_edit(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'],
                'nom' => $_POST['nom'],
                'auteur' => $_POST['auteur'],
            ];
            if (isset($_POST['submitUpdate'])) {

                if ($dashboard->editTopic($data)) {
                    echo 'success';
                    header("Location: posts");
                    exit;
                } else {
                    echo 'error';
                }
            } elseif (isset($_POST['submitDelete'])) {
                if ($dashboard->deleteTopic($_POST['id'])) {
                    echo 'WOUPLI';
                    header("Location: posts");
                    exit;
                } else {
                    echo 'et oe';
                }
            }
        }

        // Déplacer le rendu du template en dehors de la condition
        $template = $this->twig->getTwig()->load('dashboard/posts_edit.html.twig');
        $template->display([
            'data' =>  $data,
        ]);
    }

    public function categories(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);
        $categories = $dashboard->getCategories();

        // Chargement du template spécifique à la page des sujets
        echo $this->twig->getTwig()->render('dashboard/categories.html.twig', [
            'categories' => $categories,
            'user' =>  $this->userSession
        ]);
    }

    public function categories_edit(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'],
                'nom' => $_POST['nom'],
            ];
            if (isset($_POST['submitUpdate'])) {

                if ($dashboard->editCategories($data)) {
                    echo 'success';
                    header("Location: categories");
                    exit;
                } else {
                    echo 'error';
                }
            } elseif (isset($_POST['submitDelete'])) {
                if ($dashboard->deleteCategories($_POST['id'])) {
                    echo 'WOUPLI';
                    header("Location: categories");
                    exit;
                } else {
                    echo 'et oe';
                }
            }
        }

        // Déplacer le rendu du template en dehors de la condition
        $template = $this->twig->getTwig()->load('dashboard/categories_edit.html.twig');
        $template->display([
            'data' =>  $data,
        ]);
    }

    public function categories_add(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitAdd'])) {
            $data = [
                'nom' => $_POST['nom'],
            ];

            if ($dashboard->addCategories($data)) {
                echo 'success';
                header("Location: categories");
                exit;
            } else {
                echo 'error';
            }
        }

        // Déplacer le rendu du template en dehors de la condition
        $template = $this->twig->getTwig()->load('dashboard/categories_add.html.twig');
        $template->display([]);
    }

    public function users(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);
        $users = $dashboard->getUsers();

        // Chargement du template spécifique à la page des sujets
        echo $this->twig->getTwig()->render('dashboard/users.html.twig', [
            'users' => $users,
            'user' =>  $this->userSession
        ]);
    }

    public function users_edit(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'] ?? '',
                'pseudo' => $_POST['pseudo'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'idRole' => $_POST['idRole'] ?? '',
                'bloque' => $_POST['bloque'] ?? '',
            ];
            if (isset($_POST['submitUpdate'])) {
                $data["password"] =  password_hash($data['password'], PASSWORD_BCRYPT);
                if ($dashboard->editUsers($data)) {
                    echo 'success';
                    header("Location: users");
                    exit;
                } else {
                    echo 'error';
                }
            } elseif (isset($_POST['submitDelete'])) {
                if ($dashboard->deleteUsers($_POST['id'])) {
                    echo 'WOUPLI';
                    header("Location: users");
                    exit;
                } else {
                    echo 'et oe';
                }
            }elseif (isset($_POST['submitRestore'])) {
                if ($dashboard->restoreUsers($_POST['id'])) {
                    header("Location: users");
                    exit;
                } 
            }
        }

        // Déplacer le rendu du template en dehors de la condition
        $template = $this->twig->getTwig()->load('dashboard/users_edit.html.twig');
        $template->display([
            'data' =>  $data,
        ]);
    }

    public function users_add(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);
        $errorMessages = [];
        $roles = $dashboard->getRoles();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitAdd'])) {

            $data = [
                'pseudo' => $_POST['pseudo'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'idRole' => $_POST['idRole'],
            ];

            $errorMessages = array_merge(
                $errorMessages,
                $this->security->validatePseudo($data['pseudo']),
                $this->security->validateEmail($data['email']),
                $this->security->checkDoublonEmail($data['email'])
            );


            if (empty($errorMessages)) {
                $data["password"] =  password_hash($data['password'], PASSWORD_BCRYPT);
                if ($dashboard->addUsers($data)) {

                    header("Location: /users");
                    exit;
                } else {
                    $errorMessages[] = "Une erreur s'est produite. Veuillez réessayer.";
                }
            }
        }
        // Déplacer le rendu du template en dehors de la condition
        $template = $this->twig->getTwig()->load('dashboard/users_add.html.twig');
        $template->display([
            'error' => $errorMessages,
            'roles' => $roles
        ]);
    }

    public function contents(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);
        $contents = $dashboard->getContents();

        // Chargement du template spécifique à la page des sujets
        echo $this->twig->getTwig()->render('dashboard/contents.html.twig', [
            'contents' => $contents,
            'user' =>  $this->userSession
        ]);
    }

    public function contents_delete(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitDelete'])) {
            $userId = $_POST['id'] ?? '';

            if ($dashboard->deleteContent($userId)) {
                echo 'WOUPLI';
                header("Location: contents");
                exit;
            } else {
                echo 'et oe';
            }
        }

        // You may want to handle the case where the request method is not POST or submitDelete is not set

        // Move the template rendering outside of the condition
        $template = $this->twig->getTwig()->load('dashboard/contents.html.twig');
        $template->display();
    }
}
