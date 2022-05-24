<?php

    session_start();
    
	array_push($_SESSION['favoris'],$_GET['id']);

?>