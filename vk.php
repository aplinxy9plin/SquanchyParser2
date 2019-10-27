<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
header("Content-Type: application/json");
if (isset($_GET['url']) AND filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    //https://vk.com/audios90327755?z=audio_playlist-147845620_1238
    $url = $_GET['url'];
    if (preg_match("/(.+)=audio_playlist-([0-9]+)_([0-9]+).?/", $url, $matches) AND isset($matches[2]) AND isset($matches[3])) {
        $owner_id = $matches[2];
        $album_id = $matches[3];
        //echo $owner_id;
        //echo '<br>';
        //echo $album_id;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.vk.com/method/audio.get?access_token=d739a5195170e4873ae0749b587097414a09ee2dbcb800812f9b4440b89327bf4fbad85482d9aae130d2b&owner_id=-$owner_id&v=5.0&album_id=$album_id&=&=",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_COOKIE => "remixlang=0",
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {

            $data = json_decode($response, TRUE);
            $tracks = array();
            $items = array_slice($data['response']['items'], 0, 10);
            foreach ($items as $track) {
                $_track['track_name'] = $track['title'];
                //$_track['link'] = $track['url'];
                $_track['artist'] = $track['artist'];
                $_track['genres'] = getGenre($track['artist'], $track['title']);
                $tracks[] = $_track;
                $_track = null;
            }
            //echo $response;
            echo json_encode($tracks);
        }
    }
} else {
    echo json_encode(array("success" => 0, "message" => "No URL provided"));
}

function getGenre($artist, $song) {
    $cURLConnection = curl_init();

    curl_setopt($cURLConnection, CURLOPT_URL, "http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key=ec83db1fffd7219d6ab6d2c61a6f7214&artist=$artist&track=$song&format=json");
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Type: application/json'
    ));

    $response = curl_exec($cURLConnection);
    curl_close($cURLConnection);
    $genres = json_decode($response, TRUE);
    $tags = array();
    if (isset($genres['track']['toptags']['tag'])) {
        foreach ($genres['track']['toptags']['tag'] as $tag) {
            $genre = $tag['name'];
            $tags[]['name'] = $genre;
            $genre = null;
        }
    }
    return $tags;
}
