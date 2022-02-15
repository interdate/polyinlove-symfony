<?php

namespace AppBundle\Form\Type;


use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SignUpApiType extends AbstractType

{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('facebook', HiddenType::class, array(
            // 'empty_data' => 0,
            'required' => false
        ));

        $builder->add('gender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
            'label' => '* Gender', //'* אני:',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //'בחרו',
            'empty_data'  => null,
            'attr' => array('class' => 'text-editor')
        ));

        $builder->add('username', TextType::class, array(
            'label' => '* Username', //'username',// '* שם משתמש',
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
            'label' => '* Email (used for account activation)', //'* אימייל',
        ));

        $builder->add('phone', TextType::class, array(
            'label' => '* Phone ', //'* טלפון (לקבלת סמס להפעלת החשבון)',
            'required' => true,
        ));

        $builder->add('about', TextareaType::class, array(
            'label' => 'About me/us *', // ' * מעט עליי/עלינו ',
            'required' => true
        ));

        $builder->add('looking', TextareaType::class, array(
            'label' => 'I/we are looking for *', // ' * מה אני/אנחנו מחפש/ים',
            'required' => true
        ));

        $builder->add('contactGender', EntityType::class, array(
            'class' => 'AppBundle:Gender',
            'label' => 'Can contact me', // 'רשאים לפנות אלינו',
            'choice_label' => 'name',
            'placeholder' => 'Choose',// 'בחרו',
            'empty_data'  => null,
            'multiple' => true,
            'expanded' => true,
        ));

        $choices = [];
        for($i = 18; $i <= 99; $i++) {
            $choices[$i] = $i;
        }
        $builder->add('ageTo', ChoiceType::class, array(
            'label' => 'Age range from -', // ' טווח גילאים מ -  ',
            'choices' => $choices,
            'placeholder' => 'Choose',// 'בחרו',
            'empty_data'  => null,
        ));

        $builder->add('ageFrom', ChoiceType::class, array(
            'label' => 'to -', // ' עד - ',
            'choices' => $choices,
            'placeholder' => 'Choose',// 'בחרו',
            'empty_data'  => null,
        ));
        $builder->add('agree', CheckboxType::class, array(
//            'label' => '', // ' אני מסכים/ה לתנאי השימוש באתר ',
            'mapped' => false,
//            'required' => true,
            'empty_data' => '0',
            'data' => false,
        ));

        $builder->add('birthday', BirthdayType::class, array(
            'label' => '* Birthday', //'* תאריך לידה',
            'years' => range(date('Y') - 18, date('Y') - 120),
            'placeholder' => array('year' => 'שנה', 'month' => 'חודש', 'day' => 'יום'),
            'empty_data'  => null,
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
                return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC')->setMaxResults(200);
            }
        ));

        $builder->add('zipCode', Type::TEXT, array(
            'label' => 'zipcode',
            'empty_data' => '',
            'required' => true,
        ));

        $i = 0;
        $height = 1.2;
        $choices = array();

        while( (float) $height <= 2.2){
            $choices[(string) $height] = (string) $height;
            $height = (float) $height + 0.01;
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
            'label' => 'Body type', //'* מבנה גוף',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data'  => null,
            'required' => false,
        ));

        $builder->add('relationshipStatus', EntityType::class, array(
            'class' => 'AppBundle:RelationshipStatus',
            'label' => '* Relationship status', //'* מצב משפחתי',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data'  => null,
        ));

        $builder->add('relationshipType', EntityType::class, array(
            'class' => 'AppBundle:RelationshipType',
            'label' => '* Relationship type',//'סוג מערכת היחסים שאני נמצא/ת בה',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data'  => null,
        ));

//        $builder->add('relationshipTypeDetails', TextType::class, array(
//            'label' => 'Details',
//            'required' => false,
//        ));

        $builder->add('sexOrientation', EntityType::class, array(
            'class' => 'AppBundle:SexOrientation',
            'label' => '* Sex orientation', //'* נטיה מינית',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data'  => null,

        ));

//        $builder->add('sexOrientationDetails', TextType::class, array(
//            'label' => 'Details',//'פירוט',
//            'required' => false,
//        ));

        $builder->add('lookingFor', EntityType::class, array(
            'class' => 'AppBundle:LookingFor',
            'label' => '* Looking for', //'* מה אני מחפש/ת',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data'  => null,
            'required' => false,
//            'mapped' => false,
            'multiple' => true,
            'expanded' => true,
        ));

//        $builder->add('lookingForDetails', TextType::class, array(
//            'label' => 'Details',
//            'required' => false,
//        ));

        $builder->add('origin', EntityType::class, array(
            'class' => 'AppBundle:Origin',
            'label' => 'Origin',// 'מוצא',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data'  => null,
            'required' => false,
        ));

        $builder->add('smoking', EntityType::class, array(
            'class' => 'AppBundle:Smoking',
            'label' => '* Smoking', //'* הרגלי עישון',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'empty_data'  => null,
        ));
    }


    public function getName()
    {
        return 'signUpApi';
    }

}
