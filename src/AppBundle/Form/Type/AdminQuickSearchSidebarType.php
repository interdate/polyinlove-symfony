<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AdminQuickSearchSidebarType extends QuickSearchSidebarType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', TextType::class, array(
            'label' => 'user id',
            'attr' => array('placeholder' =>  'id'),
            'required' => false,
        ));

        $builder->add('email', TextType::class, array(
            'label' => 'email',
            'attr' => array('placeholder' =>  'email'),
            'required' => false,
        ));

        $builder->add('username', TextType::class, array(
            'label' => 'username',
            'attr' => array('placeholder' => 'username'),
            'required' => false,
        ));

        $builder->add('phone', TextType::class, array(
            'label' => 'phone number',
            'attr' => array('placeholder' => 'phone number'),
            'required' => false,
        ));

    }
}
