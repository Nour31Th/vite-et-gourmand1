<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\HistoriqueStatut;
use App\Repository\CommandeRepository;
use App\Repository\AvisRepository;
use App\Repository\UserRepository;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function dashboard(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findBy([], ['date_commande' => 'DESC']);

        return $this->render('admin/dashboard.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/employes', name: 'app_admin_employes')]
    public function employes(UserRepository $userRepository): Response
    {
        $employes = $userRepository->findByRole('ROLE_EMPLOYE');

        return $this->render('admin/employes.html.twig', [
            'employes' => $employes,
        ]);
    }

    #[Route('/employe/creer', name: 'app_admin_creer_employe', methods: ['GET', 'POST'])]
    public function creerEmploye(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if ($request->isMethod('POST')) {
            $employe = new User();
            $employe->setEmail($request->request->get('email'));
            $employe->setNom($request->request->get('nom'));
            $employe->setPrenom($request->request->get('prenom'));
            $employe->setRoles(['ROLE_EMPLOYE']);
            $employe->setActif(true);
            $motDePasse = $request->request->get('password');
            $employe->setPassword($hasher->hashPassword($employe, $motDePasse));

            $em->persist($employe);
            $em->flush();

            $this->addFlash('success', 'Compte employé créé. Le mot de passe doit être communiqué en main propre.');
            return $this->redirectToRoute('app_admin_employes');
        }

        return $this->render('admin/creer_employe.html.twig');
    }

    #[Route('/employe/{id}/toggle', name: 'app_admin_toggle_employe', methods: ['POST'])]
    public function toggleEmploye(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $employe = $userRepository->find($id);
        if ($employe) {
            $employe->setActif(!$employe->isActif());
            $em->flush();
            $this->addFlash('success', 'Compte employé mis à jour.');
        }
        return $this->redirectToRoute('app_admin_employes');
    }

    #[Route('/stats', name: 'app_admin_stats')]
    public function stats(CommandeRepository $commandeRepository): Response
    {
        $statsParMenu = $commandeRepository->getStatsParMenu();

        return $this->render('admin/stats.html.twig', [
            'stats' => $statsParMenu,
        ]);
    }

    #[Route('/commande/{id}/statut', name: 'app_admin_update_statut', methods: ['POST'])]
    public function updateStatut(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        CommandeRepository $commandeRepository
    ): Response {
        $commande = $commandeRepository->find($id);
        if (!$commande) {
            throw $this->createNotFoundException();
        }

        $commande->setStatut($request->request->get('statut'));

        $historique = new HistoriqueStatut();
        $historique->setCommande($commande);
        $historique->setStatut($request->request->get('statut'));
        $historique->setDateHeure(new \DateTime());
        $historique->setCommentaire($request->request->get('commentaire'));
        $historique->setModeContact($request->request->get('mode_contact'));

        $em->persist($historique);
        $em->flush();

        $this->addFlash('success', 'Statut mis à jour.');
        return $this->redirectToRoute('app_admin_dashboard');
    }
#[Route('/avis', name: 'app_admin_avis')]
public function avis(AvisRepository $avisRepository): Response
{
    $avis = $avisRepository->findBy(['valide' => false], ['date_avis' => 'DESC']);

    return $this->render('admin/avis.html.twig', [
        'avis' => $avis,
    ]);
}

#[Route('/avis/{id}/valider', name: 'app_admin_valider_avis', methods: ['POST'])]
public function validerAvis(int $id, AvisRepository $avisRepository, EntityManagerInterface $em): Response
{
    $avi = $avisRepository->find($id);
    if ($avi) {
        $avi->setValide(true);
        $em->flush();
        $this->addFlash('success', 'Avis validé.');
    }
    return $this->redirectToRoute('app_admin_avis');
}

#[Route('/avis/{id}/refuser', name: 'app_admin_refuser_avis', methods: ['POST'])]
public function refuserAvis(int $id, AvisRepository $avisRepository, EntityManagerInterface $em): Response
{
    $avi = $avisRepository->find($id);
    if ($avi) {
        $em->remove($avi);
        $em->flush();
        $this->addFlash('success', 'Avis refusé.');
    }
    return $this->redirectToRoute('app_admin_avis');
}
#[Route('/horaires', name: 'app_admin_horaires')]
public function horaires(HoraireRepository $horaireRepository): Response
{
    $horaires = $horaireRepository->findAll();
    return $this->render('admin/horaires.html.twig', [
        'horaires' => $horaires,
    ]);
}

#[Route('/horaires/{id}/modifier', name: 'app_admin_modifier_horaire', methods: ['POST'])]
public function modifierHoraire(
    int $id,
    Request $request,
    HoraireRepository $horaireRepository,
    EntityManagerInterface $em
): Response {
    $horaire = $horaireRepository->find($id);
    if ($horaire) {
        $ferme = $request->request->get('ferme');
        if ($ferme) {
            $horaire->setHeureOuverture(new \DateTime('00:00'));
            $horaire->setHeureFermeture(new \DateTime('00:00'));
        } else {
            $horaire->setHeureOuverture(new \DateTime($request->request->get('ouverture')));
            $horaire->setHeureFermeture(new \DateTime($request->request->get('fermeture')));
        }
        $em->flush();
        $this->addFlash('success', 'Horaire mis à jour.');
    }
    return $this->redirectToRoute('app_admin_horaires');
}   
}
