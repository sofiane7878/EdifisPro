# 📋 Documentation CI/CD - BTP Manager

## 🎯 Vue d'Ensemble

Cette documentation détaille la configuration et l'utilisation de la pipeline CI/CD avec GitHub Actions pour le projet BTP Manager.

## 🏗️ Architecture de la Pipeline

### Structure des Workflows

```
.github/workflows/
├── ci.yml           # Pipeline CI principale
└── pr-check.yml     # Vérifications pour Pull Requests
```

### Jobs de la Pipeline

| Job | Description | Dépendances |
|-----|-------------|-------------|
| **Setup** | Configuration environnement | Aucune |
| **Test** | Exécution des tests | Setup |
| **Quality** | Analyse de qualité | Setup |
| **Report** | Génération rapports | Test, Quality |
| **Notify** | Notifications | Test, Quality, Report |

## 🔧 Configuration Détaillée

### 1. Job Setup

**Objectif :** Préparer l'environnement de test

```yaml
setup:
  name: 🔧 Setup Environment
  runs-on: ubuntu-latest
  
  services:
    mysql:
      image: mysql:8.0
      env:
        MYSQL_ROOT_PASSWORD: root
        MYSQL_DATABASE: btp_management1_test
```

**Étapes :**
1. **Checkout** : Récupération du code
2. **PHP Setup** : Installation PHP 8.2 + extensions
3. **Dependencies** : Installation Composer
4. **Environment** : Configuration .env.local
5. **Database** : Création base de test + migrations
6. **Cache** : Nettoyage cache Symfony

### 2. Job Test

**Objectif :** Exécuter les 68 tests

```yaml
test:
  name: 🧪 Run Tests
  runs-on: ubuntu-latest
  needs: setup
```

**Étapes :**
1. **Setup identique** au job setup
2. **PHPUnit** : Exécution des tests avec couverture
3. **Artifacts** : Upload des rapports de couverture

**Tests exécutés :**
- Tests d'Entités (43 tests)
- Tests de Contrôleurs (13 tests)
- Tests de Formulaires (9 tests)
- Tests d'Intégration (3 tests)

### 3. Job Quality

**Objectif :** Analyser la qualité du code

```yaml
quality:
  name: 📊 Code Quality
  runs-on: ubuntu-latest
  needs: setup
```

**Outils utilisés :**
- **PHPStan** : Analyse statique niveau 8
- **PHP CodeSniffer** : Standards PSR-12
- **Security Checker** : Vulnérabilités des dépendances

### 4. Job Report

**Objectif :** Générer des rapports

```yaml
report:
  name: 📈 Generate Reports
  runs-on: ubuntu-latest
  needs: [test, quality]
  if: always()
```

**Fonctionnalités :**
- Téléchargement des artifacts de couverture
- Génération de résumés
- Création de rapports détaillés

### 5. Job Notify

**Objectif :** Notifications de statut

```yaml
notify:
  name: 🔔 Notifications
  runs-on: ubuntu-latest
  needs: [test, quality, report]
  if: always()
```

**Types de notifications :**
- **Succès** : Pipeline réussie
- **Échec** : Pipeline échouée avec détails

## 🚀 Utilisation

### Déclenchement Automatique

La pipeline se déclenche automatiquement sur :
- **Push** vers `main` ou `develop`
- **Pull Request** vers `main` ou `develop`

### Déclenchement Manuel

Pour déclencher manuellement :
1. Aller dans l'onglet **Actions** de GitHub
2. Sélectionner le workflow **CI - BTP Manager**
3. Cliquer sur **Run workflow**

### Branches Supportées

- `main` : Branche principale
- `develop` : Branche de développement
- `feature/*` : Branches de fonctionnalités (via PR)

## 📊 Monitoring et Rapports

### Rapports de Couverture

**Localisation :** Artifacts de la pipeline
**Format :** HTML + XML
**Rétention :** 30 jours

### Métriques Disponibles

- **Couverture de lignes** : Pourcentage de code testé
- **Couverture de branches** : Branches conditionnelles testées
- **Couverture de fonctions** : Fonctions testées

### Accès aux Rapports

1. Aller dans l'onglet **Actions**
2. Sélectionner une exécution
3. Télécharger les artifacts **coverage-report**

## 🔍 Dépannage

### Problèmes Courants

#### 1. Échec de Setup

**Symptômes :**
```
Error: Database connection failed
```

**Solutions :**
- Vérifier la configuration MySQL
- S'assurer que le service MySQL est démarré
- Vérifier les variables d'environnement

#### 2. Échec des Tests

**Symptômes :**
```
PHPUnit tests failed
```

**Solutions :**
- Vérifier les données de test
- S'assurer que les migrations sont à jour
- Vérifier la configuration de la base de test

#### 3. Échec de Qualité

**Symptômes :**
```
PHPStan analysis failed
```

**Solutions :**
- Corriger les erreurs PHPStan
- Vérifier les types et annotations
- Mettre à jour les dépendances

### Logs et Debugging

**Accès aux logs :**
1. Onglet **Actions** → Sélectionner une exécution
2. Cliquer sur un job spécifique
3. Consulter les logs détaillés

**Informations utiles :**
- Temps d'exécution de chaque étape
- Messages d'erreur détaillés
- Variables d'environnement utilisées

## ⚙️ Configuration Avancée

### Variables d'Environnement

```yaml
env:
  PHP_VERSION: '8.2'
  COMPOSER_CACHE_DIR: ~/.composer/cache
```

### Cache

```yaml
- name: 💾 Cache dependencies
  uses: actions/cache@v3
  with:
    path: vendor
    key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
```

### Services

```yaml
services:
  mysql:
    image: mysql:8.0
    env:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: btp_management1_test
    ports:
      - 3306:3306
    options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
```

## 📈 Optimisations

### Performance

1. **Cache Composer** : Réutilisation des dépendances
2. **Parallélisation** : Jobs indépendants en parallèle
3. **Artifacts** : Partage des données entre jobs

### Coût

- **GitHub Actions** : Gratuit pour repos publics
- **Limite** : 2000 minutes/mois pour repos privés
- **Optimisation** : Cache et parallélisation

## 🔄 Maintenance

### Mises à Jour

**Actions GitHub :**
- Vérifier régulièrement les nouvelles versions
- Mettre à jour les actions utilisées
- Tester après mise à jour

**Dépendances :**
- Maintenir Composer à jour
- Vérifier les vulnérabilités
- Mettre à jour PHP et extensions

### Monitoring

**Métriques à surveiller :**
- Temps d'exécution de la pipeline
- Taux de réussite des tests
- Couverture de code
- Qualité du code (PHPStan, PHPCS)

## 📞 Support

### Ressources

- **Documentation GitHub Actions** : https://docs.github.com/en/actions
- **PHPStan** : https://phpstan.org/
- **PHP CodeSniffer** : https://github.com/squizlabs/PHP_CodeSniffer
- **Security Checker** : https://github.com/enlightn/security-checker

### Contact

Pour toute question sur la CI/CD :
- Ouvrir une issue sur GitHub
- Consulter les logs d'exécution
- Vérifier la documentation

---

**Dernière mise à jour :** 21 Août 2025  
**Version :** 1.0  
**Statut :** ✅ Opérationnel
