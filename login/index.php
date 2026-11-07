<?php
session_start();
if(isset($_SESSION['id_user'])){
    $p = ['stagiaire'=>'/phpprojet/stagiaire/accueil/accueil.php','mentor'=>'/phpprojet/mentor/accueil/accueil.php','formateur'=>'/phpprojet/formateur/accueil/accueil.php','admin'=>'/phpprojet/admin/accueil/accueil.php'];
    header('Location: '.$p[$_SESSION['role']]);
    exit;
}
include("../dbopen/db.php");
$err = [];
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])){
    extract($_POST);
    $email = trim(isset($email) ? $email : '');
    $motdepasse = isset($motdepasse) ? $motdepasse : '';
    if(empty($email)) $err['email'] = "L'email est obligatoire";
    if(empty($motdepasse)) $err['motdepasse'] = "Le mot de passe est obligatoire";
    if(empty($err)){
        $req = $db->prepare("SELECT * FROM utilisateurs WHERE email=?");
        $req->execute([$email]);
        $user = $req->fetch();
        if($user && password_verify($motdepasse, $user['motdepasse'])){
            if(!$user['valide']){
                $err['auth'] = "Compte en attente de validation par un administrateur.";
            }else{
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['filiere'] = $user['filiere'] ?? '';
                $_SESSION['photo'] = $user['photo'] ?? '';
                $_SESSION['date_inscription'] = $user['date_inscription'] ?? date('Y-m-d');
                $_SESSION['score'] = $user['score'] ?? 0;
                $p = ['stagiaire'=>'/phpprojet/stagiaire/accueil/accueil.php','mentor'=>'/phpprojet/mentor/accueil/accueil.php','formateur'=>'/phpprojet/formateur/accueil/accueil.php','admin'=>'/phpprojet/admin/accueil/accueil.php'];
                header('Location: '.$p[$user['role']]);
                exit;
            }
        }else{
            $err['auth'] = "Email ou mot de passe incorrect";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ISMO-SkillSwap - Connexion</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <aside class="left">
      <a class="back" href="#">← Retour à l'accueil</a>
      <div class="brand">
        <div class="logo">🏆</div>
        <div class="brand-text">
          <h1>ISMO-SkillSwap</h1>
          <h2>Échange tes compétences</h2>
          <p>Plateforme d'entraide pour les stagiaires de l'ISMO Tétouan</p>
        </div>
      </div>
      <ul class="features">
        <li>
          <div class="ico">👥</div>
          <div>
            <strong>Apprentissage collaboratif</strong>
            <div class="muted">Aide tes camarades et gagne des points</div>
          </div>
        </li>
        <li>
          <div class="ico">🎓</div>
          <div>
            <strong>Validation de compétences</strong>
            <div class="muted">Fais valider tes skills par les formateurs</div>
          </div>
        </li>
        <li>
          <div class="ico">🛡️</div>
          <div>
            <strong>Passeport digital</strong>
            <div class="muted">Télécharge tes compétences validées en PDF</div>
          </div>
        </li>
      </ul>
    </aside>
    <main class="right">
      <div class="card">
        <h3>Bienvenue</h3>
        <p class="sub">Connecte-toi pour continuer</p>
        <form method="POST">
          <label class="field">
            <span class="label">Email</span>
            <input name="email" type="email" placeholder="votre.email@ismo.ma" value="<?= htmlspecialchars(isset($email) ? $email : '') ?>">
            <?php if(isset($err['email'])): ?><div class="field-error"><?= $err['email'] ?></div><?php endif; ?>
          </label>
          <label class="field">
            <span class="label">Mot de passe</span>
            <input name="motdepasse" type="password" placeholder="••••••••">
            <?php if(isset($err['motdepasse'])): ?><div class="field-error"><?= $err['motdepasse'] ?></div><?php endif; ?>
          </label>
          <label class="checkbox">
            <input type="checkbox" onclick="var p=document.querySelector('[name=motdepasse]'); p.type=this.checked?'text':'password';"> Afficher le mot de passe
          </label>
          <?php if(isset($err['auth'])): ?><div class="field-error" style="margin-bottom:12px"><?= $err['auth'] ?></div><?php endif; ?>
          <button class="btn primary" name="login" type="submit">Se connecter</button>
        </form>
        <p class="signup">Pas encore de compte ? <a href="../inscription/index.php">S'inscrire</a></p>
      </div>
    </main>
  </div>
</body>
</html>