<?php

namespace AppBundle\Form\Type;


use AppBundle\AppBundle;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignUpTwoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('birthday', BirthdayType::class, array(
            'label' => '* Birthday', //'* תאריך לידה',
            'years' => range(date('Y') - 18, date('Y') - 120),
            'placeholder' => array('year' => 'year', 'month' => 'month', 'day' => 'day'),
            'empty_data' => null,
        ));

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
                return $er->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC')
                    ->setMaxResults(200);
            }
        ));

        $builder->add('zipCode', Type::TEXT, array(
            'label' => 'zipcode',
            'empty_data' => '',
            'required' => false
        ));

        $i = 0;
        $height = 40;
        $choices = array();

        while ((float)$height <= 230) {
            $choices[(string)$height] = (string)$height;
            $height = (float)$height + 1;
            $i++;
        }

        $builder->add('height', IntegerType::class, array(
            'label' => '* Height (cm)', //'* גובה',
//            'choices' => $choices,
//            'placeholder' => 'Choose', //בחרו',
            'empty_data' => 160,
            'attr' => array('min' => 40, 'max' => 230),
        ));

        $builder->add('body', EntityType::class, array(
            'class' => 'AppBundle:Body',
            'label' => 'Body', //'* מבנה גוף',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
            'required' => false,
        ));

        $builder->add('relationshipStatus', EntityType::class, array(
            'class' => 'AppBundle:RelationshipStatus',
            'label' => '* Relationship status', //'* מצב משפחתי',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
        ));

        $builder->add('relationshipType', EntityType::class, array(
            'class' => 'AppBundle:RelationshipType',
            'label' => '* Relationship type',//'סוג מערכת היחסים שאני נמצא/ת בה',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
        ));

        $builder->add('sexOrientation', EntityType::class, array(
            'class' => 'AppBundle:SexOrientation',
            'label' => '* Sex orientation', //'* נטיה מינית',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
            'required' => true,

        ));

        $builder->add('lookingFor', EntityType::class, array(
            'class' => 'AppBundle:LookingFor',
            'label' => '* Looking for', //'* מה אני מחפש/ת',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
//            'mapped' => false,
            'required' => false,
            'multiple' => true,
            'expanded' => true,
        ));


        $builder->add('origin', EntityType::class, array(
            'class' => 'AppBundle:Origin',
            'label' => 'Origin',// 'מוצא',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
            'required' => false,
        ));

        $builder->add('smoking', EntityType::class, array(
            'class' => 'AppBundle:Smoking',
            'label' => '* Smoking', //'* הרגלי עישון',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data' => null,
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
        return 'signUpTwo';
    }

}
