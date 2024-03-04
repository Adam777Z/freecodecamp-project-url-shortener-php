<?php
$path_prefix = '';

if ( isset( $_SERVER['PATH_INFO'] ) ) {
	$path_count = substr_count( $_SERVER['PATH_INFO'], '/' ) - 1;

	for ( $i = 0; $i < $path_count; $i++ ) {
		$path_prefix .= '../';
	}

	if ( strpos( $_SERVER['PATH_INFO'], '/api/shorturl' ) !== false ) {
		try {
			$db = new PDO( 'sqlite:database.db' );
		} catch ( PDOException $e ) {
			exit( $e->getMessage() );
		}

		if ( isset( $_POST['url'] ) && ! empty( $_POST['url'] ) ) {
			$url = trim( $_POST['url'] );
			$parsed_url = parse_url( $url );

			if ( isset( $parsed_url['scheme'] ) && in_array( $parsed_url['scheme'], ['http', 'https'] ) && filter_var( $url, FILTER_VALIDATE_URL ) ) {
				$original_url = $url;
				$short_url = find_short_url( $original_url );

				if ( ! $short_url ) {
					$short_url = generate_random_string( 10 );

					$data = [
						'original_url' => $original_url,
						'short_url' => $short_url,
					];
					$sth = $db->prepare( 'INSERT INTO urls (original_url, short_url) VALUES (:original_url, :short_url)' );
					$sth->execute( $data );
				}

				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( [
					'original_url' => $original_url,
					'short_url' => $short_url,
				] );
				exit;
			} else {
				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( [
					'error' => 'invalid URL',
				] );
				exit;
			}
		} else {
			$short_url = str_replace( 'api/shorturl/', '', trim( $_SERVER['PATH_INFO'], '/' ) );
			$original_url = find_original_url( $short_url );

			if ( $original_url ) {
				header( "Location: $original_url" );
				exit;
			} else {
				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( [
					'error' => 'invalid short URL',
				] );
				exit;
			}
		}
	} else {
		redirect_to_index();
	}
}

function redirect_to_index() {
	global $path_prefix;

	if ( $path_prefix == '' ) {
		$path_prefix = './';
	}

	header( "Location: $path_prefix" );
	exit;
}

function find_short_url( $original_url ) {
	global $db;

	$query = $db->query( "SELECT short_url FROM urls WHERE original_url = {$db->quote( $original_url )}" );
	$result = $query->fetchAll( PDO::FETCH_ASSOC );

	return $result ? $result[0]['short_url'] : false;
}

function find_original_url( $short_url ) {
	global $db;

	$query = $db->query( "SELECT original_url FROM urls WHERE short_url = {$db->quote( $short_url )}" );
	$result = $query->fetchAll( PDO::FETCH_ASSOC );

	return $result ? $result[0]['original_url'] : false;
}

// From: https://stackoverflow.com/a/4356295
function generate_random_string( $length = 10 ) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$characters_length = strlen( $characters );
	$random_string = '';

	for ( $i = 0; $i < $length; $i++ ) {
		$random_string .= $characters[ random_int( 0, $characters_length - 1 ) ];
	}

	return $random_string;
}
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>URL Shortener Microservice</title>
	<meta name="description" content="freeCodeCamp - APIs and Microservices Project: URL Shortener Microservice">
	<link rel="icon" type="image/x-icon" href="<?php echo $path_prefix; ?>favicon.ico">
	<link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/css/style.min.css">
</head>
<body>
	<div class="container">
		<div class="p-4 my-4 bg-light rounded-3">
			<div class="row">
				<div class="col">
					<h1 id="title" class="text-center">URL Shortener Microservice</h1>

					<h3>User Stories:</h3>
					<ol>
						<li>I can POST a URL to <code>[project_url]/api/shorturl</code> and I will receive a shortened URL in the JSON response.<br>Example: <code>{"original_url":"https://www.freecodecamp.org","short_url":"jf71vgvg3n"}</code></li>
						<li>If I pass an invalid URL that doesn't follow the <code>http(s)://(www.)example.com(/more/routes)</code> format, the JSON response will contain an error like <code>{"error":"invalid URL"}</code></li>
						<li>When I visit the shortened URL, it will redirect me to my original URL.</li>
					</ol>

					<h3>Short URL Creation</h3>
					<p>Example: <code>POST /api/shorturl</code> - <code>https://www.freecodecamp.org</code></p>
					<form class="d-flex align-items-center" action="<?php echo $path_prefix; ?>api/shorturl" method="POST">
						<label for="url_input" class="form-label mb-0">URL to be shortened:</label>
						<input id="url_input" class="form-control ms-2 me-2" type="text" name="url" placeholder="URL to be shortened" value="https://www.freecodecamp.org">
						<input class="btn btn-primary" type="submit" value="POST URL">
					</form>

					<h3>Example Usage:</h3>
					<ul>
						<li><a href="<?php echo $path_prefix; ?>api/shorturl/jf71vgvg3n" target="_blank">/api/shorturl/jf71vgvg3n</a> will redirect to <a href="https://www.freecodecamp.org" target="_blank">https://www.freecodecamp.org</a></li>
						<li><a href="<?php echo $path_prefix; ?>api/shorturl/jbx411b0xe" target="_blank">/api/shorturl/jbx411b0xe</a> will redirect to <a href="https://www.freecodecamp.org/forum/" target="_blank">https://www.freecodecamp.org/forum/</a></li>
						<li><a href="<?php echo $path_prefix; ?>api/shorturl/invalid" target="_blank">/api/shorturl/invalid</a></li>
					</ul>

					<div class="footer text-center">by <a href="https://www.freecodecamp.org" target="_blank">freeCodeCamp</a> & <a href="https://www.freecodecamp.org/adam777" target="_blank">Adam</a> | <a href="https://github.com/Adam777Z/freecodecamp-project-url-shortener-php" target="_blank">GitHub</a></div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>