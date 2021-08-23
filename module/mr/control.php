<?php
class mr extends control
{
    /**
     * Browse mr.
     *
     * @param  int    $objectID
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($objectID = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $this->view->title    = $this->lang->mr->common . $this->lang->colon . $this->lang->mr->browse;
        $this->view->MRList   = $this->mr->getList($orderBy, $pager);
        $this->view->orderBy  = $orderBy;
        $this->view->objectID = $objectID;
        $this->view->pager    = $pager;
        $this->display();
    }

    /**
     * Create MR function.
     *
     * @access public
     * @return void
     */
    public function create()
    {
        if($_POST)
        {
            $result = $this->mr->create();
            return $this->send($result);
        }

        $this->view->title       = $this->lang->mr->create;
        $this->view->gitlabHosts = $this->loadModel('gitlab')->getPairs();
        $this->display();
    }

    /**
     * Edit MR function.
     *
     * @access public
     * @return void
     */
    public function edit($MRID)
    {
        if($_POST)
        {
            $result = $this->mr->edit($MRID);
            return $result;
        }

        $MR = $this->mr->getByID($MRID);
        $this->view->MR    = $MR;
        $this->view->title = $this->lang->mr->edit;
        $this->view->users = array("" => "") + $this->loadModel('gitlab')->getUserIdRealnamePairs($MR->gitlabID); /* Get user list for assignee and reviewer. */

        $this->display();
    }

    /**
     * Delete a mr.
     *
     * @param  int    $MR
     * @access public
     * @return void
     */
    public function delete($id, $confim = 'no')
    {
        if($confim != 'yes') die(js::confirm($this->lang->gitlab->confirmDelete, inlink('delete', "id=$id&confirm=yes")));

        $MRList = $this->mr->getByID($id);

        $this->mr->apiDeleteMR($MRList->gitlabID, $MRList->projectID, $MRList->mrID);
        $this->mr->deleteMR($id);
        die(js::reload('parent'));
    }

    /**
     * View a MR.
     *
     * @access public
     * @return void
     */
    public function view($id)
    {
        $MR = $this->mr->getByID($id);
        if(isset($MR->gitlabID)) $rawMR = $this->mr->apiGetSingleMR($MR->gitlabID, $MR->projectID, $MR->mrID);

        $this->view->title = $this->lang->mr->view;
        $this->view->MR    = $MR;
        $this->view->rawMR = isset($rawMR) ? $rawMR : false;

        $this->display();
    }

    /**
     * AJAX: Get MR target projects.
     *
     * @param  int    $gitlabID
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function ajaxGetMRTragetProjects($gitlabID, $projectID)
    {
        $this->loadModel('gitlab');
        /* First step: get forks. */
        $projects = $this->gitlab->apiGetForks($gitlabID, $projectID);
        /* Second step: get project itself. */
        $projects[] = $this->gitlab->apiGetSingleProject($gitlabID, $projectID);

        /* Last step: find its upstream recursively. */
        $project = $this->gitlab->apiGetUpstream($gitlabID, $projectID);
        if(!empty($project)) $projects[] = $project;

        while(!empty($project) and isset($project->id))
        {
            $project = $this->gitlab->apiGetUpstream($gitlabID, $project->id);
            if(empty($project)) break;
            $projects[] = $project;
        }

        if(!$projects) return $this->send(array('message' => array()));

        $options = "<option value=''></option>";
        foreach($projects as $project)
        {
            $options .= "<option value='{$project->id}' data-name='{$project->name}'>{$project->name_with_namespace}</option>";
        }

        $this->send($options);
    }
}
