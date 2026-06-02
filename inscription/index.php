<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    if (isset($inscription)) {
        $err = [];
        if (!isset($fullname) || $fullname == "") $err["fullname"] = "le nom est obligatoire";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err["email"] = "l'email est invalide";
        if (!isset($password) || $password == "") $err["password"] = "le mot de passe est obligatoire";
        if (!isset($confirmPassword) || $confirmPassword == "") $err["confirmPassword"] = "la confirmation est obligatoire";
        if ($password != $confirmPassword)$err["confirmPassword"] = "les mots de passe ne correspondent pas";
        if (empty($err)) {
            $fullname = htmlspecialchars($fullname);
            $email = htmlspecialchars($email);
            $password = htmlspecialchars($password);
            include("../dbopen/db.php");
            try {
                $reqi = $db->prepare("INSERT INTO Personne(nom,email,motdepasse)VALUES(?,?,?)
                ");
                $r = $reqi->execute([$fullname,$email,$password]);
                if ($r == false) $err["errconnection"] = "erreur de connexion";
                else
                    echo "<div style='color:green'>inscription réussie</div>";
            } catch(PDOException $e) {
                echo "Erreur : " . $e->getMessage();
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
  <title>ISMO-SkillSwap - Inscription</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <aside class="left">
      <a class="back" href="../login/index.html">← Retour à la connexion</a>

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
        <h3>Créer un compte</h3>
        <p class="sub">Rejoins la plateforme d'apprentissage</p>

        <form id="inscriptionForm">
          <label class="field">
            <span class="label">Nom complet</span>
            <?php if (isset($err["fullname"])) echo "<div style='color:red'>" . $err["fullname"] . "</div>"; ?>
            <input name="fullname" type="text" placeholder="Ton nom complet" required>
          </label>

          <label class="field">
            <span class="label">Email</span>
            <?php if (isset($err["email"])) echo "<div style='color:red'>" . $err["email"] . "</div>"; ?>
            <input name="email" type="email" placeholder="votre.email@ismo.ma" required>
          </label>

          <label class="field">
            <span class="label">Mot de passe</span>
            <?php if (isset($err["password"])) echo "<div style='color:red'>" . $err["password"] . "</div>"; ?>
            <input name="password" type="password" placeholder="••••••••" required>
          </label>

          <label class="field">
            <span class="label">Confirmer le mot de passe</span>
            <?php if (isset($err["confirmPassword"])) echo "<div style='color:red'>" . $err["confirmPassword"] . "</div>"; ?>
            <input name="confirmPassword" type="password" placeholder="••••••••" required>
          </label>

          <label class="checkbox">
            <input id="agreeTerms" type="checkbox" required> J'accepte les conditions d'utilisation
          </label>

          <button class="btn primary" name="inscription" type="submit">S'inscrire</button>

          <p class="quick">Connexion rapide (Démo)</p>
          <div class="demo-grid">
            <button class="btn demo" data-role="stagiaire" type="button">👤 Stagiaire</button>
            <button class="btn demo" data-role="formateur" type="button">👨‍🏫 Formateur</button>
            <button class="btn demo" data-role="admin" type="button">🛡️ Admin</button>
          </div>
        </form>

        <p class="login">Déjà un compte ? <a href="../login/index.html">Se connecter</a></p>
      </div>
    </main>
  </div>

  <script>
    const demoData = {
      stagiaire: {
        fullname: 'Ahmed Sahraoui',
        email: 'ahmed.sahraoui@ismo.ma',
        password: 'Demo123!',
        confirmPassword: 'Demo123!'
      },
      formateur: {
        fullname: 'Fatima El Mansoury',
        email: 'fatima.mansoury@ismo.ma',
        password: 'Demo123!',
        confirmPassword: 'Demo123!'
      },
      admin: {
        fullname: 'Mohamed Admin',
        email: 'admin@ismo.ma',
        password: 'Demo123!',
        confirmPassword: 'Demo123!'
      }
    };

    document.querySelectorAll('.btn.demo').forEach(btn => {
      btn.addEventListener('click', function(e){
        e.preventDefault();
        const role = this.dataset.role;
        const data = demoData[role];
        
        document.getElementById('fullname').value = data.fullname;
        document.getElementById('email').value = data.email;
        document.getElementById('password').value = data.password;
        document.getElementById('confirmPassword').value = data.confirmPassword;
        document.getElementById('agreeTerms').checked = true;
      });
    });

    document.getElementById('inscriptionForm').addEventListener('submit', function(e){
      e.preventDefault();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      
      if (password !== confirmPassword) {
        alert('Les mots de passe ne correspondent pas !');
        return;
      }
      
      alert('Demo: formulaire d\'inscription soumis (ici vous ajoutez la logique serveur)');
    });
  </script>
</body>
</html>
