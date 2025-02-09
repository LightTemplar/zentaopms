<?php
/**
 *本文件主要进行初始化数据库实例，运行脚本时调度初始化的数据库
 *
 * All request of entries should be routed by this router.
 *
 * @copyright   Copyright 2009-2017 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      liyang <liyang@easycorp.ltd>
 * @package     ZenTaoPMS
 * @version     $Id: $
 * @link        http://www.zentao.net/
 */
class db
{
    /**
     * global config
     *
     * @var object
     * @access public
     */
    public $config;

    /**
     * global app
     *
     * @var object
     * @access public
     */
    public $app;

    /**
     * switch DB file
     *
     * @var string
     * @access public
     */
    public $dbLogFile;

    /**
     * init DB file
     *
     * @var string
     * @access public
     */
    public $sqlFile;

    /**
     * global dao
     *
     * @var object
     * @access public
     */
    public $dao;

    /**
     * __construct function load app config dao.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        global $config, $app, $dao;
        $this->config    = $config;
        $this->app       = $app;
        $this->dao       = $dao;
        $this->dbLogFile = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'test/tmp/dblog.php';
        $this->sqlFile   = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'test/tmp/raw.sql';
    }

    /**
     * switch database.
     *
     * @access public
     * @return void
     */
    function switchDB()
    {
        global $tester, $config;
        $dbUsed = $this->getUsedDbList();
        $lastDB = 0;
        if(!empty($dbUsed)) $lastDB = max($dbUsed);

        /* If all db is used. */
        if($lastDB == $this->config->test->dbNum)
        {
            shell_exec("> $this->dbLogFile");
            $this->initDB();
            $lastDB = 0;
        }

        $dbSN   = $lastDB + 1;
        $dbName = $config->test->dbPrefix . $dbSN;

        $this->dao->query("use $dbName");

        $tester = $this->app->loadCommon();
        $logHandle = fopen($this->dbLogFile, 'a');
        fwrite($logHandle, $dbSN . PHP_EOL);
        fclose($logHandle);
    }

    /**
     * restore database.
     *
     * @access public
     * @return void
     */
    function restoreDB()
    {
        $this->dao->query("use " . $this->config->test->rawDB);
    }


    /**
     * get used DbList.
     *
     * @access public
     * @return array
     */
    function getUsedDbList()
    {
        $dbUsed = explode("\n", file_get_contents($this->dbLogFile));
        $dbUsed = array_unique($dbUsed);

        foreach($dbUsed as $key => $db)
        {
            if(!is_numeric($db)) unset($dbUsed[$key]);
        }

        return $dbUsed;
    }


    /**
     * Init all test databases.
     *
     * @access public
     * @return void
     */
    function initDB()
    {
        for($i = 1; $i <= $this->config->test->dbNum; $i++)
        {
            $this->dao->query('DROP DATABASE ' . $this->config->test->dbPrefix . $i);
            $this->dao->query('CREATE DATABASE ' . $this->config->test->dbPrefix . $i);

            shell_exec("mysql -h{$this->config->db->host} -u {$this->config->db->user} -p{$this->config->db->password} -P {$this->config->db->port} {$this->config->test->dbPrefix}{$i} < {$this->sqlFile}");
        }
    }
}
