<?php
require_once('template.php');
$link = mysql_connect("localhost", "root", "594551")
    or die ("could not connect:".mysql_error());
mysql_select_db ("vglog",$link)
    or die ("could not select db:".mysql_error());
if (isset($_POST["id"])){
	if($_POST["submit"]=="mark as verified"){
		mysql_query("update errors set checked=1 where id = ".$_POST["id"]);
		header("location:errordisplay.php?id=".$_POST["id"]);
	}
}
if(!isset($_GET["id"])){
	header("location:index.php");
}
print_header('');
echo '<form method="POST">
<fieldset>
<input type="hidden" name="id" value="'.$_GET["id"].'">
<input type="submit" value="mark as verified" name="submit"> 
</fieldset>
</form>';
$id = $_GET["id"];
$result = mysql_query("SELECT r.id as id, r.name as name, r.description as descr FROM runs r, errors e WHERE e.id = $id and e.run_id = r.id");
echo "<div>";
while ($row = mysql_fetch_assoc ($result)){
	echo "<p>Run: <a href='run.php?id=".$row["id"]."'>".$row["name"]."</a> ".$row["descr"]."</p>";
}
echo "</div>";
$result = mysql_query("SELECT * FROM errors WHERE id = $id");
echo "<table><tr><th colspan='2'>Error description</th><tr>";
while ($row = mysql_fetch_assoc($result)){
    foreach($row as $key=>$value){
		if($key == "xml_file"){
			echo "<tr><td>$key</td><td><a href='$value'>".substr($value,5)."</a></td></tr>";
		}
		else{
        	echo "<tr><td>$key</td><td>$value</td></tr>";
		}
    }
}
echo "</table>";
echo "<br>";

$stacks = array();
$result = mysql_query ("select distinct stack_id from frames where error_id = $id");
while ($row = mysql_fetch_assoc ($result)){
	$stacks[] = $row["stack_id"];
}
foreach ($stacks as $key=>$stack){
	echo "<table>";
	echo "<tr><th colspan='5'>Callstack</th></tr><tr><th>function</th><th>file</th><th>line</th><th>dir</th><th>obj</th></tr>";
	$result = mysql_query("SELECT fn, file, line, dir, obj FROM frames WHERE error_id = $id and stack_id = $stack order by id");
	while ($row = mysql_fetch_assoc($result)){
		echo "<tr>";
		foreach($row as $key=>$value){
			if ($key=='dir'){
				$pos = strpos ($value,"../src");
				$value=substr($value,$pos);
			}
			echo "<td class='$key'>&nbsp;$value</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}
print_footer('');
?>

