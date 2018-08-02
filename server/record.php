<?php
//all the variables defined here are accessible in all the files that include this one
include_once 'db.config';
$con= new mysqli($db_host, $db_user, $db_passwd, $db_dbname)or die("Could not connect to mysql".mysqli_error($con));
$con->query("set names utf8");

if (isset($_GET['id']) && isset($_GET['H']) && isset($_GET['T']))
{
	$id=$_GET['id'];
	$Humi=$_GET['H'];
	$Temp=$_GET['T'];
	$PM1=$_GET['PM1'];
	$PM2=$_GET['PM2'];
	$PM3=$_GET['PM3'];
	$remoteip = $_SERVER['REMOTE_ADDR'];

	$sql = "INSERT INTO `iot` (`seq`, `userid`, `remoteip`, `Humi`, `Temp`, `PM1_0`, `PM2_5`, `PM10`, `msg`) VALUES (NULL, '$id', '$remoteip', '$Humi', '$Temp', '$PM1', '$PM2', '$PM3', '$msg')";
	//print($sql);
	$q3 = mysqli_query($con, $sql);
}
else
{
	echo("No parameters");
}
?>