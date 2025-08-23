# Guide de Déploiement Railway pour BTP Manager

Ce guide vous accompagne pour déployer votre application Symfony sur Railway avec MySQL.

## 🚀 Prérequis

- Un compte GitHub avec votre projet
- Un compte Railway ([railway.app](https://railway.app))
- Docker installé localement (pour les tests)

## 📋 Étapes de Déploiement

### 1. Test Local avec MySQL

Avant de déployer, testez votre application avec MySQL en local :

```bash
# Construire et lancer l'application avec MySQL
docker-compose -f compose.mysql.yaml up --build

# Accéder à l'application
# http://localhost:8080
# Credentials: admin@edifispro.com / admin123
```

### 2. Déploiement sur Railway

#### Étape 2.1: Créer le projet Railway

1. Allez sur [railway.app](https://railway.app)
2. Connectez votre compte GitHub
3. Cliquez sur "New Project" → "Deploy from GitHub repo"
4. Sélectionnez votre repository `BtpManager`

#### Étape 2.2: Ajouter MySQL

1. Dans votre projet Railway, cliquez sur "+ New"
2. Sélectionnez "Database" → "Add MySQL"
3. Railway créera automatiquement une base MySQL

#### Étape 2.3: Configurer les Variables d'Environnement

Dans l'onglet "Variables" de votre service **web** (pas de la base de données) :

**Variables obligatoires :**
```env
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=VOTRE_SECRET_GENERE_32_CARACTERES
MAILER_DSN=null://null
SYMFONY_DEPRECATIONS_HELPER=disabled
```

**Variables automatiques :**
Railway configurera automatiquement ces variables grâce à la base MySQL :
- `DATABASE_URL` - URL de connexion MySQL
- `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`

#### Étape 2.4: Déploiement

1. Railway détectera automatiquement votre `Dockerfile`
2. Le build commencera automatiquement
3. Surveillez les logs dans l'onglet "Deployments"

### 3. Vérification Post-Déploiement

Une fois déployé :

1. **Accédez à votre application** via l'URL fournie par Railway
2. **Connectez-vous** avec : `admin@edifispro.com` / `admin123`
3. **Vérifiez les fonctionnalités** principales
4. **Consultez les logs** en cas de problème

## 🔧 Configuration Avancée

### Variables d'Environnement Optionnelles

```env
# Pour la production, personnalisez ces valeurs
MAILER_DSN=smtp://user:pass@smtp.example.com:587
```

### Domaine Personnalisé

1. Dans Railway, allez dans l'onglet "Settings" de votre service
2. Section "Domains" → "Custom Domain"
3. Ajoutez votre domaine personnalisé

## 🛠️ Maintenance

### Mise à jour de l'Application

```bash
# Push sur la branche main pour redéployer automatiquement
git push origin main
```

### Consultation des Logs

```bash
# Avec Railway CLI (optionnel)
npm install -g @railway/cli
railway login
railway link
railway logs
```

### Sauvegarde de la Base de Données

Railway gère automatiquement les sauvegardes, mais vous pouvez aussi :

1. Aller dans l'onglet de votre base MySQL
2. Utiliser l'onglet "Data" pour explorer/exporter

## 🚨 Dépannage

### Problème de Connexion à la Base

1. Vérifiez que la variable `DATABASE_URL` est bien configurée
2. Consultez les logs du service web
3. Vérifiez que la base MySQL est bien démarrée

### Erreur 500

1. Activez temporairement `APP_DEBUG=true`
2. Consultez les logs détaillés
3. Vérifiez les permissions des dossiers `var/` et `public/uploads/`

### Migration des Données

Si vous migrez depuis un autre environnement :

```bash
# Export depuis votre ancienne base
mysqldump -u user -p ancienne_base > backup.sql

# Import dans Railway (via interface ou CLI)
# Utiliser l'onglet "Data" de votre base MySQL Railway
```

## 📞 Support

- Documentation Railway : [docs.railway.app](https://docs.railway.app)
- Symfony Deployment : [symfony.com/doc/current/deployment.html](https://symfony.com/doc/current/deployment.html)

---

✅ **Votre application BTP Manager est maintenant prête pour la production sur Railway !**
