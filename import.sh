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
echo -e "\n${red}BEWISE LOCALEZE DATA IMPORTER"
echo -e "================================================================================${NC}"
echo    "Importer Started on `date`"
echo -e "Downloading Zip file from FTP"
echo -e "--------------------------------------------------------------------------------"
source config.cfg

#Check OS TYPE Start

platform="UNKNOWN"

if [[ $OSTYPE == linux-gnu ]]; then
        platform='LINUX'
elif [[ $OSTYPE == darwin* ]]; then
        platform="MAC"
elif [[ $OSTYPE == cygwin ]]; then
		platform='CYG'
        
elif [[ $OSTYPE == win32 ]]; then
        platform='WINDOWS'

elif [[ $OSTYPE == freebsd* ]]; then
        platform='FREEBSD'

fi

echo 'Checking Downloaded file if it is already downloaded or not'

#Check OS TYPE END
wget -N  --user=$ftp_username --password=$ftp_password $ftp_path -P $ftp_outputdir
# curl -u $ftp_username:$ftp_password $ftp_path -o $ftp_outputfile
echo -e "--------------------------------------------------------------------------------"
echo "Downloaded to $ftp_extractdir"


#Check if the permission is right

downloaded=$?
md5hash=''

echo 'Generating Checksum of downloaded file'

if [[ $platform == MAC ]];
	then
	md5hash=`md5 $ftp_outputfile | cut -d '='  -f2 | tr -d ' '`
elif [[ $platform == LINUX ]]; then
	md5hash=`md5sum $ftp_outputfile | cut -d " " -f1`
fi
	

if [[ -f $datastore_checksums ]]; then
	oldprint=`cat $datastore_checksums`

	if [[ $oldprint == $md5hash ]]; then
		echo "Data file already imported. exiting importer..."
		exit 0
	else
		echo 'Data not imported. Starting importer...'
	fi
else
	echo 'No Checksums available. Initial Import. Starting importer...'
fi

if [[ $downloaded -eq 0 ]];
	then
		already_extracted=0
		if [[ -f $extracted_checksums ]]; then
			oldextracted_checksum=`cat $extracted_checksums`
				if [[ $oldextracted_checksum == $md5hash ]]; then
					echo 'File already extracted...'
					already_extracted=1
				fi
		fi

		if [[ $already_extracted -eq 0 ]]; then
			echo 'Extracting Downloaded file...'
			unzip -o $ftp_outputfile -d $ftp_extractdir
		fi

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

echo "Waiting for importer to complete..."
wait

echo 'Saving checksums for future checks...'
echo $md5hash > $datastore_checksums 
echo $md5hash > $extracted_checksums 

echo "Imported Records on `date`"
echo "Created by Sanjeev Shrestha on 22 Jul 2014"