# Dépannage Railway - BTP Manager

## 🔍 Diagnostic des Problèmes de Connexion Base de Données

### Problème Observé
```
PDO::__construct(): php_network_getaddresses: getaddrinfo for host failed: No address associated with hostname
```

### ✅ Solutions à Vérifier

#### 1. Variables d'Environnement Railway

Dans l'onglet **Variables** de votre service **web** Railway, vérifiez :

**Variables Obligatoires :**
```env
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=votre_secret_32_caracteres
```

**Variables Database (automatiques) :**
- `DATABASE_URL` - doit être automatiquement créée par Railway
- `MYSQLHOST` - nom d'hôte de la base MySQL
- `MYSQLPORT` - port MySQL (généralement 3306)
- `MYSQLDATABASE` - nom de la base
- `MYSQLUSER` - utilisateur MySQL
- `MYSQLPASSWORD` - mot de passe MySQL

#### 2. Vérifier la Base de Données MySQL

1. **Dans votre projet Railway :**
   - Allez dans l'onglet de votre **service MySQL**
   - Vérifiez qu'il est bien **démarré** et **healthy**
   - Notez l'**URL de connexion**

2. **Variables de connexion :**
   - Railway doit automatiquement créer `DATABASE_URL`
   - Format attendu : `mysql://user:password@host:port/database`

#### 3. Ordre de Démarrage des Services

Railway doit démarrer la base **avant** l'application :

1. **Service MySQL** → Status: Running
2. **Service Web** → Status: Running

#### 4. Configuration du Service Web

Dans l'onglet **Settings** de votre service web :

- **Builder** : Dockerfile
- **Root Directory** : `/` (racine)
- **Dockerfile Path** : `Dockerfile`

#### 5. Réseau Railway

Vérifiez que les services peuvent communiquer :
- Les services dans le même projet Railway peuvent se parler
- Le nom d'hôte MySQL doit être résolvable depuis l'app

### 🛠️ Actions Correctives

#### Solution 1: Recréer la Base de Données

1. **Supprimer la base MySQL actuelle**
2. **Créer une nouvelle base :** `+ New → Database → Add MySQL`
3. **Attendre que Railway configure les variables**
4. **Redéployer l'application**

#### Solution 2: Vérifier les Variables d'Environnement

Dans l'onglet **Variables** du service web :

```bash
# Variables à vérifier
echo $DATABASE_URL
echo $MYSQLHOST
echo $MYSQLPORT
echo $MYSQLDATABASE
```

#### Solution 3: Configuration Manuelle DATABASE_URL

Si les variables automatiques ne fonctionnent pas, créez manuellement :

```env
DATABASE_URL=mysql://${MYSQLUSER}:${MYSQLPASSWORD}@${MYSQLHOST}:${MYSQLPORT}/${MYSQLDATABASE}?serverVersion=8.0&charset=utf8mb4
```

### 📊 Logs de Diagnostic

#### Logs à Surveiller

1. **Service MySQL** :
   - Démarrage réussi
   - Port d'écoute
   - Utilisateur créé

2. **Service Web** :
   - Variables d'environnement chargées
   - Tentatives de connexion DB
   - Erreurs de réseau

#### Commandes de Debug

```bash
# Dans les logs Railway, chercher :
- "Database connection attempt"
- "DATABASE_URL not set"
- "Database connected successfully"
- "php_network_getaddresses"
```

### 🚀 Test de Connexion

Notre script de démarrage amélioré :
- ✅ Teste la connexion DB pendant 60 secondes
- ✅ Continue sans DB si pas disponible
- ✅ Affiche des messages clairs de diagnostic
- ✅ Ne plante pas l'application

### 📞 Support

Si le problème persiste :

1. **Vérifiez les logs Railway détaillés**
2. **Contactez le support Railway** avec :
   - ID du projet
   - Logs de déploiement
   - Configuration des services

---

**Note :** Railway peut parfois avoir des problèmes de réseau temporaires. Attendez quelques minutes et redéployez.
