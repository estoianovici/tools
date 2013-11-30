<?php
class Error{
    var $id;
	var $run_id;
    var $vgid;
    var $tid;
    var $kind;
    var $what;
    var $auxwhat;
    var $frames;
	var $file;

    public function addFrame($frame){
        if (!isset($this->frames)){
            $this->frames = array();
        }
        $frame->parent = $this;
        $this->frames[] = $frame;
    }
    public function write($link){
        $query = "";
        $query = "INSERT INTO errors(run_id, vg_id, tid, kind, what, auxwhat,xml_file) VALUES(";
        $query .= "'".mysql_real_escape_string($this->run_id)."',";
        $query .= "'".mysql_real_escape_string($this->vgid)."',";
		$query .= "'".mysql_real_escape_string($this->tid)."',";
		$query .= "'".mysql_real_escape_string($this->kind)."',";
		$query .= "'".mysql_real_escape_string($this->what)."',";
		$query .= "'".mysql_real_escape_string($this->auxwhat)."',";
		$query .= "'".mysql_real_escape_string($this->file)."')";
        mysql_query($query, $link)
            or die ("$query\n ".mysql_error());;
        $this->id = mysql_insert_id();
        foreach ($this->frames as $key=>$frame){
            $frame->write($link);
        }
    }
}
?>
