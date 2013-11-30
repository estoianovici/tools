<?php
require_once('error.php');
class Frame{
    var $parent;
    var $ip;
    var $obj;
    var $fn;
    var $dir;
    var $file;
    var $line;
	var $stack_id;

    function write ($link){
        $query = "INSERT INTO frames(error_id, stack_id, ip, obj, fn, dir, file, line) VALUES(";
        $query .= $this->parent->id.",";
        $query .= $this->stack_id.",";
		$query .= "'".mysql_real_escape_string($this->ip)."',";
		$query .= "'".mysql_real_escape_string($this->obj)."',";
		$query .= "'".mysql_real_escape_string($this->fn)."',";
		$query .= "'".mysql_real_escape_string($this->dir)."',";
		$query .= "'".mysql_real_escape_string($this->file)."',";
		$query .= "'".mysql_real_escape_string($this->line)."')";
        mysql_query($query, $link)
            or die($query."\n".mysql_error());
    }
}
?>
