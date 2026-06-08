<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Formateur - Badges</title>
  <link rel="stylesheet" href="../../css/styles.css">
  <link rel="stylesheet" href="badges.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="container">
                <aside class="sidebar sidebar-formateur">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <div>
          <h1>ISMO-SkillSwap</h1>
          <p>ISMO Tétouan</p>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a href="../accueil/accueil.php" class="nav-item">
          <i class="fas fa-home"></i>
          <span>Accueil</span>
        </a>
        <a href="../marketplace/marketplace.php" class="nav-item">
          <i class="fas fa-store"></i>
          <span>Marketplace</span>
        </a>
        <a href="../profile/profile.php" class="nav-item">
          <i class="fas fa-user"></i>
          <span>Profil</span>
        </a>
        <a href="../passport/passport.php" class="nav-item">
          <i class="fas fa-passport"></i>
          <span>Passport</span>
        </a>
        <a href="../classement/classement.php" class="nav-item">
          <i class="fas fa-ranking-star"></i>
          <span>Classement</span>
        </a>
        <a href="../validation/validations.php" class="nav-item">
          <i class="fas fa-check-circle"></i>
          <span>Validations</span>
        </a>
        <a href="../badge/badges.php" class="nav-item active">
          <i class="fas fa-trophy"></i>
          <span>Badges</span>
        </a>
      </nav>
      <div class="sidebar-footer">
        <div class="user-info">
          <div class="avatar">MF</div>
          <div>
            <p class="user-name">Marc Formateur</p>
            <p class="user-role">Formateur</p>
          </div>
        </div>
      </div>
    </aside>

    
          <header class="top-header">
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Rechercher...">
      </div>
      <div class="header-actions">
        <button class="notification-btn">
          <i class="fas fa-bell"></i>
          <span class="notification-badge">3</span>
        </button>
        <div class="user-menu" id="profileToggle">
          <div class="avatar-large">MF</div>
          <div>
            <p class="user-name">Marc Formateur</p>
            <p class="user-role">Formateur</p>
          </div>
          <i class="fas fa-chevron-down" id="chevronIcon"></i>
        </div>
      </div>
      <!-- Profile Dropdown -->
      <div class="profile-dropdown" id="profileMenu">
        <div class="dropdown-header">
          <div class="dropdown-avatar">MF</div>
          <div class="dropdown-user-info">
            <h4>Marc Formateur</h4>
            <p>Platform Formateur</p>
            <span class="role-badge role-badge-formateur">Formateur</span>
          </div>
        </div>
        <div class="dropdown-meta">
          <div class="meta-item"><i class="fas fa-envelope"></i> formateur@ismo.com</div>
          <div class="meta-item"><i class="fas fa-calendar"></i> Joined 3/9/2022</div>
          <div class="meta-item"><i class="fas fa-chart-line"></i> 4,200 Reputation Points</div>
          <div class="meta-item"><i class="fas fa-medal"></i> 35 Skills Validated</div>
        </div>
        <div class="dropdown-actions">
          <a href="../passport/passport.php" class="dropdown-action">
            <i class="fas fa-download"></i> Download Skills Passport (PDF)
          </a>
          <a href="../profile/profile.php" class="dropdown-action">
            <i class="fas fa-user"></i> View Profile
          </a>
          <a href="../profile/profile.php" class="dropdown-action">
            <i class="fas fa-gear"></i> Settings
          </a>
        </div>
        <div class="dropdown-signout">
          <a href="../../login/index.php" class="dropdown-action signout">
            <i class="fas fa-right-from-bracket"></i> Sign Out
          </a>
        </div>
      </div>
    </header>

      <main class="main-content">
      <section class="profile-container">
        <h2>Gestion des Badges</h2>
        <p class="subtitle">Créez et attribuez des badges aux stagiaires</p>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-header">
              <h3>Total Badges</h3>
              <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-value">12</div>
            <div class="stat-change">6 catégories différentes</div>
          </div>
          <div class="stat-card">
            <div class="stat-header">
              <h3>Attribués ce mois</h3>
              <i class="fas fa-award"></i>
            </div>
            <div class="stat-value">28</div>
            <div class="stat-change positive">+8 ce mois</div>
          </div>
          <div class="stat-card">
            <div class="stat-header">
              <h3>Stagiaires Badgés</h3>
              <i class="fas fa-users"></i>
            </div>
            <div class="stat-value">18</div>
            <div class="stat-change">sur 24 stagiaires</div>
          </div>
        </div>

        <section class="badges-section">
          <h3>Badges Disponibles</h3>
          <div class="badges-grid">
            <div class="badge-card">
              <div class="badge-icon">⭐</div>
              <h4>Premier Aide</h4>
              <p>A aidé son premier camarade</p>
              <span class="badge-status obtained">7 attribués</span>
            </div>
            <div class="badge-card">
              <div class="badge-icon">⚡</div>
              <h4>Réponse Rapide</h4>
              <p>Réponse ultra-rapide aux questions</p>
              <span class="badge-status obtained">5 attribués</span>
            </div>
            <div class="badge-card">
              <div class="badge-icon">⚛️</div>
              <h4>Expert React</h4>
              <p>Maîtrise avancée de React</p>
              <span class="badge-status obtained">4 attribués</span>
            </div>
            <div class="badge-card">
              <div class="badge-icon">🐍</div>
              <h4>Python Master</h4>
              <p>Maîtrise avancée de Python</p>
              <span class="badge-status obtained">6 attribués</span>
            </div>
            <div class="badge-card">
              <div class="badge-icon">💾</div>
              <h4>SQL Wizard</h4>
              <p>Expert en bases de données</p>
              <span class="badge-status obtained">3 attribués</span>
            </div>
            <div class="badge-card">
              <div class="badge-icon">🐳</div>
              <h4>Docker Pro</h4>
              <p>Maîtrise de la conteneurisation</p>
              <span class="badge-status obtained">2 attribués</span>
            </div>
            <div class="badge-card">
              <div class="badge-icon">🔧</div>
              <h4>Git Guru</h4>
              <p>Expert en gestion de versions</p>
              <span class="badge-status obtained">4 attribués</span>
            </div>
            <div class="badge-card">
              <div class="badge-icon">🤝</div>
              <h4>Team Player</h4>
              <p>Esprit d'équipe exceptionnel</p>
              <span class="badge-status obtained">9 attribués</span>
            </div>
            <div class="badge-card">
              <div class="badge-icon">☕</div>
              <h4>Code Ninja</h4>
              <p>Code propre et bien structuré</p>
              <span class="badge-status obtained">1 attribué</span>
            </div>
          </div>
        </section>

        <section class="competences-section assign-section">
          <h3>Attribuer un Badge</h3>
          <div class="assign-form">
            <div class="assign-row">
              <div class="assign-field">
                <label>Stagiaire</label>
                <select class="filter-select">
                  <option>Sélectionner un stagiaire...</option>
                  <option>Sarah Chen</option>
                  <option>Mike Johnson</option>
                  <option>Emma Davis</option>
                  <option>Alex Kumar</option>
                  <option>Lisa Wang</option>
                  <option>Omar Hassan</option>
                  <option>Yuki Tanaka</option>
                  <option>Maria Garcia</option>
                </select>
              </div>
              <div class="assign-field">
                <label>Badge</label>
                <select class="filter-select">
                  <option>Sélectionner un badge...</option>
                  <option>⭐ Premier Aide</option>
                  <option>⚡ Réponse Rapide</option>
                  <option>⚛️ Expert React</option>
                  <option>🐍 Python Master</option>
                  <option>💾 SQL Wizard</option>
                  <option>🐳 Docker Pro</option>
                  <option>🔧 Git Guru</option>
                  <option>🤝 Team Player</option>
                  <option>☕ Code Ninja</option>
                </select>
              </div>
              <div class="assign-field">
                <label>Date</label>
                <input type="date" class="filter-input" value="2024-05-24">
              </div>
              <div class="assign-field assign-btn-wrap">
                <label>&nbsp;</label>
                <button class="btn-assign"><i class="fas fa-gift"></i> Attribuer</button>
              </div>
            </div>
          </div>
        </section>

        <section class="competences-section">
          <h3>Attributions Récentes</h3>
          <table class="validations-table">
            <thead>
              <tr>
                <th>Stagiaire</th>
                <th>Badge</th>
                <th>Date</th>
                <th>Attribué par</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Sarah Chen</strong></td>
                <td><span class="badge-tag">🐍 Python Master</span></td>
                <td>22/05/2024</td>
                <td>Marc Formateur</td>
              </tr>
              <tr>
                <td><strong>Mike Johnson</strong></td>
                <td><span class="badge-tag">⚛️ Expert React</span></td>
                <td>21/05/2024</td>
                <td>Marc Formateur</td>
              </tr>
              <tr>
                <td><strong>Emma Davis</strong></td>
                <td><span class="badge-tag">🤝 Team Player</span></td>
                <td>20/05/2024</td>
                <td>Marc Formateur</td>
              </tr>
              <tr>
                <td><strong>Alex Kumar</strong></td>
                <td><span class="badge-tag">⭐ Premier Aide</span></td>
                <td>19/05/2024</td>
                <td>Marc Formateur</td>
              </tr>
              <tr>
                <td><strong>Lisa Wang</strong></td>
                <td><span class="badge-tag">🔧 Git Guru</span></td>
                <td>18/05/2024</td>
                <td>Marc Formateur</td>
              </tr>
            </tbody>
          </table>
        </section>

      </section>
    </main>
  </div>
  <script>
    const profileToggle = document.getElementById('profileToggle');
    const profileMenu   = document.getElementById('profileMenu');
    const chevronIcon   = document.getElementById('chevronIcon');
    profileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      profileMenu.classList.toggle('open');
      chevronIcon.classList.toggle('rotated');
    });
    document.addEventListener('click', (e) => {
      if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
        profileMenu.classList.remove('open');
        chevronIcon.classList.remove('rotated');
      }
    });
  </script>
</body>
</html>
