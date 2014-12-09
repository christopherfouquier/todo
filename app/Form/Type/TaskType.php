<?php

namespace app\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => '* Tâche',
            'attr' => array(
                'class' => 'form-control',
                'placeholder' => 'Description de la tâche'
            ),
            'constraints' => array(
                new Assert\NotBlank()
            )
        ));
        $builder->add('created', 'text', array(
            'label' => '* Date',
            'attr' => array(
                'class' => 'form-control',
                'placeholder' => 'Date de rappel'
            ),
            'constraints' => array(
                new Assert\NotBlank()
            )
        ));
        /*$builder->add('categories', 'model', array(
            'class' => 'app\model\Categories',
            'property' => 'name',
        ));*/
    }

    public function getName()
    {
        return 'form_task';
    }
}