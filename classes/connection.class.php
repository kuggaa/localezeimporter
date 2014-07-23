<?php

class Connection
{
	static  $connection=null;

	private $conn;

	private $db;


	public static function getInstance()
	{
		if(!self::$connection)
		{
			self::$connection=new Connection();
		}

		return self::$connection;
	}


	public function __construct()
	{
		$this->open();
		
	}

	public function __destruct() {

		$this->close();
	}

	public function open()
	{
		global $config;
		$this->conn=new mysqli($config['database']['host'],$config['database']['username'],$config['database']['password'],$config['database']['database']);

	}

	public function getConnection()
	{
		return $this->conn;
	}

	public function execute($query)
	{
		if(!$result = $this->conn->query($query)){
			echo 'There was an error running the query [' . $this->conn->error . ']';
			return false;
		}

		return $result;
	}


	public function close()
	{
		$this->conn->close();
	}
}