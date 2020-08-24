<?php
	// See all errors and warnings
	error_reporting(E_ALL);
	ini_set('error_reporting', E_ALL);

	$server = "localhost";
	$username = "root";
	$password = "";
	$database = "dbUser";
	$mysqli = mysqli_connect($server, $username, $password, $database);

	$email = isset($_POST["loginEmail"]) ? $_POST["loginEmail"] : false;
	$pass = isset($_POST["loginPass"]) ? $_POST["loginPass"] : false;	
	// if email and/or pass POST values are set, set the variables to those values, otherwise make them false

	$pic = isset($_FILES["picToUpload"]) ? $_FILES["picToUpload"] : false;

	/////////////////////  UPLOAD PICTURES  /////////////////////////

	//$_FILES["picToUpload"] is an associative array with informative tags liek type and size
	$target_dir = "gallery/";
	$allowed = array('jpeg', 'jpg', 'JPG', 'JPEG');
	$pic; //the uploaded file

	if($pic) //check that its set
	{
		//MULTIPLE UPLOADS
		$numFiles = count($pic["name"]);

		for($i = 0; $i < $numFiles; $i++) //loop through
		{
			$indi = $pic["name"][$i];

			$ext = pathinfo($indi, PATHINFO_EXTENSION);
			$target_file = $target_dir . basename($indi); //creates path to file using the name the user had it saved as

			if (!in_array($ext, $allowed)) {
			   echo 	'<div class="alert alert-danger mt-3" role="alert">
		  					Only allowed jpg/jpeg file types for upload. 
		  				</div>';	  		
			}else
			{
				
				if($pic["size"][$i] < 1048576) //1MB in bytes binary scaling
				{ 
					if($pic["error"][$i] > 0){ //there is an error for some reason
					echo "Error: " . $pic["error"][$i] . "<br/>";
					echo 	'<div class="alert alert-danger mt-3" role="alert">
				  				Error: '. $pic['error'][$i] .
				  			'</div>';
					} 
					else //can be uploaded and added to DB
					{
						if(file_exists( $target_dir. $indi)){
							// echo 	'<div class="alert alert-info mt-3" role="alert">'.
					  // 					$indi . ' already exists, file overwritten'.
					  // 				'</div>';
						}else{
							if(isset($_POST['id'])) // the user id was sent
						{
							$sql = "INSERT INTO tbgallery (user_id, filename) 
							VALUES (" . $_POST['id'] . ", '" . $indi . "');";
							if ($mysqli->query($sql)) {
								//echo "New record created successfully";
								echo '<div class="alert alert-info mt-3" role="alert">
					  					Upload of '.$indi.' successful.
					  				</div>';
							} else {
							//echo "Error: " . $sql . "<br>" . $mysqli->error;
								//echo "Error: " . $sql . "<br>" . $mysqli->error;
							}
						}
						}

						move_uploaded_file($pic["tmp_name"][$i], $target_dir . $indi);
						//echo "<br/>Stored in: " . $target_dir . $pic["name"];

						//ADD TO DB tbgallery using user id sent in
						
						
					}
				} else {
					echo '<div class="alert alert-danger mt-3" role="alert">
		  					Upload too large.
		  				</div>';
				}
			}//end else
		} //end for

	}
	
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>IMY 220 - Assignment 2</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="style.css" />
	<meta charset="utf-8" />
	<meta name="author" content="Michaela Schormann">
	<!-- Replace Name Surname with your name and surname -->
</head>
<body>
	<div class="container">
		<?php
			if($email && $pass){
				$query = "SELECT * FROM tbusers WHERE email = '$email' AND password = '$pass'";
				$res = $mysqli->query($query);
				if($row = mysqli_fetch_array($res)){
					echo 	"<table class='table table-bordered mt-3'>
								<tr>
									<td>Name</td>
									<td>" . $row['name'] . "</td>
								<tr>
								<tr>
									<td>Surname</td>
									<td>" . $row['surname'] . "</td>
								<tr>
								<tr>
									<td>Email Address</td>
									<td>" . $row['email'] . "</td>
								<tr>
								<tr>
									<td>Birthday</td>
									<td>" . $row['birthday'] . "</td>
								<tr>
							</table>";
				
					$id = $row['user_id'];
					//UPLOAD IMAGE - submit to itself
					echo 	"<form action='' method='POST' enctype='multipart/form-data'
								<div class='form-group'>
									<input type='hidden' name='loginEmail' value='" . $email . "' />
									<input type='hidden' name='loginPass' value='" . $pass . "' />".
									"<input type='hidden' name='id' value='" . $id . "' />". //for adding to the gallery table
									"<input type='file' class='form-control' name='picToUpload[]' id='picToUpload' multiple='multiple'/><br/>
									<input type='submit' class='btn btn-standard' value='Upload Image' name='submit' />
								</div>
						  	</form>";


					$query = "SELECT * FROM tbgallery WHERE user_id = '$id'";
					$res = $mysqli->query($query);

					echo "<div class='container'> <div class='row imageGallery'>";
					while($galrow = $res->fetch_assoc())
					{
						echo "
					            <div class='col-3' style='background-image: url(".$target_dir.$galrow['filename'].")'></div>
					          ";
					}
					
					echo "</div></div>";


				}

				else{
					//margin top is 3 (utilities)
					echo 	'<div class="alert alert-danger mt-3" role="alert"> 
	  							You are not registered on this site!
	  						</div>';
				}
			}
			else{
				echo 	'<div class="alert alert-danger mt-3" role="alert">
	  						Could not log you in
	  					</div>';
			}
		?>

		<!-- GALLERY -->
		<!-- constainer makes it fit within the div its already in -->


	</div>
</body>
</html>