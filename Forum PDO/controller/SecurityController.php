<?php

namespace Controller;

use App\Session;
use App\AbstractController;
use App\ControllerInterface;
use DateTime;
use Model\Managers\UserManager;
use Model\Managers\TopicManager;
use Model\Managers\PostManager;
use Model\Managers\CategoryManager;

// implements = oblige la classe à avoir des fonctions qui ont le meme noms que ici dans ControllerInterface 
class SecurityController extends AbstractController implements ControllerInterface
{

    public function index()
    {
        $categoryManager = new CategoryManager;
        $topicManager = new TopicManager;
        $categories = $categoryManager->findAll();
        $topics = $topicManager;
        $title = "Page d'accueil";

        return [
            "view" => VIEW_DIR . "/home.php",
            "data" => [
                "topics" => $topics,
                "categories" => $categories,
                "title" => $title
            ]
        ];
        
    }

    public function disconnect()
    {
        // Supprime l'utilisateur de la session en cours
        unset($_SESSION['user']);
    
        // Ajoute un message flash pour indiquer que la déconnexion a réussi
        Session::addFlash('success', 'Vous êtes bien déconnecté !');
    
        // Redirige l'utilisateur vers la page d'accueil
        return $this->redirectTo('security', 'index');
    }

public function signup()
{
    // Vérifie si le formulaire a été soumis (lorsqu'un utilisateur appuie sur le bouton "submit")
        if (isset($_POST['submit'])) {
            // Récupère les données du formulaire
            $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
            $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $confirmedPassword = filter_input(INPUT_POST, "confirmedPassword", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        // Crée une instance du gestionnaire d'utilisateurs
        $userManager = new UserManager;

        // Recherche un utilisateur existant avec le même nom d'utilisateur dans la base de données
        $userBdd = $userManager->findOneByUsername($username);

        // Recherche un utilisateur existant avec la même adresse e-mail dans la base de données
        $emailBdd = $userManager->findOneByEmail($email);

        // {12,}: doit comporter minimum 12 caractères
        // (?=.*?[A-Z]): doit comporter au moins une lettre majuscule
        // (?=.*?[a-z]): doit comporter au moins une lettre minuscule
        //(?=.*?[0-9]): doit comporter au moins un chiffre
        //(?=.*?[#?!@$%^&*-]): doit comporter au moins un caractère spécial
        $regex = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{12,}$/';

        if ($userBdd) {
            echo "Username already used"; // Affiche un message si le nom d'utilisateur est déjà utilisé
        } elseif ($emailBdd) {
            echo "Email already used"; // Affiche un message si l'e-mail est déjà utilisé
        } elseif (!preg_match($regex, $password)) {
            echo "Le mot de passe doit contenir minimum 12 caractères, une lettre majuscule, une lettre minuscule et un symbole";
            // Affiche un message si le mot de passe ne respecte pas les critères requis
        } elseif ($password != $confirmedPassword) {
            echo "Les mots de passe ne correspondent pas"; // Affiche un message si les mots de passe ne correspondent pas
        } elseif (!$userBdd && !$emailBdd && $password === $confirmedPassword) {

        // Si toutes les conditions précédentes sont satisfaites, l'inscription est effectuée

        // Hash le mot de passe pour le stocker de manière sécurisée dans la base de données
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

        // Ajoute l'utilisateur à la base de données avec son nom d'utilisateur, e-mail, mot de passe hashé et rôle
        $signup = $userManager->add(["email" => $email, "pseudo" => $username, "passWord" => $passwordHashed, 'role' => json_encode("ROLE_USER")]);

        // Redirige l'utilisateur vers la page d'accueil après l'inscription
        return $this->redirectTo('security', 'index');
        }
    }

    public function login()
    {

        // Vérifie si le formulaire de connexion a été soumis
        if (isset($_POST['submit'])) {
            // Récupère et nettoie les données du formulaire (nom d'utilisateur et mot de passe)
            $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        
        // Instancie le gestionnaire d'utilisateurs
        $userManager = new UserManager;
        
        // Recherche l'utilisateur dans la base de données par nom d'utilisateur
        $userBdd = $userManager->findOneByUsername($username);
        
        // Vérifie si un utilisateur avec le nom d'utilisateur saisi existe et si le mot de passe correspond
        if ($userBdd && password_verify($password, $userBdd->getPassword())) {
            // Vérifie également si l'utilisateur est banni (s'il est marqué comme banni dans la base de données)
            if ($userBdd->getIsbanned()) {
                // Si l'utilisateur est banni, affiche un message d'erreur et ne le connecte pas
                Session::addFlash('error', 'Vous êtes banni !');
            } else {
                // Si l'utilisateur n'est pas banni et que les identifiants sont corrects, connecte l'utilisateur
                Session::setUser($userBdd);
            }
        } else {
            // Si l'utilisateur n'est pas trouvé dans la base de données ou si le mot de passe est incorrect, affiche un message d'erreur
            echo "You are not registered yet. Please sign up";
        }
        
        // Redirige l'utilisateur vers la page d'accueil
        return $this->redirectTo('security', 'index');
    }

    public function ban()
    {
        // Récupère l'ID de l'utilisateur à bannir depuis la requête GET
        $userId = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);

        // Instancie le gestionnaire d'utilisateurs
        $userManager = new UserManager;

        // Met à jour l'utilisateur en définissant la propriété 'isBanned' à 1 (indiquant qu'il est banni)
        $ban = $userManager->update(["id" => $userId, "isBanned" => 1]);

        // Redirige l'utilisateur vers la page de la liste des catégories du forum
        return $this->redirectTo('security', 'index');
    }


    public function unban()
    {
        // Récupère l'ID de l'utilisateur à débannir depuis la requête GET
        $userId = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);

        // Instancie le gestionnaire d'utilisateurs
        $userManager = new UserManager;

        // Met à jour l'utilisateur en définissant la propriété 'isBanned' à 0 (indiquant qu'il n'est pas banni)
        $unban = $userManager->update(["id" => $userId, "isBanned" => 0]);

        // Redirige l'utilisateur vers la page de la liste des catégories du forum
        return $this->redirectTo('security', 'index');
    }

    public function viewProfile()
{
    $userId = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
    
    $userManager = new UserManager;
    
    $user = $userManager->findOneById($userId);
    
    return [
        "view" => VIEW_DIR . "security/viewProfile.php",
        "data" => [
            "user" => $user
        ]
    ];
}

    public function deleteProfile()
{
    $userId = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
    // $userId =$id;
    // var_dump($userId);die;

    $userManager = new UserManager;

    $user = $userManager->findOneById($userId);

    $newPassword = bin2hex(random_bytes(8));

    $deletedPasswordHashed = password_hash($newPassword, PASSWORD_DEFAULT);

    $uniqId = uniqid();
    $email = "email_supprimé_".$uniqId;
    $pseudo = "Utilisateur_supprimé_".$uniqId;
    

    $deleteProfile = $userManager->update(["id"=> $userId, "email" => $email, "pseudo" => $pseudo, "passWord" => $deletedPasswordHashed, 'isBanned'=> 1]);

    Session::setUser(null);

    return $this->redirectTo ('security', 'index');
}

    public function changePasswordForm($id)
    {
        // Crée une instance du gestionnaire de catégories.
        $userManager = new UserManager;

        // Utilise le gestionnaire de catégories pour trouver la catégorie par son ID.
        $user = $userManager->findOneById($id);
        var_dump($user);die;

        // Retourne un tableau associatif avec deux éléments :
        // 1. La vue à afficher pour le formulaire de mise à jour.
        // 2. Les données nécessaires pour remplir le formulaire, ici la catégorie à mettre à jour.
        return [
            "view" => VIEW_DIR . "security/changePasswordForm.php",
            "data" => [
                "user" => $user
            ]
        ];
    }

    public function passwordUpdate($id)
    {
        $currentPassword = filter_input(INPUT_POST, "currentPassword", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $newPassword = filter_input(INPUT_POST, "newPassword", FILTER_SANITIZE_FULL_SPECIAL_CHARS);


        // Instancie le gestionnaire d'utilisateurs
        $userManager = new UserManager;
        
        // Recherche l'utilisateur dans la base de données par nom d'utilisateur
        $userBdd = $userManager->findOneByUsername($id);
        
        // Vérifie si un utilisateur avec le nom d'utilisateur saisi existe et si le mot de passe correspond
        if ($userBdd && password_verify($currentPassword, $userBdd->getPassword())) {

            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $passwordChange = $userManager->update(["id" => $id, "passWord" => $newHashedPassword]);
        }
        return $this->redirectTo ('security', 'index');
    }
}
