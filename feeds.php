<?php 
session_start();	
include("config.php");	
include("db-function.php");
include("functions.php");
$user = getUserParams();
if($user) {
	$feeds = mysql_gettable("SELECT * FROM feed WHERE author='" . $user["user_login"] . "'");
}
if(!isset($_POST["feeds"])) {
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Feed Waze Russia</title>
    <link rel="stylesheet" href="style.css?v=008" type="text/css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  </head>
  <body>
	<?php 
		if($user) echo "Здравствуйте, " . $user['user_login'];
		else {
			echo "Для работы в системе необходимо <a href=\"login.php?redir=" . urlencode($_SERVER['REQUEST_URI']) . "\" >авторизоваться</a> или <a href=\"register.php\" >зарегистрироваться</a>";
			echo "</body></html>";
			exit();
		}
	?>	  
	
	<form action="feeds.php" method="POST">
		<table border="1">
		<?php foreach($feeds AS $ind => $feed) { ?>
			<tr>
				<td><?php echo $ind+1;?></td>
				<td><?php echo $feed["id"];?></td>
				<td><?php echo $feed["incident_id"];?></td>
				<td><?php echo $feed["creationtime"];?></td>
				<td><?php echo $feed["description"];?></td>
				<td><?php echo $feed["polyline"];?></td>
				<td><?php echo $feed["starttime"];?></td>
				<td><?php echo $feed["endtime"];?></td>
				<td><?php echo $feed["street"];?></td>
				<td><?php echo $feed["type"];?></td>
				<td><?php echo $feed["subtype"];?></td>
				<td><?php echo $feed["direction"];?></td>
				<td><?php echo $feed["comment"];?></td>
				<td><input type='checkbox' name='feeds[]' value='<?php echo $feed["id"];?>'></td>
			</tr>
		<?php } ?>
		</table>
		<input type='submit' class='buttons' value="Удалить">
	</form>
  </body>
<?php } else {
	if(mysql_q("DELETE FROM feed WHERE id IN (".implode(",",$_POST['feeds']).") AND author='" . $user["user_login"] . "'")) {
			echo "Записи IDs ".implode(",",$_POST['feeds'])." были удалены. Показать <a href=\"feeds.php\">список ваших фидов.</a>";
	};
	
}
?>
