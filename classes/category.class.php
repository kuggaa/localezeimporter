<?php

class Category
{

	private $conn;

	private $data;


	public function __construct($data,$conn)
	{
		$this->conn=$conn;
		$this->data=$data;
	}


	public function __desctruct()
	{
		unset($this->data);
	}

	public function insert()
	{
		
	}
}