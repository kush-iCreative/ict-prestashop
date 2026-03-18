<?php

class AdminOrdersController extends AdminOrdersControllerCore
{
    public function postProcess(): void
    {
        parent::__construct();

        // Define the new 'actions_column' column with a callback
        $actions_column = array(
            'title' => $this->trans('Slips', array(), 'Admin.Global'),
            'align' => 'text-center',
            'callback' => 'displaySlipsActions',
            'orderby' => false,
            'search' => false,
            'remove_onclick' => true,
        );

        // Add the new column to the fields_list array
        // Use array_slice to place it where you want (e.g., after 'customer')
        $part1 = array_slice($this->fields_list, 0, 4); // Adjust the index (4) to your desired position
        $part2 = array_slice($this->fields_list, 4);
        $part1['slips_actions'] = $actions_column;
        $this->fields_list = array_merge($part1, $part2);
    }

    /**
     * Callback function to display the action links
     */
    public function displaySlipsActions($value, $row)
    {
        $id_order = (int)$row['id_order'];

        // Generate link for Delivery Slip
        $delivery_slip_link = $this->context->link->getAdminLink('AdminPdf') . '&submitAction=generateDeliverySlipPDF&id_order=' . $id_order;

        // to check if a credit slip exists. This link goes to the order view page where you can generate it.
        $credit_slip_link = $this->context->link->getAdminLink('AdminOrders') . '&vieworder&id_order=' . $id_order;

        $html = '<a href="' . $delivery_slip_link . '" title="' . $this->trans('View delivery slip', array(), 'Admin.Orderscustomers.Feature') . '" class="btn btn-default">';
        $html .= '<i class="icon-truck"></i> ' . $this->trans('Delivery Slip', array(), 'Admin.Orderscustomers.Feature');
        $html .= '</a>';

        // The 'Credit Slip' link typically involves creating a return/credit first in the order details page
        $html .= '<a href="' . $credit_slip_link . '" title="' . $this->trans('Manage credit slips', array(), 'Admin.Orderscustomers.Feature') . '" class="btn btn-default">';  
        $html .= '<i class="icon-book"></i> ' . $this->trans('Credit Slip', array(), 'Admin.Orderscustomers.Feature');
        $html .= '</a>';

        return $html;
    }
}
