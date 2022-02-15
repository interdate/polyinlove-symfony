<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProfileOneApiType extends SignUpApiOneType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('password');

        $builder->remove('gender');

        $builder->remove('agree');

        $builder->remove('birthday');

        $builder->remove('facebook');

//        $builder->remove('phone');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('profile_one'),
        ));
    }

//    public function getName()
//    {
//        return 'profileOne';
//    }

}
