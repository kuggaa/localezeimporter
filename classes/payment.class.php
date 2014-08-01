<?php

class Payment
{
	private $conn;
	private $item;

	private static $paymenttypearrays=array();

	public function __construct($conn,$item=null)
	{
		$this->conn=$conn;
		$this->item=$item;
		self::$paymenttypearrays=array();
		self::$paymenttypearrays['Cash']=1;
		self::$paymenttypearrays['Check']=2;
		self::$paymenttypearrays['Visa']=3;
		self::$paymenttypearrays['Master Card']=4;
		self::$paymenttypearrays['Discover']=5;
		self::$paymenttypearrays['American Express']=6;
		self::$paymenttypearrays['Diners']=7;
		self::$paymenttypearrays['Debit']=8;
		
	}


	public function insert()
	{
		if(!$this->item)
			return false;

		$paymentid=self::$paymenttypearrays[$this->item->paymenttype];
		if(!$this->exists($this->item->pid,$paymentid))
		{
			$connection=$this->conn->getConnection();

			$statement=$connection->prepare('INSERT into n7k9w_localeze_companypaymenttypes
				(pid,paymenttypeid)
				values(?,?)');

			$statement->bind_param('ii',$this->item->pid,$paymentid);

			$statement->execute();

		}
	}

	private function exists($pid,$paymentid)
	{
	 	$query='SELECT * from n7k9w_localeze_companypaymenttypes where pid='.$pid.' AND paymenttypeid='.$paymentid;

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