#!/bin/bash
i=0;
for file  in `find archives/ -name \*.tgz`
do
	i=$(($i+1))
	size=$(stat -c%s $file) 
	if [ $size -ne 0 ]
	then
		echo importing $file;
		php do.php $i $i $file; 
	fi
done
