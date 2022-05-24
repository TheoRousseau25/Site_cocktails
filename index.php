<?php

    include 'Donnees.inc.php';
    include 'Recherche.php';
    include 'functions.php';

    /***********************************************/
    /*                      MAIN                   */
    /*                                             */
	/***********************************************/

    $alimentCourant = "Aliment";
    $sousCategories = [];
    $dirname = 'Photos/';
    $dir = opendir($dirname);
    $images = [];
    $recettesValides = [];
    $fichier= 'loginAndPassword.txt';
    $loginAndPassword = lireFichier($fichier);
    $affichageConnection = '';

    //Pour la connexion
	if(isset($_POST["connection"])) { //Formulaire de connexion
        $correspondance = false;
        //Eléments forcément set -> required partout dans le formulaire
        foreach($loginAndPassword as $key => $user) {
                if ($user["login"] == $_POST["loginConnection"] && $user["password"] == $_POST["passwordConnection"]) {
                    $correspondance = true;
                    $thisUserKey = $key;
                    break;
                }
        }
        if($correspondance==true) {
            setcookie("UserInfo", $thisUserKey); //Cookies d'informations de l'utilisateur
            $affichageConnection="Connexion ! Veuillez patientez...";
            header("Refresh:3");
        }
        else $affichageConnection="Login ou Mot de passe incorrecte";
    }
    else if(isset($_POST["disconnect"]) && isset($_COOKIE["UserInfo"])) { //Déconnexion
        echo 'Clic';
        unset($_POST["disconnect"]);
        setcookie("UserInfo"); //Suppression des Cookies
        sleep(1.5);
        header("Location: index.php");
    }
	
	session_start();
	if(!isset($_SESSION['favoris'])){
	    $_SESSION['favoris'] = array();
		}
	else{
		foreach($_SESSION['favoris'] as $value){
	        echo $value;
		}
	}
?>



<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Acceuil</title>
        <meta charset="utf-8" />
		<link rel="stylesheet" href="index.css" type="text/css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		
    <script>
	
	    window.onload = function favoris() {
			
            for (const key in  localStorage ) {
                    $coeur = document.getElementById(key);
					if($coeur){
			        $coeur.style.backgroundColor = "red";
			}	}
            }
		


        function fav(id)
        {
	
	        $coeur = document.getElementById(id);

	        if(localStorage.getItem(id) === null)
	        {
	            $coeur.style.backgroundColor = "red";
		        $coeur.setAttribute('name', 'fav');
		        localStorage.setItem(id, id);
                $.ajax({
                        type : 'GET',
                        url : 'fav.php',
                        data: {
                            id : id,
						},
                    success : function(data){
                       
                    },
                error : function(XMLHttpRequest, textStatus, errorThrown) 
                {alert ("Error Occured");}
                });
	        }
	        else
	        {
		        $coeur.style.backgroundColor = "green";
		        $coeur.setAttribute('name', 'non-fav');
		        localStorage .removeItem(id);
                $.ajax({
                        type : 'GET',
                        url : 'nonfav.php',
                        data: {
                            id : id,
						},
                    success : function(data){
                       
                    },
                error : function(XMLHttpRequest, textStatus, errorThrown) 
                {alert ("Error Occured");}
                    });
	        }	
        }
		
		function test(id)
		{
			alert(id);
		}
	

