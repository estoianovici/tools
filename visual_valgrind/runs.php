<html>
<head><link rel="stylesheet" type="text/css" href="css/style.css" /></head>
<body>
<div>
<div id='form_id'>
<form method="post" action='import.php' enctype="multipart/form-data" style='float:none'>
	<legend>Upload new run</label>
	<fieldset style='border:1px solid black'>
		<label for="name" style='width:150px;float:left'>Name:</label>
		<input type='text' name='name' id='name'/><br/>
		<label for='description' style='width:150px;float:left'>Description:</label>
		<textarea id='description' name='description'></textarea><br>
		<label for="file" style='width:150px;float:left'>Filename:</label>
		<input type="file" name="file" id="file" /> <br/>
		<input type="submit" name="action" value="Upload" style='float:right'/>
	</fieldset>
</form>
</div>
<br>
<?php
require_once ('utils.php');
$link = mysql_connect("localhost", "root", "594551")
    or die ("could not connect:".mysql_error());
mysql_select_db ("vglog",$link)
    or die ("could not select db:".mysql_error());

if(isset ($_POST["action"])){
	if($_POST["action"]=='Sterge'){
		remove_run($link,$_POST);
	}
}

$result = mysql_query("SELECT * FROM runs");
echo "<table>";
echo "<tr><th>name</th><th>created at</th><th>description</th><th>&nbsp;</th><th>&nbsp</th></tr>";
while($row = mysql_fetch_assoc($result)){
	echo "<tr>";
	echo "<td>".$row["name"]."</td>";
	echo "<td>".$row["import_time"]."</td>";
	echo "<td>".$row["description"]."</td>";
	echo "<td><a href='index.php?id=".$row["id"]."'>View</a></td>";
	echo "<td>
				<form method='POST' style='width:100px;min-width=100px'>
					<input type='hidden' name='id' value='".$row["id"]."'>
					<input type='submit' name='action' value='Sterge'>
				</form>
		 </td>";
	echo "</tr>";
}
echo "</table>";
?>
</div>
</body>
</html>

<?php
function remove_run($link, $options){
	$id = $options["id"];
	mysql_query("DELETE FROM frames WHERE error_id in (SELECT id FROM errors where run_id=$id)");
	mysql_query("DELETE FROM errors where run_id = $id");
	mysql_query("DELETE FROM runs where id = $id");
}
?>
