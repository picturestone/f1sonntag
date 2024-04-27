<?php

namespace App\Form\Admin;

use App\Entity\Race;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dateTimePickerOptions = [
            'model_timezone' => 'UTC',
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
        ];

        if ($options['view_timezone']) {
            $dateTimePickerOptions['view_timezone'] = $options['view_timezone'];
        }

        $builder
            ->add('name')
            ->add('place')
            ->add('startDateTime', DateTimeType::class, $dateTimePickerOptions)
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Race::class,
            'view_timezone' => null,
        ]);

        $resolver->setAllowedTypes('view_timezone', 'string');
    }
}
