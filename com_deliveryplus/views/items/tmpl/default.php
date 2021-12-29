<?php
/**
 * @version     2.0.0
 * @package     com_deliveryplus
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Fakas Agamemnon <info@easylogic.gr> - https://easylogic.gr/
 */

// no direct access
// defined('_JEXEC') or die;

// JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
// JHtml::_('bootstrap.tooltip');
// JHtml::_('behavior.multiselect');
// JHtml::_('formbehavior.chosen', 'select');
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;

// Import CSS
$document = Factory::getDocument();

$wa = $document->getWebAssetManager();
$wa->registerAndUseScript('dp.jquery', 'administrator/components/com_deliveryplus/assets/js/jquery.min.js');

DeliveryplusHelper::loadAssets('css');

$user	= Factory::getUser();
$userId	= $user->get('id');

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_deliveryplus');
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_deliveryplus&task=items.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>
<script type="text/javascript">

	function inStockForChange(itemID,valueSelected,currRow){
	
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: {
			option: 'com_deliveryplus',
			task: 'ajax_update_item_instock',
			item_id: itemID,
			checked: (valueSelected?'1':'0')
		},
		dataType: 'json',
		success: function(response){

			if(response.msg == "#OK#"){ //status value changed so we update colors

			}else{
				alert("Ουπς..κάτι πηγε στραβά!")
			}

		}
	});
}
</script>

<div class="deliveryplus-admin">

	<div class="deliveryplus-main">
		<?php
		// sidebar
		echo LayoutHelper::render('sidebar');
		?>

		<div class="deliveryplus-content">

<form action="<?php echo JRoute::_('index.php?option=com_deliveryplus&view=items'); ?>" method="post" name="adminForm" id="adminForm">
<div class="row">
<div class="col-md-12">
	<div id="j-main-container">
	<?php
	// Search tools bar
	echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>

<?php if (empty($this->items)) : ?>
	<div class="alert alert-info">
		<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
		<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
