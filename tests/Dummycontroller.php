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


    public function echoDependencyAction()
    {
        $id = $this->getParam("id");
        print $this->get($id);
    }


    public function echoDependencyAgainAction()
    {
        $id = $this->getParam("id");
        print $this->$id;
    }

}
