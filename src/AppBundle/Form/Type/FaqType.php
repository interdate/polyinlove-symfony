<?php

namespace AppBundle\Form\Type;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'question',
        ));

        $builder->add('content', TextareaType::class, array(
            'label' => 'answer',
        ));

        $builder->add('category', EntityType::class, array(
            'class' => 'AppBundle:FaqCategory',
            'label' => 'category',
            'choice_label' => 'name',
            'placeholder' => 'choose',
            'empty_data'  => null,
        ));

        $builder->add('isActive', CheckboxType::class, array(
            'label' => 'active',
            'required' => false,
        ));

    }
    public function getName()
    {
        return 'faq';
    }

}
