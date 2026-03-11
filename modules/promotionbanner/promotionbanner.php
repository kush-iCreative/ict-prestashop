<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

// Ensure the class file exists
require_once dirname(__FILE__) . '/classes/PromotionBannerClass.php';

class PromotionBanner extends Module
{
    public function __construct()
    {
        $this->name = 'promotionbanner';
        $this->version = '1.0';
        $this->author = 'Kush';
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
            &&    $this->createTable();
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

    public function getContent()
    {
        $output = '';

        // ONLY save if the specific submit button was clicked
        if (Tools::isSubmit('submitBanner')) {
            if ($this->saveBanner()) {
                $output .= $this->displayConfirmation($this->l('Banner saved successfully.'));
            } else {
                $output .= $this->displayError($this->l('Error saving banner.'));
            }
        }

        // Determine which view to show
        if (Tools::isSubmit('addbanner') || Tools::isSubmit('updatepromotion_banner')) {
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
        $helper->table = 'promotion_banner'; // This affects the 'update' submit name
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
        // 1. Initialize Object
        $id = (int)Tools::getValue('id_banner');
        $banner = new PromotionBannerClass($id);

        // 2. Assign Standard Fields
        $banner->title = Tools::getValue('title');
        // Use true as the second parameter for Tools::getValue to allow HTML for the description
        $banner->description = Tools::getValue('description', true);
        $banner->cta_link = Tools::getValue('cta_link');
        $banner->category_id = (int)Tools::getValue('category_id');
        $banner->start_date = Tools::getValue('start_date');
        $banner->end_date = Tools::getValue('end_date');
        $banner->status = (int)Tools::getValue('status');

        // 3. Handle Multiple Image Uploads
        $imageNames = [];

        // Load existing images if they exist (for editing)
        if (!empty($banner->image)) {
            $imageNames = json_decode($banner->image, true) ?: [];
        }

        // Check if new images are being uploaded
        // Note: Use 'image' as the key because HelperForm creates names without brackets for multiple files
        if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'][0])) {
            $targetDir = _PS_MODULE_DIR_ . $this->name . '/views/img/';

            foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['image']['error'][$key] === UPLOAD_ERR_OK) {
                    // Secure filename: Sanitize and add unique ID to prevent overwriting
                    $extension = pathinfo($_FILES['image']['name'][$key], PATHINFO_EXTENSION);
                    $cleanName = Tools::str2url(pathinfo($_FILES['image']['name'][$key], PATHINFO_FILENAME));
                    $finalName = time() . '_' . uniqid() . '_' . $cleanName . '.' . $extension;

                    if (move_uploaded_file($tmpName, $targetDir . $finalName)) {
                        $imageNames[] = $finalName;
                    }
                }
            }
            // Update the image field with the new combined list
            $banner->image = json_encode($imageNames);
        }

        // 4. Persistence
        // save() automatically determines whether to use add() or update() based on the ID
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
        // If $id_category is 0, we treat it as Home/Global
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
    // public function hookDisplayHome($params)
    // {
    //     // Assuming category_id 2 is usually "Home" in PrestaShop
    //     $banners = $this->getBannersByCategory(2);

    //     $this->context->smarty->assign([
    //         'banners' => $banners,
    //         'banner_img_path' => $this->context->link->getBaseLink() . 'modules/' . $this->name . '/views/img/'
    //     ]);

    //     return $this->display(__FILE__, 'views/templates/hook/banner.tpl');
    // }

    // Hook for Category Pages
    public function hookDisplayWrapperTop($params)
    {
        // Detect if we are on a category page
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

    /*  public function hookDisplayBanner($params)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'promotion_banner WHERE status = 1 ORDER BY id_banner DESC';
        $banners = Db::getInstance()->executeS($sql);

        // Decode the JSON image strings into arrays for Smarty
        if ($banners) {
            foreach ($banners as &$banner) {
                $banner['image'] = json_decode($banner['image'], true);
            }
        }

        $this->context->smarty->assign([
            'banners' => $banners,
            'banner_img_path' => $this->context->link->getBaseLink() . 'modules/' . $this->name . '/views/img/'
        ]);

        return $this->display(__FILE__, 'views/templates/hook/banner.tpl');
    }
        */
}
