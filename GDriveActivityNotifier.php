<?php
require_once 'config.php';
require_once 'lib/google-api-php-client/src/Google_Client.php';
require_once 'lib/google-api-php-client/src/contrib/Google_DriveService.php';
/**
 * Retrieve a list of Change resources.
 *
 * @param Google_DriveService $service Drive API service instance.
 * @param String $startChangeId ID of the change to start retrieving subsequent
                                changes from or NULL.
 * @return Array List of Google_Change resources.
 */
function retrieveLastChanges($service, $startChangeId = NULL) {
  $result = array();
  $pageToken = NULL;

  do {
    try {
      $parameters = array();
      if ($startChangeId) {
        $parameters['startChangeId'] = $startChangeId;
      }
      if ($pageToken) {
        $parameters['pageToken'] = $pageToken;
      }
      $changes = $service->changes->listChanges($parameters);

      if ($changes) {
          $result = array_merge($result, $changes);
      }
      #$pageToken = $changes['nextPageToken'];
    } catch (Exception $e) {
      print "An error occurred: " . $e->getMessage();
      $pageToken = NULL;
    }
  } while ($pageToken);
  return $result;
}


function run_auth_and_get_token($client) {
    $authUrl = $client->createAuthUrl();

    //Request authorization
    print "Please visit:\n$authUrl\n\n";
    exec("open '$authUrl'");
    print "Please enter the auth code:\n";
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for access token
    $accessToken = $client->authenticate($authCode);
    $client->setAccessToken($accessToken);
    return $accessToken;
}


$client = new Google_Client();
// Get your credentials from the console
$client->setClientId(CLIENT_ID);
$client->setClientSecret(CLIENT_SECRET);
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
$client->setScopes(array('https://www.googleapis.com/auth/drive'));

$service = new Google_DriveService($client);
$token = @file_get_contents('token.txt');
if ($token) {
    $client->setAccessToken($token);
} else {
    $token = run_auth_and_get_token($client);
    file_put_contents('token.txt', $token);
}

$last = @file_get_contents('largestChangeId.txt');
if ($last) {
    $data = retrieveLastChanges($service, $last);
} else {
    $data = retrieveLastChanges($service);
}
file_put_contents('largestChangeId.txt', $data['largestChangeId']);

if (count(@$data['item'])  >= 10) {
    // skip notification when more than 10 items updated.
    exit(0);
}

foreach($data['items'] as $item) {
    if ($item['id'] != $last) {
        $deleted = @$item['deleted'];
        $title = @$item['file']['title'];
        $selfLink = @$item['file']['selfLink'];
        if ($deleted) {
            exec("echo -e 'display notification \"$title 已被刪除\" with title \"已刪除 $title\" subtitle \"\"\\ndelay 2' | osascript");
        }
        else if ($title && $selfLink) {
            exec("echo 'display notification \"$title 已被更新\" with title \"已更新 $title\" subtitle \"$selfLink\"\\ndelay 2' | osascript");
        }
    }
}

