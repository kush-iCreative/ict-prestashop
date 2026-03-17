<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/PromotionBannerClass.php';

class PromotionBanner extends Module
{
    public function __construct()
    {
        $this->name = 'promotionbanner';
        $this->version = '1.0';
        $this->author = 'iCreative';
        $this->bootstrap = true;
        $this->tab = 'front_office_features';

        parent::__construct();

        $this->displayName = $this->l('Promotion Banner');
        $this->description = $this->l('Category promotional banner module');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('displayWrapperTop')
            && $this->registerHook('displayHeader')
            && $this->createTable();
    }
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'promotion_banner`';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    private function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'promotion_banner (
                id_banner INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255),
                description TEXT,
                cta_link VARCHAR(255),
                category_id INT,
                image VARCHAR(255),
                start_date DATETIME,
                end_date DATETIME,
                status TINYINT(1)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return Db::getInstance()->execute($sql);
    }
    public function hookDisplayHeader(array $params)
    {

        $context    = Context::getContext();
        $controller = $context->controller;

        $controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/style.css');
        $controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/script.js');
    }
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitBanner')) {
            if ($this->saveBanner()) {
                Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name);
            } else {
                $output .= $this->displayError($this->l('Error saving banner.'));
            }
        }

        if (Tools::isSubmit('deletepromotion_banner')) {
            $id_banner = (int)Tools::getValue('id_banner');
            // dd($id_banner);
            if ($id_banner > 0) {
                $res = Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'promotion_banner` WHERE `id_banner` = ' . $id_banner);
                if ($res) {
                    Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name);
                } else {
                    $output .=   $this->displayError($this->l('An error occurred while deleting the banner.'));
                }
            }
        }
        if (Tools::isSubmit('addbanner') || Tools::isSubmit('updatepromotion_banner') || Tools::isSubmit('deletepromotion_banner')) {
            return $output . $this->renderForm();
        }

        return $output . $this->renderList();
    }

    protected function renderList()
    {
        $results = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'promotion_banner');

        $fields_list = [
            'id_banner' => ['title' => $this->l('ID'), 'type' => 'text'],
            'title' => ['title' => $this->l('Title'), 'type' => 'text'],
            'status' => ['title' => $this->l('Status'), 'active' => 'status', 'type' => 'bool'],
        ];

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_banner'; // The ID in your DB
        $helper->actions = ['edit', 'delete'];
        $helper->show_toolbar = true;
        $helper->title = $this->l('Promotion Banners');
        $helper->table = 'promotion_banner';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // This adds the "Add New" button to the top toolbar
        $helper->toolbar_btn['new'] = [
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&addbanner&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add New Banner'),
        ];

        return $helper->generateList($results, $fields_list);
    }
    private function saveBanner()
    {
        $id = (int)Tools::getValue('id_banner');
        $banner = new PromotionBannerClass($id);

        $banner->title = Tools::getValue('title');
        $banner->description = Tools::getValue('description', true);
        $banner->cta_link = Tools::getValue('cta_link');
        $banner->category_id = (int)Tools::getValue('category_id');
        $banner->start_date = Tools::getValue('start_date');
        $banner->end_date = Tools::getValue('end_date');
        $banner->status = (int)Tools::getValue('status');

        $imageNames = [];

        // Load existing images if they exist
        if (!empty($banner->image)) {
            $imageNames = json_decode($banner->image, true) ?: [];
        }

        if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'][0])) {
            $targetDir = _PS_MODULE_DIR_ . $this->name . '/views/img/';

            foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['image']['error'][$key] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($_FILES['image']['name'][$key], PATHINFO_EXTENSION);
                    $cleanName = Tools::str2url(pathinfo($_FILES['image']['name'][$key], PATHINFO_FILENAME));
                    $finalName = time() . '_' . uniqid() . '_' . $cleanName . '.' . $extension;

                    if (move_uploaded_file($tmpName, $targetDir . $finalName)) {
                        $imageNames[] = $finalName;
                    }
                }
            }
            // Update the image field
            $banner->image = json_encode($imageNames);
        }


        return $banner->save();
    }

    private function renderForm()
    {
        $categories = Category::getSimpleCategories($this->context->language->id);
        $options = [];
        foreach ($categories as $c) {
            $options[] = ['id' => $c['id_category'], 'name' => $c['name']];
        }
        $id_banner = (int)Tools::getValue('id_banner');
        $banner_data = [];

        if ($id_banner) {
            $banner_data = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'promotion_banner WHERE id_banner = ' . $id_banner);
        }
        //image show
        $image_list_html = '';
        if (isset($banner_data['image']) && !empty($banner_data['image'])) {
            $images = json_decode($banner_data['image'], true);
            if (is_array($images)) {
                foreach ($images as $img) {
                    $url = _MODULE_DIR_ . $this->name . '/views/img/' . $img;
                    $image_list_html .= '<div style="display:inline-block; margin:5px;">';
                    $image_list_html .= '<img src="' . $url . '" style="width:100px;"><br/>';
                    $image_list_html .= '</div>';
                }
            }
        }
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Add/Edit Promotion Banner'),
            ],
            'input' => [
                ['type' => 'hidden', 'name' => 'id_banner'],
                ['type' => 'text', 'label' => $this->l('Title'), 'name' => 'title', 'required' => true],
                ['type' => 'textarea', 'label' => $this->l('Description'), 'name' => 'description', 'autoload_rte' => true],
                ['type' => 'text', 'label' => $this->l('CTA Button Link'), 'name' => 'cta_link'],
                ['type' => 'select', 'label' => $this->l('Category'), 'name' => 'category_id', 'options' => ['query' => $options, 'id' => 'id', 'name' => 'name']],
                [
                    'type' => 'file',
                    'label' => $this->l('Images'),
                    'name' => 'image',
                    'multiple' => true,
                    'display_image' => true,
                    'desc' => $image_list_html ?: $this->l('Upload one or more images'),
                ],
                ['type' => 'datetime', 'label' => $this->l('Start Date'), 'name' => 'start_date'],
                ['type' => 'datetime', 'label' => $this->l('End Date'), 'name' => 'end_date'],
                ['type' => 'switch', 'label' => $this->l('Enable Banner'), 'name' => 'status', 'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
            ],
            'submit' => [
                'title' => $this->l('Save Banner'),
                'name' => 'submitBanner',
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Load current values
        $helper->fields_value['id_banner'] = Tools::getValue('id_banner', (isset($banner_data['id_banner']) ? $banner_data['id_banner'] : ''));
        $helper->fields_value['title'] = Tools::getValue('title', (isset($banner_data['title']) ? $banner_data['title'] : ''));
        $helper->fields_value['description'] = Tools::getValue('description', (isset($banner_data['description']) ? $banner_data['description'] : ''));
        $helper->fields_value['cta_link'] = Tools::getValue('cta_link', (isset($banner_data['cta_link']) ? $banner_data['cta_link'] : ''));
        $helper->fields_value['category_id'] = Tools::getValue('category_id', (isset($banner_data['category_id']) ? $banner_data['category_id'] : ''));
        $helper->fields_value['start_date'] = Tools::getValue('start_date', (isset($banner_data['start_date']) ? $banner_data['start_date'] : date('Y-m-d H:i:s')));
        $helper->fields_value['end_date'] = Tools::getValue('end_date', (isset($banner_data['end_date']) ? $banner_data['end_date'] : ''));
        $helper->fields_value['status'] = Tools::getValue('status', (isset($banner_data['status']) ? $banner_data['status'] : 1));

        $helper->fields_value['image'] = Tools::getValue('image', (isset($banner_data['image']) ? $banner_data['image'] : ''));


        return $helper->generateForm($fields_form);
    }
    private function getBannersByCategory($id_category = 0)
    {
        // If $id_category is 0 show Home/Global
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'promotion_banner 
            WHERE status = 1 
             AND (start_date <= NOW() OR start_date = "0000-00-00 00:00:00" OR start_date IS NULL) 
             AND (end_date >= NOW() OR end_date = "0000-00-00 00:00:00" OR end_date IS NULL) 
            AND category_id = ' . (int)$id_category . ' 
            ORDER BY id_banner DESC';

        $banners = Db::getInstance()->executeS($sql);
        if ($banners) {
            foreach ($banners as &$banner) {
                $banner['image'] = json_decode($banner['image'], true);
            }
        }
        return $banners;
    }
    public function hookDisplayHome($params)
    {
        // $id_category = (int)Tools::getValue('id_category');
        $id_category = (int)Tools::getValue('id_category', 2);
        // dd($id_category); die();

        $banners = $this->getBannersByCategory($id_category);

        $this->context->smarty->assign([
            'banners' => $banners,
            'banner_img_path' => $this->context->link->getBaseLink() . 'modules/' . $this->name . '/views/img/'
        ]);

        return $this->display(__FILE__, 'views/templates/hook/banner.tpl');
    }

    public function hookDisplayWrapperTop($params)
    {
        if ($this->context->controller->php_self == 'category') {
            $id_category = (int)Tools::getValue('id_category');
            $banners = $this->getBannersByCategory($id_category);

            if ($banners) {
                $this->context->smarty->assign([
                    'banners' => $banners,
                    'banner_img_path' => $this->context->link->getBaseLink() . 'modules/' . $this->name . '/views/img/'
                ]);
                return $this->display(__FILE__, 'views/templates/hook/banner.tpl');
            }
        }
        return false;
    }
}
