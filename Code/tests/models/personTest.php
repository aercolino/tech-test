<?php

require_once( dirname(__FILE__, 3) . '/src/config.php' );
require_once(CONFIG::get('ABSPATH') . '/src/models/person.php'); 

class PersonTest extends PHPUnit_Framework_TestCase 
{

    public function testAPersonObjectIsCreated()
    {
        $data = array(
            'this' => 'is',
            'a' => 'sample',
            'firstname' => 'Donald',
            'surname' => 'Duck',
        );
        $person = new Person($data);
        $this->assertEquals($person->toHash(), array(
            'firstname' => 'Donald',
            'surname' => 'Duck',
        ));
    }

    public function testAPersonIsStrigified()
    {
        $data = array(
            'firstname' => 'Donald',
            'surname' => 'Duck',
        );
        $person = new Person($data);
        $this->assertEquals("$person", 'Donald Duck');
    }

    
}
