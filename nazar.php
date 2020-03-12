<?php
/*
	This script posts Madam Nazar location on Twitter
	
	It builds a tweet with random flavor text that does not repeat day after day
	and adds the map to it too.
	
	Also if the location repeated it selects another flavor text about repetaion,
	this one I wanted to automate but never done it as it is rare the location
	repeats.
	
	Created by Ákos Nikházy 2019-2020
*/
session_start();

require_once 'twitteroauth/autoload.php';//GET THIS HERE: https://github.com/abraham/twitteroauth
use Abraham\TwitterOAuth\TwitterOAuth;

if(isset($_GET['outofhere']))
{//logout
	unset($_SESSION['nazar-post']);
	header('location:https://your-domain.com/nazar.php');
	exit();
}

if(!isset($_SESSION['nazar-post']))
{//if we are not logged in: try to log in
	
	if(isset($_POST['b']))
	{//login by form
		
		if(hash('sha256',$_POST['login'])== 'PUT YOUR PASSWORD HASH HERE')
		{//also this is where you make it multi user if you want it to be that
			
			$_SESSION['nazar-post'] = 1;
		
			header('location:https://your-domain.com/nazar.php');
			exit();
		}
        
		
	}
    
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'PART OF YOUR USER AGENT THAT IS ONLY PRESENT IN YOUR PHONE') !== false && $_SERVER['HTTP_CF_IPCOUNTRY'] == 'HU') 
    {//login by very unsecure automatic way

	 //On my mobile I instantly logged in. This was secure until I disclosed this in this repository. :D So you shouldn't do it!
	 //I leave it here so I show off the idea. As only I knew what mobile I have and where am I, also only I knew about this script
	 //this was safe enough... It was very convenient for me, as I use long passwords I have no way using on phone, and posting 
	 //the location was something I wanted to do fast: hence I logged in this way. There are better ways (cookies for example) but
	 //lazy won in this case, and this is not such a serious matter that I was concerned about security.
	 
    	$_SESSION['nazar-post'] = 1;
		
		header('location:https://your-domain.com/nazar.php');
		exit();
    }
	
	
	exit(file_get_contents('nazar-login.html')); //if login did not happen we show the login page
	
} 
else 
{
	
	if(isset($_POST['nazar']))
	{
			
		$image = 'nazar-images/nazar-map' . $_POST['image'] . '.jpg';
		
		/*
		
			the 12 possible location's name
		
		*/
		$nazarText = array(
				0 =>  '',
				1 =>  'Rio Bravo, New Austin',
				2 =>  'Cholla Springs, New Austin',
				3 =>  "Henningan's Stead, New Austin",
				4 =>  'Tall Trees, West Elizabeth ',
				5 =>  'Big Valley, West Elizabeth',
				6 =>  'The Heartlands, New Hanover',
				7 =>  'Grizzlies West, Ambarino ',
				8 =>  'Grizzlies East, Ambarino',
				9 =>  'Heartlands, New Hanover',
				10 => 'Roanoke Ridge, New Hanover',
				11 => 'Bluewater Marsh, Lemoyne',
				12 => 'Bolger Glade, Scarlett Meadows'
			
		);
	
		/*
		
			flavor text for the tweet. I started out with 10
			the rule is: it ends with "at".
		
		*/
		$randomText = array(
				'She is at',
				'She is reading her book at',
				'The Madam waiting for your offerings at',
				'#MadamNazar is at',
				'Madam Nazar listening to her music at',
				'On this beautiful day she is at',
				'She is enjoying nature at',
				'The Madam is found at',
				'Her shop is open at',
                'Madam Nazar sells you maps at',
                'She is waiting for your weekly collection at',
				'She assumes you are not so honorable at',
				'She is making collectors rich at',
				'Nazar paying for your tat at',
				'She moves a lot yet she has no horse. She is at',
                'Madam Nazar is waiting for you at',
                'The Madam is buying your tarot cards at',
                'Madam Nazar makes you rich at',
                'At',
                'She is selling refined binoculars at',
                'She is selling metal detector at',
                'She is selling herbs at',
                'She is selling maps at',
                'She pays more for full sets, you know. At',
                'She is selling goods for you at',
                '',
                'You can enjoy her accent at',
                'You can listen to her phonograph at',
                'She making #RDO a richer place at',
                'Madam Nazar hanging out at',
                'Some people call her Lazar. She doesn\'t like that.',
                'The Madam is chilling at',
                'She is at this fine place: ',
                'She is hiding at',
                'She is a seer. She is a businesswoman. She is at',
                'Teller of Fortunes and Finder of Lost Things. At',
                'She is not Madam Nazelle, but Madam Nazar! And she is at',
                'She is a palmist. But she never reads your palm. She is at',
                'Bring your #jewelry to',
                'You can buy maps from her at',
                'She buys all your rare herbs at',
                'She know you would kill other collectors for profit. She is at',
                'Enjoying nature barefoot at',
                'Killing time at',
                'Spending millions on tat at',
                'Selling you Moonshine at',
                'You can buy role cloting from her at',
                'You can spend gold at her at',
                'Her shop is open at',
                'Give tarot cards to her at'
		);
        
		/*
			
			flavor text for the rare event when she doesn't move
        
		*/
		$randomTextRepeat = array(
        		'She did not move today. Still at',
                'Still at',
                'Madam Nazar did not move. She is at',
                'Madam Nazar stayed put today:',
                'Today she is at the same place:',
                'Same place it is:',
                'She did not get bored with this place. Still at'
        );

        $today = $yesterday = file_get_contents('nazar-yesterday.txt');
	
		/*
			
			if it is a repeated location
		
		*/
		if(!isset($_POST['repeat']))
		{
			while($today == $yesterday)
			{
				$today = rand(0,count($randomText)-1);
			
			}
			
			$flavorText = $randomText[$today];
			
		} 
		else
		{//if new location
			$flavorText = $randomTextRepeat[rand(0,count($randomTextRepeat)-1)];
		}

		/*
			
			//Hah... remeber the time when cycles was this easy? :D
		
		
			$cycle = 0;
			
			$dayOfWeek = date('N');
			
			switch($dayOfWeek){
				case 1:
				case 5:
					$cycle = 3;
					break;
				case 2:
				case 4:
				case 6:
					$cycle = 1;
					break;
				case 3:
				case 7:
					$cycle = 2;
					break;
				
			}
			
			$cycleText = 'cycle ' . $cycle;
			$cycleText = 'cycle ???';
			
        */
		
		/*
		
			update yesterday file with todays text id
		
		*/
		file_put_contents('nazar-yesterday.txt',$today);
		if(isset($_POST['image'])){
        	file_put_contents('nazar-today.txt',$_POST['image']);
        }
       	//file_put_contents('nazar-cycle.txt',$cycle);
       
    	
		
		/*
		
			these are your Twitter API keys, secrets and tokens.
			Go to https://developer.twitter.com/ to create an app, and get these info.
			
			Also keep in mind this happened once, could happen again:
			https://stackoverflow.com/questions/59858831/twitter-api-tells-me-media-type-unrecognized-for-images-that-always-worked
		
		*/
		define('CONSUMER_KEY', 'YOUR CONSUMER KEY');
		define('CONSUMER_SECRET', 'YOUR  CONSUMER SECRET');
		define('ACCESS_TOKEN', 'YOUR ACCESS TOKEN');
		define('ACCESS_TOKEN_SECRET', 'YOUR ACCESS TOKEN SECRET');
		 
		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

		$postfields = array(
		  'media_data' => base64_encode(file_get_contents($image))
		);

		$media = $connection->upload('media/upload', ['media' => $image]);
	
		$parameters = [
			'status' => $flavorText . ' ' . $nazarText[$_POST['image']] . '. #madamnazar #location #madamnazartoday',
			'media_ids' => $media->media_id_string
		];
		
		$result = $connection->post('statuses/update', $parameters);
		
		header('location:https://your-domain.com/nazar.php');
		exit();
		
	}
	include 'nazar-data.html';
	exit();
}
