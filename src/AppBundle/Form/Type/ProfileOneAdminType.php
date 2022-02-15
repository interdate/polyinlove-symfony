<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProfileOneAdminType extends SignUpOneType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('password');

        $builder->remove('agree');


        $builder->add('birthday', BirthdayType::class, array(
            'label' => '', //'* תאריך לידה',
            'years' => range(date('Y') - 18, date('Y') - 120),
            'placeholder' => array('year' => 'year', 'month' => 'month', 'day' => 'day'),
            'empty_data'  => null,
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('profile_one'),
        ));
    }

    public function getName()
    {
        return 'profileOne';
    }

}
