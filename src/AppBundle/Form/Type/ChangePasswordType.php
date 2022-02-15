<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;



class ChangePasswordType extends AbstractType
{
    public $disableToken = true;

    public function disableToken  ()
    {
        $this->disableToken = false;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('oldPassword', PasswordType::class, array('label' => '* Old password'));
        $builder->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'Incompatible passwords',
            'required' => true,
            'first_options'  => array('label' => '* New password'),
            'second_options' => array('label' => '* New password again'),
        ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('change_password'),
            'csrf_protection' => $this->disableToken,
            'translation_domain' => 'forms'
        ));
    }

    public function getName()
    {
        return 'changePassword';
    }

}
