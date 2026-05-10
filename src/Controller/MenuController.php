<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class MenuController extends AbstractController
{
    #[Route('/menus', name: 'app_menu_index')]
    public function index(MenuRepository $menuRepository): Response
    {
        $menus = $menuRepository->findBy(['actif' => true]);

        return $this->render('menu/index.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('/menus/{id}', name: 'app_menu_show')]
    public function show(int $id, MenuRepository $menuRepository): Response
    {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            throw $this->createNotFoundException('Menu non trouvé');
        }

        return $this->render('menu/show.html.twig', [
            'menu' => $menu,
        ]);
    }

    #[Route('/menus/filter', name: 'app_menu_filter', methods: ['GET'])]
    public function filter(Request $request, MenuRepository $menuRepository): JsonResponse
    {
        $prixMax    = $request->query->get('prix_max');
        $prixMin    = $request->query->get('prix_min');
        $theme      = $request->query->get('theme');
        $regime     = $request->query->get('regime');
        $nbPersonnes = $request->query->get('nb_personnes');

        $menus = $menuRepository->findWithFilters(
            $prixMax, $prixMin, $theme, $regime, $nbPersonnes
        );

        $data = array_map(function($menu) {
            return [
                'id'              => $menu->getId(),
                'titre'           => $menu->getTitre(),
                'description'     => $menu->getDescription(),
                'prix'            => $menu->getPrix(),
                'nb_personnes_min' => $menu->getNbPersonnesMin(),
                'theme'           => $menu->getTheme(),
                'regime'          => $menu->getRegime(),
            ];
        }, $menus);

        return new JsonResponse($data);
    }
}
