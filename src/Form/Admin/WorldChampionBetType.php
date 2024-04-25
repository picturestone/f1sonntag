<?php

namespace App\Form\Admin;

use App\Entity\Driver;
use App\Entity\Season;
use App\Repository\DriverRepository;
use App\Repository\SeasonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class WorldChampionBetType extends AbstractType
{
    public function __construct(private readonly DriverRepository $driverRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $options =  [
            'class' => Driver::class,
            'choice_label' => 'name',
        ];

        $activeDrivers = $this->driverRepository->findBy(['isActive' => true]);
        if ($activeDrivers) {
            $options['data'] = $activeDrivers[0];
        }

        $builder
            ->add('driverId', EntityType::class, $options)
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ])
        ;
    }
}
