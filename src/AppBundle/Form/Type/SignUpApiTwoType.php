<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;



class SignUpApiTwoType extends SignUpTwoType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('nutrition');
        $builder->remove('children');
        $builder->remove('religion');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('sign_up_two'),
            'translation_domain' => 'forms',
        ));
    }

    public function getName()
    {
        return 'SignUpApiTwo';
    }

}
