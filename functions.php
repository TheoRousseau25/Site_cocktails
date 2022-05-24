<?php
    /***********************************************/
    /*  Stocke l'ensemble des sous-catégories      */
    /*    d'un aliment passé en paramtères         */
    /*         au sein d'un tableau                */
    /***********************************************/

    function recupererSousCateg($tab1, $tab2, $aliment){
        foreach($tab1[$aliment]['sous-categorie'] as $ingredient){
            if(!in_array($ingredient,$tab2)){
                array_push($tab2, $ingredient);
            }
        }
        unset($tab2[array_search($aliment, $tab2)]);
        return $tab2;
    }

    /***********************************************/
    /*  Vérifie si au moins aliment d'un tableau   */
    /*    passé en paramètres possède une          */
    /*         sous-catégorie                      */
    /***********************************************/

    function verifierSousCateg($tab1, $tab2){
        foreach($tab2 as $value){
            if(array_key_exists('sous-categorie', $tab1[$value])){
                return true;
            }
        }
        return false;
    }

    /***********************************************/
    /*          Retourne la variable stockée       */
    /*                 dans $fichier               */
    /***********************************************/

    function lireFichier($fichier) {
        //Fichier Vide
        if(filesize($fichier)==0) return array();
        //Fichier non Vide
        $fh = fopen($fichier, 'a+');
        $lit = fread($fh, filesize($fichier));
        fclose($fh);
        return unserialize($lit);
    }

    /***********************************************/
    /*          Ecrit dans $fichier la $data       */
    /*           des lettres ou des espaces        */
    /***********************************************/
    function ecrireFichier($fichier, $data) {
        $dataSrzed = serialize($data);
        $fh = fopen($fichier, 'r+');
        fwrite($fh, $dataSrzed);
        fclose($fh);
    }

    /***********************************************/
    /*      Regarde si $temp est valide par        */
    /*              rapport a $regex               */
    /*        Retourne false si non valide,        */
    /*              sinon renvoie $temp            */
    /***********************************************/
    function checkAndFormat($temp, $regex) {
        $temp = trim($temp); //On enlève les espaces au début et a la fin
        //On vérifie si la chaîne correspond au $regex
        $res = preg_match($regex, $temp);
        if($res==0) return false;
        return $temp;
    }

    /***********************************************/
    /*      Modifie $user en ajoutant les          */
    /*    valeurs misent dans un formulaire        */
    /*                     $_POST.                 */
    /***********************************************/
    function configInfoFacultatives($user) {
        //Gestion des informations facultatives
        if(isset($_POST["gender"]) && ($_POST["gender"]=="men" || $_POST["gender"]=="women")) $user["gender"] = $_POST["gender"];
        if(isset($_POST["name"])) {
            $temp = checkAndFormat($_POST["name"], "/^[a-zA-Zàéèêôâû -']+$/");
            if($temp != false) $user["name"] = $temp;
        }
        if(isset($_POST["firstName"])) {
            $temp = checkAndFormat($_POST["firstName"], "/^[a-zA-Zàéèêôâû -']+$/");
            if($temp != false) $user["firstName"] = $temp;
        }
        if(isset($_POST["birth"])) {
            $date2 = date_create_from_format('Y-m-d', date('Y-m-d')); //Date actuelle
            $temp = checkAndFormat($_POST["birth"], "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/");
            if($temp != false) {
                $date1 = date_create_from_format('Y-m-d', $temp);
                $diff = (array) date_diff($date1, $date2);
                if($diff["y"] >= 18) $user["birth"] = $_POST["birth"];
            }
        }
        if(isset($_POST["email"]) && strlen($_POST["email"])>0) { //Test déjà fait avec l'attribut type de input
            $user["email"] = $_POST["email"];
        }
        if(isset($_POST["adress"]) && isset($_POST["postalCode"]) && isset($_POST["town"])) {
            //Test de l'adresse
            $adresse = checkAndFormat($_POST["adress"], "/^[0-9]+[a-zA-Zàéèêôâû -]+$/");
            $codePostal = checkAndFormat($_POST["postalCode"], "/^[0-9]{2}[ ]?[0-9]{3}$/");
            $ville = checkAndFormat($_POST["town"], "/^[a-zA-Zàéèêôâû -]+$/");
            if($adresse != false && $codePostal != false && $ville != false) {
                $user["adress"] = $adresse;
                $user["postalCode"] = $codePostal;
                $user["town"] = $ville;
            }
        }
        if(isset($_POST["phone"])) {
            $temp = checkAndFormat(str_replace(" ", "", $_POST["phone"]), "/^(0|\+33)[0-9]{9}$/");
            if($temp!=false) $user["phone"] = $temp;
        }
        return $user;
    }