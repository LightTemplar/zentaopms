<?php
/**
 * The model file of mr module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2021 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      dingguodong <dingguodong@easycorp.ltd>
 * @package     mr
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class mrModel extends model
{
    /**
     * The construct method, to do some auto things.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('gitlab');
    }

    /**
     * Get a MR by id.
     *
     * @param  int    $id
     * @param  bool   $process
     * @access public
     * @return object
     */
    public function getByID($id, $process = true)
    {
        $MR =  $this->dao->select('*')->from(TABLE_MR)->where('id')->eq($id)->fetch();

        if($process) return $this->processMR($MR);
        return $MR;
    }

    /**
     * Get MR list of gitlab project.
     *
     * @param  string   $orderBy
     * @param  object   $pager
     * @access public
     * @return array
     */
    public function getList($orderBy = 'id_desc', $pager = null)
    {
        $MRList = $this->dao->select('*')
            ->from(TABLE_MR)
            ->where('deleted')->eq('0')
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');

        foreach($MRList as $MR) $MR = $this->processMR($MR);

        return $MRList;
    }

    /**
     * Process MR info by api. Append extra attributes in GitLab.
     *
     * @param  object    $MR
     * @access public
     * @return object
     */
    public function processMR($MR)
    {
        $rawMR = $this->apiGetSingleMR($MR->gitlabID, $MR->projectID, $MR->mrID);

        $MR->name          = $rawMR->title;
        $MR->sourceProject = $rawMR->source_project_id;
        $MR->sourceBranch  = $rawMR->source_branch;
        $MR->targetProject = $rawMR->target_project_id;
        $MR->targetBranch  = $rawMR->target_branch;
        $MR->canMerge      = $rawMR->merge_status;
        $MR->status        = $rawMR->state;
        return $MR;
    }

    /**
     * Delete one MR.
     *
     * condition: when user deleting a repo.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function deleteMR($id)
    {
        $this->dao->delete()->from(TABLE_MR)
            ->andWhere('id')->eq($id)
            ->exec();
    }

    /**
     * Get gitlab pairs.
     *
     * @access public
     * @return array
     */
    public function getPairs($repoID)
    {
        $MR = $this->dao->select('id,title')
            ->from(TABLE_MR)
            ->where('deleted')->eq('0')
            ->AndWhere('repoID')->eq($repoID)
            ->orderBy('id')->fetchPairs('id', 'title');
        return array('' => '') + $MR;
    }

    /**
     * Create MR function.
     *
     * @access public
     * @return int|bool|object
     */
    public function create()
    {
        $gitlabID  = $this->post->gitlabID;
        $projectID = $this->post->sourceProject;

        $MR = new stdclass;
        $MR->target_project_id = $this->post->targetProject;
        $MR->source_branch     = $this->post->sourceBranch;
        $MR->target_branch     = $this->post->targetBranch;
        $MR->title             = $this->post->title;
        $MR->description       = $this->post->description;
        $MR->assignee_ids      = $this->post->assignee;
        $MR->reviewer_ids      = $this->post->reviewer;

        $rawMR = $this->apiCreateMR($gitlabID, $projectID, $MR);

        /* Another open merge request already exists for this source branch. */
        if(isset($rawMR->message) and !isset($rawMR->iid)) return $rawMR;

        /* Create MR failed. */
        if(!isset($rawMR->iid)) return false;

        $MR = fixer::input('post')
            ->add('repoID', 0)
            ->add('gitlabID', $gitlabID)
            ->add('projectID', $rawMR->project_id) /* sourceProject can be not project of the created MR. */
            ->add('mrID', $rawMR->iid)
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', helper::now())
            ->get();

        /* Remove extra fields before inserting db table. */
        foreach(explode(',', $this->config->MR->create->skippedFields) as $field) unset($MR->$field);

        $this->dao->insert(TABLE_MR)->data($MR)
            ->batchCheck($this->config->MR->create->requiredFields, 'notempty')
            ->autoCheck()
            ->exec();
        if(dao::isError()) return false;

        return $this->dao->lastInsertId();
    }

    /**
     * Update MR function.
     *
     * @access public
     * @return void
     */
    public function update($MRID)
    {
        /* Get MR in zentao database and do not append extra attributes in GitLab. */
        $MR = $this->getByID($MRID, $process = false);
        $MR->title         = $this->post->title;
        $MR->description  = $this->post->description;
        $MR->assignee     = $this->post->assignee;
        $MR->reviewer     = $this->post->reviewer;

        /* Update MR in GitLab. */
        $newMR = new stdclass;
        $newMR->title        = $MR->title;
        $newMR->description  = $MR->description;
        $newMR->assignee     = $MR->assignee;
        $newMR->reviewer     = $MR->reviewer;
        $newMR->targetBranch = $this->post->targetBranch;
        $this->apiUpdateMR($MR->gitlabID, $MR->projectID, $MR->mrID, $newMR);

        /* Update MR in Zentao database. */
        $this->dao->update(TABLE_MR)->data($MR)
            ->batchCheck($this->config->MR->update->requiredFields, 'notempty')
            ->autoCheck()
            ->exec();
        if(dao::isError()) return false;

   }

    /**
     * Create MR by API.
     *
     * @docs   https://docs.gitlab.com/ee/api/merge_requests.html#create-mr
     * @param  int    $gitlabID
     * @param  int    $projectID
     * @param  object $params
     * @access public
     * @return object
     */
    public function apiCreateMR($gitlabID, $projectID, $params)
    {
        $url = sprintf($this->gitlab->getApiRoot($gitlabID), "/projects/$projectID/merge_requests");
        return json_decode(commonModel::http($url, $data=$params, $options = array()));
    }

    /**
     * Get MR list by API.
     *
     * @docs   https://docs.gitlab.com/ee/api/merge_requests.html#list-project-merge-requests
     * @param  int    $gitlabID
     * @param  int    $projectID
     * @access public
     * @return object
     */
    public function apiGetMRList($gitlabID, $projectID)
    {
        $url = sprintf($this->gitlab->getApiRoot($gitlabID), "/projects/$projectID/merge_requests");
        return json_decode(commonModel::http($url));
    }

    /**
     * Get single MR by API.
     *
     * @docs   https://docs.gitlab.com/ee/api/merge_requests.html#get-single-mr
     * @param  int    $gitlabID
     * @param  int    $projectID
     * @param  int    $MRID
     * @access public
     * @return object
     */
    public function apiGetSingleMR($gitlabID, $projectID, $MRID)
    {
        $url = sprintf($this->gitlab->getApiRoot($gitlabID), "/projects/$projectID/merge_requests/$MRID");
        return json_decode(commonModel::http($url));
    }

    /**
     * Update MR by API.
     *
     * @docs   https://docs.gitlab.com/ee/api/merge_requests.html#update-mr
     * @param  int    $gitlabID
     * @param  int    $projectID
     * @param  int    $MRID
     * @param  object $MR
     * @access public
     * @return object
     */
    public function apiUpdateMR($gitlabID, $projectID, $MRID, $MR)
    {
        $url = sprintf($this->gitlab->getApiRoot($gitlabID), "/projects/$projectID/merge_requests/$MRID");
        return json_decode(commonModel::http($url, $MR, $options = array(CURLOPT_CUSTOMREQUEST => 'PUT')));
    }

    /**
     * Delete MR by API.
     *
     * @docs   https://docs.gitlab.com/ee/api/merge_requests.html#delete-a-merge-request
     * @param  int    $gitlabID
     * @param  int    $projectID
     * @param  int    $MRID
     * @access public
     * @return object
     */
    public function apiDeleteMR($gitlabID, $projectID, $MRID)
    {
        $url = sprintf($this->gitlab->getApiRoot($gitlabID), "/projects/$projectID/merge_requests/$MRID");
        return json_decode(commonModel::http($url, null, array(CURLOPT_CUSTOMREQUEST => 'DELETE')));
    }

    /**
     * Accept MR by API.
     *
     * @docs   https://docs.gitlab.com/ee/api/merge_requests.html#accept-mr
     * @param  int    $gitlabID
     * @param  int    $projectID
     * @param  int    $MRID
     * @access public
     * @return object
     */
    public function apiAcceptMR($gitlabID, $projectID, $MRID)
    {
        $url = sprintf($this->gitlab->getApiRoot($gitlabID), "/projects/$projectID/merge_requests/$MRID");
        return json_decode(commonModel::http($url, $data, $options = array(CURLOPT_CUSTOMREQUEST => 'PUT')));
    }

    /**
     * Get MR diff versions by API.
     *
     * @docs   https://docs.gitlab.com/ee/api/merge_requests.html#get-mr-diff-versions
     * @param  int    $gitlabID
     * @param  int    $projectID
     * @param  int    $MRID
     * @access public
     * @return object
     */
    public function apiGetDiffVersions($gitlabID, $projectID, $MRID)
    {
        $url = sprintf($this->gitlab->getApiRoot($gitlabID), "/projects/$projectID/merge_requests/$MRID");
        return json_decode(commonModel::http($url));
    }

    /**
     * Get single diff version by API.
     *
     * @docs   https://docs.gitlab.com/ee/api/merge_requests.html#get-a-single-mr-diff-version
     * @param  int    $gitlabID
     * @param  int    $projectID
     * @param  int    $MRID
     * @param  int    $versionID
     * @access public
     * @return object
     */
    public function apiGetSingleDiffVersion($gitlabID, $projectID, $MRID, $versionID)
    {
        $url = sprintf($this->gitlab->getApiRoot($gitlabID), "/projects/$projectID/merge_requests/$MRID/versions/$versionID");
        return json_decode(commonModel::http($url));
    }
}

