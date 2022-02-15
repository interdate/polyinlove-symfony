<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminAdvancedSearchType extends AdvancedSearchType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('gender', EntityType::class, array(
        		'class' => 'AppBundle:Gender',
        		'label' =>'Gender',
        		'choice_label' => 'name',
        		'multiple' => true,
        		'expanded' => true,
        ));

        $builder->add('zodiac', EntityType::class, array(
            'class' => 'AppBundle:Zodiac',
            'label' =>'Zodiac',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('loginFrom', EntityType::class, array(
            'class' => 'AppBundle:LoginFrom',
            'label' => 'Last login from',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ));

        $builder->add('country', EntityType::class, array(
            'class' => 'AppBundle:LocCountries',
            'label' => '* Country', //'* אזור מגורים',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'required' => false,
            'empty_data' => null,
        ));

        $builder->add('region', EntityType::class, array(
            'class' => 'AppBundle:LocRegions',
            'label' => '* Region', //'* עיר',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
            'required' => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
            }
        ));

        $builder->add('city', EntityType::class, array(
            'class' => 'AppBundle:LocCities',
            'label' => '* City', //'* עיר',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
            'required' => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC')
                    ->setMaxResults(200);
            }

        ));

        /*Boolean Props*/

        $builder->add('isActive', ChoiceType::class, array(
            'label' => 'active',
            'choices'  => array(
                'select' => null,
                'true' => true,
                'false' => false,
            ),
            'choices_as_values' => true,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('isFrozen', ChoiceType::class, array(
            'label' => 'frozen',
            'choices'  => array(
                'select' => null,
                'true' => true,
                'false' => false,
            ),
            'choices_as_values' => true,
            'mapped' => false,
            'required' => false,
        ));


        $builder->add('isPhoto', ChoiceType::class, array(
            'label' => 'with photos',
            'choices'  => array(
                'select' => null,
                'true' => true,
                'false' => false,
            ),
            'choices_as_values' => true,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('isPaying', ChoiceType::class, array(
            'label' => 'subscribers',
            'choices'  => array(
                'select' => null,
                'true' => true,
                'false' => false,
            ),
            'choices_as_values' => true,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('hasPoints', ChoiceType::class, array(
            'label' => 'with points',
            'choices'  => array(
                'select' => null,
                'true' => true,
                'false' => false,
            ),
            'choices_as_values' => true,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('isPhone', ChoiceType::class, array(
            'label' => "with phone numbers",
            'choices'  => array(
                'select' => null,
                'true' => true,
                'false' => false,
            ),
            'choices_as_values' => true,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('isActivated', ChoiceType::class, array(
            'label' => 'activated accounts',
            'choices'  => array(
                'select' => null,
                'true' => true,
                'false' => false,
            ),
            'choices_as_values' => true,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('isVerify', ChoiceType::class, array(
            'label' => 'verified',
            'choices'  => array(
                'select' => null,
                'true' => true,
                'false' => false,
            ),
            'choices_as_values' => true,
            'mapped' => false,
            'required' => false,
        ));

        /* Date Props */

        $builder->add('startSubscriptionFrom', TextType::class, array(
            'label' => 'subscription start',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('startSubscriptionTo', TextType::class, array(
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('endSubscriptionFrom', TextType::class, array(
            'label' => 'subscription end',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('endSubscriptionTo', TextType::class, array(
            'mapped' => false,
            'required' => false,
        ));


        $builder->add('signUpFrom', TextType::class, array(
            'label' => 'joined on',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('signUpTo', TextType::class, array(
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('lastVisitedFrom', TextType::class, array(
            'label' => 'last visit',
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('lastVisitedTo', TextType::class, array(
            'mapped' => false,
            'required' => false,
        ));


        /* Other */

        $builder->add('ip', TextType::class, array(
            'label' => 'IP',
            'required' => false,
        ));

        $builder->add('isOnHomepage', ChoiceType::class, array(
            'label' => 'may show on homepage',
            'choices'  => array(
                'select' => null,
                'true' => true,
                'false' => false,
            ),
            'choices_as_values' => true,
            'mapped' => false,
            'required' => false,
        ));

    }
}
