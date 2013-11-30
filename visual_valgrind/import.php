<?php
require_once ('error.php');
require_once ('frame.php');
require_once ('utils.php');
require_once ('folder_utils.php');
require_once ('errorcount.php');
require_once ('template.php');
ini_set('memory_limit', '512M');
$element = "";
$error = null;
$error_count = null;
$frame = null;
$link = null;
$is_stop = false;
$errors = 0;
$xml_file = "";
$stack_id = 0;
$run_id = 0;
$is_error_counts = false;
function start ($parser, $element_name, $element_attrs){
    global $element, $error, $error_count, $frame, $is_stop,$xml_file, $stack_id,$run_id, $is_error_counts;
    $is_stop = false;
    $element = $element_name;

    switch($element){
    case "ERROR":
		$is_error_counts = false;
        $error = new Error();
		$error->run_id = $run_id;
		$error->file = $xml_file;
		$stack_id = 0;
        break;
	case "STACK":
		$stack_id+=1;
		break;
    case "FRAME":
        $frame = new Frame();
        break;
	case "ERRORCOUNTS":
		$is_error_counts = true;
		break;
	case "PAIR":
		$error_count = new ErrorCount();
		$error_count->run_id = $run_id;
		$is_error_counts = true;
		break;
    default:
        break;
    }
}


function stop ($parser,$element_name){
    global $element, $error, $error_count, $frame, $link, $is_stop, $errors, $stack_id, $is_error_counts;
    $is_stop = true;
    switch($element_name){
    case "ERROR":
        $errors++;
        $error->write($link);
        unset($error);
        $error = null;
        break;
    case "FRAME":
		$frame->stack_id = $stack_id;
        $error->addFrame($frame);
        unset($frame);
        $frame = null;
        break;
	case "PAIR":
		$error_count->write($link);
		unset($error_count);
		$error_count = null;
		break;
    }
}

function char($parser,$data){
    global $element, $error, $error_count, $frame, $is_stop;
    if ($is_stop)
        return;
    switch ($element){
    case "UNIQUE":
		if($is_error_count){
			$error_count->error_id = $data;
		} else {
        	$error->vgid = $data;
		}
        break;
	case "COUNT":
		$error_count->error_count = $data;
		break;
    case "TID":
        $error->tid = $data;
        break;
    case "KIND":
        $error->kind = $data;
        break;
    case "WHAT":
        $error->what = $data;
        break;
    case "AUXWHAT":
        $error->auxwhat = $data;
        break;
    case "IP":
        $frame->ip = $data;
        break;
    case "OBJ":
        $frame->obj = $data;
        break;
    case "FN":
        $frame->fn = $data;
        break;
    case "DIR":
        $frame->dir = $data;
        break;
    case "FILE":
        $frame->file = $data;
        break;
    case "LINE":
        $frame->line = $data;
        break;
    default:
        break;
    }
}

function import_file($file, $link){
    global $errors,$xml_file;
    $errors = 0;
    echo "importing $file\n";
	$xml_file = $file;
    $parser = xml_parser_create();
    xml_set_element_handler($parser,"start","stop");
    xml_set_character_data_handler($parser,"char");

    $fp = fopen($file,"r")
        or die("cannot open $file");
    while ($data=fread($fp,4096)){
        xml_parse($parser,$data,feof($fp)) or 
            die (sprintf("$file Error: %s at line %d", 
                    xml_error_string(xml_get_error_code($parser)),
                    xml_get_current_line_number($parser)));
    }
    xml_parser_free($parser);
    fclose($fp);
    echo "found $errors\n";
}



function import_files ($path, $link){
    $fu = new FolderUtils();
    $fu->set_path($path);
    while ($file = $fu->get_next_file("xml")){
        normalize_xml($file);
        import_file($file, $link);
    }
    $fu->close();
}
function clear_data ($link){
    mysql_query("truncate table errors");
    mysql_query("truncate table frames");
}

function validate_data($form_data){
	if ($form_data["name"] == ""){
		die ("please provide a name");
	}
	if ($form_data["localfile"]!=""){
		$file = $form_data["localfile"];
		$ext = get_file_extension($file);
		if ($ext != "tgz" && $ext!='zip'){
			die ("uknown file type $ext(".$form_data["localfile"].")<br>only zip and tgz suported<br>");
		}	
	}	
	else if ($_FILES["file"]["error"] > 0){
  		die ("Error: " . $_FILES["file"]["error"] . "<br />");
  	} else {
		$file = $_FILES["file"]["name"];
		$ext = get_file_extension($file);
		if ($ext != "tgz" && $ext!='zip'){
			die ("uknown file type $ext<br>only zip and tgz suported<br>");
		}
  	}
}
function create_run($link, $form_data){
	global $run_id;
	$query = "INSERT INTO runs(name, description) VALUES(";
	$query .= "'".mysql_real_escape_string($form_data['name'])."',";
	$query .= "'".mysql_real_escape_string($form_data['description'])."')";
	mysql_query($query)
		or die ("Error running $query<br>".mysql_error());
	$run_id = mysql_insert_id();
	$files_path = "runs/$run_id";
	echo "creating $files_path<br>";
	echo "<pre>";
	echo create_folder($files_path);
	$archive_path ="$files_path/vglogs.tgz";
	mysql_query("UPDATE runs SET archive_path = '$archive_path' where id = $run_id"); 
	if ($form_data['localfile']!=''){
		copy($form_data["localfile"], $archive_path)
			or die ("cannot copy ".$form_data["localfile"]." to $archive_path ".error_get_last());
	} else {
		copy($_FILES["file"]["tmp_name"], $archive_path) 
			or die ("cannot copy ".$_FILES["file"]["tmp_name"]." to $archive_path ".error_get_last());
	}
	extract_files ($archive_path,$files_path);
	import_files($files_path,$link);
	echo "</pre>";
}
$link = mysql_connect("localhost", "root", "594551")
    or die ("could not connect:".mysql_error());
mysql_select_db ("vglog",$link)
    or die ("could not select db:".mysql_error());
print_header('');
validate_data($_POST);
create_run($link, $_POST);
print_footer('');
