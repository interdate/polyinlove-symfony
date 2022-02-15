<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class AdvancedSearchType extends QuickSearchSidebarType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username');

//        $builder->add('region', 'entity', array(
//            'class' => 'AppBundle:Region',
//            'label' => 'אזור מגורים',
//            'choice_label' => 'name',
//            'multiple' => true,
//            'expanded' => true,
//        ));

        $i = 0;
        $height = 1.2;
        $choices = array();

        while( (float) $height <= 2.2){
            $choices[(string) $height] = (string) $height;
            $height = (float) $height + 0.01;
            $i++;
        }

        $builder->add('heightFrom', 'choice', array(
            'choices' => $choices,
            'placeholder' => 'Choose',
            'empty_data'  => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('heightTo', 'choice', array(
            'choices' => $choices,
            'placeholder' => 'Choose',
            'empty_data'  => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('body', 'entity', array(
            'class' => 'AppBundle:Body',
            'label' => 'Body',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('relationshipStatus', 'entity', array(
            'class' => 'AppBundle:RelationshipStatus',
            'label' => 'Relationship status',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('relationshipType', 'entity', array(
            'class' => 'AppBundle:RelationshipType',
            'label' => 'Relationship type',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('origin', 'entity', array(
            'class' => 'AppBundle:Origin',
            'label' => 'Origin',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('zodiac', 'entity', array(
            'class' => 'AppBundle:Zodiac',
            'label' => 'Zodiac',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('lookingFor', 'entity', array(
            'class' => 'AppBundle:LookingFor',
            'label' => 'There for',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('sexOrientation', 'entity', array(
            'class' => 'AppBundle:SexOrientation',
            'label' => 'Sex orientation',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('smoking', 'entity', array(
            'class' => 'AppBundle:Smoking',
            'label' => 'Smoking',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

//        $builder->add('city', 'entity', array(
//            'class' => 'AppBundle:City',
//            'label' => ' עיר', //'* עיר',
//            'choice_label' => 'name',
//            'placeholder' => 'Choose', //בחרו',
//            'empty_data'  => null,
//            'multiple' => true,
//            'expanded' => true,
//        ));

        $builder->add('religion', 'entity', array(
            'class' => 'AppBundle:Religion',
            'label' => 'religion', //'* אזור מגורים',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'required' => false,
            'empty_data'  => null,
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('children', 'entity', array(
            'class' => 'AppBundle:Children',
            'label' => 'children', //'* אזור מגורים',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'required' => false,
            'empty_data'  => null,
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('nutrition', 'entity', array(
            'class' => 'AppBundle:Nutrition',
            'label' => 'vegan?', //'* אזור מגורים',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'required' => false,
            'empty_data'  => null,
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('withPhoto', CheckboxType::class, array(
            'label' => 'With photo',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('zipCode', TextType::class, array(
            'label' => 'zipcode',
            'mapped' => false,
            'required' => false,
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
        return 'advancedSearch';
    }

}
