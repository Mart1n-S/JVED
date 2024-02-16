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

        // $affichage = new Affichage($this->connexionDB);
        // Chargement du template spécifique à la page d'accueil
        $template = $this->twig->getTwig()->load('dashboard/index.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
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

    /**
     * Affiche la page des catégories.
     * Vérifie d'abord les autorisations de l'utilisateur et redirige si nécessaire.
     * Récupère les catégories depuis le tableau de bord.
     * Charge ensuite le template spécifique à la page des catégories avec les données récupérées.
     */
    public function categories(): void
    {

        $this->security->checkAutorisation();
        $this->security->checkAutorisationSuperAdmin();

        $dashboard = new Dashboard($this->connexionDB);
        $categories = $dashboard->getCategories();

        // Chargement du template spécifique à la page des sujets
        echo $this->twig->getTwig()->render('dashboard/categories.html.twig', [
            'categories' => $categories,
            'user' =>  $this->userSession
        ]);
    }

    /**
     * Affiche et gère les actions sur la page de modification des catégories.
     * Vérifie d'abord les autorisations de l'utilisateur et redirige si nécessaire.
     * Traite les données du formulaire en cas de soumission POST.
     * Effectue les opérations de mise à jour, suppression ou restauration des catégories en fonction des actions de l'utilisateur.
     * Charge le template spécifique à la page de modification des catégories avec les données nécessaires.
     */
    public function categories_edit(): void
    {
        $this->security->checkAutorisation();
        $this->security->checkAutorisationSuperAdmin();

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

    /**
     * Affiche et gère les actions sur la page d'ajout de catégories.
     * Vérifie d'abord les autorisations de l'utilisateur et redirige si nécessaire.
     * Traite les données du formulaire en cas de soumission POST.
     * Ajoute une nouvelle catégorie si les données sont valides, sinon affiche un message d'erreur.
     */
    public function categories_add(): void
    {
        $this->security->checkAutorisation();
        $this->security->checkAutorisationSuperAdmin();

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

    /**
     * Affiche la liste des utilisateurs dans le tableau de bord.
     * 
     * Cette méthode vérifie d'abord les autorisations de l'utilisateur.
     * Ensuite, elle récupère la liste des utilisateurs depuis la base de données à l'aide du tableau de bord.
     * Enfin, elle charge le template spécifique à la page des utilisateurs et affiche les utilisateurs.
     */
    public function users(): void
    {
        $this->security->checkAutorisation();
        $this->security->checkAutorisationSuperAdmin();

        $dashboard = new Dashboard($this->connexionDB);
        $users = $dashboard->getUsers();

        // Chargement du template spécifique à la page des sujets
        echo $this->twig->getTwig()->render('dashboard/users.html.twig', [
            'users' => $users,
            'user' =>  $this->userSession
        ]);
    }

    /**
     * Affiche la page d'édition des utilisateurs du tableau de bord.
     * 
     * Cette méthode vérifie les autorisations de l'utilisateur connecté et vérifie qu'il s'agit d'un super administrateur.
     * Elle récupère les données nécessaires depuis la base de données pour afficher la page d'édition des utilisateurs.
     * Elle traite les actions de modification, de suppression, de restauration, de blocage et de déblocage des utilisateurs en fonction des soumissions de formulaire.
     * Elle affiche les erreurs éventuelles et les données des utilisateurs à éditer dans le template HTML.
     */
    public function users_edit(): void
    {
        $this->security->checkAutorisation();
        $this->security->checkAutorisationSuperAdmin();
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


    /**
     * Met à jour un élément via le tableau de données, le tableau de bord spécifié, la variable et la redirection.
     *
     * @param array $data Les données à utiliser pour la mise à jour.
     * @param Dashboard $dashboard Le tableau de bord à utiliser pour la mise à jour.
     * @param string $var La table pour la mise à jour.
     * @param string $redirection La redirection après la mise à jour.
     * @return bool Le résultat de la mise à jour (true si réussie, sinon false).
     */
    private function updateItem(array $data, Dashboard $dashboard, string $var, string $redirection): bool
    {

        $resultat = $dashboard->editItem($data, $var) ? true : false;
        if ($resultat) {
            header("Location: $redirection");
            exit;
        }

        return $resultat;
    }

    /**
     * Supprime un élément via son identifiant, le tableau de bord spécifié, la variable et la redirection.
     *
     * @param int $id L'identifiant de l'élément à supprimer.
     * @param Dashboard $dashboard Le tableau de bord à utiliser pour la suppression.
     * @param string $var La table  à utiliser pour la suppression.
     * @param string $redirection La redirection après la suppression.
     * @return bool Le résultat de la suppression (true si réussie, sinon false).
     */
    private function deleteItem(int $id, Dashboard $dashboard, string $var, string $redirection): bool
    {
        $resultat = $dashboard->deleteItem($id, $var) ? true : false;
        if ($resultat) {
            header("Location: $redirection");
            exit;
        }

        return $resultat;
    }

    /**
     * Restaure un élément via son identifiant, le tableau de bord spécifié, la variable et la redirection.
     *
     * @param int $id L'identifiant de l'élément à restaurer.
     * @param Dashboard $dashboard Le tableau de bord à utiliser pour la restauration.
     * @param string $var La table  à utiliser pour la restauration.
     * @param string $redirection La redirection après la restauration.
     * @return bool Le résultat de la restauration (true si réussie, sinon false).
     */
    private function restoreItem(int $id, Dashboard $dashboard, string $var, string $redirection): bool
    {
        if ($dashboard->restoreItem($id, $var)) {
            header("Location: $redirection");
            exit;
        }
        return false;
    }

    /**
     * Bloque un élément via son identifiant, le tableau de bord spécifié, la variable et la redirection.
     *
     * @param int $id L'identifiant de l'élément à bloquer.
     * @param Dashboard $dashboard Le tableau de bord à utiliser pour le blocage.
     * @param string $var la table à utiliser pour le blocage.
     * @param string $redirection La redirection après le blocage.
     * @return bool Le résultat du blocage (true si réussi, sinon false).
     */
    private function blockItem(int $id, Dashboard $dashboard, string $var, string $redirection): bool
    {
        if ($dashboard->blockItem($id, $var)) {
            header("Location: $redirection");
            exit;
        }
        return false;
    }

    /**
     * Débloque un élément via son identifiant, le tableau de bord spécifié, la variable et la redirection.
     *
     * @param int $id L'identifiant de l'élément à débloquer.
     * @param Dashboard $dashboard Le tableau de bord à utiliser pour le déblocage.
     * @param string $var La table à utiliser pour le déblocage.
     * @param string $redirection La redirection après le déblocage.
     * @return bool Le résultat du déblocage (true si réussi, sinon false).
     */
    private function unblockItem(int $id, Dashboard $dashboard, string $var, string $redirection): bool
    {
        if ($dashboard->unblockItem($id, $var)) {
            header("Location: $redirection");
            exit;
        }

        return false;
    }

    /**
     * Accepte un élément via son identifiant, le tableau de bord spécifié et la redirection.
     *
     * @param int $id L'identifiant de l'élément à accepter.
     * @param Dashboard $dashboard Le tableau de bord à utiliser pour l'acceptation.
     * @param string $redirection La redirection après l'acceptation.
     * @return bool Le résultat de l'acceptation (true si réussie, sinon false).
     */
    private function acceptItem(int $id, Dashboard $dashboard, string $redirection): bool
    {
        if ($dashboard->acceptItem($id)) {
            header("Location: $redirection");
            exit;
        }

        return false;
    }

    /**
     * Affiche le formulaire d'ajout d'utilisateur et traite sa soumission.
     *
     * Cette méthode vérifie d'abord les autorisations de l'utilisateur, récupère les rôles disponibles,
     * puis gère la soumission du formulaire d'ajout d'utilisateur. Si le formulaire est soumis avec succès,
     * l'utilisateur est ajouté à la base de données et redirigé vers la liste des utilisateurs.
     * En cas d'erreur, un message d'erreur est affiché.
     */
    public function users_add(): void
    {
        $this->security->checkAutorisation();
        $this->security->checkAutorisationSuperAdmin();

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

    /**
     * Affiche les contenus associés à un sujet spécifique.
     *
     * Cette méthode vérifie d'abord les autorisations de l'utilisateur, puis récupère les contenus
     * associés à un sujet spécifique à partir des paramètres d'URL. Ensuite, elle charge le template
     * pour afficher les contenus.
     *
     * @param array $match Les paramètres d'URL associés à la route.
     */
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

    /**
     * Gère les actions de modification des contenus.
     *
     * Cette méthode vérifie d'abord les autorisations de l'utilisateur. Si la requête est de type POST,
     * elle traite les actions de suppression ou de restauration de contenu. Sinon, elle redirige vers le tableau de bord.
     */
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
            } elseif (isset($_POST['submitShow'])) {
                // Déplacer le rendu du template en dehors de la condition
                $template = $this->twig->getTwig()->load('dashboard/contents_show.html.twig');
                $template->display([
                    'data' =>  $data,
                    'user' =>  $this->userSession
                ]);
            };
        } else {
            header('Location: dashboard');
            exit;
        }
    }

    /**
     * Affiche les articles en attente de validation.
     *
     * Cette méthode vérifie d'abord les autorisations de l'utilisateur, puis récupère les articles
     * en attente de validation à partir du tableau de bord. Ensuite, elle charge le template pour
     * afficher ces articles.
     */
    public function validationPosts(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);
        // Chargement du template spécifique à la page d'accueil
        $template = $this->twig->getTwig()->load('dashboard/validationPosts.html.twig');

        // Affichage du template avec les données nécessaires
        $template->display([
            'postsWaitingValidation' => $dashboard->getTopicsWaitingValidation(),
            'user' =>  $this->userSession
        ]);
    }

    /**
     * Gère les actions d'édition des articles en attente de validation.
     *
     * Cette méthode vérifie d'abord les autorisations de l'utilisateur. Si la requête est de type POST,
     * elle traite les actions de suppression ou d'acceptation d'article en attente de validation. Ensuite,
     * elle redirige vers le tableau de bord.
     */
    public function editValidationPosts(): void
    {
        $this->security->checkAutorisation();

        $dashboard = new Dashboard($this->connexionDB);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'] ?? ''
            ];

            if (isset($_POST['submitDelete'])) {
                $this->deleteItem($data['id'], $dashboard, 'topic', "validation-posts");
            } elseif (isset($_POST['submitAccept'])) {
                $this->acceptItem($data['id'], $dashboard, "validation-posts");
            };
        }
        header('Location: dashboard');
        exit;
    }
}
