<?php

namespace App\Form;

use App\Entity\Annonce;
use App\Entity\Categorie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [
                'attr' => [
                    'placeholder' => 'Recherche via un mot clé...'
                ]
            ])
            // ->add('titre')
            // ->add('description')
            // ->add('dateCreation', null, [
            //     'widget' => 'single_text',
            // ])
            // ->add('ville')
            // ->add('prix')
            // ->add('categorie', EntityType::class, [
            //     'class' => Categorie::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('publier', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'data_class' => Annonce::class,
        ]);
    }
}
