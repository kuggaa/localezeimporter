<?php

class Datastore
{
	static $instance;

	private $reader;

	private $current;

	private $datafile;


	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance=new Datastore();
		}

		return self::$instance;
	}


	public function __construct()
	{


	}


	public function __destruct() {

		$this->close();
	}



	public function setDataFile($file='')
	{
		$this->datafile=$file;
	}

	public function open($file='')
	{
		if(!empty($file))
		{
			$this->datafile=$file;
		}

		$this->reader=new XMLReader();
		$this->reader->open($this->datafile);


	}

	public function skipHead()
	{
		while ($this->reader->read() && $this->reader->name !== 'Listing');

	}

	public function close()
	{
		$this->reader->close();
	}


	public function getNext()
	{
		if($this->reader->name !== 'Listing')
		{
			return false;	
		}
		$this->current = new SimpleXMLElement($this->reader->readOuterXML());

		$this->reader->next('Listing');

		return $this->current;


	}

	public function getCurrent()
	{
		return $this->current;
	}
}
