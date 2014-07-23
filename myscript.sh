#!/bin/bash

# Configuration stuff

fspec=./datastore/large.xml
num_lines=200000
num_files=26

# Work out lines per file.

total_lines=$(cat ${fspec} | wc -l)
((lines_per_file = (total_lines + num_files - 1) / num_files))

# Split the actual file, maintaining lines.

split -l ${lines_per_file} ${fspec} splitted/xedb_split.

# Debug information

echo "Total lines     = ${total_lines}"
echo "Lines  per file = ${lines_per_file}"    
wc -l splitted/xedb_split.*

count=0


for entry in splitted/*
do 
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

#echo 
 php import.php $entry >log$count.txt &

		
done

wait

echo "Imported Records"