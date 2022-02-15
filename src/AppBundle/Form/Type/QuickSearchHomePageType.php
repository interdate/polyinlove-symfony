<?php

namespace AppBundle\Form\Type;
//use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class QuickSearchHomePageType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder, $options);

        $builder->add('gender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
            'choice_label' => 'name',
            'placeholder' => '',
        ));



        $builder->add('contactGender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
            'choice_label' => 'name',
            'placeholder' => '',
        ));

        $builder->add('lookingFor', EntityType::class, array(
            'class' => 'AppBundle:LookingFor',
            'choice_label' => 'name',
            'placeholder' => '',
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
        return 'quickSearchHomePage';
    }

}
