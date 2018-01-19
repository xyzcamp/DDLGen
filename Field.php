<?php

class Field
{
    protected $attributes;
    public $table_name;
    public $field_name;
    public $data_type;
    public $field_comments;
    public $not_null;
    public $default;
    public $more;
    public $pk;
    public $uk;
    public $index;


    public function __construct()
    {

    }

    public function __get($key)
    {
        return $this->attributes[$key];
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

}
