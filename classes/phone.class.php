<?php

class Phone
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
		if(!$this->item)
			return false;
		if(!$this->exists($this->item->pid,$this->item->number,$this->item->type))
		{
						$connection=$this->conn->getConnection();

			$statement=$connection->prepare('INSERT into n7k9w_localeze_businesslist_phones
				(item_pid,phone_for,phone_number)
			values(?,?,?)');

			$statement->bind_param('iss',$this->item->pid,$this->item->type,$this->item->number);

			$statement->execute();

		}
	}

	private function exists($pid,$phonenumber,$phonetype)
	{
		$query='SELECT * from n7k9w_localeze_businesslist_phones where item_pid='.$pid.' AND phone_for="'.$phonetype.'" AND phone_number="'.$phonenumber.'"';

		$result=$this->conn->execute($query);

		if($result->num_rows>0)
		{
			return true;
		}
		else
		{
			return false;
		}


	}
}