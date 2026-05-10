<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\HistoriqueStatut;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande_new')]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        MenuRepository $menuRepository,
        EntityManagerInterface $em
    ): Response {
        $menuId = $request->query->get('menu_id');
        $menu = $menuId ? $menuRepository->find($menuId) : null;
        $menus = $menuRepository->findBy(['actif' => true]);

        if ($request->isMethod('POST')) {
            $user = $this->getUser();
            $menuChoisi = $menuRepository->find($request->request->get('menu_id'));

            if (!$menuChoisi) {
                $this->addFlash('error', 'Menu invalide.');
                return $this->redirectToRoute('app_commande_new');
            }

            $nbPersonnes = (int) $request->request->get('nb_personnes');

            if ($nbPersonnes < $menuChoisi->getNbPersonnesMin()) {
                $this->addFlash('error', 'Le nombre de personnes minimum est ' . $menuChoisi->getNbPersonnesMin());
                return $this->redirectToRoute('app_commande_new', ['menu_id' => $menuChoisi->getId()]);
            }

            // Calcul du prix
            $prixParPersonne = $menuChoisi->getPrix() / $menuChoisi->getNbPersonnesMin();
            $prixTotal = $prixParPersonne * $nbPersonnes;

            // Réduction 10% si nb_personnes >= min + 5
            if ($nbPersonnes >= $menuChoisi->getNbPersonnesMin() + 5) {
                $prixTotal = $prixTotal * 0.90;
            }

            // Frais de livraison
            $villeLivraison = $request->request->get('ville_livraison');
            $prixLivraison = 0;
            if (strtolower(trim($villeLivraison)) !== 'bordeaux') {
                $prixLivraison = 5.00;
            }

            $commande = new Commande();
            $commande->setUtilisateur($user);
            $commande->setMenu($menuChoisi);
            $commande->setNbPersonnes($nbPersonnes);
            $commande->setPrixMenu((string) $menuChoisi->getPrix());
            $commande->setPrixLivraison((string) $prixLivraison);
            $commande->setPrixTotal((string) ($prixTotal + $prixLivraison));
            $commande->setAdresseLivraison($request->request->get('adresse_livraison'));
            $commande->setVilleLivraison($villeLivraison);
            $commande->setDatePrestation(new \DateTime($request->request->get('date_prestation')));
            $commande->setHeureLivraison(new \DateTime($request->request->get('heure_livraison')));
            $commande->setStatut('en_attente');
            $commande->setPretMateriel(false);
            $commande->setMaterielRestitue(false);

            // Historique
            $historique = new HistoriqueStatut();
            $historique->setCommande($commande);
            $historique->setStatut('en_attente');
            $historique->setDateHeure(new \DateTime());
            $historique->setCommentaire('Commande créée');

            // Décrémente le stock
            $menuChoisi->setStock($menuChoisi->getStock() - 1);

            $em->persist($commande);
            $em->persist($historique);
            $em->flush();

            $this->addFlash('success', 'Votre commande a bien été enregistrée ! Numéro : ' . $commande->getNumeroCommande());
            return $this->redirectToRoute('app_home');
        }

        return $this->render('commande/new.html.twig', [
            'menu'  => $menu,
            'menus' => $menus,
            'user'  => $this->getUser(),
        ]);
    }
}