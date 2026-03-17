<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\HtmlColumn;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PdfOrderColumn extends Module {
    public function __construct() {
        $this->name = 'pdfordercolumn';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'iCreative';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = $this->l('Custom Order Grid PDF Column');
        $this->description = $this->l('Adds PDF column with Invoice/Delivery links to order list.');
    }

    public function install() {
        return parent::install() &&
            $this->registerHook('actionOrderGridDefinitionModifier') &&
            $this->registerHook('actionOrderGridDataModifier'); // Changed hook
    }

    public function hookActionOrderGridDefinitionModifier(array $params) {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        // Add a new column using HtmlColumn to render links
        $definition->getColumns()->addAfter(
            'osname',
            (new HtmlColumn('order_pdf_links'))
                ->setName($this->l('Documents'))
                ->setOptions([
                    'render' => function (array $record) {
                        // $record contains the data processed by actionOrderGridDataModifier
                        return $record['pdf_links'];
                    }
                ])
        );
    }

    // This hook fetches the links for each row
    public function hookActionOrderGridDataModifier(array $params) {
        $data = $params['data'];
        /** @var \PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection $records */
        $records = $data->getRecords();
        
        $newRecords = [];
        foreach ($records->all() as $record) {
            $order = new Order((int)$record['id_order']);
            
            $links = '<div class="btn-group-action">';
            
            // Delivery Slip
            if ($order->delivery_number) {
                $links .= '<a href="' . $this->context->link->getAdminLink('AdminPdf', true, [], ['submitAction' => 'generateDeliverySlipPDF', 'id_order' => (int)$order->id]) . '" class="btn btn-default" title="' . $this->l('Download Delivery Slip') . '"><i class="material-icons">local_shipping</i></a>';
            }

            // Credit Slip
            if ($order->invoice_number) {
                $links .= '<a href="' . $this->context->link->getAdminLink('AdminPdf', true, [], ['submitAction' => 'generateInvoicePDF', 'id_order' => (int)$order->id]) . '" class="btn btn-default" title="' . $this->l('Download Invoice') . '"><i class="material-icons">description</i></a>';
            }
            
            $links .= '</div>';
            
            $record['pdf_links'] = $links;
            $newRecords[] = $record;
        }

        $records->set($newRecords);
    }
}
