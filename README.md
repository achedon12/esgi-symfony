# **Tindoo** 💖

![Tindoo Logo](assets/favicon.png)

## **Description** 🎉
Tindoo est une réinterprétation de l'application Tinder, adaptée pour une utilisation sur desktop. Ce projet vise à recréer les principales fonctionnalités de Tinder tout en mettant en avant une approche backend robuste avec Symfony, et une interface utilisateur simple et efficace grâce à Twig et Bootstrap. 🚀

---

## **Fonctionnalités** ✨
### **1. Authentification et gestion des utilisateurs** 🔐
- Inscription avec validation des données utilisateur (email, mot de passe sécurisé, etc.).
- Connexion sécurisée avec gestion des sessions.
- Réinitialisation de mot de passe via email.

### **2. Gestion des profils** 📝
- Création et personnalisation des profils utilisateurs (photo, bio, centres d'intérêt).
- Modification des informations personnelles.
- Téléchargement et gestion des photos de profil.

### **3. Système de "swipe"** 👈👉
- Découverte aléatoire des profils d'autres utilisateurs.
- Fonctionnalités "Like" et "Dislike" pour interagir avec les profils.
- Historique des interactions pour éviter les doublons.

### **4. Matching et messagerie** 💬
- Création d'un "match" lorsque deux utilisateurs se "likent".
- Système de messagerie simple pour échanger entre utilisateurs connectés.
- Historique des conversations.

### **5. Recherche avancée** 🔍
- Filtres pour rechercher des profils selon :
    - Âge.
    - Localisation.
    - Centres d'intérêt.

### **6. Multilingue** 🌐
- Interface disponible en plusieurs langues (ex. : français, anglais).
- Sélection de la langue via une barre de navigation.

### **7. Gestion des erreurs et administration** ⚙️
- Pages personnalisées pour les erreurs courantes (404, 500, etc.).
- Interface d'administration pour gérer les utilisateurs et modérer les contenus.
- Système de logs pour suivre et analyser les erreurs.

---

## **Technologies utilisées** 🛠️
- **Backend** : Symfony
- **Frontend** : Twig, Bootstrap
- **Base de données** : MySQL (ou autre SGBD compatible avec Doctrine)
- **Autres outils** :
    - Symfony Security pour l'authentification.
    - Symfony Translation pour la gestion multilingue.
    - Symfony Validator pour la validation des données.
    - Symfony Messenger pour les messages et notifications.

---

## **Installation et utilisation** 📦
### **Prérequis** 📝
- PHP 8.1 ou supérieur.
- Composer.
- Serveur web (Apache ou Nginx).
- MySQL ou tout autre SGBD compatible.

### **Étapes d'installation** 🛠️
1. Clonez le dépôt :
   ```bash
   git clone https://github.com/achedon12/tindoo.git
   cd tindoo
    ```
   
2. Installez les dépendances PHP :

    ```bash
    composer install
    ```
   
3. Configurer et créer la base de données :
    - Copiez le fichier `.env` en `.env.local` et configurez les variables d'environnement pour la base de données.
    - Créez la base de données :
        ```bash
          php bin/console doctrine:migrations:migrate
        ```
   
4. Lancez le serveur local :

    ```bash
      symfony server:start
    ```
   
5. Accédez à l'application dans votre navigateur :

    ```
    http://localhost:8000
    ```

---

## **Auteurs** ✍️

- **Léo Deroin** - [achedon12](https://github.com/achedon12)
- **Mathys Farineau** - [IPandragonI](https://github.com/IPandragonI)