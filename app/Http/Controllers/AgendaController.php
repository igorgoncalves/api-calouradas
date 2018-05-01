<?php

namespace App\Http\Controllers;

use App\Calourada;


class AgendaController extends Controller
{
    protected $client;
    public function __construct()
    {
      $this->client = $this->getClient();
    }

    function index(){
        // Get the API client and construct the service object.

      $service = new \Google_Service_Calendar($this->client);

      // Print the next 10 events on the user's calendar.
      $calendarId = 'primary';
      $optParams = array(
        'maxResults' => 10,
        'orderBy' => 'startTime',
        'singleEvents' => true,
        'timeMin' => date('c'),
      );

      $results = $service->events->listEvents($calendarId, $optParams);
      $events = array();
      if (!empty($results->getItems())) {
          foreach ($results->getItems() as $event) {
              $start = $event->start->dateTime;
              if (empty($start)) {
                  $start = $event->start->date;
              }
              $novo = new Calourada($event->getSummary(), $start);
              array_push($events, $novo);
          }
      }
      return json_encode($events);
    }

    function store(){

    }

    protected function getClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Google Calendar API PHP Quickstart');
        $client->setScopes(\Google_Service_Calendar::CALENDAR_READONLY);
        $client->setAuthConfig('client_secret.json');
        $client->setAccessType('offline');
        // Load previously authorized credentials from a file.
        $credentialsPath = $this->getHomeDirectory('credentials.json');
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            $authCode = "4/AABtpKMEW1Gtiyys-NcPiEyuuVxzTmrxFfIQKd1LEolzcugnHSVbhvfcB-G8isToY4BEjzSQQaWGrDApkjvp9Ng";

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            // Store the credentials to disk.
            if (!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0700, true);
            }
            file_put_contents($credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    /**
     * Expands the home directory alias '~' to the full path.
     * @param string $path the path to expand.
     * @return string the expanded path.
     */
    protected function getHomeDirectory($path)
    {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }



}