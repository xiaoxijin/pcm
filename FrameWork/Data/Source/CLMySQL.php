<?php
namespace Xphp\Data\Source;


/**
 * MySQL数据库封装类
 *
 *
 */
class CLMySQL implements \Xphp\IFace\Database {
	public $debug = false;
	public $conn = null;
	public $config, $error = '';
	const DEFAULT_PORT = 9701;

	function __construct($db_config) {
		if (empty($db_config['port'])) {
			$db_config['port'] = self::DEFAULT_PORT;
		}
		$this->config = $db_config;
	}

	/**
	 * 连接数据库
	 *
	 * @see Xphp.\Xphp\IFace\Database::connect()
	 */
	function connect() {
		$db_config = $this->config;
		$this->conn = \Xphp\Data\Client\CLMySQL::connect($db_config['host'], $db_config['port'], empty($db_config['persistent']) ? false : true);
		if (!$this->conn) {
			\Xphp\Exception\Error::info(__CLASS__ . " SQL Error", \Xphp\Data\Client\CLMySQL::get_last_erro_msg($this->conn));
			return false;
		}
		if (!\Xphp\Data\Client\CLMySQL::select_db($db_config['name'], $this->conn)) {
			\Xphp\Exception\Error::info("SQL Error", \Xphp\Data\Client\CLMySQL::get_last_erro_msg($this->conn));
		}
		if ($db_config['setname']) {
			if (!\Xphp\Data\Client\CLMySQL::query('set names ' . $db_config['charset'], $this->conn)) {
				\Xphp\Exception\Error::info("SQL Error", \Xphp\Data\Client\CLMySQL::get_last_erro_msg($this->conn));
			}
		}
		return true;
	}

	function errorMessage($sql) {
		return \Xphp\Data\Client\CLMySQL::get_last_erro_msg($this->conn) . "(" . \Xphp\Data\Client\CLMySQL::get_last_errno($this->conn) . ")" . "<hr />$sql<hr />MySQL Server: {$this->config['host']}:{$this->config['port']}";
	}

	/**
	 * 执行一个SQL语句
	 *
	 * @param string $sql 执行的SQL语句
	 *
	 * @return MySQLRecord | false
	 */
	function query($sql) {
		$res = false;

		for ($i = 0; $i < 2; $i++) {
			$res = \Xphp\Data\Client\CLMySQL::query($sql, $this->conn);
			if ($res === false) {
				if (\Xphp\Data\Client\CLMySQL::get_last_errno($this->conn) == 2006 or \Xphp\Data\Client\CLMySQL::get_last_errno($this->conn) == 2013) {
					$r = $this->checkConnection();
					if ($r === true) {
						continue;
					}
				}
				\Xphp\Exception\Error::info(__CLASS__ . " SQL Error", $this->errorMessage($sql));
				return false;
			}
			break;
		}

		if (!$res) {
			\Xphp\Exception\Error::info(__CLASS__ . " SQL Error", $this->errorMessage($sql));
			return false;
		}
		if (is_bool($res)) {
			return $res;
		}
		return new CLMySQLRecord($res);
	}

	/**
	 * 返回上一个Insert语句的自增主键ID
	 * @return int
	 */
	function lastInsertId() {
		return \Xphp\Data\Client\CLMySQL::insert_id($this->conn);
	}

	function quote($value) {
		return mysql_escape_string($value);
		#return addslashes($value);
	}

	/**
	 * 检查数据库连接,是否有效，无效则重新建立
	 */
	protected function checkConnection() {
		if (!@$this->ping()) {
			$this->close();
			return $this->connect();
		}
		return true;
	}

	function ping() {
		//
		return \Xphp\Data\Client\CLMySQL::query("ping", $this->conn);
	}

	/**
	 * 获取上一次操作影响的行数
	 *
	 * @return int
	 */
	function affected_rows() {
		return \Xphp\Data\Client\CLMySQL::affected_rows($this->conn);
	}

	/**
	 * 关闭连接
	 *
	 * @see libs/system/\Xphp\IFace\Database#close()
	 */
	function close() {
		\Xphp\Data\Client\CLMySQL::close($this->conn);
	}

	/**
	 * 获取受影响的行数
	 * @return int
	 */
	function getAffectedRows() {
		return \Xphp\Data\Client\CLMySQL::affected_rows($this->conn);
	}

	/**
	 * 获取错误码
	 * @return int
	 */
	function errno() {
		$this->error = \Xphp\Data\Client\CLMySQL::get_last_erro_msg($this->conn);
		return \Xphp\Data\Client\CLMySQL::get_last_errno($this->conn);
	}
}

class CLMySQLRecord implements \Xphp\IFace\DbRecord {
	public $result;
	private $seek = 0;

	function __construct($result) {
		$this->result = $result;
	}

	function fetch() {
		return \Xphp\Data\Client\CLMySQL::fetch_row($this->result, $this->seek++);
	}

	function fetchall() {
		return \Xphp\Data\Client\CLMySQL::fetch($this->result);
	}

	function free() {
		\Xphp\Data\Client\CLMySQL::free_result($this->result);
	}
}
