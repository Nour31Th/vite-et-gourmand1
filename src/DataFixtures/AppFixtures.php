<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Allergene;
use App\Entity\Avis;
use App\Entity\Horaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ===== UTILISATEURS =====

        // Admin José
        $admin = new User();
        $admin->setEmail('jose@viteetgourmand.fr');
        $admin->setNom('Fernandez');
        $admin->setPrenom('José');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setActif(true);
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin@ViteGourmand2026!'));
        $manager->persist($admin);

        // Employé Julie
        $employe = new User();
        $employe->setEmail('julie@viteetgourmand.fr');
        $employe->setNom('Martin');
        $employe->setPrenom('Julie');
        $employe->setRoles(['ROLE_EMPLOYE']);
        $employe->setActif(true);
        $employe->setPassword($this->hasher->hashPassword($employe, 'Employe@ViteGourmand2026!'));
        $manager->persist($employe);

        // Utilisateur test
        $user = new User();
        $user->setEmail('client@test.fr');
        $user->setNom('Dupont');
        $user->setPrenom('Marie');
        $user->setRoles(['ROLE_USER']);
        $user->setActif(true);
        $user->setGsm('0612345678');
        $user->setAdresse('12 rue de la Paix');
        $user->setVille('Bordeaux');
        $user->setCodePostal('33000');
        $user->setPassword($this->hasher->hashPassword($user, 'Client@Test2026!'));
        $manager->persist($user);

        // ===== ALLERGENES =====
        $allergenes = [];
        foreach (['Gluten', 'Lactose', 'Oeufs', 'Fruits à coque', 'Poisson', 'Crustacés', 'Soja', 'Céleri'] as $nom) {
            $a = new Allergene();
            $a->setNom($nom);
            $manager->persist($a);
            $allergenes[$nom] = $a;
        }

        // ===== PLATS =====
        $platData = [
            ['Velouté de butternut', 'entree', 'Velouté onctueux de courge butternut avec crème fraîche', ['Lactose']],
            ['Salade de chèvre chaud', 'entree', 'Salade verte, toast de chèvre, noix et miel', ['Lactose', 'Fruits à coque', 'Gluten']],
            ['Magret de canard', 'plat', 'Magret de canard rôti, sauce aux cerises, purée maison', ['Lactose']],
            ['Pavé de saumon', 'plat', 'Pavé de saumon grillé, sauce hollandaise, légumes de saison', ['Poisson', 'Lactose', 'Oeufs']],
            ['Risotto aux champignons', 'plat', 'Risotto crémeux aux champignons des bois et parmesan', ['Lactose']],
            ['Tarte Tatin', 'dessert', 'Tarte tatin aux pommes caramélisées, crème fraîche', ['Gluten', 'Lactose', 'Oeufs']],
            ['Mousse au chocolat', 'dessert', 'Mousse au chocolat noir 70%, coulis de framboises', ['Oeufs', 'Lactose']],
            ['Salade de fruits frais', 'dessert', 'Assortiment de fruits frais de saison', []],
        ];

        $plats = [];
        foreach ($platData as [$nom, $type, $desc, $allergs]) {
            $plat = new Plat();
            $plat->setNom($nom);
            $plat->setType($type);
            $plat->setDescription($desc);
            foreach ($allergs as $allergNom) {
                $plat->addAllergene($allergenes[$allergNom]);
            }
            $manager->persist($plat);
            $plats[$nom] = $plat;
        }

        // ===== MENUS =====

        // Menu 1
        $menu1 = new Menu();
        $menu1->setTitre('Menu Prestige');
        $menu1->setDescription('Un menu gastronomique pour vos événements les plus prestigieux. Produits frais et de saison sélectionnés avec soin par Julie et José.');
        $menu1->setTheme('evenement');
        $menu1->setRegime('classique');
        $menu1->setNbPersonnesMin(10);
        $menu1->setPrix(350.00);
        $menu1->setStock(5);
        $menu1->setConditions('Ce menu doit être commandé minimum 7 jours avant la prestation. Nécessite un acompte de 30% à la commande. Conservation au frais obligatoire.');
        $menu1->setActif(true);
        $menu1->addPlat($plats['Velouté de butternut']);
        $menu1->addPlat($plats['Magret de canard']);
        $menu1->addPlat($plats['Tarte Tatin']);
        $manager->persist($menu1);

        // Menu 2
        $menu2 = new Menu();
        $menu2->setTitre('Menu Déjeuner Express');
        $menu2->setDescription('Parfait pour vos déjeuners d\'affaires. Rapide, savoureux et professionnel. Livraison garantie à l\'heure.');
        $menu2->setTheme('classique');
        $menu2->setRegime('classique');
        $menu2->setNbPersonnesMin(5);
        $menu2->setPrix(120.00);
        $menu2->setStock(10);
        $menu2->setConditions('Commande à passer avant 10h pour une livraison le midi. Délai minimum de 48h pour les nouvelles commandes.');
        $menu2->setActif(true);
        $menu2->addPlat($plats['Salade de chèvre chaud']);
        $menu2->addPlat($plats['Pavé de saumon']);
        $menu2->addPlat($plats['Mousse au chocolat']);
        $manager->persist($menu2);

        // Menu 3
        $menu3 = new Menu();
        $menu3->setTitre('Menu Végétarien Saison');
        $menu3->setDescription('Un menu 100% végétarien élaboré avec les légumes et produits de saison. Savoureux et équilibré pour tous vos événements.');
        $menu3->setTheme('classique');
        $menu3->setRegime('vegetarien');
        $menu3->setNbPersonnesMin(8);
        $menu3->setPrix(200.00);
        $menu3->setStock(8);
        $menu3->setConditions('Commander minimum 5 jours à l\'avance. Certains plats peuvent contenir des traces de fruits à coque.');
        $menu3->setActif(true);
        $menu3->addPlat($plats['Velouté de butternut']);
        $menu3->addPlat($plats['Risotto aux champignons']);
        $menu3->addPlat($plats['Salade de fruits frais']);
        $manager->persist($menu3);

        // ===== HORAIRES =====
        $horairesData = [
            ['Lundi',    null,    null,    true],
            ['Mardi',    null,    null,    true],
            ['Mercredi', '11:00', '23:00', false],
            ['Jeudi',    '11:00', '23:00', false],
            ['Vendredi', '11:00', '23:00', false],
            ['Samedi',   '11:00', '23:00', false],
            ['Dimanche', '11:00', '23:00', false],
        ];

        foreach ($horairesData as [$jour, $ouv, $ferm, $ferme]) {
            $h = new Horaire();
            $h->setJour($jour);
            if (!$ferme) {
                $h->setHeureOuverture(new \DateTime($ouv));
                $h->setHeureFermeture(new \DateTime($ferm));
            } else {
                $h->setHeureOuverture(new \DateTime('00:00'));
                $h->setHeureFermeture(new \DateTime('00:00'));
            }
            $manager->persist($h);
        }

        $manager->flush();

        echo "Fixtures chargées avec succès !\n";
        echo "Admin : jose@viteetgourmand.fr / Admin\@ViteGourmand2026!\n";
        echo "Employé : julie@viteetgourmand.fr / Employe\@ViteGourmand2026!\n";
        echo "Client : client@test.fr / Client\@Test2026!\n";
    }
}
