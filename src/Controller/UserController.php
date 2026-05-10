<?php

namespace App\Controller;

use App\Entity\HistoriqueStatut;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_user_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'commandes' => $user->getCommandes(),
        ]);
    }

    #[Route('/mon-compte/profil', name: 'app_user_profil')]
    public function profil(): Response
    {
        return $this->render('user/profil.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/mon-compte/commande/{id}/annuler', name: 'app_user_annuler_commande')]
    public function annulerCommande(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $commande = $em->getRepository(\App\Entity\Commande::class)->find($id);

        if (!$commande || $commande->getUtilisateur() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($commande->getStatut() === 'en_attente') {
            $commande->setStatut('annulee');
            $historique = new HistoriqueStatut();
            $historique->setCommande($commande);
            $historique->setStatut('annulee');
            $historique->setDateHeure(new \DateTime());
            $historique->setCommentaire('Annulée par le client');
            $em->persist($historique);
            $em->flush();
            $this->addFlash('success', 'Commande annulée avec succès.');
        }

        return $this->redirectToRoute('app_user_dashboard');
    }

    #[Route('/mon-compte/commande/{id}/avis', name: 'app_user_avis')]
    public function laisserAvis(int $id): Response
    {
        return $this->render('user/avis.html.twig', [
            'commande_id' => $id,
        ]);
    }
}

