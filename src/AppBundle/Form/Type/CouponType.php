<?php

namespace AppBundle\Form\Type;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'Title',
        ));

        $builder->add('code', TextType::class, array(
            'label' => 'code',
        ));

        $builder->add('value', TextType::class, array(
            'label' => 'value',
        ));

        $choices = array(
            'percentage' => 'percentage',
            'nominal' => 'nominal'
        );

        $builder->add('type', ChoiceType::class, array(
            'label' => 'type',
            'choices' => $choices,
            'placeholder' => 'choose',
        ));

        $builder->add('isActive', CheckboxType::class, array(
            'label' => 'active',
            'required' => false,
        ));

    }

    public function getName()
    {
        return 'coupon';
    }

}
