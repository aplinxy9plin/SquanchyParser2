<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
header('Content-Type: application/json');
if (isset($_GET['url']) AND filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    //https://music.youtube.com/playlist?list=RDCLAK5uy_k1TXOdxTGfvM_s7dlTsiap_hnQSyKEJ8Y
    $url = $_GET['url'];
    if (preg_match("#(.+)\/playlist\?list=(.+)(.?)#", $url, $matches) AND isset($matches[2])) {
        $key = "AIzaSyDbZk_VRJziQYwvXj6HYWQshqW02CpjM24";
        //$playlist_id = 'RDCLAK5uy_k1TXOdxTGfvM_s7dlTsiap_hnQSyKEJ8Y';
        $playlist_id = $matches[2];
        $cURLConnection = curl_init();

        curl_setopt($cURLConnection, CURLOPT_URL, 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=' . $playlist_id . '&key=' . $key);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
        ));

        $playlist = curl_exec($cURLConnection);
        curl_close($cURLConnection);
        $songs = json_decode($playlist, true);

        $tracks = array();
        foreach ($songs['items'] AS $item) {
            $o_title = $item['snippet']['title'];
            preg_match("/^(.+)-(.+)$/", $o_title, $matches);
            $track['track_name'] = trim($matches[2]);
            $track['artist'][]['name'] = trim($matches[1]);
            $tracks[] = $track;
            $track = NULL;
        }
        echo json_encode($tracks);
    } else {
        echo json_encode(array("success" => 0, "message" => "No Playlist Found"));
    }
} else {
    echo json_encode(array("success" => 0, "message" => "No URL provided"));
}

