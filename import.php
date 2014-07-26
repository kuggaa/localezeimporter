<?php

$starttime=time();
error_reporting(-1);

$basepath=dirname(__FILE__);
require_once($basepath.'/config.php');
$classpath=$basepath.'/classes';
set_include_path(get_include_path().PATH_SEPARATOR.$classpath);
spl_autoload_extensions('.class.php');
spl_autoload_register();

if(isset($argv[1]))
{
	$datafile=$argv[1];
}
else
{
	$datafile=$basepath.'/datastore/'.$config['data']['file'];

}

$connection=Connection::getInstance();
$datastore=Datastore::getInstance();
$datastore->setDataFile($datafile);
$datastore->open();
$datastore->skipHead();
$count=0;

$startmemory=memory_get_peak_usage();
$sysload=0;

while($node=$datastore->getNext())
{
	$item=new Item($node,$connection);
	$item->insert();
	unset($item);

	$load = sys_getloadavg();
	$sysload=$load[0]>$sysload?$load[0]:$sysload;
	$count++;
}

$datastore->close();
$endtime=time();
$endmemory=memory_get_peak_usage();

echo PHP_EOL.'---------------------------------------------'.PHP_EOL;

echo 'Data File        : '.$datafile;
echo PHP_EOL;
echo 'Data Imported    : '.$count;
echo PHP_EOL;
echo 'Data Imported On : '.date('D, d M Y H:i:s');
echo PHP_EOL;
echo 'Start Time       : '.$starttime;
echo PHP_EOL;
echo 'End Time         : '.$endtime;
echo PHP_EOL;
echo 'Total Time       : '.($endtime-$starttime) .' secs';
echo PHP_EOL;
echo 'Max System Load  : '.$sysload;
echo PHP_EOL;
echo 'Total Memory     : '.(($endmemory-$startmemory) /1024).' KB';
echo PHP_EOL;
echo '---------------------------------------------';
echo PHP_EOL;
