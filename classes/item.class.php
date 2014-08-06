<?php

class Item
{

	private $conn=null;
	private $node=null;

	private $exists=null;

	private $localupdated=null;


	public function __construct($node,$conn=null)
	{
		$this->conn=$conn;
		$this->node=$node;
	}

	public function insert()
	{
		if(!$this->node)
		{
			return false;
		}

		$rev=$this->node['rev'];
		$date=gmdate("Y-m-d g:i:s");
		$pid =(int)$this->node->PID;

		if($this->needsUpdate($pid,$rev))
		{
			$connection=$this->conn->getConnection();
			$data=array();
			$data['title']=(string)$this->node->Who->DisplayName;
			$data['introtext']=$data['title'];
			$data['description']=$data['title'];
			$data['address1']=(string)$this->node->Where->AddressLine1;
			$data['address2']=(string)$this->node->Where->AddressLine2;
			$data['address']=implode(', ',array_filter(array($data['address1'],$data['address2'])));

			$data['city']=(string)$this->node->Where->City;
			$data['statecode']=(string)$this->node->Where->State;
			$data['zip']=(string)$this->node->Where->Zip;
			$data['latitude']=(string)$this->node->Where->Latitude;
			$data['longitude']=(string)$this->node->Where->Longitude;
			$data['latlng']=$data['latitude'].','.$data['longitude'];
			$data['chainid']=(string)$this->node->Who->ChainID;
			$data['tagline']=(string)$this->node->Attrs->TagLine->Val;
			$data['catid']=0;

			//$data['timezone']=(string)$this->node->Attrs->TagLine->Val;
			if(isset($this->node->Attrs->URL->Website))
			{
				$data['website']=(string)$this->node->Attrs->URL->Website->Val;
			}

			if(isset($this->node->Attrs->StandardHours))
			{
				$data['stdhours']=(string)$this->node->Attrs->StandardHours->Val;
			}

			if(isset($this->node->Attrs->FirstYear))
			{
				$data['firstyear']=(string)$this->node->Attrs->FirstYear->Val;
			}

			$phones=array();
			if(isset($this->node->How))
			{
				if(count($this->node->How->children()))
				{
					$count=0;
					foreach($this->node->How->Phone as $phone)
					{
						$p=new stdClass();
						$p->number=(string)$phone->Number;
						$p->type=(string)$phone->Attr->Type;
						$p->date=(string)$phone->Attr->ValDate;
						$phones[$count]=$p;
						if($count==0)
						{
							$data['phone']=$p->number;
							$data['pubdate']=$p->date;
							if(empty($data['pubdate']))
							{
								$data['pubdate']=gmdate("Y-m-d g:i:s");
							}

						}
						$count++;
					}
				}
			}

			$data['phones']=$phones;
			$categories=array();

			if(isset($this->node->Attrs->Headings))	
			{
				$headings=$this->node->Attrs->Headings->children();
				if(count($headings->Heading))
				{
					$count=0;
					foreach($headings->Heading as $heading)
					{
						$cat=new stdClass();
						$cat->id=(int)$heading->NormalizedID;
						$cat->name=(string)$heading->NormalizedHeading;
						$categories[$count]=$cat;

						if($count==0)
						{
							$data['catid']=$cat->id;

						}
						$count++;

					}
				}
			}
			$data['categories']=$categories;


			$emails=array();

			if(isset($this->node->Attrs->Emails))	
			{
				$emailsarray=$this->node->Attrs->Emails->children();
				if(count($emailsarray->Email))
				{
					$count=0;
					foreach($emailsarray->Email as $email)
					{
						$emails[$count]=(string)$email->Val;
					}
				}
			}

			$data['emails']=$emails;
			$payments=array();

			if(isset($this->node->Attrs->PaymentType))
			{
				$paymenttypes=$this->node->Attrs->PaymentType->children();
				if(count($paymenttypes->Val))
				{
					foreach($paymenttypes->Val as $val)
					{
						$payments[]=(string)$val;
					}
				}
			}

			$data['paymenttypes']=$payments;


			if($this->exists){

				//Check Dirty flag for the item

				if(!$this->localupdate)
				{
					$statement=$connection->prepare('UPDATE n7k9w_localeze_businesslist set
						pubdate=?,catid=?,title=?,introtext=?,description=?,address=?,city=?,
						statecode=?,zip=?,phone=?,latitude=?, stdhours=?,tagline=?,chainid=?,revision=?,lastupdated=?
						where pid=?');
					$statement->bind_param('sisssssssssssissi',$data['pubdate'],$data['catid'],$data['title'],$data['introtext'],
						$data['description'],$data['address'],$data['city'],$data['statecode'],$data['zip'],$data['phone'],$data['latlng'],
						$data['stdhours'],$data['tagline'],$data['chainid'],$rev,$date,$pid);
					if($statement->execute())
					{
						echo 'U'.$pid;
						echo "\t";
							//echo "\t".date('Ymdgis');
							//echo "\t".mb_strlen(serialize($data), '8bit');

							//echo PHP_EOL;
					}
					else
					{
						echo PHP_EOL."Error                 : ".$pid."\t".$statement->error.PHP_EOL;
					}

				}
				else
				{
					$statement=$connection->prepare('INSERT into n7k9w_localeze_businesslist_versions 
						(pid,pubdate,catid,title,introtext,description,address,city,
							statecode,zip,phone,latitude, stdhours,tagline,chainid,revision,lastupdated)
					values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

					$statement->bind_param('isisssssssssssiss',$pid,$data['pubdate'],$data['catid'],$data['title'],$data['introtext'],
						$data['description'],$data['address'],$data['city'],$data['statecode'],$data['zip'],$data['phone'],$data['latlng'],
						$data['stdhours'],$data['tagline'],$data['chainid'],$rev,$date);

					if($statement->execute())
					{
						echo 'R'.$pid;
					//echo "\t".date("Ymdgis");
					//echo "\t".mb_strlen(serialize($data), '8bit');
					//echo PHP_EOL;
						echo "\t";

						$statement2=$connection->prepare('UPDATE n7k9w_localeze_businesslist set
							revision=?,lastupdated=?
							where pid=?');
						$statement2->bind_param('isi',$rev,$date,$pid);

						$statement2->execute();
					}
					else
					{
						echo PHP_EOL."Error                 : ".$pid."\t".$statement->error.PHP_EOL;
					}
				}

				
			}
			else
			{
				$statement=$connection->prepare('INSERT into n7k9w_localeze_businesslist 
					(pid,pubdate,catid,title,introtext,description,address,city,
						statecode,zip,phone,latitude, stdhours,tagline,chainid,revision,lastupdated)
				values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

				$statement->bind_param('isisssssssssssiss',$pid,$data['pubdate'],$data['catid'],$data['title'],$data['introtext'],
					$data['description'],$data['address'],$data['city'],$data['statecode'],$data['zip'],$data['phone'],$data['latlng'],
					$data['stdhours'],$data['tagline'],$data['chainid'],$rev,$date);

				if($statement->execute())
				{
					echo 'I'.$pid;
					//echo "\t".date("Ymdgis");
					//echo "\t".mb_strlen(serialize($data), '8bit');
					//echo PHP_EOL;
					echo "\t";
				}
				else
				{
					echo PHP_EOL."Error                 : ".$pid."\t".$statement->error.PHP_EOL;
				}
			}


/*
				if(count($data['phones']))
				{
					foreach($data['phones'] as $phone)
					{
						$phone->pid=$pid;
						$phoneObj=new Phone($this->conn,$phone);
						$phoneObj->insert();
					}
				}*/
/*
			if(count($data['paymenttypes']))
				{
					foreach($data['paymenttypes'] as $payment)
					{
						$payobject=new stdClass();
						$payobject->pid=$pid;
						$payobject->paymenttype=$payment;
						$paymentobj=new Payment($this->conn,$payobject);
						$paymentobj->insert();
					}
				}*/
		}
		else
		{
			echo "NUN\t";
		}
	}

	private function needsUpdate($pid,$rev)
	{
		$query='SELECT pid,revision,lastupdated,localupdate from n7k9w_localeze_businesslist where pid='.$pid;

		$result=$this->conn->execute($query);

		if($result->num_rows>0)
		{
			$this->exists=true;
			$row=$result->fetch_assoc();
			$this->localupdate=$row['localupdate']?true:false;

			if($row['revision']<$rev)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->localupdate=false;
			$this->exists=false;
			return true;
		}
	}


}