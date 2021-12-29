<?php
/**
 * @version     2.0.0
 * @package     com_deliveryplus
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Fakas Agamemnon <info@easylogic.gr> - https://easylogic.gr/
 */
// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;

class DeliveryplusController extends BaseController {
// class DeliveryplusController extends JControllerLegacy {

    /**
     * Method to display a view.
     *
     * @param	boolean			$cachable	If true, the view output will be cached
     * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return	JController		This object to support chaining.
     * @since	1.5
     */
    public function display($cachable = false, $urlparams = false) {
        require_once JPATH_COMPONENT . '/helpers/deliveryplus.php';

        $view = JFactory::getApplication()->input->getCmd('view', 'deliveryplus');
        JFactory::getApplication()->input->set('view', $view);

        parent::display($cachable, $urlparams);

        return $this;
    }

    public function ajax_user() {
        include(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'ajaxAdminPhpFiles'.DIRECTORY_SEPARATOR.'ajax_user.php');
    }

    public function ajax_order() {
        include(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'ajaxAdminPhpFiles'.DIRECTORY_SEPARATOR.'ajax_order.php');
    }

    public function ajax_print_order() {
        include(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'ajaxAdminPhpFiles'.DIRECTORY_SEPARATOR.'ajax_print_order.php');
    }

    public function ajax_update_order_status() {
        include(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'ajaxAdminPhpFiles'.DIRECTORY_SEPARATOR.'ajax_update_order_status.php');
    }

    public function ajax_update_item_instock() {
        include(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'ajaxAdminPhpFiles'.DIRECTORY_SEPARATOR.'ajax_update_item_instock.php');
    }

    public function get_customer_stats() {
        include(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'ajaxAdminPhpFiles'.DIRECTORY_SEPARATOR.'get_customer_stats.php');
    }

    public function get_general_stats() {
        include(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'ajaxAdminPhpFiles'.DIRECTORY_SEPARATOR.'get_general_stats.php');
    }

}
