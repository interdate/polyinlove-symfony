<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProfileOneType extends SignUpOneType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('password');

        $builder->remove('gender');

        $builder->remove('agree');

        $builder->remove('phone');

        $builder->remove('facebook');

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('profile_one'),
            'translation_domain' => 'forms',
        ));
    }

    public function getName()
    {
        return 'profileOne';
    }

}
