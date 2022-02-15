<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\loc_countries;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class QuickSearchSidebarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();
        for ($i = 18; $i <= 120; $i++) {
            $choices[(string)$i] = $i;
        }

        $builder->add('username', TextType::class, array(
            'label' => 'Username',// 'חיפוש לפי כינוי',
            'required' => false,
        ));

        $builder->add('gender', 'entity', array(
            'class' => 'AppBundle:Gender',
            'label' => 'Gender', // 'מין',
            'choice_label' => 'name',
            'multiple' => true,
            //  'placeholder' => 'Choose',
            'expanded' => true,
            'empty_data' => null,
            'required' => false
        ));

        $builder->add('ageFrom', 'choice', array(
            'label' => 'from', // 'גיל',
            'choices' => $choices,
//            'placeholder' => 'Choose',// 'בחרו',
            'empty_data' => null,
            'mapped' => false,
            'required' => false,
        ));

        $builder->add('ageTo', 'choice', array(
            'label' => 'To', //'עד',
            'choices' => $choices,
//            'placeholder' => 'Choose',// 'בחרו',
            'empty_data' => null,
            'mapped' => false,
            'required' => false,
        ));

//        $builder->add('country', EntityType::class, array(
//            'class' => 'AppBundle:LocCountries',
//            'label' => 'Country', //'* עיר',
//            'choice_label' => 'name',
////            'placeholder' => 'Choose', //בחרו',
//            'expanded' => true,
//            'empty_data' => null,
//            'query_builder' => function (EntityRepository $er) {
//                return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
//            }
//        ));
//
//        $formModifier = function (FormInterface $form, loc_countries $country = null) {
////            $positions = null === $sport ? [] : $sport->getAvailablePositions();
////            dump($form, $country);
//            $query = function (EntityRepository $er) use ($country) {
//                if (is_null($country)) {
//                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC')->setMaxResults(200);
//                }
//                return $er->createQueryBuilder('c')
//                    ->where('c.country = :country')
//                    ->orderBy('c.name', 'ASC')
//                    ->setParameter('country', $country->getId());
//            };
//
//            $form->add('region', EntityType::class, array(
//                'class' => 'AppBundle:LocRegions',
//                'label' => 'Region', //'* עיר',
//                'choice_label' => 'name',
//                'placeholder' => 'Choose', //בחרו',
//                'empty_data' => null,
//                'query_builder' => $query,
//            ));
//        };
//
//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) use ($formModifier) {
////                dump($event);
//                $data = $event->getData();
//                $formModifier($event->getForm(), $data->getCountry);
//            }
//        );
//
//
//        $builder->add('city', EntityType::class, array(
//            'class' => 'AppBundle:LocCities',
//            'label' => 'City', //'* עיר',
//            'choice_label' => 'name',
//            'placeholder' => 'Choose', //בחרו',
//            'empty_data' => null,
//            'query_builder' => function (EntityRepository $er) {
//                return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC')->setMaxResults(200);
//            }
//        ));

        $builder->add('lookingFor', 'entity', array(
            'class' => 'AppBundle:LookingFor',
            'label' => 'For',
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ));

        $builder->add('filter', 'hidden', array(
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
        return 'quickSearchSidebar';
    }

}
