<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Annonce;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as SymfonySearchType;

class RechercheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('keyword', SymfonySearchType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Recherche...',
                ],
                'required' => false, 
            ])
            ->add('ville', SymfonySearchType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Ville...',
                ],
                'required' => false,
            ])
            ->add('minPrix', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Prix minimum',
                ],
                'required' => false,
            ])
            ->add('maxPrix', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Prix maximum',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
    
        ]);
    }
}
