<?php
require_once ('template.php');
require_once ('utils.php');
print_header("");
?>
<div id='form_id'>
<form method="post" action='import.php' enctype="multipart/form-data" style='float:none'>
	<legend>Upload new run</label>
	<fieldset style='border:1px solid black'>
		<label for="name" style='width:150px;float:left'>Name:</label>
		<input type='text' name='name' id='name'/><br/>
		<label for='description' style='width:150px;float:left'>Description:</label>
		<textarea id='description' name='description'></textarea><br>
		<label for='localfile' style='width:150px;float:left'>Local File:</label>
		<input type='text' name='localfile' id ='localfile'><br>
		<label for="file" style='width:150px;float:left'>Filename:</label>
		<input type="file" name="file" id="file" /> <br/>
		<input type="submit" name="action" value="Upload" style='float:right'/>
	</fieldset>
</form>
</div>
<?
$link = mysql_connect("localhost", "edit this", " edit this ")
    or die ("could not connect:".mysql_error());
mysql_select_db ("vglog",$link)
    or die ("could not select db:".mysql_error());

if(isset ($_POST["action"])){
	if($_POST["action"]=='Delete'){
		remove_run($link,$_POST);
	}
}

$query = "SELECT
				r.id,
				r.name,
				date_format(r.import_time,'%d.%m.%Y %H:%i:%s') as it,
				r.archive_path,
				r.description,
				count(e.id) as err_count
			FROM runs r, errors e 
			WHERE r.id = e.run_id
			GROUP BY r.id
			ORDER BY r.import_time DESC";
$result = mysql_query($query)
			or die ("error $query<br>".mysql_error());
$i = 1;
echo "<table>";
echo "<tr><th>&nbsp;</th><th>name</th><th>created at</th><th>description</th><th>unique errors</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th></tr>";
while($row = mysql_fetch_assoc($result)){
	echo "<tr>";
	echo "<td style='padding-right:25px'>$i.</td>";
	echo "<td>".$row["name"]."</td>";
	echo "<td>".$row["it"]."</td>";
	echo "<td>".$row["description"]."</td>";
	echo "<td>".$row["err_count"]."</td>";
	echo "<td><a href='run.php?id=".$row["id"]."'>View</a></td>";
	echo "<td><a href='".$row["archive_path"]."'>Download Archive</a></td>";
	echo "<td>
				<form method='POST' style='width:100px;min-width=100px'>
					<input type='hidden' name='id' value='".$row["id"]."'>
					<input type='submit' name='action' value='Delete'>
				</form>
		 </td>";
	echo "</tr>";
	$i++;
}
echo "</table>";
print_footer("");
function remove_run($link, $options){
	$id = $options["id"];
	mysql_query("DELETE FROM frames WHERE error_id in (SELECT id FROM errors where run_id=$id)");
	mysql_query("DELETE FROM errors where run_id = $id");
	mysql_query("DELETE FROM runs where id = $id");
}
?>
