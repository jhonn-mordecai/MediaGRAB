<?php
	
//ERROR REPORTING
//error_reporting(E_ALL);
//ini_set("display_errors",1);

// Include library
require_once('classes/APILink.php');
require_once('classes/NYTimesAPI.php');
require_once('classes/GuardianAPI.php');
require_once('vendor/autoload.php');
use Abraham\TwitterOAuth\TwitterOAuth;
use Madcoda\Youtube;


///////////////////
/// START LOGIC
//////////////////


// Init vars
$q = ''; // The user supplied search string
$twitter_response = null; // The response from twitter
$youtube_response = null; // Response from YouTube
$nytimes_response = null; // Response from NYTimes
$guardian_response = null; // Response from the Guardian
$all_results = array();
$twitter_results = array();
$youtube_results = array();
$nytimes_results = array();
$guardian_results = array();
$type = null;
$timestamp = null;

// Process possible form submission
if (isset($_GET['q']) && strlen($_GET['q'])) {
	$q = htmlspecialchars($_GET['q'], ENT_QUOTES);
}

// Get statuses
if ($q) {

	// Connect to Twitter
	$consumer_key = 'hPRKhxqTSlLISaNVa1SOwE8lV';
	$consumer_secret = 'x5BledEUphCNXdChOikRhDtCngnemPb5gZxBqJBKSNgE04gaRk';
	$access_token = '369030195-FJGcaKh04eXnAXocVaeBVp93cn3EeP7UVU2raXsx';	
	$access_token_secret = 'REF0JLKk9YPf9NVTZqej3Laxu6CWNZ3ivHcSLtRzkOaiB';
	$connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);

	// Get Twitter results
	$twitter_response = $connection->get("search/tweets", ["q" => $_GET['q']]);
	
	// Connect to youtube
	$youtube_api_key = 'AIzaSyBX6eHnOXl9SY55hIbGHHhj_Coe7P17t6M';
	$youtube_api = new Youtube(array('key' => $youtube_api_key));
	$youtube_response = $youtube_api->searchVideos($q);
	
	// Connext to NYTimes
	$nyapikey = '8bfb07dcd48f4b4093ed1bd55bc50af6';
	$nytimes = new NYTimesAPI($nyapikey);

	// Get NYTimes Results
	$startdate = date('Ymd', strtotime('-7 days'));
	$enddate = date('Ymd');
	$nytimes->search($_GET['q'], $startdate, $enddate);
	$nytimes_response = $nytimes->response;
	
	
	//Connect to the Guardian
	$guardian_api_key = '95fe0b59-677a-4eeb-a138-49feb18ba80c';
	$guardian = new GuardianAPI($guardian_api_key);
	
	//Get Guardian Results
	$from_date = date('Y-m-d', strtotime('-7 days'));
	$to_date = date('Y-m-d');
	$guardian->search($_GET['q'], $from_date, $to_date);
	$guardian_response = $guardian->response;
	
	
	// SEND ALL API RESULTS TO NEW ARRAY
	
	if ($twitter_response) {
		foreach ($twitter_response->statuses as $key => $value) {
			$all_results[] = array (
				'type' => 'twitter',
				'profile_img' => $value->user->profile_image_url,
				'twitter_user' => $value->entities->user_mentions[0]->screen_name,
				'name' => $value->entities->user_mentions[0]->name,
				'tweet' => $value->text,
				'tweet_id' => $value->id,
				'timestamp' => strtotime($value->created_at)
			);			
		}
		
	}
	
	if ($youtube_response) {	
		foreach ($youtube_response as $key => $value) {
			$all_results[] = array (
				'type' => 'youtube',
				'video' => $youtube_response[$key]->id->videoId,
				'video_title' => $youtube_response[$key]->snippet->title,
				'timestamp' => strtotime($youtube_response[$key]->snippet->publishedAt),
				'channel_title' => $youtube_response[$key]->snippet->channelTitle,
				'channel_id' => $youtube_response[$key]->snippet->channelId
			);
		}
	}
	
	if ($nytimes_response) {
		foreach ($nytimes_response['response']['docs'] as $key => $value) {
			$all_results[] = array(
				'type' => 'nytimes',
				'nyt_url' => $value['web_url'],
				'nyt_headline' => $value['headline']['main'],
				'nyt_blurb' => $value['snippet'],
				'timestamp' => strtotime($value['pub_date'])
			);
		}
	}
	
	if ($guardian_response) {
		foreach ($guardian_response['response']['results'] as $key=>$value) {
			$all_results[] = array(
				'type' => 'guardian',
				'guardian_url' => $value['webUrl'],
				'guardian_title' => $value['webTitle'],
				'section' => $value['sectionName'],
				'timestamp' => strtotime($value['webPublicationDate'])
			);
		}
	}
	
	//SORT ALL_RESULTS ARRAY
	usort($all_results, function($a, $b) {
		return $b['timestamp'] - $a['timestamp'];
	});
	
	// GET RANDOM ARRAY VALUES
	shuffle($all_results);
	

/////////////////////////////////////
// For Troubleshooting/Testing
/////////////////////////////////////

/*
	echo '<pre>';
	print_r($twitter_response);
	echo '</pre>';
	exit;
*/
	
}

/////////////////////////////
/// END LOGIC
/// START OUTPUT (VIEW)
/////////////////////////////

?>

