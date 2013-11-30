<?php
require_once ('template.php');
require_once ('utils.php');
$run_id = $_GET["id"];
if ($run_id <= 0){
	die ("invalid id $run_id");
}
print_header('');
$link = mysql_connect("localhost", "root", "594551")
    or die ("could not connect:".mysql_error());
mysql_select_db ("vglog",$link)
    or die ("could not select db:".mysql_error());

$result = mysql_query("SELECT r.id as id, r.name as name, r.description as descr FROM runs r WHERE r.id = $run_id");
echo "<div>";
while ($row = mysql_fetch_assoc ($result)){
	echo "<p>Run: <a href='run.php?id=".$row["id"]."'>".$row["name"]."</a> (".$row["descr"]."</p>";
}
echo "</div>";
$error_types = array();
$result = mysql_query("SELECT DISTINCT kind, count(kind) as cnt FROM errors WHERE run_id = $run_id GROUP BY kind");
echo "<div id='error_types' style='float:left'>";
echo "<ol>";
while ($row = mysql_fetch_assoc($result)){
    echo "<li><a href='run.php?id=$run_id&error_type=".$row["kind"]."'>".$row["kind"]."(".$row["cnt"].")</a></li>";
	$error_types[] = $row["kind"];
}
echo "</ol>";
echo "</div>";
echo "<div style='float:right'>";
echo create_filter_form($_GET, $error_types);
echo "</div>";
$query = build_query($_GET); 
$result = mysql_query($query)
    or die ($query."<br>".mysql_error());
echo "<div id='errors' style='clear:both'><br><br>";
while ($r = mysql_fetch_assoc($result)){
	$color = "#FFFFFF";
	if($r["echecked"]==1){
		$color = "#C0C0C0";
	}
	echo "<div id='err' style='background-color:$color'>";
    echo "<a href=errordisplay.php?id=".$r["id"]." target='_blank'>".$r["vgid"]."</a><img src='img/external.png'>
			<a href='".$r["exml_file"]."'>(".substr($r["exml_file"],5).")</a><br>";
    echo "<p><span class='label'>Type:</span><b>".$r["ekind"]."</b></p>";
    echo "<p><span class='label'>Description:</span><b>".$r["ewhat"]."</b></p>";
    echo "<p><b>Callstack</b></p>";
    echo "<pre>";
    echo rtrim(ltrim($r["call_stack"],"\n"), "\n")."\n";
    echo "</pre>";
    echo "<hr>";
	echo "</div>";
}
print_footer('');
?>
