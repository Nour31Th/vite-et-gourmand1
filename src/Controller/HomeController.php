<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use App\Repository\HoraireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        AvisRepository $avisRepository,
        HoraireRepository $horaireRepository
    ): Response {
        $avis = $avisRepository->findBy(['valide' => true], ['date_avis' => 'DESC'], 4);
        $horaires = $horaireRepository->findAll();

        return $this->render('home/index.html.twig', [
            'avis' => $avis,
            'horaires' => $horaires,
        ]);
    }
}
