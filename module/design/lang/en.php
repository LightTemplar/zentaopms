<?php
/**
 * The English file of design module.
 *
 * @copyright   Copyright 2009-2020 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     design
 * @version     $Id: en.php 4729 2013-05-03 07:53:55Z chencongzhi520@gmail.com $
 * @link        http://www.zentao.net
 */
/* Field list. */
$lang->design->id            = 'ID';
$lang->design->name          = 'Name';
$lang->design->story         = 'Story';
$lang->design->type          = 'Type';
$lang->design->ditto         = 'Ditto';
$lang->design->submission    = 'Commit';
$lang->design->version       = 'Revision';
$lang->design->assignedTo    = 'AssignedTo';
$lang->design->actions       = 'Actions';
$lang->design->byQuery       = 'Search';
$lang->design->products      = 'Linked Product';
$lang->design->story         = 'Linked Story';
$lang->design->file          = 'File';
$lang->design->desc          = 'Description';
$lang->design->range          = 'Impact';
$lang->design->product       = 'Linked Product';
$lang->design->basicInfo     = 'Basic Information';
$lang->design->commitDate    = 'Committed Date';
$lang->design->commitBy      = 'Commit By';
$lang->design->affectedStory = "{$lang->storyCommon}";
$lang->design->affectedTasks = 'Task';
$lang->design->reviewObject  = 'Review Object';
$lang->design->createdBy     = 'CreatedBy';
$lang->design->createdDate   = 'CreatedDate';
$lang->design->basicInfo     = 'Basic Information';
$lang->design->noAssigned    = 'Unassigned';

/* Action list. */
$lang->design->common        = 'Design';
$lang->design->create        = 'Create Design';
$lang->design->batchCreate   = 'Batch Create';
$lang->design->edit          = 'Edit';
$lang->design->delete        = 'Delete';
$lang->design->view          = 'View';
$lang->design->browse        = 'List';
$lang->design->commit        = 'Link Commit';
$lang->design->submit        = 'Submit Review';
$lang->design->assignTo      = 'Assign';
$lang->design->revision      = 'Linked Code';

/* Field value. */
$lang->design->typeList         = array();
$lang->design->typeList['']     = '';
$lang->design->typeList['HLDS'] = 'High-Level';
$lang->design->typeList['DDS']  = 'Detailed';
$lang->design->typeList['DBDS'] = 'Database';
$lang->design->typeList['ADS']  = 'API';

$lang->design->rangeList           = array();
$lang->design->rangeList['all']    = 'All';
$lang->design->rangeList['assign'] = 'Selected';

$lang->design->featureBar['all'] = 'All';
$lang->design->featureBar += $lang->design->typeList;

/* Prompt information. */
$lang->design->errorSelection = 'No record selected!';
$lang->design->noDesign       = 'No record.';
$lang->design->noCommit       = 'No record commited.';
$lang->design->confirmDelete = 'Do you want to delete this design?';
