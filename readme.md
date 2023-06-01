# Devoir de contrôle continu (Symfony)
### Université d’Orléans (2022-2023)
## Sommaire
1. [Description global du projet](#Description-global-du-projet)
2. [Membres du Groupe](#Membres-du-Groupe)
3. [Questions](#Réponses-aux-questions)
    1. [Question 2](#Question-2)
    2. [Question 3](#Question-3)
    3. [Question 4](#Question-4)
    4. [Question 5](#Question-5)
    5. [Question 6](#Question-6)
    6. [Question 7](#Question-7)
    7. [Question 8](#Question-8)
    8. [Question 9](#Question-9)
    9. [Question 10](#Question-10)
    10. [Question 11](#Question-11)
    11. [Question 12](#Question-12)
   12. [Question 13](#Question-13)
   13. [Question 14](#Question-14)
   14. [Question 15](#Question-15)
   15. [Question 16](#Question-16)
## Description global du projet
***
## Membres du Groupe:
1. #### Melissa chabane
2. #### Houchal yanis
3. #### Brocard hanane
4. #### Lahouel bechir

## Réponses aux questions :
### Question 2
```
symfony console make:entity Atelier
```
***
### Question 3
on a changé le fichier .env pour la base de donnée en commentant la ligne 28 et en décommentant la ligne 26
```
symfony console doctrine:database:create
symfony console make:migration
symfony console doctrine:migrations:migrate
```
Si vous n'avez pas le package "orm-fixtures" :
```
symfony composer require --dev orm-fixtures
```
Creer maintenant la fixture :
```
symfony console make:fixture
```
spécifier le nom de fixture : 
```
AtelierFixtures
```
Si vous avez le package faker :
```
symfony composer require fakerphp/faker
```

ajouter les données dans la base
```
symfony console doctrine:fixtures:load
```
***
### Question 4
create des opérations CRUD
```
symfony console make:crud Atelier
```
***
### Question 5
installer bootstrap
```
symfony composer require symfony/webpack-encore-bundle
npm install
npm install bootstrap
npm install bootstrap-icons
```
Dans assets/app.js, ajouter
> import 'bootstrap/dist/css/bootstrap.min.css’;
> import 'bootstrap-icons/font/bootstrap-icons.css’;

Dans config/package/twig.yaml, ajouter dans twig

> form_themes: ['bootstrap_5_layout.html.twig']

exécuter la commande
```
npm run dev
```
***
### Question 6
```
symfony composer require cebe/markdown
```
***
### Question 7
```
symfony console make:user
symfony console make:auth
symfony console make:registration-form
```
***
### Question 8
créer un fixture pour les utilisateurs
```
symfony console make:fixture
```
Modifier l'entité Atelier pour ajouter un champ "user" qui est une relation ManyToOne avec l'entité User
```
symfony console make:entity Atellier
```

New property name (press <return> to stop adding fields):
> user

Field type (enter ? to see all types) [string]:
> relation

What class should this entity be related to?:
> User

Relation type? [ManyToOne, OneToMany, ManyToMany, OneToOne]:
> ManyToOne

Is the Atellier.user property allowed to be null (nullable)? (yes/no) [yes]:
> no

Do you want to add a new property to User so that you can access/update Atellier objects from it - e.g. $user->getateliers()? (yes/no) [yes]:
> yes

A new property will also be added to the User class so that you can access the related Atellier objects from it. New field name inside User [ateliers]:
> yes

Do you want to automatically delete orphaned App\Entity\Atellier objects (orphanRemoval)? (yes/no) [no]:
> no

```
symfony console make:migration
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
```

***
### Question 9
pour faire en sorte que la création d’un atelier ne soit possible que par un instructeur
connecté, on a modifié le fichier src/Controller/AtelierController.php (new) de tel sorte elle vérifie si l’utilisateur est connecté et si c’est un instructeur, sinon elle redirige vers la page de connexion.

***
### Question 10
comme on a met la creation d'un atelier que pour les instructeurs connéctés. Donc on doit affiché 
les boutons de suppression et de modification que pour l'instructeur qui a créé l'atelier (dans la page show).

aussi on a chagé le navBar de tel sort il affiche "mes ateliers" et "créer un atelier" que pour les instructeurs connéctés.
sinon il affiche "se connecter" et "s'inscrire".

aussi on doit modifier les fonction edit et delete dans le controleur AtelierController.php
de tel sorte elle doit vérifier si l'utilisateur est connecté et si c'est l'instructeur qui a créé l'atelier, sinon elle redirige vers la page actueil.
***
### Question 11
utilisatin de bootstrap et fontawesome
***
### Question 12
on ajoute un relation ManyToMany entre les entités Atelier et User
```
symfony console make:entity Atelier
```

New property name (press <return> to stop adding fields):
> participants

Field type (enter ? to see all types) [string]:
> relation

What class should this entity be related to?:
> User

Relation type? [ManyToOne, OneToMany, ManyToMany, OneToOne]:
> ManyToMany

Do you want to add a new property to User so that you can access/update Atelier objects from it - e.g. $user->getAteliers()? (yes/no) [yes]:
> yes

New field name inside User [ateliers]:
> atelierInscrés

```
symfony console make:migration
symfony console doctrine:migrations:migrate
```
modifier le fichier "templates/atelier/show.html.twig" en ajoutant un bouton pour s'inscrire à l'atelier
```
{% if app.user %}
    {% if app.user not in atelier.participants %}
        <a href="{{ path('atelier_inscription', {'id': atelier.id}) }}" class="btn btn-primary">S'inscrire</a>
    {% else %}
        <a href="{{ path('atelier_desinscription', {'id': atelier.id}) }}" class="btn btn-primary">Se désinscrire</a>
    {% endif %}
{% endif %}
```

mofiier le fichier "src/Controller/AtelierController.php" en ajoutant les fonctions "inscription" et "desinscription"
```
    /**
     * @Route("/atelier/inscription/{id}", name="atelier_inscription")
     */
    public function inscription(Atelier $atelier, EntityManagerInterface $em);

    /**
     * @Route("/atelier/desinscription/{id}", name="atelier_desinscription")
     */
    public function desinscription(Atelier $atelier, EntityManagerInterface $em);
```
***
### Question 13


modifier le fichier "templates/atelier/show.html.twig" pour afficher les participants de l'atelier
```
{% for participant in atelier.participants %}
    <li>{{ participant.username }}</li>
{% endfor %}
```
modifier le fichier controlleur "src/Controller/AtelierController.php" pour afficher les participants de l'atelier
```
   #[Route('/mesParticipations', name: 'app_atelier_mesParticipations', methods: ['GET'])]
    public function mesParticipations(AtelierRepository $atelierRepository): Response
    {
        $ateliers = $atelierRepository->findBy(['participants' => $this->getUser()]);
        return $this->render('atelier/mesParticipations.html.twig', [
            'ateliers' => $ateliers,
        ]);
    }
```
ansi que le navBar
***
### Question 14
modifier index.html.twig en ajouter les bouttons "s'inscrir" et "Se désinscrire" pour faciliter les inscréptions
modification de ficture pour ajouter des participants à des ateliers aléatoirement
***
### Question 15

dans cette partie nous avons creer CRUD pour les utilisateurs
on ajouter un profil pour chaque utilisateur
l'administrateur peut modifier le role de chaque utilisateur


***
### Question 16
changer les droits d'accès pour les utilisateurs dans les fichier Controlleurs
