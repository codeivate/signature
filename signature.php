<?php


/**
 * Generates signature image based on user profile
 *
 * This can be used for forum signatures, email footers, or embedded on your site.
 * 
 * @param  object $data
 * 
 * @return exit()
 */
function _signature_image($data) {
	// Set the enviroment variable for GD
	putenv('GDFONTPATH=' . realpath( dirname(__FILE__).'/fonts/'));

	//Set the path to our true type font 
	define('FONT_FILE', 'Ubuntu-L.ttf');

	// TEXT SIZE CONSTANTS
	define('FP_SIZE', 9); // flow_state / programming
	define('LEVEL_SIZE', 18);
	define('NAME_SIZE', 23);
	define('TITLE_SIZE', 11);
	define('TEXT_SIZE', 9);

	
	$image_width = 562;
	$image_height = 117;


	// CONSTANTS strings used as headings
	$titles = array(
		"best_streaks" => array(
			'value' => "Best streak",
			'size'  => TITLE_SIZE,

		),
		"major_languages" => array(
			'value' => "Major languages",
			'size'  => TITLE_SIZE,

		),
		"total_time" => array(
			'value' => "Total time programming",
			'size'  => TITLE_SIZE,

		),
		"programming" => array(
			'value' => "programming",
			'size'  => FP_SIZE,

		),
		"flow_state" => array(
			'value' => "flow_state",
			'size'  => FP_SIZE,
		)
	);

	if(strlen($data['name']['value']) > 14) {
		$titles['programming']['value'] = "p";
		$titles['flow_state']['value'] = "f";
	}


	//Create our basic image background
	$image = imagecreate($image_width, $image_height);


	/**
	* Determine width and height of text items for use later.
	* 
	* 
	* 0	lower left corner, X position
	* 1	lower left corner, Y position
	* 2	lower right corner, X position
	* 3	lower right corner, Y position
	* 4	upper right corner, X position
	* 5	upper right corner, Y position
	* 6	upper left corner, X position
	* 7	upper left corner, Y position
	*/
	foreach($data as $k => &$v) {
		if(isset($v['value'])) {
			$size = TEXT_SIZE;
			if($k == 'current_level') {
				$size = LEVEL_SIZE;
			}
			$box = imagettfbbox($size, 0 ,FONT_FILE , $v['value']);
			$v['width'] = abs($box[4] - $box[0]);
			$v['height'] = abs($box[5] - $box[1]);
			$v['size'] = $size;	
		}
	}

	foreach($titles as $k => &$v) {
		$box = imagettfbbox($v['size'], 0 ,FONT_FILE , $v['value']);
		$v['width'] = abs($box[4] - $box[0]);
		$v['height'] = abs($box[5] - $box[1]);	

	}
	
	// -------------------------------------------------------------------------------------------------------
	// ------------------------------------------ COLOURS ----------------------------------------------------
	// -------------------------------------------------------------------------------------------------------

	//Set up some colors
	$dark_grey = imagecolorallocate($image, 102, 102, 102);
	$white = imagecolorallocate($image, 255, 255, 255);
	$blue = imagecolorallocate($image, 58, 135, 173);
	$red = imagecolorallocate($image, 255, 0, 0);
	$green = imagecolorallocate($image, 70, 136, 71);
	 
	//colors for use
	$c_background = $dark_grey;
	$c_text = $white;
	$c_programming = $green;
	$c_flow_state = $blue;
	$c_bar_background = $white;
	$c_bar_foreground = $green;



	// -------------------------------------------------------------------------------------------------------
	// ----------------------------------------- COLOMN ONE --------------------------------------------------
	// -------------------------------------------------------------------------------------------------------


	// user name
	$p['name'] = imagettftext($image, NAME_SIZE, 0, 5, 5 + NAME_SIZE, $c_text, FONT_FILE, $data['name']['value']);

	$margin_x = 3;
	$margin_fp_x = 1;
	$margin_y = 5;

	$width_programming = $titles['programming']['width'] + $margin_fp_x * 2;
	$width_flow_state = $titles['flow_state']['width'] + $margin_fp_x * 2;

	// -------------------------------------- DYNAMIC STATE BUTTONS --------------------------------------------

	if($data['programming'] == 'true') {		
		$p['programming']['x1'] = $margin_x + $p['name'][4];
		$p['programming']['y1'] = $margin_y + $p['name'][5];
		$p['programming']['x2'] = $margin_x + $p['programming']['x1'] + $width_programming;
		$p['programming']['y2'] = $margin_y + NAME_SIZE;
		$p['programming']['color'] = $c_programming;

		$output = $p['programming'];
		imagefilledrectangle($image, $output['x1'], $output['y1'], $output['x2'], $output['y2'], $output['color']);
		imagettftext($image, FP_SIZE, 0, $output['x1'] + $margin_x, $output['y1'] + FP_SIZE + floor(($output['y2'] - $output['y1'] - FP_SIZE) / 2), $c_text, FONT_FILE, $titles['programming']['value']);

		if($data['flow_state'] == 'true') {			
			$p['flow_state']['x1'] = $margin_x + $output['x2'];
			$p['flow_state']['y1'] = $output['y1'];
			$p['flow_state']['x2'] = $p['flow_state']['x1'] + $width_flow_state;
			$p['flow_state']['y2'] = $p['programming']['y2'];
			$p['flow_state']['color'] = $c_flow_state;			

			$output = $p['flow_state'];
			imagefilledrectangle($image, $output['x1'], $output['y1'], $output['x2'], $output['y2'], $output['color']);
			imagettftext($image, FP_SIZE, 0, $output['x1'] + $margin_x, $output['y1'] + FP_SIZE + floor(($output['y2'] - $output['y1'] - FP_SIZE) / 2), $c_text, FONT_FILE, $titles['flow_state']['value']);
		}
	}

	// --------------------------------------------  LEVEL --------------------------------------------------

	$output['size'] = $data['current_level']['size'];
	$output['x'] = 5;
	$output['y'] = $p['name'][1] + $size + 15;
	$output['color'] = $c_text;
	$output['text'] = $data['current_level']['value'];

	$p['current_level'] = imagettftext($image, $output['size'], 0, $output['x'], $output['y'], $output['color'], FONT_FILE, $output['text']);



	// -------------------------------------- PROGRESS LEVEL ------------------------------------------------

	$width_level = 170;

	$p['level_bar']['x1'] = $margin_x + $p['current_level'][4];
	$p['level_bar']['y1'] = $p['current_level'][5];
	$p['level_bar']['x2'] = $p['level_bar']['x1'] + $width_level;
	$p['level_bar']['y2'] = $p['current_level'][1];
	$p['level_bar']['color'] = $c_bar_background;
	$output = $p['level_bar'];
	imagefilledrectangle($image, $output['x1'], $output['y1'], $output['x2'], $output['y2'], $output['color']);


	$width_progress = round(($data['current_level']['raw'] - floor($data['current_level']['raw'])) * $width_level);

	$p['level_progress']['x1'] = $p['level_bar']['x1']; 
	$p['level_progress']['y1'] = $p['level_bar']['y1']; 
	$p['level_progress']['x2'] = $p['level_bar']['x1'] + $width_progress; 
	$p['level_progress']['y2'] = $p['level_bar']['y2']; 
	$p['level_progress']['color'] = $c_bar_foreground;
	$output = $p['level_progress'];
	imagefilledrectangle($image, $output['x1'], $output['y1'], $output['x2'], $output['y2'], $output['color']);



	// -------------------------------------------------------------------------------------------------------
	// ----------------------------------------- COLOMN TWO --------------------------------------------------
	// -------------------------------------------------------------------------------------------------------

	$col2 = floor($image_width / 2) + 35;
	$margin_y = 3;
	// -------------------------------------- MAJOR LANGUAGES ------------------------------------------------

	//title major_languages
	$p['major_languages']['x1'] = $col2;
	$p['major_languages']['y1'] = $margin_y + $titles['major_languages']['height'];
	$p['major_languages']['color'] = $c_text;
	$p['major_languages']['text'] = $titles['major_languages']['value'];
	$p['major_languages']['size'] = $titles['major_languages']['size'];

	$output = $p['major_languages'];
	imagettftext($image, $output['size'], 0, $output['x1'], $output['y1'], $output['color'], FONT_FILE, $output['text']);

	//major_languages
	$p['major_languages_results']['x1'] = $col2;
	$p['major_languages_results']['y1'] = $output['y1'] + $data['major_languages']['height'];
	$p['major_languages_results']['color'] = $c_text;
	$p['major_languages_results']['text'] = $data['major_languages']['value'];
	$p['major_languages_results']['size'] = $data['major_languages']['size'];

	$output = $p['major_languages_results'];
	imagettftext($image, $output['size'], 0, $output['x1'], $output['y1'], $output['color'], FONT_FILE, $output['text']);

	// -------------------------------------- BEST STREAKS ------------------------------------------------

	//title best streaks
	$p['best_streaks']['x1'] = $col2;
	$p['best_streaks']['y1'] = $output['y1'] + $margin_y + $titles['best_streaks']['height'];
	$p['best_streaks']['color'] = $c_text;
	$p['best_streaks']['text'] = $titles['best_streaks']['value'];
	$p['best_streaks']['size'] = $titles['best_streaks']['size'];

	$output = $p['best_streaks'];
	imagettftext($image, $output['size'], 0, $output['x1'], $output['y1'], $output['color'], FONT_FILE, $output['text']);


	//best streaks results
	$p['best_streaks_results']['x1'] = $col2;
	$p['best_streaks_results']['y1'] = $output['y1'] + $margin_y + $data['best_streaks']['height'];
	$p['best_streaks_results']['color'] = $c_text;
	$p['best_streaks_results']['text'] = $data['best_streaks']['value'];
	$p['best_streaks_results']['size'] = $data['best_streaks']['size'];

	$output = $p['best_streaks_results'];
	imagettftext($image, $output['size'], 0, $output['x1'], $output['y1'], $output['color'], FONT_FILE, $output['text']);

	// -------------------------------------- TOTAL TIME ------------------------------------------------

	//title total_time
	$p['total_time']['x1'] = $col2;
	$p['total_time']['y1'] = $output['y1'] + $margin_y + $titles['total_time']['height'];
	$p['total_time']['color'] = $c_text;
	$p['total_time']['text'] = $titles['total_time']['value'];
	$p['total_time']['size'] = $titles['total_time']['size'];

	$output = $p['total_time'];
	imagettftext($image, $output['size'], 0, $output['x1'], $output['y1'], $output['color'], FONT_FILE, $output['text']);


	//total_time results
	$p['total_time_results']['x1'] = $col2;
	$p['total_time_results']['y1'] = $output['y1'] + $margin_y + $data['total_time']['height'];
	$p['total_time_results']['color'] = $c_text;
	$p['total_time_results']['text'] = $data['total_time']['value'];
	$p['total_time_results']['size'] = $data['total_time']['size'];

	$output = $p['total_time_results'];
	imagettftext($image, $output['size'], 0, $output['x1'], $output['y1'], $output['color'], FONT_FILE, $output['text']);



	// -------------------------------------- URL OF SITE  -----------------------------------------------
	$size = 9;
	$x = 5;
	$y = 100;
	$color = $c_text;
	$text = 'www.codeivate.com/users/' . $data['name']['value'];

	imagettftext($image, $size, 0, $x, $y, $color, FONT_FILE, $text);


	/**
	 * Set the content type
	 *
	 * This is at the end so you can call print_r($var); die(); 
	 * anywhere above this point during debuging.
	 */
	header('content-type: image/png');
	 
	//Create our final image 
	imagepng($image);
	 
	//Clear up memory 
	imagedestroy($image);

	die();

}





