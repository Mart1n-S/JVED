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

        // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //     $data = [
        //         'id' => $_POST['id'],
        //         'nom' => $_POST['nom'],
        //         'auteur' => $_POST['auteur'],
        //     ];
        //     if (isset($_POST['submitUpdate'])) {

        //         if ($dashboard->editTopic($data)) {
        //             echo 'success';
        //             header("Location: posts");
        //             exit;
        //         } else {
        //             echo 'error';
        //         }
        //     } elseif (isset($_POST['submitDelete'])) {
        //         if ($dashboard->deleteTopic($_POST['id'])) {
        //             echo 'WOUPLI';
        //             header("Location: posts");
        //             exit;
        //         } else {
        //             echo 'et oe';
        //         }
        //     }
        // }

        $errorMessages = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'],
                'nom' => $_POST['nom'],
                'nomSujet' => $_POST['nomSujet'],
                'auteur' => $_POST['auteur'],
            ];

            if (isset($_POST['submitUpdate'])) {
                if (empty($data['nom']) && empty($_POST['nomSujet'])) {
                    $errorMessages = ['Merci de remplire les champs.'];
                }

                if (empty($errorMessages)) {

                    $this->updateItem($data, $dashboard, 'categorie', 'categories');
                }
            } elseif (isset($_POST['submitDelete'])) {
                $this->deleteItem($data['id'], $dashboard, 'topic', 'posts');
            } elseif (isset($_POST['submitRestore'])) {
                $this->restoreItem($data['id'], $dashboard, 'topic', 'posts');
            }
        }

        // Déplacer le rendu du template en dehors de la condition
        $template = $this->twig->getTwig()->load('dashboard/posts_edit.html.twig');
        $template->display([
            'data' =>  $data,
            'error' =>  $errorMessages,
            'user' =>  $this->userSession
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
        $errorMessages = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'],
                'nom' => $_POST['nom'],
            ];

            if (isset($_POST['submitUpdate'])) {
                if (empty($data['nom'])) {
                    $errorMessages = ['Merci de remplire les champs.'];
                }

                if (empty($errorMessages)) {

                    $this->updateItem($data, $dashboard, 'categorie', 'categories');
                }
            } elseif (isset($_POST['submitDelete'])) {
                $this->deleteItem($data['id'], $dashboard, 'categorie', 'categories');
            } elseif (isset($_POST['submitRestore'])) {
                $this->restoreItem($data['id'], $dashboard, 'categorie', 'categories');
            }
        }

        // Déplacer le rendu du template en dehors de la condition
        $template = $this->twig->getTwig()->load('dashboard/categories_edit.html.twig');
        $template->display([
            'data' =>  $data,
            'error' =>  $errorMessages,
            'user' =>  $this->userSession
        ]);
    }

    public function categories_add(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);
        $categories = $dashboard->getCategories();

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
        $template->display([
            'categories' =>  $categories,
            'user' =>  $this->userSession
        ]);
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
        $errorMessages = [];
        $dashboard = new Dashboard($this->connexionDB);
        $roles = $dashboard->getRoles();
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
                $errorMessages = array_merge(
                    $errorMessages,
                    $this->security->validatePseudo($data['pseudo'])
                );

                if (!empty($data['email'])) {
                    $errorMessages = array_merge(
                        $errorMessages,
                        $this->security->validateEmail($data['email']),
                        $this->security->checkDoublonEmail($data['email'])
                    );
                }


                if (empty($errorMessages)) {
                    if (!empty($data['password'])) {
                        $data["password"] =  password_hash($data['password'], PASSWORD_BCRYPT);
                    }
                    $this->updateItem($data, $dashboard, 'user', 'users');
                }
            } elseif (isset($_POST['submitDelete'])) {
                $this->deleteItem($_POST['id'], $dashboard, 'user', 'users');
            } elseif (isset($_POST['submitRestore'])) {
                $this->restoreItem($_POST['id'], $dashboard, 'user', 'users');
            } elseif (isset($_POST['submitBloque'])) {
                $this->blockItem($_POST['id'], $dashboard, 'user', 'users');
            } elseif (isset($_POST['submitDebloque'])) {
                $this->unblockItem($_POST['id'], $dashboard, 'user', 'users');
            }
        }

        // Déplacer le rendu du template en dehors de la condition
        $template = $this->twig->getTwig()->load('dashboard/users_edit.html.twig');
        $template->display([
            'data' =>  $data,
            'roles' => $roles,
            'error' =>  $errorMessages,
            'user' =>  $this->userSession
        ]);
    }


    // Fonctions d'action
    private function updateItem($data, $dashboard, $var, $redirection)
    {

        $resultat = $dashboard->editItem($data, $var) ? true : false;
        if ($resultat) {
            header("Location: $redirection");
            exit;
        }

        return $resultat;
    }

    private function deleteItem($id, $dashboard, $var, $redirection)
    {
        $resultat = $dashboard->deleteItem($id, $var) ? true : false;
        if ($resultat) {
            header("Location: $redirection");
            exit;
        }

        return $resultat;
    }

    private function restoreItem($id, $dashboard, $var, $redirection)
    {
        if ($dashboard->restoreItem($id, $var)) {
            header("Location: $redirection");
            exit;
        }
        return false;
    }

    private function blockItem($id, $dashboard, $var, $redirection)
    {
        if ($dashboard->blockItem($id, $var)) {
            header("Location: $redirection");
            exit;
        }
        return false;
    }

    private function unblockItem($id, $dashboard, $var, $redirection)
    {
        if ($dashboard->unblockItem($id, $var)) {
            header("Location: $redirection");
            exit;
        }

        return false;
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
            'roles' => $roles,
            'user' =>  $this->userSession
        ]);
    }

    public function contents($match): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);
        $contents = $dashboard->getContents($match['params']['id']);

        // Chargement du template spécifique à la page des sujets
        echo $this->twig->getTwig()->render('dashboard/contents.html.twig', [
            'contents' => $contents,
            'user' =>  $this->userSession,
            'idTopic' => $match['params']['id']
        ]);
    }

    public function contents_edit(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'] ?? '',
                'idTopic' => $_POST['idTopic'] ?? '',
                'commentaire' => $_POST['commentaire'] ?? '',
                'deletedAt' => $_POST['deletedAt'] ?? ''
            ];

            if (isset($_POST['submitDelete'])) {
                $this->deleteItem($data['id'], $dashboard, 'content', "contents/{$data['idTopic']}");
            } elseif (isset($_POST['submitRestore'])) {
                $this->restoreItem($data['id'], $dashboard, 'content', "contents/{$data['idTopic']}");
            }elseif (isset($_POST['submitShow'])) {
                // Déplacer le rendu du template en dehors de la condition
            $template = $this->twig->getTwig()->load('dashboard/contents_show.html.twig');
            $template->display([
                'data' =>  $data,
                'user' =>  $this->userSession
            ]);
            };
        }
            header('Location: dashboard');
            exit;
       
    }
}
