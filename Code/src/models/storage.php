<?php

class Storage {
    
    static protected $filename = '/storage.txt';


    static public function write($data) {
        $filename = CONFIG::get('DATAPATH') . self::$filename;
        $serialized = serialize($data);
        file_put_contents($filename, $serialized);
    }


    static public function read() {
        $filename = CONFIG::get('DATAPATH') . self::$filename;
        if (! file_exists($filename)) {
            return null;
        }
        $serialized = file_get_contents(CONFIG::get('DATAPATH') . self::$filename);
        $data = unserialize($serialized);
        return $data;
    }

}
