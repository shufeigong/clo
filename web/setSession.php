<?php
session_start();

if(isset($_GET['open']))
{
  $_SESSION['open']=$_GET['open'];
}

?>