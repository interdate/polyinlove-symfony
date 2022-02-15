<?php

namespace AppBundle\Form\Type;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignUpFlow extends FormFlow
{
    public $disableToken = true;

    protected function loadStepsConfig()
    {

        return array(
            array(
                'label' => 'Create Account At {$this->getParameter('site_name')}',// 'יצירת חשבון בגרינדייט',
            	'form_type' => 'AppBundle\Form\Type\SignUpOneType',
               // 'form_type' => new SignUpOneType(),
                'form_options' => array(
                    'validation_groups' => array('sign_up_one'),
                     'csrf_protection' => $this->disableToken,
                ),
            ),
            array(
                'label' => 'Create Profile',//'יצירת פרופיל',
                'form_type' => 'AppBundle\Form\Type\SignUpTwoType',
            	'form_options' => array(
                    'validation_groups' => array('sign_up_two'),
                   'csrf_protection' => $this->disableToken,
                ),
            ),
            array(
                'label' => 'Create Profile',//'יצירת פרופיל',
                'form_type' => 'AppBundle\Form\Type\SignUpThreeType',
                'form_options' => array(
                    'validation_groups' => array('sign_up_three'),
                    'csrf_protection' => $this->disableToken,
                ),
            ),

        );
    }


    
    public function getName()
    {
        return 'signUp';
    }


    public function disableToken()
    {
        $this->disableToken = false;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
        ));
    }
}
