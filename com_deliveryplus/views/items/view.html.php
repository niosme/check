<?php
/**
 * @version     2.0.0
 * @package     com_deliveryplus
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Fakas Agamemnon <info@easylogic.gr> - https://easylogic.gr/
 */
// No direct access
// defined('_JEXEC') or die;

// jimport('joomla.application.component.view');

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

// use Joomla\CMS\Form\Form;
/**
 * View class for a list of Deliveryplus.
 */
class DeliveryplusViewItems extends HtmlView {

    public $filterForm;
    public $activeFilters = [];
    protected $items;
    protected $pagination;
    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        $this->items            = $this->get('Items');
        $this->pagination       = $this->get('Pagination');
        $this->state            = $this->get('State');
        $this->filterForm       = $this->get('FilterForm');
        $this->activeFilters    = $this->get('ActiveFilters');

        // Check for errors.
        if (count( $errors = $this->get('Errors')))
        {
            $app = Factory::getApplication();
            $app->enqueueMessage(implode('<br />',$errors), 'error');
            $app->setHeader('status', 500, true);
        }

        DeliveryplusHelper::keepSessionAlive();
        $this->settings = DeliveryplusHelper::getSettings();
        // DeliveryplusHelper::addSubmenu('items');

        // if (DeliveryplusHelper::isRestaurantAdmin()) {
        //     unset($this->activeFilters['restaurant']);
        //     $this->filterForm->removeField('restaurant', 'filter');
        // }
        $this->addToolbar();

        // $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar() {
        require_once JPATH_COMPONENT . '/helpers/deliveryplus.php';

        $state  = $this->get('State');
        $canDo = DeliveryplusHelper::getActions('com_deliveryplus', 'category', $this->state->get('filter.category_id'));

        $user  = Factory::getApplication()->getIdentity();

        $toolbar = Toolbar::getInstance('toolbar');
        ToolBarHelper::title(Text::_('COM_DELIVERYPLUS_TITLE_ITEMS'), 'file');

        //Check if the form exists before showing the add/edit buttons
        // $formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/item';
        // if (file_exists($formPath)) {

        //     if ($canDo->get('core.create')) {
        //         JToolBarHelper::addNew('item.add', 'JTOOLBAR_NEW');
        //     }

        //     if ($canDo->get('core.edit') && isset($this->items[0])) {
        //         JToolBarHelper::editList('item.edit', 'JTOOLBAR_EDIT');
        //     }
        // }
        if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_deliveryplus', 'core.create')) > 0)
        {
            $toolbar->addNew('item.add');
        }

        if ($canDo->get('core.edit') && isset($this->items[0])) {
            ToolBarHelper::editList('item.edit', 'JTOOLBAR_EDIT');
        }

