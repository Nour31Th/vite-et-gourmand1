<?php

namespace App\Controller;

use App\Entity\HistoriqueStatut;
use App\Repository\CommandeRepository;
use App\Repository\AvisRepository;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EMPLOYE')]
#[Route('/employe')]
class EmployeController extends AbstractController
{
    #[Route('', name: 'app_employe_dashboard')]
    public function dashboard(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findBy([], ['date_commande' => 'DESC']);

        return $this->render('employe/dashboard.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commande/{id}/statut', name: 'app_employe_update_statut', methods: ['POST'])]
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

        $nouveauStatut = $request->request->get('statut');
        $commentaire = $request->request->get('commentaire');
        $modeContact = $request->request->get('mode_contact');

        $commande->setStatut($nouveauStatut);

        $historique = new HistoriqueStatut();
        $historique->setCommande($commande);
        $historique->setStatut($nouveauStatut);
        $historique->setDateHeure(new \DateTime());
        $historique->setCommentaire($commentaire);
        $historique->setModeContact($modeContact);

        $em->persist($historique);
        $em->flush();

        $this->addFlash('success', 'Statut mis à jour avec succès.');
        return $this->redirectToRoute('app_employe_dashboard');
    }

    #[Route('/avis', name: 'app_employe_avis')]
    public function avis(AvisRepository $avisRepository): Response
    {
        $avis = $avisRepository->findBy(['valide' => false], ['date_avis' => 'DESC']);

        return $this->render('employe/avis.html.twig', [
            'avis' => $avis,
        ]);
    }

    #[Route('/avis/{id}/valider', name: 'app_employe_valider_avis', methods: ['POST'])]
    public function validerAvis(
        int $id,
        AvisRepository $avisRepository,
        EntityManagerInterface $em
    ): Response {
        $avi = $avisRepository->find($id);
        if ($avi) {
            $avi->setValide(true);
            $em->flush();
            $this->addFlash('success', 'Avis validé.');
        }
        return $this->redirectToRoute('app_employe_avis');
    }

    #[Route('/avis/{id}/refuser', name: 'app_employe_refuser_avis', methods: ['POST'])]
    public function refuserAvis(
        int $id,
        AvisRepository $avisRepository,
        EntityManagerInterface $em
    ): Response {
        $avi = $avisRepository->find($id);
        if ($avi) {
            $em->remove($avi);
            $em->flush();
            $this->addFlash('success', 'Avis refusé et supprimé.');
        }
        return $this->redirectToRoute('app_employe_avis');
    }
#[Route('/horaires', name: 'app_employe_horaires')]
public function horaires(HoraireRepository $horaireRepository): Response
{
    $horaires = $horaireRepository->findAll();
    return $this->render('employe/horaires.html.twig', [
        'horaires' => $horaires,
    ]);
}

#[Route('/horaires/{id}/modifier', name: 'app_employe_modifier_horaire', methods: ['POST'])]
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
    return $this->redirectToRoute('app_employe_horaires');
}
    }
