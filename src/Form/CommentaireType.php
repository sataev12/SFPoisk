<?php

namespace App\Form;

use App\Entity\Annonce;
use App\Entity\Commentaire;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextareaType::class)
            // ->add('dateCreation', null, [
            //     'widget' => 'single_text',
            // ])
            // ->add('annonce', EntityType::class, [
            //     'class' => Annonce::class,
            //     'choice_label' => 'id',
            // ])
            ->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}
