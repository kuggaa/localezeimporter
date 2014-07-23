<?php

class Payment
{
	private $conn;
	private $item;

	public function __construct($conn,$item=null)
	{
		$this->conn=$conn;
		$this->item=$item;
	}


	public function insert()
	{
		
	}


}