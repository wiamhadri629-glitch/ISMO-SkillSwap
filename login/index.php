<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    if (isset($login)) {
        $err = [];
        if (!isset($email) || $email == "") $err["email"] = "l'email est invalide";
        if (!isset($motdepasse) || $motdepasse == "") $err["motdepasse"] = "le mot de passe est invalide";
        if (empty($err)){
            $email = htmlspecialchars($email);
            $motdepasse = hash('sha256', $motdepasse);
            include("../dbopen/db.php");
            try {
                $reqi=$db->prepare("SELECT role FROM utilisateurs WHERE email=? AND motdepasse=?");
                $r=$reqi->execute([$email,$motdepasse]);
                $user = $reqi->fetch(PDO::FETCH_ASSOC);
                if($user==false) $err["errconnection"]="erreur de connexion";
                else {
                if ($user) {
                $role = $user['role'];
                if ($role == "admin") {
                    header("Location: ../admin/accueil/accueil.php");
                    exit;
                } elseif ($role == "formateur") {
                    header("Location: ../formateur/accueil/accueil.php");
                    exit;
                } elseif ($role == "stagiaire") {
                    header("Location: ../stagiaire/accueil/accueil.php");
                    exit;
                } else {
                    $err["errconnection"] = "Utilisateur introuvable ou login incorrect";
                }
               }
              }
            }catch (PDOException $e) {
                echo "Erreur: " . $e->getMessage();
              }
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

        <form id="loginForm" method="POST">
          <label class="field">
            <span class="label">Email</span>
            <input name="email" type="email" placeholder="votre.email@ismo.ma" >
            <?php if (isset($err["email"])) echo "<div style='color:red'>" . $err["email"] . "</div>"; ?>
          </label>

          <label class="field">
            <span class="label">Mot de passe</span>
            <input id="motdepasse" name="motdepasse" type="password" placeholder="••••••••">
            <?php if (isset($err["motdepasse"])) echo "<div style='color:red'>" . $err["motdepasse"] . "</div>"; ?>
          </label>

          <label class="checkbox">
            <input id="showPass" type="checkbox"> Afficher le mot de passe
          </label>

          <button class="btn primary" name="login" type="submit">Se connecter</button>
        </form>
        <p class="signup">Pas encore de compte ? <a href="../inscription/index.php">S'inscrire</a></p>
      </div>
    </main>
  </div>

  <script>
    document.getElementById('showPass').addEventListener('change', function(e){
      const pw = document.getElementById('motdepasse');
      pw.type = this.checked ? 'text' : 'password';
    });

    document.querySelectorAll('.demo').forEach(btn=>{
      btn.addEventListener('click', function(){
        document.getElementById('email').value = this.dataset.email;
        document.getElementById('motdepasse').value = this.dataset.pass;
      });
    });
  </script>
</body>
</html>
