<?php

session_start();

if (isset($_GET['url']) AND filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    $url = $_GET['url'];
    if (preg_match("#(.+)\/playlists?\/(.+)(/|\?)#", $url, $matches)) {
        //print_r($matches);
        if (isset($matches[2])) {
            $playlist = $matches[2];
            $cURLConnection = curl_init();

            curl_setopt($cURLConnection, CURLOPT_URL, "https://api.spotify.com/v1/playlists/$playlist/tracks?limit=10");
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . getSpotifyAuth()
            ));

            $musicList = curl_exec($cURLConnection);
            curl_close($cURLConnection);
            if (isset($musicList)) {
                $jsonArrayResponse = json_decode($musicList, TRUE);
                
                $tracks = array();
                foreach ($jsonArrayResponse['items'] as $track) {
                    $_track['track_name'] = $track['track']['name'];
                    $_track['link'] = $track['track']['href'];
                    $artist_id = $track['track']['album']['artists'][0]['id'];
                    foreach ($track['track']['album']['artists'] as $artist) {
                        $_track['artist'][]['name'] = $artist['name'];
                    }
                    $_track['genres'] = getGenres($artist_id);
                    $tracks[] = $_track;
                    $_track = NULL;
                }
                header('Content-Type: application/json');
                echo json_encode($tracks);
            } else {
                $_SESSION['auth'] = null;
                echo json_encode(array("success" => 0, "message" => "Token Expired, retry again"));
            }

            //print_r($tracks);
        }
    } else {
        echo json_encode(array("success" => 0, "message" => "No Playlist ID found in the URL"));
    }
} else {
    echo json_encode(array("success" => 0, "message" => "No URL provided"));
}

function getSpotifyAuth() {
    if (isset($_SESSION['time']) and ( time() - $_SESSION['time']) < 3600) {
        return $_SESSION['auth'];
    }
    $data = array("grant_type" => "client_credentials");
    $payload = http_build_query($data);
    $encoded = base64_encode("7aa13c6bbaa34db5842b34d4c3005096:15d131e7849d4d1c904aa2bca528edba");
// Prepare new cURL resource
    $ch = curl_init('https://accounts.spotify.com/api/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . $encoded
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
// Submit the POST request
    $result = curl_exec($ch);
// Close cURL session handle
    curl_close($ch);
    //print_r($result);
    if (isset($result)) {
        $response = json_decode($result, TRUE);
        $_SESSION['auth'] = $response['access_token'];
        $_SESSION['time'] = time();
        return $_SESSION['auth'];
    }
}

function getGenres($id) {
    $cURLConnection = curl_init();

    curl_setopt($cURLConnection, CURLOPT_URL, "https://api.spotify.com/v1/artists/$id");
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . getSpotifyAuth()
    ));

    $response = curl_exec($cURLConnection);
    curl_close($cURLConnection);
    $genres = json_decode($response, TRUE);
    return $genres['genres'];
}
