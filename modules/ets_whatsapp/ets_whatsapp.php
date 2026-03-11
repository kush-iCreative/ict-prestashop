<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_'))
    exit;
if (!defined('_PS_JQUERY_VERSION_')) {
    define('_PS_JQUERY_VERSION_', '3.4.1');
}
require_once(dirname(__FILE__) . '/classes/ets_whatsapp_defines.php');
class Ets_whatsapp extends Module
{
    public $hooks = array(
        'displayBackOfficeHeader',
        'displayHeader',
        'displayFooter',
    );
    public static $use_js_rendering = true;
    public $_html;
    public $_errors = array();
    /**
     * @var string
     */
    protected $secure_key;
    /**
     * @var array
     */
    public $fields_form;

    public function __construct()
    {
        $this->name = 'ets_whatsapp';
        $this->tab = 'front_office_features';
        $this->version = '1.0.7';
        $this->author = 'ETS-Soft';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
		$this->module_key = '7a77b0a666933ff72256b7e373193511';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('WhatsApp Messenger');
        $this->description = $this->l('Integrate WhatsApp messenger service into your PrestaShop website');
$this->refs = 'https://prestahero.com/';
    }
    public function install()
    {
        return parent::install() && $this->installHooks()&& $this->_installDefaultConfig();
    }
    public function unInstall()
    {
        return parent::unInstall() && $this->unInstallHooks() && $this->_unInstallDefaultConfig();
    }
    public function installHooks()
    {
        foreach($this->hooks as $hook)
            $this->registerHook($hook);
        return true;
    }
    public function unInstallHooks()
    {
        foreach($this->hooks as $hook)
            $this->unregisterHook($hook);
        return true;
    }
    public function _installDefaultConfig()
    {
        $inputs = $this->getConfigInputs();
        $languages = Language::getLanguages(false);
        if($inputs)
        {
            foreach($inputs as $input)
            {
                if($input['type']=='html')
                    Continue;
                if(isset($input['default']) && $input['default'])
                {
                    if(isset($input['lang']) && $input['lang'])
                    {
                        $values = array();
                        foreach($languages as $language)
                        {
                            if(isset($input['default_is_file']) && $input['default_is_file'])
                                $values[$language['id_lang']] = file_exists(dirname(__FILE__).'/default/'.$input['default_is_file'].'_'.$language['iso_code'].'.txt') ? Tools::file_get_contents(dirname(__FILE__).'/default/'.$input['default_is_file'].'_'.$language['iso_code'].'.txt') : Tools::file_get_contents(dirname(__FILE__).'/default/'.$input['default_is_file'].'_en.txt');
                            else
                                $values[$language['id_lang']] = isset($input['default_lang']) && $input['default_lang'] ? $this->getTextLang($input['default_lang'],$language,'ets_whatsapp_defines') : $input['default'];
                        }
                        Configuration::updateGlobalValue($input['name'],$values,isset($input['autoload_rte']) && $input['autoload_rte'] ? true : false);
                    }
                    else
                        Configuration::updateGlobalValue($input['name'],$input['default']);
                }
            }
        }
        return true;
    }
    public function _unInstallDefaultConfig()
    {
        $inputs = $this->getConfigInputs();
        if($inputs)
        {
            foreach($inputs as $input)
            {
                if($input['type']=='html')
                    Continue;
                Configuration::deleteByName($input['name']);
            }
        }
        return true; 
    }
    public function getConfigInputs()
    {
        return Ets_whatsapp_defines::getInstance()->getConfigInputs();
    }
    public function renderForm($inputs,$submit,$title,$configTabs=array())
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $title,
                    'icon' => ''
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $ETS_WA_CALL_PREFIX = (int)Tools::getValue('ETS_WA_CALL_PREFIX',Configuration::get('ETS_WA_CALL_PREFIX') ? : Configuration::get('PS_COUNTRY_DEFAULT'));
        $country = new Country($ETS_WA_CALL_PREFIX,$this->context->language->id); 
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = $this->id;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = $submit;
        $helper->currentIndex = $this->getAdminConfigureLink();
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $language->id;
        $helper->override_folder ='/';
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
            'fields_value' => $this->getFieldsValues($inputs),
            'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
            'link' => $this->context->link,
            'configTabs' => $configTabs,
            'current_currency'=> $this->context->currency,
            'call_prefix' => $country->call_prefix,
            'country_name' => $country->name,
            'country_iso_code' => Tools::strtolower($country->iso_code),
            'countries' => Ets_whatsapp_defines::getCountries($this->context->language->id),
            'display_icon' => $this->getUploadedIconUrl(),
            'icon_delete_link' => $this->getAdminConfigureLink(array('action' => 'deleteIcon', 'ajax' => 1)),
        );
        $this->fields_form = array();
        return $helper->generateForm(array($fields_form));
    }
    public function getFieldsValues($inputs)
    {
        $languages = Language::getLanguages(false);
        $fields = array();
        $inputs = $inputs;
        if($inputs)
        {
            foreach($inputs as $input)
            {
                if(!isset($input['lang']))
                {
                    $fields[$input['name']] = Tools::getValue($input['name'],Configuration::get($input['name']));
                }
                else
                {
                    foreach($languages as $language)
                    {
                        $fields[$input['name']][$language['id_lang']] = Tools::getValue($input['name'].'_'.$language['id_lang'],Configuration::get($input['name'],$language['id_lang']));
                    }
                }
            }
        }
        return $fields;
    }
    public function getContent()
    {
        $this->_html = '';
        if (Tools::isSubmit('ajax') && Tools::getValue('action') == 'deleteIcon') {
            $this->ajaxDeleteIcon();
        }
        $inputs = $this->getConfigInputs();
        if (Tools::isSubmit('btnSubmit')) {
            $this->saveSubmit($inputs);
        }
        $this->_html .= $this->renderForm($inputs,'btnSubmit',$this->l('Settings'));
        $this->_html .= $this->displayIframe();
        return $this->_html;
    }
    public function saveSubmit($inputs)
    {
        $this->_postValidation($inputs);
        if (!count($this->_errors)) {
            $languages = Language::getLanguages(false);
            $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
            if($inputs)
            {
                foreach($inputs as $input)
                {
                    if($input['type']!='html')
                    {
                        if(isset($input['lang']) && $input['lang'])
                        {
                            $values = array();
                            foreach($languages as $language)
                            {
                                $value_default = Tools::getValue($input['name'].'_'.$id_lang_default);
                                $value = Tools::getValue($input['name'].'_'.$language['id_lang']);
                                $values[$language['id_lang']] = ($value && Validate::isCleanHtml($value,true)) || !isset($input['required']) ? $value : (Validate::isCleanHtml($value_default,true) ? $value_default :'');
                            }
                            Configuration::updateValue($input['name'],$values,isset($input['autoload_rte']) && $input['autoload_rte'] ? true : false);
                        }
                        else
                        {
                            
                            if($input['type']=='checkbox')
                            {
                                $val = Tools::getValue($input['name'],array());
                                if(is_array($val) && self::validateArray($val))
                                {
                                    Configuration::updateValue($input['name'],implode(',',$val));
                                }
                            }
                            elseif($input['type']=='file')
                            {
                                if(isset($_FILES[$input['name']]) && isset($_FILES[$input['name']]['name']) && $_FILES[$input['name']]['name'])
                                {
                                    $file = $_FILES[$input['name']];
                                    $type = Tools::strtolower(Tools::substr(strrchr($file['name'], '.'), 1));
                                    $file_dest = _PS_IMG_DIR_.$this->name.'/';
                                    if (!is_dir($file_dest) && !@mkdir($file_dest, 0755, true))
                                    {
                                        $this->_errors[] = sprintf($this->l('The directory "%s" does not exist.'), $file_dest);
                                        continue;
                                    }
                                    if (!@file_exists($file_dest.'index.php'))
                                    {
                                        @copy(dirname(__FILE__).'/index.php', $file_dest.'index.php');
                                    }
                                    $file_base = Tools::strtolower(pathinfo($file['name'], PATHINFO_FILENAME));
                                    $file_base = preg_replace('/[^a-z0-9\-_]+/i', '-', $file_base);
                                    $file_base = trim($file_base, '-');
                                    if ($file_base === '')
                                        $file_base = 'icon';
                                    $file_name = $file_base.'-'.time().'.'.$type;
                                    if (!is_uploaded_file($file['tmp_name']))
                                    {
                                        $this->_errors[] = $this->l('An error occurred during the image upload process.');
                                        continue;
                                    }
                                    if (!ImageManager::resize($file['tmp_name'], $file_dest.$file_name, null, null, $type))
                                    {
                                        $this->_errors[] = $this->l('An error occurred during the image upload process.');
                                        continue;
                                    }
                                    if (($old = Configuration::get($input['name'])) && $old != $file_name)
                                    {
                                        $old_path = $file_dest.$old;
                                        if (@file_exists($old_path))
                                            @unlink($old_path);
                                    }
                                    Configuration::updateValue($input['name'],$file_name);
                                }
                            }
                            else
                            {
                                $val = Tools::getValue($input['name']);
                                if(Validate::isCleanHtml($val))
                                    Configuration::updateValue($input['name'],$val);
                            }
                           
                        }
                    }
                    
                }
            }
            if(count($this->_errors))
            {
                if(Tools::isSubmit('ajax'))
                {
                    die(
                        json_encode(
                            array(
                                'errors' => $this->displayError($this->_errors),
                            )
                        )
                    );
                }
                $this->_html .= $this->displayError($this->_errors);
                return;
            }
            if(Tools::isSubmit('ajax'))
            {
                die(
                    json_encode(
                        array(
                            'success' => $this->l('Settings updated'),
                        )
                    )
                );
            }
            $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        } else {
            if(Tools::isSubmit('ajax'))
            {
                die(
                    json_encode(
                        array(
                            'errors' => $this->displayError($this->_errors),
                        )
                    )
                );
            }
            $this->_html .= $this->displayError($this->_errors);
        }
    }
    public function _postValidation($inputs)
    {
        $languages = Language::getLanguages(false);
        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
        foreach($inputs as $input)
        {
            if($input['type']=='html')
                continue;
            if(isset($input['lang']) && $input['lang'])
            {
                if(isset($input['required']) && $input['required'])
                {
                    $val_default = Tools::getValue($input['name'].'_'.$id_lang_default);
                    if(!$val_default)
                    {
                        $this->_errors[] = sprintf($this->l('%s is required'),$input['label']);
                    }
                    elseif( isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate',$validate) && !Validate::{$validate}($val_default,true))
                        $this->_errors[] = sprintf($this->l('%s is not valid'),$input['label']);
                    elseif(!Validate::isCleanHtml($val_default,true))
                        $this->_errors[] = sprintf($this->l('%s is not valid'),$input['label']);
                    else
                    {
                        foreach($languages as $language)
                        {
                            if(($value = Tools::getValue($input['name'].'_'.$language['id_lang'])) && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate',$validate)  && !Validate::{$validate}($value,true))
                                $this->_errors[] = sprintf($this->l('%s is not valid in %s'),$input['label'],$language['iso_code']);
                            elseif($value && !Validate::isCleanHtml($value,true))
                                $this->_errors[] = sprintf($this->l('%s is not valid in %s'),$input['label'],$language['iso_code']);
                        }
                    }
                }
                else
                {
                    foreach($languages as $language)
                    {
                        if(($value = Tools::getValue($input['name'].'_'.$language['id_lang'])) && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate',$validate)  && !Validate::{$validate}($value,true))
                            $this->_errors[] = sprintf($this->l('%s is not valid in %s'),$input['label'],$language['iso_code']);
                        elseif($value && !Validate::isCleanHtml($value,true))
                            $this->_errors[] = sprintf($this->l('%s is not valid in %s'),$input['label'],$language['iso_code']);
                    }
                }
            }
            else
            {
                if($input['type']=='file')
                {
                    
                    if(isset($input['required']) && $input['required'] && (!isset($_FILES[$input['name']]) || !isset($_FILES[$input['name']]['name']) ||!$_FILES[$input['name']]['name']))
                    {
                        $this->_errors[] = sprintf($this->l('%s is required'),$input['label']);
                    }
                    elseif(isset($_FILES[$input['name']]) && isset($_FILES[$input['name']]['name'])  && $_FILES[$input['name']]['name'])
                    {
                        $file_name = $_FILES[$input['name']]['name'];
                        $file_size = $_FILES[$input['name']]['size'];
                        $max_file_size = Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')*1024*1024;
                        $type = Tools::strtolower(Tools::substr(strrchr($file_name, '.'), 1));
                        if(isset($input['is_image']) && $input['is_image'])
                            $file_types = array('jpg', 'png', 'gif', 'jpeg');
                        else
                            $file_types = array('jpg', 'png', 'gif', 'jpeg','zip','doc','docx');
                        if(!in_array($type,$file_types))
                            $this->_errors[] = sprintf($this->l('The file name "%s" is not in the correct format, accepted formats: %s'),$file_name,'.'.trim(implode(', .',$file_types),', .'));
                        $max_file_size = $max_file_size ? : Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')*1024*1024;
                        if($file_size > $max_file_size)
                            $this->_errors[] = sprintf($this->l('The file name "%s" is too large. Limit: %s'),$file_name,Tools::ps_round($max_file_size/1048576,2).'Mb');
                    }
                }
                else
                {
                    $val = Tools::getValue($input['name']);
                    if($input['type']!='checkbox')
                    {
                       
                        if($val===''&& isset($input['required']) && $input['required'])
                        {
                            $this->_errors[] = sprintf($this->l('%s is required'),$input['label']);
                        }
                        if($val!=='' && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate',$validate) && !Validate::{$validate}($val))
                        {
                            $this->_errors[] = sprintf($this->l('%s is not valid'),$input['label']);
                        }
                        elseif($val!=='' && $val<=0 && isset($input['validate']) && ($validate = $input['validate']) && $validate=='isUnsignedInt')
                        {
                            $this->_errors[] = sprintf($this->l('%s is not valid'),$input['label']);
                        }
                        elseif($val!==''&& !Validate::isCleanHtml($val))
                            $this->_errors[] = sprintf($this->l('%s is not valid'),$input['label']);
                    }
                    else
                    {
                        if(!$val&& isset($input['required']) && $input['required'] )
                        {
                            $this->_errors[] = sprintf($this->l('%s is required'),$input['label']);
                        }
                        elseif($val && !self::validateArray($val,isset($input['validate']) ? $input['validate']:''))
                            $this->_errors[] = sprintf($this->l('%s is not valid'),$input['label']);
                    }
                }
            }
        }
    }
    public function getTextLang($text, $lang,$file_name='')
    {
        if(is_array($lang))
            $iso_code = $lang['iso_code'];
        elseif(is_object($lang))
            $iso_code = $lang->iso_code;
        else
        {
            $language = new Language($lang);
            $iso_code = $language->iso_code;
        }
		$modulePath = rtrim(_PS_MODULE_DIR_, '/').'/'.$this->name;
        $fileTransDir = $modulePath.'/translations/'.$iso_code.'.'.'php';
        if(!@file_exists($fileTransDir)){
            return $text;
        }
        $fileContent = Tools::file_get_contents($fileTransDir);
        $text_tras = preg_replace("/\\\*'/", "\'", $text);
        $strMd5 = md5($text_tras);
        $keyMd5 = '<{' . $this->name . '}prestashop>' . ($file_name ? : $this->name) . '_' . $strMd5;
        preg_match('/(\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]\s*=\s*\')(.*)(\';)/', $fileContent, $matches);
        if($matches && isset($matches[2])){
           return  $matches[2];
        }
        return $text;
    }
    public function hookDisplayHeader()
    {
        if(Configuration::get('ETS_WA_ENABLED'))
        {
            $this->context->controller->addCSS($this->_path . 'views/css/front.css');
            $this->context->controller->addJS($this->_path . 'views/js/front.js');
            if (self::$use_js_rendering) {
                $ETS_WA_CALL_PREFIX = Configuration::get('ETS_WA_CALL_PREFIX') ? : Configuration::get('PS_COUNTRY_DEFAULT');
                $country = new Country((int)$ETS_WA_CALL_PREFIX);
                Media::addJsDef(array(
                    'ets_wa_params' => array(
                        'number_phone' => str_replace(array('+',' ','.'),'',Configuration::get('ETS_WA_NUMBER_PHONE')),
                        'display_title' => Configuration::get('ETS_WA_DISPLAY_TITLE',$this->context->language->id),
                        'display_position' => Configuration::get('ETS_WA_DISPLAY_POSITION'),
                        'adjust_right' => (int)Configuration::get('ETS_WA_ADJUST_RIGHT'),
                        'adjust_bottom' => (int)Configuration::get('ETS_WA_ADJUST_BOTTOM'),
                        'adjust_left' => (int)Configuration::get('ETS_WA_ADJUST_LEFT'),
                        'call_prefix' => $country->call_prefix,
                        'send_current_url' => Configuration::get('ETS_WA_SEND_CURRENT_URL') ? $this->getCurrentUrl() : '',
                        'button_color' => Configuration::get('ETS_WA_BUTTON_COLOR'),
                        'button_radius' => (int)Configuration::get('ETS_WA_BUTTON_RADIUS'),
                        'icon_url' => $this->getIconUrl(),
                    )
                ));
            }
        }
    }
    public function getJqueryPath($version = null, $folder = null, $minifier = true)
    {
        $addNoConflict = false;
        if ($version === null) {
            $version = _PS_JQUERY_VERSION_;
        } //set default version
        elseif (preg_match('/^([0-9\.]+)$/Ui', $version)) {
            $addNoConflict = true;
        } else {
            return false;
        }

        if ($folder === null) {
            $folder = _PS_JS_DIR_ . 'jquery/';
        } //set default folder
        //check if file exist
        $file = $folder . 'jquery-' . $version . ($minifier ? '.min.js' : '.js');

        // remove PS_BASE_URI on _PS_ROOT_DIR_ for the following
        $urlData = parse_url($file);
        $fileUri = _PS_ROOT_DIR_ . Tools::str_replace_once(__PS_BASE_URI__, DIRECTORY_SEPARATOR, $urlData['path']);
        $fileUriHostMode = _PS_CORE_DIR_ . Tools::str_replace_once(__PS_BASE_URI__, DIRECTORY_SEPARATOR, $urlData['path']);
        // check if js files exists, if not try to load query from ajax.googleapis.com

        $return = [];

        if (@filemtime($fileUri) || (defined('_PS_HOST_MODE_') && @filemtime($fileUriHostMode))) {
            $return[] = Media::getJSPath($file);
        } else {
            $return[] = Media::getJSPath(Tools::getCurrentUrlProtocolPrefix() . 'ajax.googleapis.com/ajax/libs/jquery/' . $version . '/jquery' . ($minifier ? '.min.js' : '.js'));
        }

        if ($addNoConflict) {
            $return[] = Media::getJSPath($this->context->shop->getBaseURL(true, false) . _PS_JS_DIR_ . 'jquery/jquery.noConflict.php?version=' . $version);
        }

        // added jQuery migrate for compatibility with new version of jQuery
        // will be removed when using latest version of jQuery
        $return[] = Media::getJSPath(_PS_JS_DIR_ . 'jquery/jquery-migrate-1.2.1.min.js');

        return $return;
    }
    public function addJquery()
    {
        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=') && version_compare(_PS_VERSION_, '1.7.7.0', '<'))
            $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery-'._PS_JQUERY_VERSION_.'.min.js');
        elseif(version_compare(_PS_VERSION_,'1.7.7','<=')){
            $this->context->controller->addJS($this->getJqueryPath());
        }
    }
    public function hookDisplayBackOfficeHeader()
    {
        $configure = Tools::getValue('configure');
        $controller = Tools::getValue('controller');
        if($controller =='AdminModules' && $configure == $this->name)
        {
            $this->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/admin.js');
            $this->context->controller->addCSS($this->_path.'views/css/admin.css');
            Media::addJsDef(array(
                'ETS_WA_DELETE_FAILED' => $this->l('Delete failed.'),
            ));
        }
    }
    public function hookDisplayFooter()
    {
        if(Configuration::get('ETS_WA_ENABLED') && !self::$use_js_rendering)
        {
            $ETS_WA_CALL_PREFIX = Configuration::get('ETS_WA_CALL_PREFIX') ? : Configuration::get('PS_COUNTRY_DEFAULT');
            $this->context->smarty->assign(
                array(
                    'ETS_WA_NUMBER_PHONE' => str_replace(array('+',' ','.'),'',Configuration::get('ETS_WA_NUMBER_PHONE')),
                    'ETS_WA_DISPLAY_TITLE' => Configuration::get('ETS_WA_DISPLAY_TITLE',$this->context->language->id),
                    'ETS_WA_DISPLAY_POSITION' => Configuration::get('ETS_WA_DISPLAY_POSITION'),
                    'ETS_WA_ADJUST_RIGHT' => Configuration::get('ETS_WA_ADJUST_RIGHT'),
                    'ETS_WA_ADJUST_BOTTOM' => Configuration::get('ETS_WA_ADJUST_BOTTOM'),
                    'ETS_WA_ADJUST_LEFT' => Configuration::get('ETS_WA_ADJUST_LEFT'),
                    'ETS_WA_COUNTRY' => new Country((int)$ETS_WA_CALL_PREFIX),
                    'ETS_WA_SEND_CURRENT_URL' => Configuration::get('ETS_WA_SEND_CURRENT_URL') ? $this->getCurrentUrl():'',
                    'ETS_WA_BUTTON_COLOR' => Configuration::get('ETS_WA_BUTTON_COLOR'),
                    'ETS_WA_BUTTON_RADIUS' => Configuration::get('ETS_WA_BUTTON_RADIUS'),
                    'ETS_WA_ICON_URL' => $this->getIconUrl(),
                )
            );
            return $this->display(__FILE__,'whatsapp.tpl');
        }
    }
    public function getIconUrl()
    {
        $icon_url = $this->_path.'views/img/whatsapp.png';
        if(($icon = Configuration::get('ETS_WA_ICON')))
        {
            $icon_path = _PS_IMG_DIR_.$this->name.'/'.$icon;
            if(@file_exists($icon_path))
                $icon_url = __PS_BASE_URI__.'img/'.$this->name.'/'.$icon;
        }
        return $this->context->link->getMediaLink($icon_url);
    }
    public function getUploadedIconUrl()
    {
        if(($icon = Configuration::get('ETS_WA_ICON')))
        {
            $icon_path = _PS_IMG_DIR_.$this->name.'/'.$icon;
            if(@file_exists($icon_path))
                return $this->context->link->getMediaLink(__PS_BASE_URI__.'img/'.$this->name.'/'.$icon);
        }
        return '';
    }
    public function ajaxDeleteIcon()
    {
        header('Content-Type: application/json');
        $icon = Configuration::get('ETS_WA_ICON');
        if ($icon) {
            $icon_path = _PS_IMG_DIR_.$this->name.'/'.$icon;
            if (@file_exists($icon_path)) {
                @unlink($icon_path);
            }
            Configuration::updateValue('ETS_WA_ICON', '');
        }
        die(json_encode(array(
            'success' => true,
            'message' => $this->l('Image deleted'),
        )));
    }
    public function getAdminConfigureLink($extra_params = array())
    {
        $params = array_merge(
            array(
                'configure' => $this->name,
                'tab_module' => $this->tab,
                'module_name' => $this->name,
            ),
            is_array($extra_params) ? $extra_params : array()
        );
        if ($token = Tools::getValue('_token')) {
            $params['_token'] = $token;
        }
        if ($legacy_token = Tools::getValue('token')) {
            $params['token'] = $legacy_token;
        }
        $sf_params = array();
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            $sf_params = array(
                'route' => 'admin_module_configure_action',
                'module_name' => $this->name,
            );
        }
        return $this->context->link->getAdminLink('AdminModules', true, $sf_params, $params);
    }
    public function getCurrentUrl()
    {
        $url ='';
        if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'])
        {
            if(isset($_SERVER['REQUEST_SCHEME']))
                $url .=$_SERVER['REQUEST_SCHEME'];
            else
                $url .='http';
            $url .='://'.$_SERVER['HTTP_HOST'];
            if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'])
                $url .= $_SERVER['REQUEST_URI'];
            
        }
        return $url;
    }
    public static function validateArray($array,$validate='isCleanHtml')
    {
        if(!is_array($array))
            return false;
        if(method_exists('Validate',$validate))
        {
            if($array && is_array($array))
            {
                $ok= true;
                foreach($array as $val)
                {
                    if(!is_array($val))
                    {
                        if($val && !Validate::$validate($val))
                        {
                            $ok= false;
                            break;
                        }
                    }
                    else
                        $ok = self::validateArray($val,$validate);
                }
                return $ok;
            }
        }
        return true;
    }
    public function displayIframe()
    {
        switch($this->context->language->iso_code) {
          case 'en':
            $url = 'https://cdn.prestahero.com/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
            break;
          case 'it':
            $url = 'https://cdn.prestahero.com/it/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
            break;
          case 'fr':
            $url = 'https://cdn.prestahero.com/fr/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
            break;
          case 'es':
            $url = 'https://cdn.prestahero.com/es/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
            break;
          default:
            $url = 'https://cdn.prestahero.com/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
        }
        $this->smarty->assign(
            array(
                'url_iframe' => $url
            )
        );
        return $this->display(__FILE__,'iframe.tpl');
    }
}
