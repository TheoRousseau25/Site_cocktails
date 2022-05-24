<?php
    /***********************************************/
    /*                      MAIN                   */
    /***********************************************/
    include 'functions.php';
    $affichageInscription="";
    $affichageConnection="";
    $affichageModification="";
    $affichageDeconnexion="";
    $correspondance = false;
    $fichier= 'loginAndPassword.txt';
    $loginAndPassword = lireFichier($fichier);
    $thisUser = 0;
    $thisUserKey = -1;

    if(isset($_POST["register"])) { //Formulaire d'inscription
        //Contrôle si login est déjà présent
        foreach($loginAndPassword as $user) {
            foreach($user as $key => $password) {
                if($user[$key]==$_POST["loginRegister"]) $correspondance=true;
            }
        }
        if($correspondance==true) $affichageInscription="Login déjà utilisé.";
        else {
            //Vérification des formats
            $temp = checkAndFormat($_POST["loginRegister"], "/^[0-9a-zA-Z]+$/");
            if($temp == false) $affichageInscription="Mauvais format pour le login";
            else {
                //Création de l'utilisateur
                $user = array(
                    "login" => $_POST["loginRegister"],
                    "password" => $_POST["passwordRegister"]);
                //Gestion des informations facultatives
                $user = configInfoFacultatives($user);
                //MAJ du Fichier
                $loginAndPassword[] = $user;
                ecrireFichier($fichier, $loginAndPassword);
                $affichageInscription="Inscrit ! Vous pouvez vous connecter !";
            }
        }
    }
    else if(isset($_POST["edit"]) && isset($_COOKIE["UserInfo"])) { //Formulaire de modification
        $temp = $loginAndPassword[$_COOKIE["UserInfo"]];
        $thisUser = configInfoFacultatives($temp);
        if($temp!=$thisUser) {
            //MAJ du Fichier, Suppression de l'ancien user et ajout du nouveau
            unset($loginAndPassword[$_COOKIE["UserInfo"]]);
            $loginAndPassword[] = $thisUser;
            foreach($loginAndPassword as $key => $user) {
                if ($user["login"] == $thisUser["login"]) {
                    $thisUserKey = $key;
                    break;
                }
            }
            setcookie("UserInfo", $thisUserKey); //MAJ Cookies
            ecrireFichier($fichier, $loginAndPassword);
            $affichageModification="Informations Modifiées !";
        }
    }
    else if(isset($_POST["changePassword"])) { //Changement de Mot de Passe
        if(isset($_COOKIE["UserInfo"])) $thisUser = $loginAndPassword[$_COOKIE["UserInfo"]];
        else { //Si l'utilisateur a supprimer les cookies
            $affichageModification = "Cookies introuvables, veuillez vous reconnecter";
            return;
        }
        //Tous les champs sont required et aucune restriction sur les mots de passes
        if(isset($_POST["oldPassword"]) && isset($_POST["newPassword"]) && isset($_POST["confirmNewPassword"]) &&
            $_POST["oldPassword"] == $thisUser["password"] && $_POST["newPassword"] == $_POST["confirmNewPassword"]) {
            $thisUser["password"] = $_POST["newPassword"];
            //MAJ du Fichier, Suppression de l'ancien user et ajout du nouveau
            unset($loginAndPassword[$_COOKIE["UserInfo"]]);
            $loginAndPassword[] = $thisUser;
            foreach($loginAndPassword as $key => $user) {
                if ($user["login"] == $thisUser["login"]) {
                    $thisUserKey = $key;
                    break;
                }
            }
            setcookie("UserInfo", $thisUserKey); //MAJ Cookies
            ecrireFichier($fichier, $loginAndPassword);
            $affichageModification = "Mot de Passe modifier, retour a la page précédente...";
            header("Refresh:2");
        }
        else {
            $affichageModification = "Erreur sur le formulaire, veuillez réessayer";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Zone de connexion</title>
        <link href="connexion.css" type="text/css" rel="stylesheet">
        <meta charset="utf-8" />
    </head>
    <body>
        <?php
            if(isset($_POST["register"])) unset($_POST["register"]);
            if(isset($_POST["connection"])) unset($_POST["connection"]);
            if(isset($_COOKIE["UserInfo"])) { // Utilisateur connecté
                if($thisUser==0) $thisUser = $loginAndPassword[$_COOKIE["UserInfo"]];
                if(isset($_POST["changePasswordButton"]) || isset($_POST["changePassword"])) { //Boutton changement de mot de passe préssé ?>
                    <form method="post" action="#">
                        <fieldset>
                            <legend>Changement de Mot de Passe</legend>
                            <label>Ancien Mot de Passe :<input type="password" name="oldPassword" required="required"/></label> <br>
                            <label>Nouveau Mot de Passe :<input type="password" name="newPassword" required="required"/></label> <br>
                            <label>Confirmer le Nouveau Mot de Passe :<input type="password" name="confirmNewPassword" required="required"/></label> <br>
                            <br>
                            <input type="submit" value="Changer le mot de passe" name="changePassword" />
                        </fieldset>
                    </form>
                <?php echo $affichageModification;
                    } else { ?>
        <h1>Bonjour <?php if(isset($thisUser["name"]) && isset($thisUser["firstName"])) echo $thisUser["name"]." ".$thisUser["firstName"]; else echo $thisUser["login"] ?> !</h1>
        <form method="post" action="#">
            <fieldset>
                <legend>Informations</legend>
                <label>Sexe :<input type="radio" name="gender" value="women" <?php if((isset($thisUser["gender"])) && ($thisUser["gender"])=='women') echo 'checked="checked"'; ?> /></label><div class="labelGender">Femme</div><br>
                <label><input type="radio" name="gender" value="men" <?php if((isset($thisUser["gender"])) && ($thisUser["gender"])=='men') echo 'checked="checked"'; ?> /></label> <div class="labelGender">Homme</div><br>
                <label>Nom :<input type="text" name="name" value="<?php if(isset($thisUser["name"])) echo $thisUser["name"]; ?>"/></label><br>
                <label>Prénom :<input type="text" name="firstName" value="<?php if(isset($thisUser["firstName"])) echo $thisUser["firstName"]; ?>"/></label><br>
                <label>Date de naissance :<input type="date" name="birth" value="<?php if(isset($thisUser["birth"])) echo $thisUser["birth"]; ?>"/></label><br>
                <label>Adresse électronique :<input type="email" name="email" value="<?php if(isset($thisUser["email"])) echo $thisUser["email"]; ?>"/></label><br>
                <label>Adresse postale : <br>
                    Adresse :
                    <input type="text" name="adress" value="<?php if(isset($thisUser["adress"])) echo $thisUser["adress"]; ?>"/> <br>
                    Code postale :
                    <input type="number" name="postalCode" value="<?php if(isset($thisUser["postalCode"])) echo $thisUser["postalCode"]; ?>"/> <br>
                    Ville :
                    <input type="text" name="town" value="<?php if(isset($thisUser["town"])) echo $thisUser["town"]; ?>"/></label> <br>
                <label>Numéro de Téléphone :<input type="tel" name="phone" value="<?php if(isset($thisUser["phone"])) echo $thisUser["phone"]; ?>"/></label> <br>
                <br>
                <input type="submit" value="Modifier" name="edit" /> <br>
                <input type="submit" value="Modifier Mot de Passe" name="changePasswordButton"/> <br>
            </fieldset>
        </form>
        <?php echo $affichageModification;
                } } else { // Utilisateur non connecté ?>
        <form method="post" action="#">
            <fieldset>
                <legend>Inscription</legend>
                <h2>Informations Obligatoires</h2>
                <label>Login :<input type="text" name="loginRegister" required="required"/></label><br>
                <label>Mot de passe :<input type="password" name="passwordRegister" required="required"/></label><br>
                <h2>Informations Facultatives</h2>
                <label>Sexe :<input type="radio" name="gender" value="women" <?php if((isset($_POST["gender"])) && ($_POST["gender"])=='women') echo 'checked="checked"'; ?> /></label><div class="labelGender">Femme</div> <br>
                <label><input type="radio" name="gender" value="men" <?php if((isset($_POST["gender"])) && ($_POST["gender"])=='men') echo 'checked="checked"'; ?> /></label><div class="labelGender">Homme</div> <br>
                <label>Nom :<input type="text" name="name" value="<?php if(isset($_POST["name"])) echo $_POST["name"]; ?>"/></label><br>
                <label>Prénom :<input type="text" name="firstName" value="<?php if(isset($_POST["firstName"])) echo $_POST["firstName"]; ?>"/></label><br>
                <label>Date de naissance :<input type="date" name="birth" value="<?php if(isset($_POST["birth"])) echo $_POST["birth"]; ?>"/></label><br>
                <label>Adresse électronique :<input type="email" name="email" value="<?php if(isset($_POST["email"])) echo $_POST["email"]; ?>"/></label><br>
                <label>Adresse postale : <br>
                    Adresse :
                    <input type="text" name="adress" value="<?php if(isset($_POST["adress"])) echo $_POST["adress"]; ?>"/> <br>
                    Code postale :
                    <input type="number" name="postalCode" value="<?php if(isset($_POST["postalCode"])) echo $_POST["postalCode"]; ?>"/> <br>
                    Ville :
                    <input type="text" name="town" value="<?php if(isset($_POST["town"])) echo $_POST["town"]; ?>"/></label><br>
                <label>Numéro de Téléphone :<input type="tel" name="phone" value="<?php if(isset($_POST["phone"])) echo $_POST["phone"]; ?>"/></label><br>
                <br><br>
                <input type="submit" value="Inscription" name="register" class="boutton" />
            </fieldset>
        </form>
        <?php echo $affichageInscription; } ?>
        <br><a href="index.php"> <button class="index">Retour à la navigation</button></a>
    </body>
</html>