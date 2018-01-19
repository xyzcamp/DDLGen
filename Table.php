<?php

class Table
{
    protected $attributes;
    public $table_name;
    public $table_comments;
    public $field;
    public $pk;     // $pk[field_order] = field_name  
    public $uk;     // $uk[unique_key_number][field_order] = field_name
    public $index;  // $index[index_number][field_order] = field_name

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
