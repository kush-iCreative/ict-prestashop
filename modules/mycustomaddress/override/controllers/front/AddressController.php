<?php

class AddressController extends AddressControllerCore
{
    public function postProcess(): void
    {
        $this->context->smarty->assign('editing', true);
        if (Tools::isSubmit('submitAddress')) {

            if (empty($phone) && empty($phone_mobile)) {
                $this->errors[] = $this->trans('You must register  phone number.', [], 'Shop.Notifications.Error');
                return; 
            }
        }

        parent::postProcess();
    }
}
