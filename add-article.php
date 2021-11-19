<?php

// Inclut le fichier à l'intérieur de l'actuel
require 'inc/config.php';

// je peux utiliser toutes les variables présentes dans le fichier "config.php" puisque j'ai utilisé "require"


$errors = [];

// Si mon formulaire est envoyé, je passe dans la condition ci-dessous
if(!empty($_POST)){

	$safe = array_map('trim', array_map('strip_tags', $_POST)); // On nettoie pour sécuriser

	if(strlen($safe['title']) < 5 || strlen($safe['title']) > 60){
		$errors[] = 'Votre titre doit compoter entre 5 et 60 caractères';
	}


	// Je m'assure que la date ne soit pas vide pour effectuer des vérifications
	if(!empty($safe['date_publish'])){
		$my_publish_date = explode('-', $safe['date_publish']);

		if(!checkdate($my_publish_date[1], $my_publish_date[2], $my_publish_date[0])){
			$errors[] = 'Votre date de publication est invalide';
		}
		elseif($safe['date_publish'] < date('Y-m-d')){ // J'empêche la publication antérieure à aujourd'hui
			$errors[] = 'Votre date de publication ne peut être passée dans le temps';
		}
	}
	else {
		$errors[] = 'Vous devez saisir une date de publication';
	}


	if(!isset($safe['category'])){ // J'ajoute cette condition puisque la premiere <option> des catégories est "disabled" donc illisible en php
		$errors[] = 'Vous devez sélectionner une catégorie';
	}
	elseif(!in_array($safe['category'], array_keys($categories))){
		$errors[] = 'Vous avez essayé de modifier la catégorie et c\'est pas très sympa et pas très gentil';
	}


	if(strlen($safe['content']) < 50 || strlen($safe['title']) > 6000){
		$errors[] = 'Votre contenu doit compoter entre 50 et 60000 caractères';
	}



	if(count($errors) == 0){
		// ici, lorsque je n'ai pas d'erreur que je vais enregistrer mon article


		// Je traite ma checkbox "à la une " 
		if(isset($safe['promote']) && $safe['promote'] === 'on'){
			$is_promote = 1;
		}
		else {
			$is_promote = 0;
		}


		$sql = 'INSERT INTO articles (title, category, content, promote, date_publish) 
				VALUES(:param_title,:param_category,:param_content,:param_promote,:param_date_publish)';

		// la variable $bdd se trouve dans le fichier config.php et est ma connexion à ma de données
		// $bdd->prepare() me permet de préparer ma requete SQL
		$query = $bdd->prepare($sql); 

		$query->bindValue(':param_title', $safe['title']);
		$query->bindValue(':param_category', $safe['category']);
		$query->bindValue(':param_content', $safe['content']);
		$query->bindValue(':param_promote', $is_promote);
		$query->bindValue(':param_date_publish', $safe['date_publish']);
		$query->execute(); // J'execute ma requete


		$formIsValid = true;
	}
	else {
		$formIsValid = false;
	}

}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Ajouter un article</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>
<body>

	<main class="container">

		<div class="row justify-content-center">
			<div class="col-6">
				<h1 class="text-center my-5">Ajouter un nouvel article</h1>


				<?php 

				if(isset($formIsValid) && $formIsValid == true){
					echo '<div class="alert alert-success">Votre article a bien été enregistré</div>';
				}
				elseif(isset($formIsValid) && $formIsValid == false){
					echo '<div class="alert alert-danger">'.implode('<br>', $errors).'</div>';

				}
				?>


				<form method="post">
					<!-- titre -->
					<div class="mb-3">
						<label for="title" class="form-label">Titre</label>
						<input type="text" id="title" name="title" class="form-control">
					</div>

					<!-- date de publication -->
					<div class="mb-3">
						<label for="date_publish" class="form-label">Date de publication</label>
						<input type="date" id="date_publish" name="date_publish" class="form-control">
					</div>
					<!-- categorie -->
					<div class="mb-3">
						<label for="category" class="form-label">Catégorie</label>
						<select id="category" name="category" class="form-control">
							<option value="0" selected disabled>-- Choisir --</option>
							<?php foreach($categories as $kCat => $vCat):?>
								<option value="<?=$kCat;?>"><?=mb_strtoupper(mb_substr($vCat, 0, 1)) . mb_substr($vCat, 1);?></option>
							<?php endforeach;?>
						</select>
					</div>

					<!-- contenu -->
					<div class="mb-3">
						<label for="content" class="form-label">Contenu</label>
						<textarea id="content" name="content" class="form-control" rows="10"></textarea>
					</div>

					<!-- checkbox à la une -->
					<div class="mb-3 form-check">
					  	<input type="checkbox" name="promote" class="form-check-input" id="promote">
						<label class="form-check-label" for="promote">Article &laquo; à la une &raquo;</label>
					</div>


					<button type="submit" class="btn btn-primary">Envoyer</button>
				</form>
			</div>
		</div>

	</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html>