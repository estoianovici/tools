<?php
class ErrorCount{
	var $run_id;
    var $error_id;
    var $error_count;

    public function write($link){
        $query = "UPDATE errors SET error_count = ".$this->error_count." WHERE vg_id = '".$this->error_id."' AND run_id = ".$this->run_id;
        mysql_query($query, $link)
            or die ("$query\n ".mysql_error());;
    }
}
?>
