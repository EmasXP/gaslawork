<?php

namespace Gaslawork\Tests;

class Dummycontroller extends \Gaslawork\Controller {

    public function helloAction()
    {
        print "World!";
    }


    public function echoIdAction()
    {
        print $this->getParam("id");
    }


    public function silentAction()
    {

    }

}
