#!/bin/bash

#Author: Sanjeev Shrestha
#Date : 22 July 2014
#Localeze importer
# Configuration stuff


#Color Codes
red='\033[0;31m'
NC='\033[0m' # No Color
blue='\033[1;34m'
####################
echo -e "\n${red}LOCALEZE DATA IMPORTER"
echo -e "================================================================================${NC}"
echo    "Importer Started on `date`"
echo -e "Downloading Zip file from FTP"
echo -e "--------------------------------------------------------------------------------"
source config.cfg

wget -N  --user=$ftp_username --password=$ftp_password $ftp_path -P $ftp_outputdir
# curl -u $ftp_username:$ftp_password $ftp_path -o $ftp_outputfile
echo -e "--------------------------------------------------------------------------------"


echo "Downloaded to $ftp_extractdir"
if [[ $? -eq 0 ]];
	then
		unzip -o $ftp_outputfile -d $ftp_extractdir
		extractedfile=`zipinfo -1 $ftp_outputfile`

fi

fspec="$ftp_extractdir/$extractedfile"
echo "Extracted to file $fspec"

if [ ! -f $fspec ]
then
echo "ERROR Couldnt locate file to import : $fspec"
exit 1
fi



num_lines=200000
num_files=26

# Work out lines per file.

echo "Counting lines in the inflated file..."
total_lines=$(cat ${fspec} | wc -l)
((lines_per_file = (total_lines + num_files - 1) / num_files))

echo "Total lines     = ${total_lines}"
echo "Lines  per file = ${lines_per_file}" 

echo "Removing Old Splitted files..."
rm -rf splitted/*


# Split the actual file, maintaining lines.

echo -e "Splitting Files..."
split -l ${lines_per_file} ${fspec} splitted/xedb_split.


#wc -l splitted/xedb_split.*

count=0

for entry in splitted/*
do 
echo "Imporing data from $entry"
	if [[ $count -eq 0 ]];
	then		
			echo '</Business></Delivery>' >>$entry

	elif [[ $count -eq 25 ]]; then
		echo '<?xml version="1.0" encoding="ISO-8859-1"?>
<Delivery xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.localeze.com/Localeze"
          xsi:schemaLocation="http://www.localeze.com/Localeze http://www.localeze.com/LocalezePublish.xsd">
    <Business>' | cat - $entry > temp && mv temp $entry
	else
		echo '<?xml version="1.0" encoding="ISO-8859-1"?>
<Delivery xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.localeze.com/Localeze"
          xsi:schemaLocation="http://www.localeze.com/Localeze http://www.localeze.com/LocalezePublish.xsd">
    <Business>' | cat - $entry > temp && mv temp $entry
    			echo '</Business></Delivery>' >>$entry
	fi
				((count++))


 php import.php $entry >./logs/log$count.txt &		
done

wait

echo "Imported Records on `date`"
echo "Created by Sanjeev Shrestha on 22 Jul 2014"