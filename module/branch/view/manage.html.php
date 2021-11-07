<?php
/**
 * The manage view file of branch module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2021 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     branch
 * @version     $Id$
 * @link        https://www.zentao.net
 */
?>
<?php include '../../common/view/header.html.php';?>
<?php include '../../common/view/sortable.html.php';?>
<?php $canCreate      = common::hasPriv('branch', 'create');?>
<?php $canBatchEdit   = common::hasPriv('branch', 'batchEdit');?>
<?php $canMergeBranch = common::hasPriv('branch', 'mergeBranch');?>
<?php $canBatchAction = ($canBatchEdit or $canMergeBranch);?>
<div id="mainMenu" class="clearfix">
  <div class="btn-toolbar pull-left">
    <?php foreach(customModel::getFeatureMenu($this->moduleName, $this->methodName) as $menuItem):?>
    <?php if(isset($menuItem->hidden)) continue;?>
    <?php $label  = "<span class='text'>{$menuItem->text}</span>";?>
    <?php $label .= $menuItem->name == $browseType ? " <span class='label label-light label-badge'>{$pager->recTotal}</span>" : '';?>
    <?php $active = $menuItem->name == $browseType ? 'btn-active-text' : '';?>
    <?php echo html::a($this->inlink('manage', "productID=$productID&browseType={$menuItem->name}&orderBy=$orderBy&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}"), $label, '', "class='btn btn-link $active' id='{$menuItem->name}'");?>
    <?php endforeach;?>
  </div>
  <div class="btn-toolbar pull-right">
    <?php if($canCreate) common::printLink('branch', 'create', "productID=$productID", "<i class='icon icon-plus'></i> " . $lang->branch->create, '', "class='btn btn-primary iframe'", true, true);?>
  </div>
</div>
<div id="mainContent">
  <?php if(empty($branchList)):?>
  <div class="table-empty-tip">
    <p>
      <span class="text-muted"><?php echo $lang->branch->noData;?></span>
      <?php if($canCreate) echo html::a($this->createLink('branch', 'create', "productID=$productID", '', true), "<i class='icon icon-plus'></i> " . $lang->branch->create, '', "class='btn btn-info iframe'");?>
    </p>
  </div>
  <?php else:?>
  <form class='main-table table-branch' data-ride='table' method='post' id='branchForm'>
    <table id="branchList" class="table has-sort-head">
      <thead>
        <tr>
          <?php $vars = "productID=$productID&browseType=$browseType&orderBy=%s&recTotal=$pager->recTotal&recPerPage=$pager->recPerPage&pageID=$pager->pageID"; ?>
          <?php if($canBatchAction):?>
          <th class='c-check'>
            <div class="checkbox-primary check-all" title="<?php echo $lang->selectAll?>"><label></label></div>
          </th>
          <?php endif;?>
          <th class='c-order sort-default'><?php echo $lang->branch->order;?></th>
          <th class='text-left'><?php common::printOrderLink('name', $orderBy, $vars, $lang->branch->name);?></th>
          <th class='c-status'><?php common::printOrderLink('status', $orderBy, $vars, $lang->branch->status);?></th>
          <th class='c-date'><?php common::printOrderLink('createdDate', $orderBy, $vars, $lang->branch->createdDate);?></th>
          <th class='c-date'><?php common::printOrderLink('closedDate', $orderBy, $vars, $lang->branch->closedDate);?></th>
          <th class='c-desc'><?php echo $lang->branch->desc;?></th>
          <th class='c-actions-2'><?php echo $lang->actions;?></th>
        </tr>
      </thead>
      <tbody class="sortable">
        <?php foreach($branchList as $branch):?>
        <tr>
          <?php if($canBatchAction):?>
          <td class='cell-id'>
            <?php echo html::checkbox('branchIDList', array($branch->id => ''));?>
          </td>
          <?php endif;?>
          <td class='c-actions sort-handler'><i class="icon icon-move"></i></td>
          <td class='c-name' title='<?php echo $branch->name;?>'><?php echo $branch->name;?></td>
          <td><?php echo zget($lang->branch->statusList, $branch->status);?></td>
          <td><?php echo helper::isZeroDate($branch->createdDate) ? '' : $branch->createdDate;?></td>
          <td><?php echo helper::isZeroDate($branch->closedDate) ? '' : $branch->closedDate;?></td>
          <td class='c-name' title='<?php echo $branch->desc;?>'><?php echo $branch->desc;?></td>
          <td class='c-actions'>
          <?php
            $disabled = $branch->id == BRANCH_MAIN ? 'disabled' : '';
            common::printIcon('branch', 'edit', "branchID=$branch->id", $branch, 'list', '', '', "$disabled iframe", true);
            if($branch->status == 'active')
            {
                common::printIcon('branch', 'close', "branchID=$branch->id", $branch, 'list', 'off', '', $disabled);
            }
            else
            {
                common::printIcon('branch', 'active', "branchID=$branch->id", $branch, 'list', 'active', '', $disabled);
            }
          ?>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
    <div class="table-footer">
      <?php if($canBatchAction):?>
      <div class="checkbox-primary check-all">
        <label><?php echo $lang->selectAll?></label>
      </div>
      <div class="table-actions btn-toolbar">
        <?php echo html::submitButton($lang->edit, '', 'btn');?>
      </div>
      <div class="table-actions btn-toolbar">
        <?php echo html::submitButton($lang->branch->merge, '', 'btn');?>
      </div>
      <?php endif;?>
      <?php $pager->show('right', 'pagerjs');?>
    </div>
  </form>
  <?php endif;?>
</div>
<?php include '../../common/view/footer.html.php';?>
