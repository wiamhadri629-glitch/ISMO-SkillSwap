<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    extract($_POST);
    if (isset($inscription)) {
        $err = [];
        if (!isset($nom) || $nom == "") $err["nom"] = "le nom est obligatoire";
        if (!isset($prenom) || $prenom == "") $err["prenom"] = "le prénom est obligatoire";
        if (!filter_var($email)) $err["email"] = "l'email est invalide";
        if (!isset($motdepasse) || $motdepasse == "") $err["motdepasse"] = "le mot de passe est obligatoire";
        if (!isset($confirmmotdepasse) || $confirmmotdepasse == "") $err["confirmmotdepasse"] = "la confirmation est obligatoire";
        if ($motdepasse != $confirmmotdepasse) $err["confirmmotdepasse"] = "les mots de passe ne sont pas identiques";
        if (!isset($role) || $role == "")
            $err["role"] = "le role est obligatoire";
        $filiere = $_POST["filiere"] ?? null;
        if ($role == "stagiaire" && (!$filiere || $filiere == "")) {
            $err["filiere"] = "la filière est obligatoire pour stagiaire";
        }
        if (empty($err)) {
            $nom = htmlspecialchars($nom);
            $prenom = htmlspecialchars($prenom);
            $email = htmlspecialchars($email);
            $motdepasse = hash('sha256', $motdepasse);
            $role = htmlspecialchars($role);
            $filiere = htmlspecialchars($filiere);
            include("../dbopen/db.php");
            try {
                $req = $db->prepare("INSERT INTO utilisateurs(nom,prenom,email,motdepasse,role,filiere) VALUES(?,?,?,?,?,?)");
                $r=$req->execute([$nom,$prenom,$email,$motdepasse,$role,$filiere]);
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
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ISMO-SkillSwap</title>

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

<h3>Créer un compte</h3>

<form method="POST" id="inscriptionForm">
<?php if (isset($err["errconnection"])) echo "<div style='color:red'>" . $err["errconnection"] . "</div>"; ?>
<?php if (isset($err["success"])) echo "<div style='color:green'>" . $err["success"] . "</div>"; ?>
<!-- NOM -->
<label class="field">
<span>Nom</span>
<input name="nom" type="text" >
<?php if(isset($err["nom"])) echo "<div style='color:red'>{$err["nom"]}</div>"; ?>
</label>

<!-- PRENOM -->
<label class="field">
<span>Prénom</span>
<input name="prenom" type="text" >
<?php if(isset($err["prenom"])) echo "<div style='color:red'>{$err["prenom"]}</div>"; ?>
</label>

<!-- EMAIL -->
<label class="field">
<span>Email</span>
<input name="email" type="email" >
<?php if(isset($err["email"])) echo "<div style='color:red'>{$err["email"]}</div>"; ?>
</label>

<!-- PASSWORD -->
<label class="field">
<span>Mot de passe</span>
<input id="password" name="motdepasse" type="password">
<?php if(isset($err["motdepasse"])) echo "<div style='color:red'>{$err["motdepasse"]}</div>"; ?>
</label>

<!-- CONFIRM -->
<label class="field">
<span>Confirmer</span>
<input id="confirmPassword" name="confirmmotdepasse" type="password">
<?php if(isset($err["confirmmotdepasse"])) echo "<div style='color:red'>{$err["confirmmotdepasse"]}</div>"; ?>
</label>

<!-- ROLE -->
<label class="field">
<span>Rôle</span>
<select id="role" name="role">
  <option value="">Choisir un rôle</option>
  <option value="stagiaire">Stagiaire</option>
  <option value="mentor">Mentor</option>
  <option value="formateur">Formateur</option>
  <option value="admin">Admin</option>
</select>
<?php if(isset($err["role"])) echo "<div style='color:red'>{$err["role"]}</div>"; ?>
</label>

<!-- FILIERE (ONLY STAGIAIRE) -->
<div id="filiereDiv" style="display:none;">

<label class="field">
<span>Filière</span>

<select name="filiere" id="filiere">
  <option value="">Choisir filière</option>
  <option value="Dev">Développement Digital</option>
  <option value="AI">Intelligence Artificielle</option>
  <option value="Infographie">Infographie</option>
  <option value="Réseaux">Réseaux</option>
  <option value="Infrastructure">Infrastructure</option>
</select>

<?php if(isset($err["filiere"])) echo "<div style='color:red'>{$err["filiere"]}</div>"; ?>

</label>

</div>

<label class="checkbox">
<input type="checkbox" required>
J'accepte les conditions
</label>

<button class="btn primary" name="inscription" type="submit">
S'inscrire
</button>

</form>
<p class="login">Déjà un compte ? <a href="../login/index.php">Se connecter</a></p>
</div>
</main>

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

  const roleSelect = document.getElementById("role");
  const filiereDiv = document.getElementById("filiereDiv");

  function toggleFiliere() {
    if (roleSelect.value === "stagiaire") {
      filiereDiv.style.display = "block";
    } else {
      filiereDiv.style.display = "none";
    }
  }

  roleSelect.addEventListener("change", toggleFiliere);

  toggleFiliere();

});

</script>

</body>
</html>