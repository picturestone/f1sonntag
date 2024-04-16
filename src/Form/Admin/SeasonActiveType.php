<?php

namespace App\Form\Admin;

use App\Entity\Season;
use App\Repository\SeasonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class SeasonActiveType extends AbstractType
{
    public function __construct(private readonly SeasonRepository $seasonRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $options =  [
            'class' => Season::class,
            'choice_label' => 'name',
        ];

        $activeSeasons = $this->seasonRepository->findBy(['isActive' => true]);
        if ($activeSeasons) {
            $options['data'] = $activeSeasons[0];
        }

        $builder
            ->add('activeSeasonId', EntityType::class, $options)
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ])
        ;
    }
}
