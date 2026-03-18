<?php

class Address extends AddressCore
{
    public function __construct($id_address = null, $id_lang = null)
    {
        self::$definition['fields']['phone']['required'] = true;

        parent::__construct($id_address, $id_lang);
    }
}
