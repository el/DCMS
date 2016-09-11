<?php

	require_once '../inc/google_api/Google_Client.php';
	require_once '../inc/google_api/contrib/Google_CalendarService.php';
	
	class GoogleUpdate {
		
		private $dbh;

		function __construct($dbh){
			$this->dbh = $dbh;
			try {
				$apps = $this->dbh->query("SELECT app FROM users RIGHT JOIN groups ON gid = group_id GROUP BY app")->fetchAll();	
			} catch (PDOException $e) {
				echo err("Kullanıcı verilerine ulaşılamadı. (Google)");
				$apps = array();
			}
			foreach($apps as $app) {
				$this->calendar($app["app"]);
				$this->contacts($app["app"]);
			}
		}
		
		function connection($app,$type) {
			global $site, $parts, $contents,$dbh;
			$connection = $parts["google"]["parts"][$type];
			if (!isset($contents[$connection["db"]]))
				return false;
				
			$g=$parts["google"]["settings"];

			$calendar_list = $this->dbh->query("SELECT * FROM $connection[db] WHERE app = $app")->fetchAll();
			if (!$calendar_list)
				return false;
			
			
			$users = $this->getUsers($app,$type);
			
			if (!$users)
				return false;
			
			return $users;
		}
		
		function getUsers($app,$type) {
			global $dbh;
			$sth = $this->dbh->query("SELECT * FROM users LEFT JOIN groups ON gid = group_id WHERE LENGTH(token)>10 AND app = $app");
			if (!$sth)
				return false;
			$tokens = $sth->fetchAll();
			if (!$tokens || sizeof($tokens)<1)
				return false;
				
			$users = array();

			foreach($tokens as $token) {
				$t = (array)json_decode($token["token"]);
				if (isset($t[$type])) {
					$token["google"] = $t[$type];
					$users[] = $token;
				}
			}
			
			return sizeof($users) ? $users : false;
		}
		
		function getClient(){
			global $parts,$site;
			$g=$parts["google"]["settings"];
			
			$client = new Google_Client();
			$client->setApplicationName($site["name"]);

			// Visit https://code.google.com/apis/console?api=plus to generate your
			$client->setClientId($g["clientid"]);
			$client->setClientSecret($g["clientsecret"]);
			$client->setRedirectUri($site["url"].$site["urla"]."google/");
			$client->setDeveloperKey($g["developerkey"]);
			return $client;
		}
		
		function calendar($app) {
			global $site,$dbh,$parts,$contents;
			$type = "calendar";
			$users = $this->connection($app,$type);
			$calSettings = $parts["google"]["parts"][$type];
			
			if ($users) foreach($users as $user) {
				$client = $this->getClient();
				$token = json_decode($user["token"]);
				$client->setAccessToken(json_encode($user["google"]->token));
				if ($client->isAccessTokenExpired()) { 
					$client->refreshToken($user["google"]->token->refresh_token);
					$sth = $this->dbh->prepare("UPDATE users SET token = ? WHERE id = $user[id]");
					$token->$type->token = json_decode($client->getAccessToken());
					$sth->execute(array(json_encode($token)));
				}
				$service = new Google_CalendarService($client);

				if (!isset($token->$type->id)) {
					$service = new Google_CalendarService($client);
					$calList = $service->calendarList->listCalendarList();
					$cal_id = false;
					if (isset($calList["items"])) 
						foreach($calList["items"] as $cal) 
							if ($cal["summary"]=="veprom")
								$cal_id = $cal["id"];
					if (!$cal_id) {
						$calendar = new Google_Calendar();
						$calendar->setSummary('veprom');
						$calendar->setTimeZone($site["timezone"]);
						$createdCalendar = $service->calendars->insert($calendar);
						$cal_id = $createdCalendar["id"];
					}
					$token->$type->id = $cal_id;
					$sth = $this->dbh->prepare("UPDATE users SET token = ? WHERE id = ?");
					$sth->execute(array(json_encode($token),$user["id"]));
				}

				try {
					$remoteEvents = $service->events->listEvents($token->$type->id);
				} catch (Exception $e) {
					echo err( "Google Calendar Hatası" , $e, $return = true);
					dump($e);
				}
				
				$sth = $this->dbh->prepare("SELECT * FROM `$calSettings[db]` 
					WHERE FIND_IN_SET(?,`$calSettings[users]`) OR FIND_IN_SET(?,`$calSettings[users]`);");
				$sth->execute(array($user["id"],$user["group_id"]+1000000));
				$localEvents = $sth->fetchAll();
				
				$added = array();
				foreach($localEvents as $localEvent) {
					$added[] = $localEvent["cid"];
					$found = false;
					foreach($remoteEvents["items"] as $remoteEvent) 
						if ($remoteEvent["sequence"] == $localEvent["cid"]) {
							$found = true;
							continue 2;
						}

					if (!$found) {
						$event = new Google_Event();
						$event->setSummary($localEvent[$calSettings["event"]]);
						$event->setSequence($localEvent["cid"]);
						$eventDetails = $contents[$calSettings["db"]];
						if (isset($localEvent[$calSettings["start"]])) {
							$start = new Google_EventDateTime();
							if ($eventDetails[$calSettings["start"]]["type"]=="datetime")
								$start->setDateTime($localEvent[$calSettings["start"]]);
							else
								$start->setDate($localEvent[$calSettings["start"]]);
							$event->setStart($start);
						}
						if (isset($localEvent[$calSettings["end"]])) {
							$end = new Google_EventDateTime();
							if ($eventDetails[$calSettings["end"]]["type"]=="datetime")
								$end->setDateTime($localEvent[$calSettings["end"]]);
							else
								$end->setDate($localEvent[$calSettings["end"]]);
							$event->setEnd($end);
						}
						if (isset($localEvent[$calSettings["description"]])) {
							$event->setDescription($localEvent[$calSettings["description"]]);
						}
						if (isset($localEvent[$calSettings["location"]])) {
							$event->setLocation($localEvent[$calSettings["location"]]);
						}
						
						$insert = $service->events->insert($token->$type->id,$event);
						dump($insert);
					}
				}
				foreach ($remoteEvents["items"] as $remoteEvent) {
					if (!in_array($remoteEvent["sequence"], $added))
						$service->events->delete($token->$type->id,$remoteEvent["id"]);
				}
				
			}
		}
		
		function contacts($app) {			
			global $site,$dbh,$parts;
			$type = "contacts";
			$users = $this->connection($app,$type);
			$conSettings = $parts["google"]["parts"][$type];
			
			$sth = $this->dbh->prepare("SELECT * FROM `$conSettings[db]` WHERE app = ?");
			$sth->execute(array($app));
			$contacts = $sth->fetchAll();

			if ($users)
			foreach ($users as $user) {
				$client = $this->getClient();
				$token = json_decode($user["token"]);
				$client->setScopes("http://www.google.com/m8/feeds/");
				$client->setAccessToken(json_encode($user["google"]->token));
				if ($client->isAccessTokenExpired()) { 
					$client->refreshToken($user["google"]->token->refresh_token);
					$sth = $this->dbh->prepare("UPDATE users SET token = ? WHERE id = $user[id]");
					$token->$type->token = json_decode($client->getAccessToken());
					$sth->execute(array(json_encode($token)));
				}
				
				if (!isset($token->$type->id)) {
					$req = new Google_HttpRequest("https://www.google.com/m8/feeds/groups/default/full/");
					$req->setRequestHeaders(array("GData-Version"=>"3.0"));
					//setRequestMethod setPostBody
					$val = $client->getIo()->authenticatedRequest($req);
					$response = json_encode(simplexml_load_string($val->getResponseBody()));
					  
					$con_id = false;
					if (isset($conList["entry"])) 
						foreach($conList["entry"] as $con) 
							if ($con["title"]=="veprom")
								$con_id = $con["id"];
					if (!$con_id) {
						//Create Group and get id
						
						$createGroup = 
									'<atom:entry xmlns:atom="http://www.w3.org/2005/Atom"
									xmlns:gd="http://schemas.google.com/g/2005">
									  <atom:category scheme="http://schemas.google.com/g/2005#kind"
									    term="http://schemas.google.com/contact/2008#group"/>
									  <atom:title type="text">veprom</atom:title>
									  <gd:extendedProperty name="more">
									    <info>Veritronik veprom grubu</info>
									  </gd:extendedProperty>
									</atom:entry>';
						$req = new Google_HttpRequest("https://www.google.com/m8/feeds/groups/default/full/?alt=json",
							"POST",
							array("GData-Version"=>"3.0","Content-Type"=>"application/atom+xml"),
							$createGroup);
//						$req->setRequestHeaders(array("GData-Version"=>"3.0"));
//						$req->setPostBody($createGroup);
//						$req->setRequestMethod("POST");
						$val = $client->getIo()->authenticatedRequest($req);
						$response = json_decode($val->getResponseBody());
						$t = '$t';
						$con_id = $response->entry->id->$t;
					}
					$token->$type->id = $con_id;
					$sth = $this->dbh->prepare("UPDATE users SET token = ? WHERE id = ?");
					$sth->execute(array(json_encode($token),$user["id"]));
				}
				
				$req = new Google_HttpRequest("https://www.google.com/m8/feeds/contacts/default/full"
				."?max-results=10000&alt=json&group=".urlencode($token->$type->id),
							"GET",array("GData-Version"=>"3.0"));
				$val = $client->getIo()->authenticatedRequest($req);
				$response = json_decode($val->getResponseBody());
				$remoteContacts = isset($response->feed->entry) ? $response->feed->entry : array();
				$remoteArray = array();
				dump($remoteContacts);
				$t = '$t';
				foreach($remoteContacts as $contact) {
					if (isset($contact->content)) {
						$remoteArray[$contact->content->$t] = $contact->id->$t;
					}
				}
				dump($remoteArray);
				foreach($contacts as $contact){
					if (isset($remoteArray[$contact["cid"]])) {
						unset($remoteArray[$contact["cid"]]);
						continue;
					} else {
						$this->createContact($contact,$client,$token->$type->id,$conSettings);
					}
				}
				
				foreach($remoteArray as $remote) {
					$this->removeContact($client,$remote);
				}
				
			}

		}
		
		function createContact($contact, $client, $id,$p) {
			$body = "<atom:entry xmlns:atom='http://www.w3.org/2005/Atom'
					xmlns:gd='http://schemas.google.com/g/2005'
					xmlns:gContact='http://schemas.google.com/contact/2008'>
				  <atom:category scheme='http://schemas.google.com/g/2005#kind'
					term='http://schemas.google.com/contact/2008#contact'/>
				  <gd:name>
					 <gd:fullName>".$contact[$p["name"]]."</gd:fullName>
				  </gd:name>
				  <atom:content type='text'>".$contact["cid"]."</atom:content>";
	  		if (isset($contact[$p["email"]])) {
	  			$emails = (array)unserialize($contact[$p["email"]]);
	  			$c = 0;
	  			foreach($emails as $email)
			  		$body .= "<gd:email rel='http://schemas.google.com/g/2005#work' ".($c++?"":"primary='true'")." address='".trim($email)."'/>";
	  		}
	  		if (isset($contact[$p["phone"]])) {
	  			$phones = (array)unserialize($contact[$p["phone"]]);
	  			$c = 0;
	  			foreach($phones as $phone)
			  		$body .= "<gd:phoneNumber rel='http://schemas.google.com/g/2005#work' ".($c++?"":"primary='true'").">".trim($phone)."</gd:phoneNumber>";
	  		}
	  		if (isset($contact[$p["organization"]])) {
		  		$body .= "<gd:organization rel='http://schemas.google.com/g/2005#other'> 
		  		<gd:orgName>".$contact[$p["organization"]]."</gd:orgName>
		  		</gd:organization>";
	  		}
	  		if (isset($contact[$p["address"]])) {
		  		$body .= "<gd:structuredPostalAddress rel='http://schemas.google.com/g/2005#work' primary='true'> <gd:formattedAddress>".$contact[$p["address"]]."</gd:formattedAddress>
		  		</gd:structuredPostalAddress>";
	  		}
	  		$body .= "<gContact:groupMembershipInfo deleted='false' href='$id'/>
	  		</atom:entry>";
	  		$req = new Google_HttpRequest("https://www.google.com/m8/feeds/contacts/default/full/",
							"POST",
							array("GData-Version"=>"3.0","Content-Type"=>"application/atom+xml"),
							$body);
			$val = $client->getIo()->authenticatedRequest($req);
			$response = $val->getResponseBody();
			dump($response);
		}
		
		function removeContact($client, $id) {
	  		$req = new Google_HttpRequest($id,
							"DELETE",
							array("GData-Version"=>"3.0"));
			$val = $client->getIo()->authenticatedRequest($req);
			$response = $val->getResponseBody();
		}
		
	}

