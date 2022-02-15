<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProfileThreeType extends SignUpThreeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('agree');

//        $builder->remove('contactGender');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('profile_three'),
            'translation_domain' => 'forms',
        ));
    }

    public function getName()
    {
        return 'profileThree';
    }

}
