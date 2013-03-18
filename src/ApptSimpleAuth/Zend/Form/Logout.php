<?php
namespace ApptSimpleAuth\Zend\Form;

use ApptSimpleAuth\Zend\Form\AbstractForm;

class Logout extends AbstractForm
{
    public function init()
    {
        if ( $this->isInit ) {
            return;
        }

        parent::init();

        $this->get('submit')->setValue('Logout');
    }

    public function __construct($name = null, $options = array())
    {
        $name = ! $name ? 'logout' : $name;
        parent::__construct($name, $options);
    }
}