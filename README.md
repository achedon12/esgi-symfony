# **Tindoo**  <img alt="Tindoo Logo" height="35" src="assets/favicon.png" width="30"/>


## **Description** 🎉
Tindoo est une réinterprétation de l'application Tinder, adaptée pour une utilisation sur desktop. 
Ce projet vise à recréer les principales fonctionnalités de Tinder tout en mettant l'accent sur le backend via Symfony, et une interface utilisateur basée sur Twig et Bootstrap. 🚀

---

## **Fonctionnalités** ✨
### **1. Authentification et gestion des utilisateurs** 🔐
- Inscription avec validation des données utilisateur (email, mot de passe sécurisé, etc.).
- Envoi d'un email de confirmation de creation de compte pour vérifier son compte
- Connexion sécurisée avec gestion des sessions.
- Réinitialisation de mot de passe via email.

### **2. Gestion des profils** 📝
- Création et personnalisation des profils utilisateurs (photo, bio, centres d'intérêt).
- Modification des informations personnelles.
- Téléchargement et gestion des photos de profil.

### **3. Système de "swipe"** 👈👉
- Découverte aléatoire des profils d'autres utilisateurs.
- Fonctionnalités "Like" et "Dislike" pour exprimer son intérêt ou non (gestion de score).

### **4. Matching et messagerie** 💬
- Création d'un "match" lorsque deux utilisateurs se "likent".
- Système de messagerie simple pour échanger entre utilisateurs.
- Historique des conversations.

### **5. Recherche avancée** 🔍
- Filtres pour rechercher des profils selon :
    - Âge.
    - Localisation.
    - Centres d'intérêt.

### **6. Multilingue** 🌐
- Interface disponible en plusieurs langues (ex. : français, anglais).

### **7. Gestion des erreurs et administration** ⚙️
- Pages personnalisées pour les erreurs courantes (404, 500, etc.).
- Interface d'administration pour gérer les utilisateurs et modérer les contenus.
- Système de logs pour suivre et analyser les erreurs.

### **8. Gestion de tâches planifiées** ⏰
- Cron permettant de supprimer les utilisateurs inactifs après un certain délai.

### **9. Gestion de son offre** 💳
- Possibilité de souscrire à un abonnement pour bénéficier de fonctionnalités supplémentaires.
- Envoi d'un email lors de la souscription à un abonnement.

---

## **Technologies utilisées** 🛠️
- **Backend** : Symfony
- **Frontend** : Twig, Bootstrap
- **Base de données** : MySQL (ou autre SGBD compatible avec Doctrine)

---

## **Auteurs** ✍️

- **Léo Deroin** - [achedon12](https://github.com/achedon12)
- **Mathys Farineau** - [IPandragonI](https://github.com/IPandragonI)

## Une fois le projet lancé...

  > Connecter votre base de données
  > Lancer les fixtures
  > Lancer le serveur symfony
  > Connecter vous avec les identifiants suivants :
  >> compte administrateur :
  >> email : `admin@gmail.com`
  >> mot de passe : `admin`
  > 
  >> compte utilisateur :
  >> email : `regular@gmail.com`
  >> mot de passe : `regular`

> **Note :** L'administrateur peut se rendre sur la page /admin qui lui permet de gérer l'application.


TODO: 

- [ ] listener for user creation for mailing
- [ ] listener for user offer change for mailing
- [ ] code refactoring
- [ ] language switcher