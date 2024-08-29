# Contrôle Wallbox

**Contrôle Wallbox** est une application web PHP qui permet de contrôler et de gérer les paramètres d'une borne de recharge pour véhicules électriques (EV) Wallbox. Cette application offre une interface utilisateur simple et intuitive pour exécuter diverses actions sur la Wallbox, telles que verrouiller/déverrouiller la borne, définir le courant de charge maximum, mettre en pause ou reprendre la charge, redémarrer la borne, et consulter le statut détaillé de la Wallbox.

## Fonctionnalités Principales

- **Verrouiller/Déverrouiller la borne** : Contrôlez l'accès à la borne en un clic.
- **Définir le courant de charge maximum** : Ajustez la puissance de charge entre 6 et 32A selon vos besoins.
- **Mettre en pause/Reprendre la charge** : Gérez l'état de la charge en fonction de votre planning.
- **Mode Eco-Smart** : Activez ou reprenez le mode "Eco Mode" ou "Full Green" pour optimiser l'efficacité énergétique.
- **Statut détaillé** : Consultez les informations en temps réel de la Wallbox, incluant la puissance de charge, le statut de la borne, le mode Eco-Smart, et l'état de mise à jour logicielle.
- **Mise à jour de la borne** : Vérifiez et installez les mises à jour logicielles disponibles pour votre borne.

## Installation

1. Clonez ce dépôt dans répertoire approprié de votre serveur PHP:  git clone https://github.com/votreutilisateur/controle-wallbox.git
2. Modifiez les variables d'identification dans le code source :
   - `$email = 'xxx@xxxx.com';` // Email utilisé pour se connecter au compte Wallbox
   - `$password = 'xxx';` // Mot de passe utilisé pour se connecter au compte Wallbox
   - `$charger_id = xxxxxx;` // Numéro de série de la Wallbox (les six chiffres après le préfixe SN)
3. Accédez à l'application via votre navigateur pour commencer à contrôler votre Wallbox.


## ⚠️ Disclaimer

> **Cette application permet de contrôler à distance votre borne de recharge Wallbox et comporte des risques de sécurité importants si elle est rendue accessible en ligne.**
>
> - **Ne pas exposer cette application directement à Internet** : Cela pourrait permettre à des tiers non autorisés de manipuler votre borne de recharge, entraînant des risques pour la sécurité des personnes, des véhicules, et des installations électriques.
> - **Utilisation sur un réseau local sécurisé uniquement** : Limitez l'accès à cette application à un réseau local sécurisé (par exemple, derrière un pare-feu) et configurez des contrôles d'accès stricts.
> - **Responsabilité** : L'utilisation de cette application se fait à vos propres risques. L'auteur décline toute responsabilité pour les dommages résultant d'une mauvaise configuration ou d'une utilisation non sécurisée de cette application.

## Acknowledgements
Ce script a été développé avec l'aide de ChatGPT et la documentation officielle disponible ici.