<!DOCTYPE HTML>
<html>
<head>
	<title>MediaGRAB</title>
	
	<meta charset="utf-8" />
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	
	<!-- font-family: 'Audiowide', cursive; -->
	<link href='https://fonts.googleapis.com/css?family=Audiowide' rel='stylesheet' type='text/css' />
	<!-- font-family: 'Raleway', sans-serif; -->
	<link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css' />

	<link href='css/api_styles.css' rel='stylesheet' type='text/css' />
	
</head>

<body>
		<div id="main_container">
		
			<!-- START HEADER -->
			
			<header></header>
			
			<!-- END HEADER -->
			
			<div id="ui_container">
				<div id="intro">
					<h1><a href="#">MediaGRAB</a></h1>
					<p>Search the Web. Get Results.</p>
				</div>
				
		        <div id="search_container">
			        <div id="form_container">
			            <p>What are you looking for?</p>
			            <form id="search_submit" action="index.php" method="GET">
			                <div id="input_box">
			                    <input type="text" name="q" id="q" size="30" placeholder="Search" value="<?php echo $q; ?>">
			                    <button id="search_submit" type="submit">Search</button>
			                </div>
						</form>
			        </div>
		        </div>
			</div>
	
			
			<div id="result_container" class="grid" data-masonry='{"itemSelector": ".grid-item", "columnWidth":400}'>
									
				<?php 	
					
					foreach ($all_results as $key => $value) {
						
						if ($value['type'] == 'twitter') { ?>
							
							<div class="tile twitter_tile grid-item">
								<a href="https://www.twitter.com/<?= $value['twitter_user']; ?>/status/<?= $value['tweet_id']; ?>" target="_blank">
									<img class="twitter_profile_img" src="<?= $value['profile_img']; ?>" alt="profile_img" />
								</a>
								<p>
									<a class="twitter-user" href="https://www.twitter.com/<?= $value['twitter_user']; ?>/status/<?= $value['tweet_id']; ?>" target="_blank"><?= $value['name']; ?></a>
									<br>
									<?= $value['tweet']; ?>
								</p>
								<p class="date"><?= date("F j,Y, g:i a", $value['timestamp']); ?></p>
								<div class="logo-cont text-center">
									<a href="https://www.twitter.com/<?= $value['twitter_user']; ?>/status/<?= $value['tweet_id']; ?>" target="_blank">
										<img class="api_logo_35" src="img/twitter_logo.svg" alt="Twitter" />
									</a>
								</div>
							</div>

						<?php } 
						
						elseif ($value['type'] == 'youtube') { ?>

							<div class="tile youtube_tile grid-item">
								<iframe width="275" height="200" src="https://www.youtube.com/embed/<?= $value['video']; ?>" frameborder="0" allowfullscreen></iframe>
								<br>
								<p><a class="video-title" href="https://youtu.be/<?= $value['video']; ?>" target="_blank"><?= $value['video_title']; ?></a></p>
								<p class="date"><?= date("F j, Y, g:i a", $value['timestamp']); ?></p>
								
								<p>By <a href="https://www.youtube.com/channel/<?= $value['channel_id']; ?>" target="_blank"> <?= $value['channel_title']; ?></a></p>
								<div class="logo-cont text-center">
									<a href="https://youtu.be/<?= $value['video']; ?>" title="youtube" target="_blank">
										<img class="api_logo_35" src="img/yt_logo.svg" alt="YouTube" />
									</a>
								</div>
							</div>
													
						<?php }
						
						elseif ($value['type'] == 'nytimes') { ?>
						
							<div class="tile nyt_tile grid-item">
								<h3>
									<a href="<?= $value['nyt_url']; ?>" title="<?= $value['nyt_headline']; ?>" target="_blank"><?= $value['nyt_headline']; ?></a>
								</h3>
								<p class="date"><?= date("F j, Y, g:i a", $value['timestamp']); ?></p>
								<div class="blurb_container">
									<p class="blurb"><?= $value['nyt_blurb']; ?></p>
								</div>
								<div class="logo-cont text-center">
									<a href="<?= $value['nyt_url']; ?>" title="the new york times" target="_blank">
										<img class="api_logo_100" src="img/nyt_logo.svg" alt="The New York Times" />
									</a>
								</div>
							</div>
														
						<?php }
						
						else if ($value['type'] == 'guardian') { ?>
							
							<div class="tile guardian_tile grid-item">
								<h3>
									<a href="<?= $value['guardian_url']; ?>" title="<?= $value['guardian_url']; ?>" target="_blank"><?= $value['guardian_title']; ?></a>
								</h3>
								<p class="date"><?= date("F j, Y, g:i a", $value['timestamp']); ?></p>
								<p class="g_section"><?= $value['section']; ?></p>
								<div class="logo-cont text-center">
									<a href="<?= $value['guardian_url']; ?>" title="the guardian" target="_blank">
										<img class="api_logo_100" src="img/guardian_logo.svg" alt="The Guardian" />
									</a>
								</div>
							</div>
														
						<?php }
					}	
				?>	
			</div>	
		
		</div>
		
		<footer>
			
			<div id="footer_container">
				
				<h4><a href="#">MediaGRAB</a></h4>
				<p>A <a href="#" title="Family Best Family">FamilyBest Company</a><br />brought to you by the <a href="#" title="Plectrum USA">Plectrum Group</a>.</p>
				<p><a href="#">Contact</a> <span class="divider">|</span> <a href="#">Privacy</a></p>
				<p class="copyright">Copyright &copy;2016 MediaGRAB. All rights reserved.</p>
				
			</div>
			
		</footer>
		
		<!-- Masonry JS CDN -->
		<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
</body>
</html>
