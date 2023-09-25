<?php
$user = $result['data']['user'];
?>

<!-- Bouton pour changer le mot de passe -->
<a href="./view/security/changePasswordForm.php">
  <button>Changer le mot de passe</button>
</a>

<!-- Bouton pour supprimer le profil utilisateur -->
<a href="index.php?ctrl=security&action=deleteProfile&id=<?= $user->getId() ?>">
  <button>Supprimer le profil utilisateur</button>
</a>

<!-- Formulaire pour changer l'image du profil -->
<form action="index.php?ctrl=forum&action=uploadImage&id=<?= $user->getId() ?>" method="POST" enctype="multipart/form-data">
  <label for="img">Changer l'image du profil:</label>
  <input type="file" name="file">
  <button type="submit" name="submit" value="Valider">Valider</button>
</form>
 