/**
 * On the live site this is pulled from the database.
 *
 * Feel free to add suggested parameters you would like to see customizable by the user.
 */
function _signature_data($user) {


	$results['programming'] = isset($_GET['programming']) ? $_GET['programming'] : false;
	$results['flow_state'] = isset($_GET['flow_state']) ? $_GET['flow_state'] : false;		

	
	$results['name']['value']			  		 = isset($_GET['name']) ? $_GET['name'] : 'paul';
	$results['current_level']['raw']			 = isset($_GET['current_level']) ? $_GET['current_level'] : 13.12;
	$results['current_level']['value']			 = "Level " . floor($results['current_level']['raw']);

	//can be between 1-5 languages
	$results['major_languages']['value']		 = isset($_GET['major_languages']) ? $_GET['major_languages'] : 'javascript, haskel, cypher-t'; 
	$results['best_streaks']['value']	 		 = isset($_GET['best_streaks']) ? $_GET['best_streaks'] : '1 hour';
	$results['total_time']['value']	 			 = "days:". (isset($_GET['days']) ? $_GET['days'] : 99) . " / time:" . (isset($_GET['time']) ? $_GET['time'] : '2 hours 26 minutes');

	/*echo "<pre>";
	var_dump($results);
	die();*/

	return  $results;
}


/**
 * get data, generete image
 * @param  [type] $user
 */
function _signature() {
	$data = _signature_data();

	// This calls PHP exit() and returns the image to the client.
	_signature_image($data);
}


_signature();