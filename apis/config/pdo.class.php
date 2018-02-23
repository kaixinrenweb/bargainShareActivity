<?php
header("content-type:text/html;charset=utf-8");

class MyPDO{
	protected static $_instance = null;
	protected $dbName = '';
	protected $dsn;
	protected $dbh;
	
	/**
	 * 构造
	 *
	 * @return MyPDO
	 */
	private function __construct($dbHost, $dbUser, $dbPasswd, $dbName, $dbCharset)
	{
		try {
			$this->dsn = 'mysql:host='.$dbHost.';dbname='.$dbName;
			$this->dbh = new PDO($this->dsn, $dbUser, $dbPasswd);
			$this->dbh->exec('SET character_set_connection='.$dbCharset.', character_set_results='.$dbCharset.', character_set_client=binary');
		} catch (PDOException $e) {
			$this->outputError($e->getMessage());
		}
	}

	/**
	 * 防止克隆
	 *
	 */
	private function __clone() {}

	/**
	 * Singleton instance
	 *
	 * @return Object
	 */
	public static function getInstance($dbHost, $dbUser, $dbPasswd, $dbName, $dbCharset)
	{
		if (self::$_instance === null) {
			self::$_instance = new self($dbHost, $dbUser, $dbPasswd, $dbName, $dbCharset);
		}
		return self::$_instance;
	}

	/**
	 * Query 查询
	 *
	 * @param String $strSql SQL语句
	 * @param String $queryMode 查询方式(All or Row)
	 * @param Boolean $debug
	 * @return Array
	 */
	public function query($strSql, $queryMode = 'all', $debug = false)
	{
		if ($debug === true) $this->debug($strSql);
		$recordset = $this->dbh->query($strSql);
		$this->getPDOError();
		if ($recordset) {
			$recordset->setFetchMode(PDO::FETCH_ASSOC);
			if ($queryMode == 'all') {
				$result = $recordset->fetchAll();
			} elseif ($queryMode == 'row') {
				$result = $recordset->fetch();
			}
		} else {
			$result = false;
		}
		return $result;
	}

	/**
	 * Update 更新
	 *
	 * @param String $table 表名
	 * @param Array $arrayDataValue 字段与值
	 * @param String $where 条件
	 * @param Boolean $debug
	 * @return Int
	 */
	public function update($strSql, $debug = false)
	{
		if ($debug === true) $this->debug($strSql);
		$result = $this->dbh->exec($strSql);
		//$this->getPDOError();
		$result = ($result===false)?false:true;
		return $result;
	}

	/**
	 * Insert 插入
	 *
	 * @param String $table 表名
	 * @param Array $arrayDataValue 字段与值
	 * @param Boolean $debug
	 * @return Int
	 */
	public function insert($strSql, $debug = false)
	{
		if ($debug === true) $this->debug($strSql);
		$result = $this->dbh->exec($strSql);
		//$this->getPDOError();
		return $this->dbh->lastInsertId();
	}

	/**
	 * Delete 删除
	 *
	 * @param String $table 表名
	 * @param String $where 条件
	 * @param Boolean $debug
	 * @return Int
	 */
	public function delete($strSql, $debug = false)
	{
		if ($debug === true) $this->debug($strSql);
		$result = $this->dbh->exec($strSql);
		//$this->getPDOError();
		return $result;
	}

	/**
	 * execSql 执行SQL语句
	 *
	 * @param String $strSql
	 * @param Boolean $debug
	 * @return Int
	 */
	public function execSql($strSql, $debug = false)
	{
		if ($debug === true) $this->debug($strSql);
		$result = $this->dbh->exec($strSql);
		$this->getPDOError();
		return $result;
	}

	/**
	 * 获取指定列的数量
	 *
	 * @param string $table
	 * @param string $field_name
	 * @param string $where
	 * @param bool $debug
	 * @return int
	 */
	public function getCount($table, $field_name, $where = '', $debug = false)
	{
		$strSql = "SELECT COUNT($field_name) AS NUM FROM $table";
		if ($where != '') $strSql .= " WHERE $where";
		if ($debug === true) $this->debug($strSql);
		$arrTemp = $this->query($strSql, 'Row');
		return $arrTemp['NUM'];
	}

	/**
	 * 获取表引擎
	 *
	 * @param String $dbName 库名
	 * @param String $tableName 表名
	 * @param Boolean $debug
	 * @return String
	 */
	public function getTableEngine($dbName, $tableName)
	{
		$strSql = "SHOW TABLE STATUS FROM $dbName WHERE Name='".$tableName."'";
		$arrayTableInfo = $this->query($strSql);
		$this->getPDOError();
		return $arrayTableInfo[0]['Engine'];
	}

	/**
	 * beginTransaction 事务开始
	 */
	private function beginTransaction()
	{
		$this->dbh->beginTransaction();
	}

	/**
	 * commit 事务提交
	 */
	private function commit()
	{
		$this->dbh->commit();
	}

	/**
	 * rollback 事务回滚
	 */
	private function rollback()
	{
		$this->dbh->rollback();
	}

	/**
	 * transaction 通过事务处理多条SQL语句
	 * 调用前需通过getTableEngine判断表引擎是否支持事务
	 *
	 * @param array $arraySql
	 * @return Boolean
	 */
	public function execTransaction($arraySql)
	{
		$retval = 1;
		$this->beginTransaction();
		foreach ($arraySql as $strSql) {
			if ($this->execSql($strSql) == 0) $retval = 0;
		}
		if ($retval == 0) {
			$this->rollback();
			return false;
		} else {
			$this->commit();
			return true;
		}
	}

	/**
	 * getPDOError 捕获PDO错误信息
	 */
	private function getPDOError()
	{
		if ($this->dbh->errorCode() != '00000') {
			$arrayError = $this->dbh->errorInfo();
			$this->outputError($arrayError[2]);
		}
	}

	/**
	 * debug
	 *
	 * @param mixed $debuginfo
	 */
	private function debug($debuginfo)
	{
		var_dump($debuginfo);
		exit();
	}

	/**
	 * 输出错误信息
	 *
	 * @param String $strErrMsg
	 */
	private function outputError($strErrMsg)
	{
		throw new Exception('MySQL Error: '.$strErrMsg);
	}

	/**
	 * destruct 关闭数据库连接
	 */
	public function destruct()
	{
		$this->dbh = null;
	}
}



















