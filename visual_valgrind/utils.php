<?php
function connect(){
	$link = mysql_connect("localhost", "root", "594551")
		or die ("could not connect:".mysql_error());
	mysql_select_db ("vglog",$link)
		or die ("could not select db:".mysql_error());
	return $link;
}
function create_folder ($path){
	$path = normalize_path($path);
	$result = shell_exec("rm -rf $path");
	$result.="<br>";
	mkdir($path, 0770, true)
		or die ("failed to create $path<br>");
}
function clear_folder($path){
    $path = normalize_path($path);
    $result = shell_exec("rm -rf $path*");
	return $result;
}

function create_filter_form ($previous, $options) {
	$error_type = "";
	$form = "<form method='GET'>
				<legend>Filter Errors</legend> 
				<fieldset>
				<label>Type:</label>
				<select name='error_type'>\n
			 	<option value=''>--Any--</option>";
	if(isset($previous["error_type"])){
		$error_type = $previous["error_type"];
	}
	foreach($options as $key=>$value){
		$selected = "";
		if ($value == $error_type){
			$selected = "selected";
		}
		$form .= "<option value='".$value."' $selected>$value</value>\n";
	}
	$form .= "</select><br>\n";
	$form .= "<input type='radio' name='contains' value='yes'>contains\n
				<input type='radio' name='contains' value='no'>does not contain<br>\n";
	$form .= " <label>Filter:</label><input type='text' name='filter'><br>\n";
	$form .= " <input type='hidden' name='id' value='".$previous['id']."'>\n";
	$form .= "<input type='submit' value='filter'>
			  </fieldset>
			  </form>\n";
	return $form;
}
function build_query($filters){
    $query = "SELECT e.id, e.vg_id vgid, e.kind as ekind, e.what as ewhat, e.auxwhat as ewhat, e.xml_file as exml_file, e.checked as echecked,
              GROUP_CONCAT(f.fn order by f.id separator '\n') as call_stack
              FROM errors e, frames f
              WHERE e.id = f.error_id and e.run_id =".$filters['id']." ";
	if(isset($filters["error_type"])){
		if ($filters["error_type"]!=""){
    		$query .= " AND e.kind = '".$_GET["error_type"]."' ";
		}
	}
    	$query .= " GROUP BY e.id,e.vg_id, e.kind, e.what, e.auxwhat, e.xml_file, f.stack_id ";
	if (isset($filters["filter"])){
		if (isset($filters['contains'])&&$filters["contains"]=='no'){
    		$query .=" HAVING call_stack not like '%".$filters["filter"]."%'";
		} else {
    		$query .=" HAVING call_stack like '%".$filters["filter"]."%'";
		}
	}
	/*echo "<div style='clear:both'><pre>".print_r($filters)."\n$query</pre></div>";*/
	return $query;
}

function normalize_path ($path){
    ltrim($path," ");
    rtrim($path," ");
    if ($path == ""){
        return "./";
    }
    if ($path[strlen($path)-1] != '/'){
        $path .="/";
    }
    return $path;
}

function get_file_extension($file){
    $exts = split("[/\\.]", $file);
    $n = count($exts) - 1;
    if ($n>=0){
        return $exts[$n];
    }
    return "";
}

function rewrite_xml ($file){
	$file_bak = $file.".bak";
    $fp = fopen ($file, "r")
        or die ("cannot open $file");
	$copy_fp = fopen($file_bak,"w")
		or die ("cannot open $file_bak");
    $line = "";
    while ($line = fgets($fp)){
        if (strpos($line,"</valgrindoutput>")!==false){
			continue;
        }
		fputs ($copy_fp, $line);
    }
    fputs($copy_fp, "</valgrindoutput>\n");
    fclose($fp);
	fclose($copy_fp);
    shell_exec("mv $file_bak $file");
}

function is_xml_end($fp){
	$line = "";
	while ($line = fgets($fp)){
		$line = trim($line, " \t");
		if ($line != "")
			return false;
	} 
	return true;
}

function normalize_xml($file){
    /* we have to add </valgrindoutput> to the end of file if it's not already there.
	   also, if it si already there, check if it si the end of file */
    $fp = fopen ($file, "r+")
        or die ("cannot open $file");
    $line = "";
    while ($line = fgets($fp)){
        if (strpos($line,"</valgrindoutput>")!==false){
			if(!is_xml_end($fp)) {
				fclose($fp);
				rewrite_xml($file);
				return;
			}
            fclose($fp);
            return;
        }
    }
    fputs ($fp, "</valgrindoutput>\n");
    fclose($fp);
}

function extract_files($archive, $path){
    $path = normalize_path($path);
    $ext = get_file_extension($archive);
    if ($ext == "tgz"||$ext == ".gz"){
        shell_exec ("tar -C $path -xzvf $archive");
    } else if ($ext == "zip"){
        shell_exec ("unzip $archive -d $path");
    }
	#shell_exec ("sudo chown -R eugen.www-data $path");
	#shell_exec ("sudo chmod -R g+rw $path");
}

?>

