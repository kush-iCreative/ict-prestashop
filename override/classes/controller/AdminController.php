<?php
class AdminController extends AdminControllerCore
{
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS(_PS_OVERRIDE_DIR_ . 'controllers/admin/custom_admin.css');
    }
}
