<?php

    session_start();
	
	$key = array_search($_GET['id'], $_SESSION['favoris']);
	unset($_SESSION['favoris'][$key]);
	


?>