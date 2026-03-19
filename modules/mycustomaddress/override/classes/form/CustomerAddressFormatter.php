<?php
class CustomerAddressFormatter extends CustomerAddressFormatterCore
{
    public function getFormat()
    {
        $format = parent::getFormat();

        if (isset($format['phone'])) {
            $format['phone']->setRequired(true);
        }
        if (isset($format['phone_mobile'])) {
            $format['phone_mobile']->setRequired(true);
        }

        return $format;
    }
}