<?php else : ?>
		<table class="table table-striped" id="itemList">
			<caption class="visually-hidden">
				<?php echo Text::_('COM_DELIVERYPLUS_ITEMS_TABLE_CAPTION'); ?>,
				<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
				<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
			</caption>
			<thead>
				<tr>
					<td class="w-1 text-center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</td>

                <?php if (isset($this->items[0]->ordering)): ?>
					<th scope="col" class="w-1 text-center d-none d-md-table-cell">
						<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
					</th>
                <?php endif; ?>
                <?php if (isset($this->items[0]->state)): ?>
					<th scope="col" class="w-1 text-center d-none d-md-table-cell">
						<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
                <?php endif; ?>

                <th scope="col">
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_DELIVERYPLUS_ITEMS_NAME', 'a.name', $listDirn, $listOrder); ?>
				</th>

                <th scope="col">
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_DELIVERYPLUS_ITEMS_CATEGORY', 'a.category', $listDirn, $listOrder); ?>
				</th>

				<?php if (!DeliveryplusHelper::isRestaurantAdmin()): ?>
				<th scope="col">
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_DELIVERYPLUS_ITEMS_RESTAURANT', 'restaurant_name', $listDirn, $listOrder); ?>
				</th>
                <?php endif; ?>
				<?php if ($this->settings->vat_handling): ?>
				<th scope="col">
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_DELIVERYPLUS_ITEMS_NET_PRICE', 'a.price', $listDirn, $listOrder); ?>
				</th>
				<th scope="col">
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_DELIVERYPLUS_ITEMS_VAT_PERCENT', 'a.vat_percent', $listDirn, $listOrder); ?>
				</th>
				<th scope="col">
				<?php echo Text::_('COM_DELIVERYPLUS_ITEMS_GROSS_PRICE') ?>
				</th>
				<?php else: ?>
				<th scope='col'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_DELIVERYPLUS_ITEMS_PRICE', 'a.price', $listDirn, $listOrder); ?>
				</th>
				<th scope='col'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_DELIVERYPLUS_ITEMS_INSTOCK', 'a.instock', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>

                <?php if (isset($this->items[0]->id)): ?>
					<th scope="col" class="w-3 d-none d-lg-table-cell">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
                <?php endif; ?>
				</tr>
			</thead>

			<tbody<?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
			<?php foreach ($this->items as $i => $item) :
				$item->max_ordering = 0;
				// $ordering   = ($listOrder == 'a.ordering');
                $canCreate	= $user->authorise('core.create',		'com_deliveryplus');
                $canEdit	= $user->authorise('core.edit',			'com_deliveryplus');
                $canCheckin	= $user->authorise('core.manage',		'com_deliveryplus');
                $canChange	= $user->authorise('core.edit.state',	'com_deliveryplus');
				?>
				<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->category_id; ?>">

					<td class="text-center">
						<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
					</td>

					<?php if (isset($this->items[0]->ordering)): ?>
                	<td class="text-center d-none d-md-table-cell">
						<?php
						$iconClass = '';
						if (!$canChange)
						{
							$iconClass = ' inactive';
						}
						elseif (!$saveOrder)
						{
							$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
						}
						?>
						<span class="sortable-handler<?php echo $iconClass ?>">
							<span class="icon-ellipsis-v" aria-hidden="true"></span>
						</span>
						<?php if ($canChange && $saveOrder) : ?>
							<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
						<?php endif; ?>
					</td>
                <?php endif; ?>

                <?php if (isset($this->items[0]->state)): ?>
					<td class="center">
						<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'items.', $canChange, 'cb'); ?>
					</td>
                <?php endif; ?>
				<td>
				<?php if (isset($item->checked_out) && $item->checked_out) : ?>
					<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'items.', $canCheckin); ?>
				<?php endif; ?>
				<?php if ($canEdit) : ?>
					<a href="<?php echo Route::_('index.php?option=com_deliveryplus&task=item.edit&id='.(int) $item->id); ?>">
					<?php echo $this->escape($item->name), empty($item->description) ? '' : '<br/><small class="so-desc">'.$this->escape($item->description).'</small>'; ?></a>
				<?php else : ?>
					<?php echo $this->escape($item->name), empty($item->description) ? '' : '<br/><small class="so-desc">'.$this->escape($item->description).'</small>'; ?>
				<?php endif; ?>
				</td>

				<td>
				<?php if ($canEdit && !empty($item->category_id)) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_deliveryplus&task=category.edit&id='.(int) $item->category_id); ?>">
					<?php echo $this->escape($item->category); ?></a>
				<?php else : ?>
					<?php echo $this->escape($item->category); ?>
				<?php endif; ?>
				</td>

				<?php if (!DeliveryplusHelper::isRestaurantAdmin()): ?>
				<td>
				<?php if ($canEdit && !empty($item->restaurant_id)) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_deliveryplus&task=restaurant.edit&id='.(int) $item->restaurant_id); ?>">
					<?php echo $this->escape($item->restaurant_name); ?></a>
				<?php else : ?>
					<?php echo $this->escape($item->restaurant_name); ?>
				<?php endif; ?>
				</td>
				<?php endif; ?>

				<?php if ($this->settings->vat_handling): ?>
				<td>
					<?php echo DeliveryplusHelper::getFormattedPrice($item->price); ?>
				</td>
				<td>
					<?php echo DeliveryplusHelper::getFormattedPrice(DeliveryplusHelper::getVatPercent($item), false); ?>
				</td>
				<td>
					<?php echo DeliveryplusHelper::getFormattedPrice(DeliveryplusHelper::getGrossPrice($item)); ?>
				</td>
				<?php else: ?>
				<td>
					<?php echo DeliveryplusHelper::getFormattedPrice($item->price); ?>
				</td>
				<?php endif; ?>

				<td>
					<input type="checkbox" class="instock" name="instock" onchange="inStockForChange(<?php echo($item->id) ?>,this.checked,this.parentNode.parentNode)" <?php echo ($this->escape($item->instock)==1)?'checked':''; ?>>
					<!-- < ?php //echo $this->escape($item->instock); ?> -->
				</td>


                <?php if (isset($this->items[0]->id)): ?>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
                <?php endif; ?>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>
		<?php endif; ?>
		
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		
		<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>

			<?php echo LayoutHelper::render('footer'); ?>
		</div>
	</div>

</div>