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
        $datePickerOptions = [
            'widget' => 'single_text',
            'model_timezone' => 'UTC',
        ];

        if ($options['view_timezone']) {
            $datePickerOptions['view_timezone'] = $options['view_timezone'];
        }

        $timePickerOptions = $datePickerOptions;
        $timePickerOptions['reference_date'] = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $builder
            ->add('name')
            ->add('place')
            ->add('startDate', DateType::class, $datePickerOptions)
            ->add('startTime', TimeType::class, $timePickerOptions)
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
