<?php
session_start();
if(isset($_SESSION['id_user'])){
    $p = ['stagiaire'=>'/phpprojet/stagiaire/accueil/accueil.php','mentor'=>'/phpprojet/mentor/accueil/accueil.php','formateur'=>'/phpprojet/formateur/accueil/accueil.php','admin'=>'/phpprojet/admin/accueil/accueil.php'];
    header('Location: '.$p[$_SESSION['role']]);
    exit;
}
include("../dbopen/db.php");
$success = false;
$err = [];
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['inscription'])){
    extract($_POST);
    $nom = trim(isset($nom) ? $nom : '');
    $prenom = trim(isset($prenom) ? $prenom : '');
    $email = trim(isset($email) ? $email : '');
    $motdepasse = isset($motdepasse) ? $motdepasse : '';
    $confirmmotdepasse = isset($confirmmotdepasse) ? $confirmmotdepasse : '';
    $role = isset($role) ? $role : '';
    $filiere = trim(isset($filiere) ? $filiere : '');
    if(empty($nom)) $err['nom'] = "Le nom est obligatoire";
    if(empty($prenom)) $err['prenom'] = "Le prénom est obligatoire";
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $err['email'] = "L'email est invalide";
    if(empty($motdepasse)) $err['motdepasse'] = "Le mot de passe est obligatoire";
    elseif(strlen($motdepasse) < 6 ) $err['motdepasse'] = "Minimum 6 caractères";
    if(empty($confirmmotdepasse)) $err['confirmmotdepasse'] = "La confirmation est obligatoire";
    elseif($motdepasse !== $confirmmotdepasse) $err['confirmmotdepasse'] = "Les mots de passe ne sont pas identiques";
    if(empty($role)) $err['role'] = "Le rôle est obligatoire";
    if($role === 'stagiaire' && empty($filiere)) $err['filiere'] = "La filière est obligatoire";
    if(empty($err)){
        $hash = password_hash($motdepasse, PASSWORD_DEFAULT);
        try {
            $valide = ($role === 'admin') ? 1 : 0;
            $req = $db->prepare("INSERT INTO utilisateurs(nom,prenom,email,motdepasse,role,filiere,valide) VALUES(?,?,?,?,?,?,?)");
            $req->execute([$nom, $prenom, $email, $hash, $role, $filiere, $valide]);
            $success = true;
        } catch (PDOException $e) {
            if($e->getCode() == 23000) $err['email'] = "Cet email est déjà utilisé";
            else $err['general'] = "Erreur lors de l'inscription";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ISMO-SkillSwap - Inscription</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
<aside class="left">
  <a class="back" href="../login/index.php">← Retour à la connexion</a>
  <div class="brand">
    <div class="logo">🏆</div>
    <div class="brand-text">
      <h1>ISMO-SkillSwap</h1>
      <h2>Échange tes compétences</h2>
      <p>Plateforme d'entraide ISMO Tétouan</p>
    </div>
  </div>
  <ul class="features">
    <li>👥 Apprentissage collaboratif</li>
    <li>🎓 Validation de compétences</li>
    <li>🛡️ Passeport digital</li>
  </ul>
</aside>
<main class="right">
<div class="card">
<?php if($success): ?>
<h3>Inscription réussie ! 🎉</h3>
<p style="margin:16px 0">Votre compte a été créé avec succès. Un administrateur doit valider votre compte avant de pouvoir vous connecter.</p>
<a href="../login/index.php" class="btn primary" style="display:inline-block;padding:12px 24px;text-decoration:none">Se connecter</a>
<?php else: ?>
<h3>Créer un compte</h3>
<form method="POST">
<?php if(isset($err['general'])): ?><div class="field-error"><?= $err['general'] ?></div><?php endif; ?>
<label class="field">
<span>Nom</span>
<input name="nom" type="text" value="<?= htmlspecialchars(isset($nom) ? $nom : '') ?>">
<?php if(isset($err['nom'])): ?><div class="field-error"><?= $err['nom'] ?></div><?php endif; ?>
</label>
<label class="field">
<span>Prénom</span>
<input name="prenom" type="text" value="<?= htmlspecialchars(isset($prenom) ? $prenom : '') ?>">
<?php if(isset($err['prenom'])): ?><div class="field-error"><?= $err['prenom'] ?></div><?php endif; ?>
</label>
<label class="field">
<span>Email</span>
<input name="email" type="email" value="<?= htmlspecialchars(isset($email) ? $email : '') ?>">
<?php if(isset($err['email'])): ?><div class="field-error"><?= $err['email'] ?></div><?php endif; ?>
</label>
<label class="field">
<span>Mot de passe</span>
<input name="motdepasse" type="password">
<?php if(isset($err['motdepasse'])): ?><div class="field-error"><?= $err['motdepasse'] ?></div><?php endif; ?>
</label>
<label class="field">
<span>Confirmer</span>
<input name="confirmmotdepasse" type="password">
<?php if(isset($err['confirmmotdepasse'])): ?><div class="field-error"><?= $err['confirmmotdepasse'] ?></div><?php endif; ?>
</label>
<label class="field">
<span>Rôle</span>
<select name="role" id="role">
  <option value="">Choisir un rôle</option>
  <option value="stagiaire" <?= (isset($role) ? $role : '') === 'stagiaire' ? 'selected' : '' ?>>Stagiaire</option>
  <option value="mentor" <?= (isset($role) ? $role : '') === 'mentor' ? 'selected' : '' ?>>Mentor</option>
  <option value="formateur" <?= (isset($role) ? $role : '') === 'formateur' ? 'selected' : '' ?>>Formateur</option>
  <option value="admin" <?= (isset($role) ? $role : '') === 'admin' ? 'selected' : '' ?>>Admin</option>
</select>
<?php if(isset($err['role'])): ?><div class="field-error"><?= $err['role'] ?></div><?php endif; ?>
</label>
<div id="filiereDiv" style="display:<?= (isset($role) ? $role : '') === 'stagiaire' ? 'block' : 'none' ?>;">
<label class="field">
<span>Filière</span>
<select name="filiere">
  <option value="">Choisir filière</option>
  <option value="Dev" <?= (isset($filiere) ? $filiere : '') === 'Dev' ? 'selected' : '' ?>>Développement Digital</option>
  <option value="AI" <?= (isset($filiere) ? $filiere : '') === 'AI' ? 'selected' : '' ?>>Intelligence Artificielle</option>
  <option value="Infographie" <?= (isset($filiere) ? $filiere : '') === 'Infographie' ? 'selected' : '' ?>>Infographie</option>
  <option value="Réseaux" <?= (isset($filiere) ? $filiere : '') === 'Réseaux' ? 'selected' : '' ?>>Réseaux</option>
  <option value="Infrastructure" <?= (isset($filiere) ? $filiere : '') === 'Infrastructure' ? 'selected' : '' ?>>Infrastructure</option>
</select>
<?php if(isset($err['filiere'])): ?><div class="field-error"><?= $err['filiere'] ?></div><?php endif; ?>
</label>
</div>
<button class="btn primary" name="inscription" type="submit">S'inscrire</button>
</form>
<p class="login">Déjà un compte ? <a href="../login/index.php">Se connecter</a></p>
<?php endif; ?>
</div>
</main>
</div>
<script>
document.getElementById('role')?.addEventListener('change', function(){
    document.getElementById('filiereDiv').style.display = this.value === 'stagiaire' ? 'block' : 'none';
});
</script>
</body>
</html>