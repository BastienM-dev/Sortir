<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date heure début',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date limite d\'inscription',
                'attr' => ['class' => 'form-control']
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre d\'inscription max',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('duree', IntegerType::class, [
                'required' => false,
                'label' => 'Durée',
                'attr' => ['class' => 'form-control']
            ])
            ->add('infosSortie', TextareaType::class, [
                'required' => false,
                'label' => 'Description et infos',
                'attr' => ['class' => 'form-control', 'rows' => 6],
            ])
            ->add('urlPhoto', TextType::class, [
                'required' => false,
            ])

            // ✅ Upload photo (tâche 22)
            ->add('photo', FileType::class, [
                'label' => 'Photo (JPG/PNG/WebP)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '4M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Merci d’uploader une image valide (jpg, png, webp).',
                    ])
                ],
            ])

            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'mapped' => false,
                'required' => false,
                'label' => 'Ville',
                'placeholder' => 'Choisissez une ville',
                'attr' => ['class' => 'form-control']
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label' => 'Lieu',
                'placeholder' => 'Choisissez d\'abord une ville',
                'attr' => ['class' => 'form-control']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer (En création)',
            ])
            ->add('publish', SubmitType::class, [
                'label' => 'Publier (Ouverte)',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
