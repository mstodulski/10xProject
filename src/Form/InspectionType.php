<?php

namespace App\Form;

use App\Entity\Inspection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InspectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'inspection.start_date',
                'html5' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTimeImmutable())->format('Y-m-d'),
                ],
            ])
            ->add('startTime', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'inspection.start_time',
                'html5' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'step' => '900', // 15 minutes in seconds
                ],
            ])
            ->add('vehicleMake', TextType::class, [
                'label' => 'inspection.vehicle_make',
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 64,
                    'placeholder' => 'inspection.vehicle_make_placeholder',
                ],
            ])
            ->add('vehicleModel', TextType::class, [
                'label' => 'inspection.vehicle_model',
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 64,
                    'placeholder' => 'inspection.vehicle_model_placeholder',
                ],
            ])
            ->add('licensePlate', TextType::class, [
                'label' => 'inspection.license_plate',
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 20,
                    'placeholder' => 'inspection.license_plate_placeholder',
                ],
            ])
            ->add('clientName', TextType::class, [
                'label' => 'inspection.client_name',
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 64,
                    'placeholder' => 'inspection.client_name_placeholder',
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'inspection.phone_number',
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 20,
                    'placeholder' => 'inspection.phone_number_placeholder',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inspection::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'inspection';
    }
}
