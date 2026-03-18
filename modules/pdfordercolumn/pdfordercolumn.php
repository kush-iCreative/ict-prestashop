<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

//use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\HtmlColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;

class PdfOrderColumn extends Module
{
    public function __construct()
    {
        $this->name = 'pdfordercolumn';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'iCreative';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = $this->l('Custom Order Grid PDF Column');
        $this->description = $this->l('Adds PDF column with Invoice/Delivery links to order list.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionOrderGridDefinitionModifier')
            && $this->registerHook('actionOrderGridDataModifier');
    }


    public function hookActionOrderGridDefinitionModifier(array $params)
    {

        $columns = $params['definition']->getColumns();

        $columns->addAfter(
            'osname',
            (new HtmlColumn('pdf_links'))
                ->setName('PDF')
                ->setOptions([
                    'field' => 'pdf_links',
                ])
        );
    }
    // public function hookActionOrderGridDataModifier(array $params)
    // {
    //     $data = $params['data'];
    //     $records = $data->getRecords()->all();

    //     foreach ($records as &$record) {
    //         $id_order = (int)$record['id_order'];
    //         $deliverySlipLink = $this->context->link->getAdminLink(
    //             'AdminPdf',
    //             true,
    //             [],
    //             [
    //                 'submitAction' => 'generateDeliverySlipPDF',
    //                 'id_order' => $id_order,
    //             ]
    //         );

    //         $creditSlipLink = $this->context->link->getAdminLink(
    //             'AdminPdf',
    //             true,
    //             [],
    //             [
    //                 'submitAction' => 'generateCreditSlipPDF',
    //                 'id_order' => $id_order,
    //             ]
    //         );

    //         $record['pdf_links'] = '
    //         <a href="' . $deliverySlipLink . '" target="_blank">Delivery Slip</a><br>
    //         <a href="' . $creditSlipLink . '" target="_blank">Credit Slip</a>
    //     ';
    //     }

    //     $params['data']->setRecords($records);
    // }

    public function hookActionOrderGridDataModifier(array $params)
    {
        $gridData = $params['data'];
        $records = $gridData->getRecords()->all();

        foreach ($records as &$record) {
            $id_order = (int) $record['id_order'];

            $invoiceLink = $this->context->link->getAdminLink('AdminPdf', true, [], [
                'submitAction' => 'generateInvoicePdf',
                'id_order' => $id_order,
            ]);

            $deliverySlipLink = $this->context->link->getAdminLink('AdminPdf', true, [], [
                'submitAction' => 'generateDeliverySlipPDF',
                'id_order' => $id_order,
            ]);

            $record['pdf_links'] = '
        <a href="' . $invoiceLink . '" target="_blank" onclick="event.preventDefault();" title="Invoice">
            <i class="material-icons">receipt</i>
        </a>
        <a href="' . $deliverySlipLink . '" target="_blank" onclick="event.preventDefault();" title="Delivery Slip">
            <i class="material-icons">local_shipping</i>
        </a>';
        }
        $modifiedRecords = new \PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection($records);
        $params['data'] = new \PrestaShop\PrestaShop\Core\Grid\Data\GridData(
            $modifiedRecords,
            $gridData->getRecordsTotal(),
            $gridData->getQuery()
        );
    }
}
