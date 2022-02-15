<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BannerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name', TextType::class, array(
            'label' => 'name',
            'required' => true,
        ));


        $builder->add('img', 'file', array(
            'label' => 'image',
            'data_class' => null,
            'required' => false,
        ));

        $builder->add('href', TextType::class, array(
            'label' => 'link',
            'required' => true,
        ));

        $builder->add('isActive', CheckboxType::class, array(
            'label' => 'active',
            'required' => false,
        ));

        $builder->add('beforeLogin', CheckboxType::class, array(
            'label' => 'before login',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('afterLogin', CheckboxType::class, array(
            'label' => 'after login',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('subscriptionPage', CheckboxType::class, array(
            'label' => 'purchase subscription',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('profileBottom', CheckboxType::class, array(
            'label' => 'profile- under image',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('profileTop', CheckboxType::class, array(
            'label' => 'profile - over image',
            'required' => false,
//            'empty_data' => true,
        ));
        $builder->add('mobile', CheckboxType::class, array(
            'label' => 'application',
            'required' => false,
//            'empty_data' => true,
        ));



        $builder->add('mans', CheckboxType::class, array(
            'label' => 'Men',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('womans', CheckboxType::class, array(
            'label' => 'Women',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('abinary', CheckboxType::class, array(
            'label' => 'Non binary',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('pay', CheckboxType::class, array(
            'label' => 'paying members',
            'required' => false,
//            'empty_data' => true,
        ));

        $builder->add('notPay', CheckboxType::class, array(
            'label' => 'non-paying members',
            'required' => false,
//            'empty_data' => true,
        ));


    }
    public function getName()
    {
        return 'banner';
    }

}
