<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class QuickSearchApiType extends QuickSearchType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('country', EntityType::class, array(
            'class' => 'AppBundle:LocCountries',
            'label' => '* Country', //'* אזור מגורים',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
        ));

        $builder->add('region', EntityType::class, array(
            'class' => 'AppBundle:LocRegions',
            'label' => '* Region', //'* עיר',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
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
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC')->setMaxResults(200);
            }
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
        return 'quickSearchApi';
    }

}
