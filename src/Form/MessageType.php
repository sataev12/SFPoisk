<?php

namespace App\Form;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu')
            ->add('destinataire', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'disabled' => true, // Rendre le champ non modifiable
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
