<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomHook extends Module
{
    public function __construct()
    {
        $this->name = 'customhook'; // Technical name
        $this->tab = 'front_office_features'; // Admin section
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 0; // Load only when needed
        $this->bootstrap = true; // Use PrestaShop's Bootstrap styles

        parent::__construct();

        $this->displayName = $this->l('Custom Hook');
        $this->description = $this->l('A short description of what this module does.');

        // Define compatibility for PrestaShop 9
        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => '9.99.99'];
    }
    public function install()
    {
        return parent::install()
            && $this->registerHook('displayCustomHook'); // Example: register a hook
    }
  
    public function uninstall()
    {
        return parent::uninstall();
    }
    public function hookDisplayCustomHook($params)
    {
        return '<div class="alert alert-info">Hello from custom hook !</div>';
    }
}
