<?php

require_once( dirname(__FILE__, 3) . '/src/config.php' );
require_once(CONFIG::get('ABSPATH') . '/src/models/storage.php'); 

class StorageTest extends PHPUnit_Framework_TestCase
{
    public function testWhatYouWriteIsReadBack()
    {
        $data = array(
            'this' => 'is', 
            'a' => 'sample'
        );
        Storage::write($data);
        $read = Storage::read();
        $this->assertEquals($read, $data);  // Actual, Expected <-- alphabetic order
    }
}
