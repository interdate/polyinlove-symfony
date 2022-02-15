<?php

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FaqCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'name',
        ));

        $builder->add('isActive', CheckboxType::class, array(
            'label' => 'active',
            'required' => false,
        ));
    }
    public function getName()
    {
        return 'faqCategory';
    }

}
