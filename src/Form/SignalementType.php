<?php

namespace App\Form;

use App\Entity\Signalement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SignalementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Spam' => 'spam',
                    'Contenu inapproprié' => 'inappropriate',
                    'Fraude' => 'fraude',
                    'Autre' => 'other',
                ],
                'label' => 'Type de signalement'
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
            ]);
            // ->add('annonce', HiddenType::class, [
            //     'data' => $options['annonce_id'],
            // ])
            // ->add('user', HiddenType::class, [
            //     'data' => $options['user_id'],
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Signalement::class,
            'annonce_id' => null,
            'user_id' => null,

        ]);
    }
}

