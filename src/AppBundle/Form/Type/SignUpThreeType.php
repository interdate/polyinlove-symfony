<?php

namespace AppBundle\Form\Type;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SignUpThreeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('about', TextareaType::class, array(
            'label' => 'About me/us *', // ' * מעט עליי/עלינו ',
            'required' => true
        ));

        $builder->add('looking', TextareaType::class, array(
            'label' => 'I/we are searching for *', // ' * מה אני/אנחנו מחפש/ים',
            'required' => true
        ));

        $builder->add('religion', EntityType::class, array(
            'class' => 'AppBundle:Religion',
            'label' => 'religion', //'* אזור מגורים',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'required' => false,
            'empty_data'  => null,
        ));

        $builder->add('children', EntityType::class, array(
            'class' => 'AppBundle:Children',
            'label' => 'children', //'* אזור מגורים',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'required' => false,
            'empty_data'  => null,
        ));


        $builder->add('nutrition', EntityType::class, array(
            'class' => 'AppBundle:Nutrition',
            'label' => 'dietary choices', //'* אזור מגורים',
            'choice_label' => 'name',
            'placeholder' => 'Choose', //בחרו',
            'required' => false,
            'empty_data'  => null,
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

        $builder->add('ageFrom', ChoiceType::class, array(
            'label' => 'Age range from -', // ' עד - ',
            'choices' => $choices,
            'placeholder' => 'Choose',// 'בחרו',
            'empty_data'  => null,
        ));

        $builder->add('ageTo', ChoiceType::class, array(
            'label' => 'To -', // ' טווח גילאים מ -  ',
            'choices' => $choices,
            'placeholder' => 'Choose',// 'בחרו',
            'empty_data'  => null,
        ));

        $builder->add('agree', CheckboxType::class, array(
//            'label' => '', // ' אני מסכים/ה לתנאי השימוש באתר ',
//            'mapped' => false,
            'required' => true,
            'empty_data' => '0',
            'data' => false,
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
