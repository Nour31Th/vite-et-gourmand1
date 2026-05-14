<?php

namespace App\Twig;

use App\Repository\HoraireRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class HoraireExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private HoraireRepository $horaireRepository
    ) {}

    public function getGlobals(): array
    {
        return [
            'horaires_footer' => $this->horaireRepository->findAll(),
        ];
    }
}
