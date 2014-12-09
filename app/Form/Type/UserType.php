<?php

namespace app\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text', array(
            'label' => '* Identifiant',
            'attr' => array(
                'class' => 'form-control',
                'placeholder' => 'Identifiant'
            ),
            'constraints' => array(
                new Assert\NotBlank()
            )
        ));
        $builder->add('password', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'Les mots de passe doivent correspondre.',
            'options' => array('required' => true),
            'first_options'  => array(
                'label' => '* Mot de passe',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Mot de passe'
                ),
            ),
            'second_options' => array(
                'label' => '* Mot de passe (validation)',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Mot de passe (validation)'
                ),
            ),
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ));
        $builder->add('email', 'email', array(
            'label' => '* E-mail',
            'attr' => array(
                'class' => 'form-control',
                'placeholder' => 'E-mail'
            ),
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Email(),
            )
        ));
    }

    public function getName()
    {
        return 'form_user';
    }
}