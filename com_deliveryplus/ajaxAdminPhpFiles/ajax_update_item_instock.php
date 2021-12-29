<?php
/**
 * @package    DeliveryPlus for Joomla 3.x
 * @author     Ákos Szabó
 * @copyright  Copyright (C) 2013- Ákos Szabó All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see        LICENSE.txt
 */

//require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'deliveryplus.php');
//require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'menu.php');

function sendResponse(array $data) {
    header('Content-type: text/json;charset=utf-8');
    echo json_encode($data);
    exit;
}

function sendMessage($message) {
    sendResponse(array('msg' => $message));
}

$mainframe = JFactory::getApplication();

$_item_id = JRequest::getInt('item_id', '', 'post');
$_checked = JRequest::getInt('checked', '', 'post');

if (!($_checked > '') && !($_item_id > '')){
	sendMessage("#NOTOK#");
}

$db = JFactory::getDBO();


$query = sprintf("UPDATE #__deliveryplus_items SET instock=%d WHERE id=%d", intval($_checked),intval($_item_id));
$db->setQuery($query)->execute();

sendMessage("#OK#");

$mainframe->close();