<?php
/**
 * The control file of action module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     action
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class action extends control
{
    /**
     * Create a action or delete all patch actions, this method is used by the Ztools.
     *
     * @param  string $objectType
     * @param  string $actionType
     * @param  string $objectName
     * @access public
     * @return void
     */
    public function create($objectType, $actionType, $objectName)
    {
        $actionID = $this->action->create($objectType, 0, $actionType, '', $objectName);

        if($actionID)
        {
            $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess));
        }
        else
        {
            $this->send(array('result' => 'fail', 'message' => 'error'));
        }
    }


    /**
     * Trash.
     *
     * @param  string $browseType
     * @param  string $type all|hidden
     * @param  bool   $byQuery
     * @param  int    $queryID
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function trash($browseType = 'all', $type = 'all', $byQuery = false, $queryID = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->loadModel('backup');

        /* Save session. */
        $uri = $this->app->getURI(true);
        $this->session->set('productList',        $uri, 'product');
        $this->session->set('productPlanList',    $uri, 'product');
        $this->session->set('storyList',          $uri, 'product');
        $this->session->set('releaseList',        $uri, 'product');
        $this->session->set('programList',        $uri, 'program');
        $this->session->set('projectList',        $uri, 'project');
        $this->session->set('executionList',      $uri, 'execution');
        $this->session->set('taskList',           $uri, 'execution');
        $this->session->set('buildList',          $uri, 'execution');
        $this->session->set('bugList',            $uri, 'qa');
        $this->session->set('caseList',           $uri, 'qa');
        $this->session->set('testtaskList',       $uri, 'qa');
        $this->session->set('docList',            $uri, 'doc');
        $this->session->set('opportunityList',    $uri, 'project');
        $this->session->set('riskList',           $uri, 'project');
        $this->session->set('trainplanList',      $uri, 'project');
        $this->session->set('roomList',           $uri, 'admin');
        $this->session->set('researchplanList',   $uri, 'project');
        $this->session->set('researchreportList', $uri, 'project');
        $this->session->set('meetingList',        $uri, 'project');
        $this->session->set('designList',         $uri, 'project');
        $this->session->set('storyLibList',       $uri, 'assetlib');
        $this->session->set('issueLibList',       $uri, 'assetlib');
        $this->session->set('riskLibList',        $uri, 'assetlib');
        $this->session->set('opportunityLibList', $uri, 'assetlib');
        $this->session->set('practiceLibList',    $uri, 'assetlib');
        $this->session->set('componentLibList',   $uri, 'assetlib');

        /* Save the object name used to replace the search language item. */
        $this->session->set('objectName', zget($this->lang->action->objectTypes, $browseType, ''), 'admin');

        /* Build the search form. */
        $queryID   = (int)$queryID;
        $actionURL = $this->createLink('action', 'trash', "browseType=$browseType&type=$type&byQuery=true&queryID=myQueryID");
        $this->action->buildTrashSearchForm($queryID, $actionURL);

        /* Get deleted objects. */
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Append id for secend sort. */
        $sort           = common::appendOrder($orderBy);
        $trashes        = $byQuery ? $this->action->getTrashesBySearch($browseType, $type, $queryID, $sort, $pager) : $this->action->getTrashes($browseType, $type, $sort, $pager);
        $objectTypeList = $this->action->getTrashObjectTypes($type);
        $objectTypeList = array_keys($objectTypeList);

        $preferredType       = array();
        $moreType            = array();
        $preferredTypeConfig = $this->config->systemMode == 'new' ? $this->config->action->preferredType->new : $this->config->action->preferredType->classic;
        foreach($objectTypeList as $objectType)
        {
            if(!isset($this->config->objectTables[$objectType])) continue;
            in_array($objectType, $preferredTypeConfig) ? $preferredType[$objectType] = $objectType : $moreType[$objectType] = $objectType;
        }
        if(count($preferredType) < $this->config->action->preferredTypeNum)
        {
            $toPreferredType = array_splice($moreType, 0, $this->config->action->preferredTypeNum - count($preferredType));
            $preferredType   = $preferredType + $toPreferredType;
        }

        /* Get the project name of executions. */
        if($browseType == 'execution')
        {
            $this->app->loadLang('execution');
            $projectIdList = array();
            foreach($trashes as $trash) $projectIdList[] = $trash->project;
            $this->view->projectList = $this->loadModel('project')->getByIdList($projectIdList, 'all');
        }

        /* Title and position. */
        $this->view->title      = $this->lang->action->trash;
        $this->view->position[] = $this->lang->action->trash;

        $this->view->trashes             = $trashes;
        $this->view->type                = $type;
        $this->view->currentObjectType   = $browseType;
        $this->view->orderBy             = $orderBy;
        $this->view->pager               = $pager;
        $this->view->users               = $this->loadModel('user')->getPairs('noletter');
        $this->view->preferredType       = $preferredType;
        $this->view->moreType            = $moreType;
        $this->view->preferredTypeConfig = $preferredTypeConfig;
        $this->view->byQuery             = $byQuery;
        $this->view->queryID             = $queryID;

        $this->display();
    }

    /**
     * Undelete an object.
     *
     * @param  int    $actionID
     * @param  string $browseType
     * @param  string $confirmChange
     * @access public
     * @return void
     */
    public function undelete($actionID, $browseType = 'all', $confirmChange = 'no')
    {
        $oldAction = $this->action->getById($actionID);
        $extra     = $oldAction->extra == ACTIONMODEL::BE_HIDDEN ? 'hidden' : 'all';

        if(in_array($oldAction->objectType, array('program', 'project', 'execution', 'product')))
        {
            if($oldAction->objectType == 'product')
            {
                $product      = $this->dao->select('*')->from(TABLE_PRODUCT)->where('id')->eq($oldAction->objectID)->fetch();
                $programID    = isset($product->program) ? $product->program : 0;
                $repeatObject = $this->dao->select('*')->from(TABLE_PRODUCT)
                    ->where('id')->ne($oldAction->objectID)
                    ->andWhere("(name = '{$product->name}' and program = {$programID})", true)
                    ->beginIF($product->code)->orWhere("code = '{$product->code}'")->fi()
                    ->markRight(1)
                    ->andWhere('deleted')->eq('0')
                    ->fetch();
            }
            else
            {
                $project       = $this->dao->select('*')->from(TABLE_PROJECT)->where('id')->eq($oldAction->objectID)->fetch();
                $sprintProject = isset($project->project) ? $project->project : '0';
                $repeatObject  = $this->dao->select('*')->from(TABLE_PROJECT)
                    ->where('id')->ne($oldAction->objectID)
                    ->beginIF($oldAction->objectType == 'program' or $oldAction->objectType == 'project')->andWhere("(name = '{$project->name}' and parent = {$project->parent})", true)->fi()
                    ->beginIF($oldAction->objectType == 'execution')->andWhere("(name = '{$project->name}' and project = {$sprintProject})", true)->fi()
                    ->beginIF($oldAction->objectType == 'project' and $project->code)->orWhere("(code = '{$project->code}' and model = '$project->model')")->fi()
                    ->beginIF($oldAction->objectType == 'execution' and $project->code)->orWhere("code = '{$project->code}'")->fi()
                    ->markRight(1)
                    ->beginIF($oldAction->objectType == 'program')->andWhere('type')->eq('program')->fi()
                    ->beginIF($oldAction->objectType == 'project')->andWhere('type')->eq('project')->fi()
                    ->beginIF($oldAction->objectType == 'execution')->andWhere('type')->in('sprint,stage,kanban')->fi()
                    ->andWhere('deleted')->eq('0')
                    ->fetch();
            }

            if($repeatObject)
            {
                $table  = $oldAction->objectType == 'product' ? TABLE_PRODUCT : TABLE_PROJECT;
                $object = $oldAction->objectType == 'product' ? $product : $project;

                $existNames = $this->dao->select('name')->from($table)->where('name')->like($repeatObject->name . '_%')->fetchPairs();
                for($i = 1; $i < 10000; $i ++)
                {
                    $replaceName = $repeatObject->name . '_' . $i;
                    if(!in_array($replaceName, $existNames)) break;
                }
                $replaceCode = '';
                if($object->code)
                {
                    $existCodes = $this->dao->select('code')->from($table)->where('code')->like($repeatObject->code . '_%')->fetchPairs();
                    for($i = 1; $i < 10000; $i ++)
                    {
                        $replaceCode = $repeatObject->code . '_' . $i;
                        if(!in_array($replaceCode, $existCodes)) break;
                    }
                }

                if($repeatObject->name == $object->name and $repeatObject->code and $repeatObject->code == $object->code)
                {
                    if($confirmChange == 'no') return print(js::confirm(sprintf($this->lang->action->repeatChange, $this->lang->{$oldAction->objectType}->common, $replaceName, $replaceCode), $this->createLink('action', 'undelete', "action={$actionID}&browseType={$browseType}&confirmChange=yes")));
                    if($confirmChange == 'yes') $this->dao->update($table)->set('name')->eq($replaceName)->set('code')->eq($replaceCode)->where('id')->eq($oldAction->objectID)->exec();
                }
                elseif($repeatObject->name == $object->name)
                {
                    if($confirmChange == 'no') return print(js::confirm(sprintf($this->lang->action->nameRepeatChange, $this->lang->{$oldAction->objectType}->common, $replaceName), $this->createLink('action', 'undelete', "action={$actionID}&browseType={$browseType}&confirmChange=yes")));
                    if($confirmChange == 'yes') $this->dao->update($table)->set('name')->eq($replaceName)->where('id')->eq($oldAction->objectID)->exec();
                }
                elseif($repeatObject->code and $repeatObject->code == $object->code)
                {
                    if($confirmChange == 'no') return print(js::confirm(sprintf($this->lang->action->codeRepeatChange, $this->lang->{$oldAction->objectType}->common, $replaceCode), $this->createLink('action', 'undelete', "action={$actionID}&browseType={$browseType}&confirmChange=yes")));
                    if($confirmChange == 'yes') $this->dao->update($table)->set('code')->eq($replaceCode)->where('id')->eq($oldAction->objectID)->exec();
                }
            }
        }

        $this->action->undelete($actionID);

        $sameTypeObjects = $this->action->getTrashes($oldAction->objectType, $extra, 'id_desc', null);
        $browseType      = ($sameTypeObjects and $browseType != 'all') ? $oldAction->objectType : 'all';

        return print(js::locate($this->createLink('action', 'trash', "browseType=$browseType&type=$extra"), 'parent'));
    }

    /**
     * Hide an deleted object.
     *
     * @param  int    $actionID
     * @param  string $browseType
     * @access public
     * @return void
     */
    public function hideOne($actionID, $browseType = 'all')
    {
        $oldAction = $this->action->getById($actionID);

        $this->action->hideOne($actionID);

        $sameTypeObjects = $this->action->getTrashes($oldAction->objectType, 'all', 'id_desc', null);
        $browseType      = ($sameTypeObjects and $browseType != 'all') ? $oldAction->objectType : 'all';

        return print(js::locate($this->createLink('action', 'trash', "browseType=$browseType"), 'parent'));
    }

    /**
     * Hide all deleted objects.
     *
     * @param  string $confirm yes|no
     * @access public
     * @return void
     */
    public function hideAll($confirm = 'no')
    {
        if($confirm == 'no')
        {
            echo js::confirm($this->lang->action->confirmHideAll, inlink('hideAll', "confirm=yes"));
        }
        else
        {
            $this->action->hideAll();
            echo js::reload('parent');
        }
    }

    /**
     * Comment.
     *
     * @param  string $objectType
     * @param  int    $objectID
     * @access public
     * @return void
     */
    public function comment($objectType, $objectID)
    {
        if(strtolower($objectType) == 'task')
        {
            $task       = $this->loadModel('task')->getById($objectID);
            $executions = explode(',', $this->app->user->view->sprints);
            if(!in_array($task->execution, $executions)) return print(js::error($this->lang->error->accessDenied));
        }
        elseif(strtolower($objectType) == 'story')
        {
            $story      = $this->loadModel('story')->getById($objectID);
            $executions = explode(',', $this->app->user->view->sprints);
            $products   = explode(',', $this->app->user->view->products);
            if(!array_intersect(array_keys($story->executions), $executions) and !in_array($story->product, $products) and empty($story->lib)) return print(js::error($this->lang->error->accessDenied));
        }

        $actionID = $this->action->create($objectType, $objectID, 'Commented', $this->post->comment);
        if(defined('RUN_MODE') && RUN_MODE == 'api')
        {
            return $this->send(array('status' => 'success', 'data' => $actionID));
        }
        else
        {
            echo js::reload('parent');
        }
    }

    /**
     * Edit comment of a action.
     *
     * @param  int    $actionID
     * @access public
     * @return void
     */
    public function editComment($actionID)
    {
        if(strlen(trim(strip_tags($this->post->lastComment, '<img>'))) != 0)
        {
            $this->action->updateComment($actionID);
        }
        else
        {
            dao::$errors['submit'][] = $this->lang->action->historyEdit;
            return $this->send(array('result' => 'fail', 'message' => dao::getError()));
        }
        return $this->send(array('result' => 'success', 'locate' => 'reload'));
    }
}
