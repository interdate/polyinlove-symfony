<?php

namespace AppBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

class MobileQuickSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();
        for($i = 18; $i <= 120; $i++){
            $choices[(string) $i] = $i;
        }

        $builder->add('gender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
//            'label' => 'Gender', // 'מין',
            'choice_label' => 'name',
//            'multiple' => true,
            'placeholder' => 'Choose',
//            'expanded' => true,
            'empty_data'  => null,
            'required' => false
        ));

        $builder->add('region', EntityType::class, array(
            'class' => 'AppBundle:LocRegions',
            'label' => '* Region', //'* אזור מגורים',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data'  => null,
            'required' => false
        ));

        $builder->add('ageFrom', ChoiceType::class, array(
            'label' => 'Age', // 'גיל',
            'choices' => $choices,
            'placeholder' => 'Choose',// 'בחרו',
            'empty_data'  => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('ageTo', ChoiceType::class, array(
            'label' => 'To', //'עד',
            'choices' => $choices,
            'placeholder' => 'Choose',// 'בחרו',
            'empty_data'  => null,
            'mapped' => false,
            'required' => false,
        ));


        $builder->add('contactGender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
            'label' => 'That search',
            'choice_label' => 'name',
//            'multiple' => true,
//            'expanded' => true,
            'empty_data'  => null,
            'placeholder' => 'Choose',
            'required' => false
        ));


        $builder->add('lookingFor', EntityType::class, array(
            'class' => 'AppBundle:LookingFor',
            'label' => 'For',
            'choice_label' => 'name',
//            'multiple' => true,
//            'expanded' => true,
            'empty_data'  => null,
            'placeholder' => 'Choose',
            'required' => false
        ));

        $builder->add('username', TextType::class, array(
            'label' => 'Search by username',// 'חיפוש לפי כינוי',
            'required' => false,
            'empty_data' => ' ',

        ));

        $builder->add('filter', HiddenType::class, array(
            'data' => 'lastActivity',
            'mapped' => false,
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
        return 'mobileQuickSearch';
    }

}
