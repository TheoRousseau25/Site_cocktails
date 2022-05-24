<?php 
	
	function get_ingredients($category, $Hierarchie){
		//Returns the list of every sub-category and ingredients
		//corresponding to the input category

		$final_array = array($category);
		$changed = true;
		$flatened_index = 0;
		while ($changed) {
			$changed = false;
			$length = count($final_array);
			for ($i=$flatened_index; $i < $length; $i++) {
				if (isset($Hierarchie[$final_array[$i]]
					['sous-categorie'])) {
				foreach ($Hierarchie[$final_array[$i]]
					['sous-categorie'] as $key => $value) 
					{$final_array[] = $value;}
				$changed = true;
				}
			}
			$flatened_index = $length;
		}

		return $final_array;
	}

	function research_analyser($research, $Recettes, $Hierarchie){

		//Research parsing, no verification is made on whether
		//given ingredients exist or not at this point
		$current_status = "+"; // "+", "-" or "unrecognized"
		$ignore_space = false; // we ignore spaces between ""
		$current_word = "";

		$parsed_search = array(
			'+' => array(),
			'-' => array(),
			'unrecognized' => array()
			);

		foreach(str_split($research." ") as $char){
			switch ($char) {
				case '+':
					if ($current_word == "") {$current_status = "+";}
					else {$current_status = 'unrecognized'; $current_word .= '+';}
					break;
				
				case '-':
					if ($current_word == "") {$current_status = "-";}
					else {$current_status = 'unrecognized'; $current_word .= '-';}
					break;

				case ' ':
					if($ignore_space) {$current_word .= ' ';}
					elseif ($current_word != "") {
						array_push($parsed_search[$current_status],
							ucfirst($current_word));
						$current_word = "";
						$current_status = "+";
					}
					break;

				case '"':
					$ignore_space = !$ignore_space;
					break;

				default:
					$current_word .= $char;
					break;
			}
		}

		//Verification of ingredients existance + feedback generation

		if ($ignore_space) {
			$feedback = 'Nombre impair de " dans la requête';
			$no_wanted = true;
			$no_unwanted = true;
		} else {
			$feedback_true = "Liste des aliments souhaités : ";
			foreach ($parsed_search['+'] as $key => $ingredient) {
				if (!(isset($Hierarchie[$ingredient]))) {
					array_push($parsed_search['unrecognized'],
						$ingredient);
					unset($parsed_search['+'][$key]);
				}
				else {$feedback_true .= $ingredient.", ";}
			}
			if (substr($feedback_true, -2) == ", ") {
				$feedback_true = substr($feedback_true, 0, -2);
				$no_wanted = false;
			} else {
				$feedback_true = "Aucun ingredient souhaité";
				$no_wanted = true;
			}


			$feedback_false = "Liste des aliments non souhaités : ";
			foreach ($parsed_search['-'] as $key => $ingredient) {
				if (!(isset($Hierarchie[$ingredient]))) {
					array_push($parsed_search['unrecognized'],
						$ingredient);
					unset($parsed_search['-'][$key]);
				}
				else {$feedback_false .= $ingredient.", ";}
			}
			if (substr($feedback_false, -2) == ", ") {
				$feedback_false = substr($feedback_false, 0, -2);
				$no_unwanted = false;
			} else {
				$feedback_false = "Aucun ingredient non souhaité";
				$no_unwanted = true;}


			$feedback_wrong = "Éléments non reconnus dans la requête : ";
			foreach ($parsed_search['unrecognized']
						as $key => $ingredient) {
				$feedback_wrong .= $ingredient.", ";
			}		
			if (substr($feedback_wrong, -2) == ", ") {
				$feedback_wrong = substr($feedback_wrong, 0, -2);
			} else {$feedback_wrong = "Aucun ingredient non reconnu";}

			$feedback = "$feedback_true <br/> $feedback_false <br/> $feedback_wrong";
		}

		// Creates the list of requests associated to the research
		// a request is a function which takes an ingredient array
		// as input and outputs a boolean answering whether or not
		// the input array checks the request

		$requests = array();

		foreach ($parsed_search['+'] as $key => $category) {
			
			$current_request = function($ingredients) use ($category, $Hierarchie) {
				
				$accepted_ingredients = get_ingredients($category, $Hierarchie);
				$ok = false;
				foreach ($ingredients as $ingredient) {
					if (in_array($ingredient, $accepted_ingredients))
						{$ok = true;}
				}

			/*	echo "+ : <br/>";
				var_dump($ingredients); echo "<br/>";
				var_dump($category); echo "<br/>";
				var_dump($accepted_ingredients); echo "<br/>";
				var_dump($ok); echo "<br/>"; */

				return $ok;
			};

			array_push($requests, $current_request);
		}

		foreach ($parsed_search['-'] as $key => $category) {
			
			$current_request = function($ingredients) use ($category, $Hierarchie) {
				$rejected_ingredients = get_ingredients($category, $Hierarchie);
				$ok = true;
				foreach ($ingredients as $ingredient) {
					if(in_array($ingredient, $rejected_ingredients))
						{$ok = false;}
				}

			/*	echo "- : <br/>";
				var_dump($ingredients); echo "<br/>";
				var_dump($category); echo "<br/>";
				var_dump($rejected_ingredients); echo "<br/>";
				var_dump($ok); echo "<br/>"; */

				return $ok;
			};

			array_push($requests, $current_request);
		}


		// Calculates the score obtained by every recipe
		// Retains only recipes with a score >= 1
		// Recipes are indexed by their index in $Recettes

		$matched_recipe = array();

		if (!($no_wanted and $no_unwanted)) {
			foreach ($Recettes as $key => $recipe) {
				$score = 0;
				foreach ($requests as $request) {
					$score += ($request($recipe['index'])) ? 1 : 0 ;
				}
				if ($score >= 1) {
					$matched_recipe[$key] =	round($score*100/count($requests), 2);
				}
			}
		}
		

		// Sort the list so that the highest score recipes
		// are placed first.
		// The index of each recipe still matches
		// the index in $Recettes

		arsort($matched_recipe);

		return (array($feedback, $matched_recipe));
	}

?>