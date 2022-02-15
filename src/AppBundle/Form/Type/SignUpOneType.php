<?php

namespace AppBundle\Form\Type;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SignUpOneType extends AbstractType

{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('facebook', HiddenType::class, array(
           // 'empty_data' => 0,
            'required' => false
        ));
        $builder->add('username', TextType::class, array(
            'label' => '* Username', //'username',// '* שם משתמש',
            'required' => true,
        ));

        $builder->add('password', PasswordType::class, array(
            'label' => '* Password', //'* סיסמה',
        ));

        $builder->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'Passwords do not match',
            //'options' => array('attr' => array('class' => 'password-field')),
            'required' => true,
            'first_options'  => array('label' => '* Password'), //'* סיסמה'),
            'second_options' => array('label' => '* Password again'), //'* סיסמה בשנית'),
        ));

        $builder->add('email', TextType::class, array(
            'required' => true,
            'label' => '* Email (used for verification)', //'* אימייל',
        ));

        $builder->add('gender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
            'label' => '* Gender', //'* אני:',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //'בחרו',
            'empty_data'  => null,
            'attr' => array('class' => 'text-editor')
        ));

        $builder->add('phone', TextType::class, array(
            'label' => '* Phone ', //'* טלפון (לקבלת סמס להפעלת החשבון)',
            'required' => true,

        ));

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
        ));
    }


    public function getName()
    {
        return 'signUpOne';
    }

}
