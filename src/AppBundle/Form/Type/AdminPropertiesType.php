<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminPropertiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder->add('isSentEmail', CheckboxType::class, array(
    			'label' => 'push to email',
    			'required' => false,
    	));
    	
    	$builder->add('isSentPush', CheckboxType::class, array(
    			'label' => 'push to phone',
    			'required' => false,
    	));
    	
    	$builder->add('isOnHomepage', CheckboxType::class, array(
            'label' => 'user is on homepage',
            'required' => false,
        ));

        $builder->add('adminComments', TextareaType::class, array(
            'label' => 'comments',
            'required' => false,
        ));

        $builder->add('banReason', TextareaType::class, array(
            'label' => 'ban because',
            'required' => false,
        ));

        $builder->add('freezeReason', TextareaType::class, array(
            'label' => 'frozen because',
            'required' => false,
        ));
    }

    public function getName()
    {
        return 'adminProperties';
    }

}
