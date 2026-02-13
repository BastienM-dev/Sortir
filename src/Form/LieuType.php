<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du lieu',
                'attr' => [
                    'placeholder' => 'Ex: Bowling de Niort',
                    'maxlength' => 30,
                    'class' => 'form-control',
                ],
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 12 rue de la République',
                    'maxlength' => 30,
                    'class' => 'form-control',
                ],
            ])
            ->add('latitude', TextType::class, [
                'label' => 'Latitude',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 46.323426',
                    'class' => 'form-control',
                ],
            ])
            ->add('longitude', TextType::class, [
                'label' => 'Longitude',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: -0.464779',
                    'class' => 'form-control',
                ],
            ])
            ->add('ville', EntityType::class, [
                'label' => 'Ville',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'placeholder' => '-- Choisir une ville --',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}