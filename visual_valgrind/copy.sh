#!/bin/bash
i=0;
for file  in `find ../runs/ -name \*.tgz`
do
	i=$(($i+1))
	cp $file $i.tgz
done
