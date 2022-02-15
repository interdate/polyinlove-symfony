<?php

namespace AppBundle\Form\Type;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class SignUpApiFlow extends FormFlow
{
    public $disableToken = false;

    protected function loadStepsConfig()
    {
        return array(
            array(
                'label' => 'sign up to {$this->getParameter('site_name')}',
            	'form_type' => new SignUpApiType(),
               // 'form_type' => new SignUpOneType(),
                'form_options' => array(
                    'validation_groups' => array(),
                    'csrf_protection' => $this->disableToken,
                ),
            ),
            array(
                'label' => 'new profile',
                'form_type' => new SignUpApiType(),
            	'form_options' => array(
            	    'validation_groups' => array('sign_up_two'),
                    'csrf_protection' => $this->disableToken,
                ),
            ),
            array(
                'label' => 'new profile',
                'form_type' => new SignUpApiType(),
                'form_options' => array(
                    'validation_groups' => array('sign_up_three'),
                    'csrf_protection' => $this->disableToken,
                ),
            ),

        );
    }

   /* public function getFormOptions($step, array $options = array()) {
    	$options = parent::getFormOptions($step, $options);
    	if ($step === 2 and is_object($this->getFormData()->getGender())) {
    		$options['is_male'] = $this->getFormData()->getGender()->getId() == 1 ? true : false;
    	}
    	return $options;
    }
    */
    
    public function getName()
    {
        return 'signUpApi';
    }


    public function disableToken()
    {
        $this->disableToken = false;
    }
}