</script>
    </head>
    <body>
	    <nav>
		    <a href="index.php"> <button class="index">Navigation</button></a> <a href="index.php"> <button class="index">Recettes favorites</button></a>
            <?php  if(isset($_COOKIE["UserInfo"])) { ?>
                <form method="post" action="#"><input type="submit" value="Deconnexion" name="disconnect" class="login"/></form>
            <?php } ?>
            <a href="connexion.php"> <button class="connection"><?php if(isset($_COOKIE["UserInfo"])) echo "Profil"; else echo "Inscription";?></button></a>
            <?php if(!isset($_COOKIE["UserInfo"])) { ?>
                <form method="post" action="#" class="formConnexion">
                    <label>Login : <input type="text" name="loginConnection" required="required"/></label>
                    <label>Mot de passe : <input type="password" name="passwordConnection" required="required"/></label>
                    <input type="submit" value="Connexion" name="connection" class="login" />
                </form>
            <?php } ?>
            <?php  if(isset($_COOKIE["UserInfo"])) {
                $thisUser = $loginAndPassword[$_COOKIE["UserInfo"]];
                echo '<p class="username">'.$thisUser["login"].'</p>';
            } ?>
		</nav>
		
		<?php
            echo $affichageConnection;
            echo '
			    <main>
                    <aside>';
					
			//On récupère le nom de l'aliment courant (celui sur lequel l'utilisateur a cliqué) dans l'url et on le stocke dans une variable et dans le fil d'ariane.

            if (!isset($_GET['aliment'])) {
                //session_start();
                $_SESSION['filAriane'] = array();
                array_push($_SESSION['filAriane'],"Aliment");
            } else {
	            //session_start();
	            $alimentCourant = $_GET['aliment'];
				
				//Si l'aliment courant est déjà dans le fil d'ariane, c'est que l'utilisateur cherche à revenir en arrière. On va donc enlever les aliments qui se trouvent après l'aliment courant.

	            if(in_array($alimentCourant,$_SESSION['filAriane'])){
	                $key = array_search($alimentCourant, $_SESSION['filAriane']);
		            $tailleFil = count($_SESSION['filAriane']);
		
		            for($i = $key+1; $i <= $tailleFil; $i++){
			            unset($_SESSION['filAriane'][$i]);
		            }
	            } else {
		            array_push($_SESSION['filAriane'],$alimentCourant);
	            }
            }
			
			echo '<h1>Aliment courant</h1>';
			
			//On affiche le fil d'ariane.

            foreach($_SESSION['filAriane'] as $navigation){
	            echo '<a href="index.php?aliment='.$navigation.'">'.$navigation."</a> / ";
            }
			
			echo '<h3>Sous-catégories : </h3>
			      <ul>';
				  
			//On affiche la navigation et on stocke chaque sous-catégorie de l'aliment courant dans le tableau "sousCategories".

            if(array_key_exists('sous-categorie', $Hierarchie[$alimentCourant])){
                foreach($Hierarchie[$alimentCourant]['sous-categorie'] as $ingredient){
                    echo '<a href="index.php?aliment='.$ingredient.'"><li>'.$ingredient.'</li></a>';
		            array_push($sousCategories, $ingredient);
                }
            }
			
			echo '</ul>';
			
			//On récupère l'ensemble des ingrédients qui dépendent de l'aliment courant et on les stocke dans le tableau "sousCategories".

            while(verifierSousCateg($Hierarchie, $sousCategories)){
                foreach($sousCategories as $value){
	                if(array_key_exists('sous-categorie', $Hierarchie[$value])){
		                $sousCategories = recupererSousCateg($Hierarchie, $sousCategories, $value);
	                }
                }
            }


            echo '<h1>Barre de recherche</h1>';
            echo '<form method="get" action="index.php">';
			echo '<input type="text" name="search"></input>';
			echo "<input type='submit' value='Confirmer'>";
			echo '</form>';

            echo 
		        '</aside>';
				
			//On récupère le nom des photos de cocktails et on les stocke dans le tableau "images".

            while($file = readdir($dir))
            {
                array_push($images, $file);
            }
        
		    closedir($dir);		
			
			//On vérifie si l'utilisateur a cliqué sur une recette en tentant de récupérer la numéro du cocktail dans l'url et on l'affiche de manière détaillée. 
						
			if (isset($_GET['cocktail'])) {
				$recette = $_GET['cocktail'];
				$ingredients = explode("|", $Recettes[$recette]['ingredients']);
				echo 
					'<section>
	                    <h2>'.$Recettes[$recette]['titre'].'</h2> <button id="'.$Recettes[$recette]['titre'].'" onclick="fav(this.id)" type="button" name="non-fav" class="favoris"><svg id="'.$Recettes[$recette]['titre'].'"  onload="favoris(this.id)" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                            </svg></button>';
						
				if(in_array(str_replace(" ", "_", $Recettes[$recette]['titre']).".jpg",$images)){
                        echo '<img src="Photos/'.str_replace(" ", "_", $Recettes[$recette]['titre']).'.jpg">';
	                } else {
		                echo '<img src="Photos/cocktail.png" class="cocktail">';
	                }
				echo
						'<p>Ingrédients :</p>
						<ul>';
				foreach($ingredients as $ingredient){
					echo '<li>'.$ingredient.'</li>';
				}
				echo
				    '</ul>
				    <p>Préparation :</p>'.$Recettes[$recette]['preparation'].
					'</section>';	 
			} else {
				echo
                '<section>
				<h1>Liste des cocktails</h1>';
				 
				//Sinon, on affiche de manière synthétique l'ensemble des cocktails qui ont un ingrédient se trouvant dans "sousCategories" dans leur recette.

				if (!isset($_GET["search"])) {
					foreach($Recettes as $key => $recette){
		                if(!empty($sousCategories)){
		                    foreach($sousCategories as $ingredient){
			                    if(in_array($ingredient, $recette['index']) && !in_array($recette, $recettesValides)){
			                    	$recette["key"] = $key;
				                    array_push($recettesValides, $recette);
			                    }
		                    }
		                } else {
			                if(in_array($alimentCourant, $recette['index']) && !in_array($recette, $recettesValides)){
			                	$recette["key"] = $key;
				                array_push($recettesValides, $recette);
			                }
		                }
	                }
				} else {
					list($feedback, $recipes) = research_analyser($_GET["search"], $Recettes, $Hierarchie);
					echo $feedback."<br/><br/>";
					if (count($recipes) == 0) {
						echo "La recherche n'a pas pu aboutir";
					} else {
						foreach ($recipes as $key => $score) {
							$recetteValide = $Recettes[$key];
							$recetteValide["score"] = $score;
							$recetteValide["key"] = $key;
							array_push($recettesValides, $recetteValide);
						}
					}
				}
				

                foreach($recettesValides as $recette){
					$score = (isset($recette["score"])) ? " : ".$recette['score'] : "";
	                echo 
	                    '<div id=name="$recette">
                            <button id="'.$recette["titre"].'" onclick="fav(this.id)" type="button" name="non-fav" class="favoris"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                            </svg></button>
                            <br><br>
	                        <h2>'.$recette["titre"].$score.'</h2>';
					if(in_array(str_replace(" ", "_", $recette['titre']).".jpg",$images)){
                        echo '<a  href="index.php?aliment='.$alimentCourant.'&cocktail='.$recette["key"].'"><img src="Photos/'.str_replace(" ", "_", $recette['titre']).'.jpg"></a>';
	                } else {
		                echo '<a  href="index.php?aliment='.$alimentCourant.'&cocktail='.$recette["key"].'"><img src="Photos/cocktail.png" class="cocktail"></a>';
	                }
	                echo 
				            '<ul>';
	                foreach($recette['index'] as $ingredient){
		                echo '<li>'.$ingredient.'</li>';
	                }
	                echo 
	                    '</ul>
	                </div>';
					
                }
	
                echo
                    '</section>
				</main>';
			}
			
        ?>
    </body>
</html>
