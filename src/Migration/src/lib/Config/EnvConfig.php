<?php

namespace Configurations\Migration\Config;

class EnvConfig{
    protected $_defaults=[];
    function __construct($defaultsFile=null)
    {
        if($defaultsFile){
            $this->_defaults=parse_ini_file($defaultsFile);
        }
    }

    function __get($name)
    {
        $val=getenv($name);
        if($val === false){
            if(array_key_exists($name,$this->_defaults)){
                $val=$this->_defaults[$name];
            }
        }
        return $val;
    }
}