<?php

class Person {

    protected $keys = ['firstname', 'surname'];
    protected $data = null;

    protected function cleanup($data) {
        $result = array_intersect_key($data, array_flip($this->keys));
        return $result;
    }

    public function __construct($data) {
        $clean = $this->cleanup($data);
        $this->data = $clean;
    }

    public function toHash() {
        return $this->data;
    }

    
}