        if (!$this->isEmptyState && $canDo->get('core.edit.state')){
            ToolBarHelper::custom('item.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
      
            ToolBarHelper::custom('item.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            ToolBarHelper::divider();
            // $dropdown = $toolbar->dropdownButton('status-group')
            //     ->text('JTOOLBAR_CHANGE_STATUS')
            //     ->toggleSplit(false)
            //     ->icon('icon-ellipsis-h')
            //     ->buttonClass('btn btn-action')
            //     ->listCheck(true);

            // $childBar = $dropdown->getChildToolbar();
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();
            $childBar->archive('item.archive')->listCheck(true);
            $childBar->checkin('item.checkin')->listCheck(true);
            $childBar->trash('item.trash')->listCheck(true);
        }

        if ($state->get('filter.published') == -2 && $canDo->get('core.delete'))
        {
            $toolbar->delete('item.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($user->authorise('core.admin', 'com_deliveryplus') || $user->authorise('core.options', 'com_deliveryplus'))
        {
            $toolbar->preferences('com_deliveryplus');
        }

        ToolbarHelper::help( 'COM_SMARTORDER_HELP', false, 'http://deliveryplus.gr/' );
        // if ($canDo->get('core.edit.state')) {

        //     if (isset($this->items[0]->state)) {
        //         JToolBarHelper::divider();
        //         JToolBarHelper::custom('items.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
        //         JToolBarHelper::custom('items.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        //     } else if (isset($this->items[0])) {
        //         //If this component does not use state then show a direct delete button as we can not trash
        //         JToolBarHelper::deleteList('', 'items.delete', 'JTOOLBAR_DELETE');
        //     }

        //     if (isset($this->items[0]->state)) {
        //         JToolBarHelper::divider();
        //         JToolBarHelper::archiveList('items.archive', 'JTOOLBAR_ARCHIVE');
        //     }
        //     if (isset($this->items[0]->checked_out)) {
        //         JToolBarHelper::custom('items.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
        //     }
        // }

        //Show trash and delete for components that uses the state field
        // if (isset($this->items[0]->state)) {
        //     if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
        //         JToolBarHelper::deleteList('', 'items.delete', 'JTOOLBAR_EMPTY_TRASH');
        //         JToolBarHelper::divider();
        //     } else if ($canDo->get('core.edit.state')) {
        //         JToolBarHelper::trash('items.trash', 'JTOOLBAR_TRASH');
        //         JToolBarHelper::divider();
        //     }
        // }

        // if ($canDo->get('core.admin')) {
        //     JToolBarHelper::preferences('com_deliveryplus');
        // }

        //Set sidebar action - New in 3.0
  //       JHtmlSidebar::setAction('index.php?option=com_deliveryplus&view=items');

  //       $this->extra_sidebar = '';

        // jimport('joomla.form.form');
        // Form::addFormPath(JPATH_COMPONENT . '/models/forms');
        // $form = Form::getInstance('com_deliveryplus.item', 'item');

        // restaurant filter

        // $field = $form->getField('restaurant');

        // $query = $form->getFieldAttribute('filter_restaurant','query');
        // $translate = $form->getFieldAttribute('filter_restaurant','translate');
        // $key = $form->getFieldAttribute('filter_restaurant','key_field');
        // $value = $form->getFieldAttribute('filter_restaurant','value_field');

        // Get the database object.
        // $db = Factory::getDBO();

        // Set the query and get the result list.
        //$db->setQuery($query);
        // $db->execute();
        // $items = $db->loadObjectlist();

        // Build the field options.
//         $options = array();
//         if (!empty($items))
//         {
//             foreach ($items as $item)
//             {
//                 if ($translate == true)
//                 {
//                     $options[] = JHtml::_('select.option', $item->$key, Text::_($item->$value));
//                 }
//                 else
//                 {
//                     $options[] = JHtml::_('select.option', $item->$key, $item->$value);
//                 }
//             }
//         }

//         if (!DeliveryplusHelper::isRestaurantAdmin()) {
//             JHtmlSidebar::addFilter(
//                 'Restaurant',
//                 'filter_restaurant',
//                 JHtml::_('select.options', $options, "value", "text", $this->state->get('filter.restaurant')),
//                 true
//             );
//         }
// print_r($options);
        // category filter

       //  $field = $form->getField('category');

       //  $query = $form->getFieldAttribute('filter_category','query');
       //  $translate = $form->getFieldAttribute('filter_category','translate');
       //  $key = $form->getFieldAttribute('filter_category','key_field');
       //  $value = $form->getFieldAttribute('filter_category','value_field');

       // // Get the database object.
       //  $db = Factory::getDBO();

       //  $restaurantId = $this->state->get('filter.restaurant');
       //  if (!empty($restaurantId)) {
       //      $allowedCats = $db->setQuery('select id from #__deliveryplus_categories where restaurant = '.intval($restaurantId))->loadColumn();
       //  }

       // Set the query and get the result list.
        // $db->setQuery($query);
        // $db->execute();
        // $items = $db->loadObjectlist();

       // Build the field options.
        // $options = array();
        // if (!empty($items))
        // {
        //     foreach ($items as $item)
        //     {
        //         if (!empty($restaurantId) && !empty($item->$key) && !in_array($item->$key, $allowedCats)) {
        //             continue;
        //         }
        //         if ($translate == true)
        //         {
        //             $options[] = JHtml::_('select.option', $item->$key, Text::_($item->$value));
        //         }
        //         else
        //         {
        //             $options[] = JHtml::_('select.option', $item->$key, $item->$value);
        //         }
        //     }
        // }


        // JHtmlSidebar::addFilter(
        //     'Category',
        //     'filter_category',
        //     JHtml::_('select.options', $options, "value", "text", $this->state->get('filter.category')),
        //     true
        // );


		// JHtmlSidebar::addFilter(

		// 	JText::_('JOPTION_SELECT_PUBLISHED'),

		// 	'filter_published',

		// 	JJHtml::_('select.options', JJHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)

		// );

    }

	// protected function getSortFields()
	// {
	// 	return array(
	// 	'a.id' => JText::_('JGRID_HEADING_ID'),
	// 	'a.category' => JText::_('COM_DELIVERYPLUS_ITEMS_CATEGORY'),
	// 	'restaurant_name' => JText::_('COM_DELIVERYPLUS_ITEMS_RESTAURANT'),
	// 	'a.name' => JText::_('COM_DELIVERYPLUS_ITEMS_NAME'),
	// 	'a.price' => JText::_('COM_DELIVERYPLUS_ITEMS_PRICE'),
	// 	'a.vat_percent' => JText::_('COM_DELIVERYPLUS_ITEMS_VAT_PERCENT'),
 //        'a.instock' => JText::_('COM_DELIVERYPLUS_ITEMS_INSTOCK'),
	// 	'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
	// 	'a.state' => JText::_('JSTATUS'),
	// 	);
	// }

}
