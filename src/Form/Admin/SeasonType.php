<?php

namespace App\Form\Admin;

use App\Entity\Driver;
use App\Entity\Season;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Name'
            ])
            ->add('worldChampion', EntityType::class, [
                'required' => false,
                'class' => Driver::class,
                'choice_label' => function (Driver $driver): string {
                    return $driver->getFirstName() . ' ' . $driver->getLastName();
                },
                'label' => 'Weltmeister'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Season::class,
        ]);
    }
}
