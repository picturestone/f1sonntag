<?php

namespace App\Form\Admin;

use App\Entity\Driver;
use App\Entity\Team;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DriverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, [
                'label' => 'Vorname'
            ])
            ->add('lastName', null, [
                'label' => 'Nachname'
            ])
            ->add('team', EntityType::class, [
                'required' => false,
                'class' => Team::class,
                'choice_label' => 'name',
                'label' => 'Team'
            ])
            ->add('isActive', null, [
                'label' => 'Ist aktiv?'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Driver::class,
        ]);
    }
}
