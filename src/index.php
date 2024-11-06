<?php
declare(strict_types=1);

use iutnc\deefy\audio\lists\PlayList;
use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\repository\DeefyRepository;

session_start();

require_once '../vendor/autoload.php';

DeefyRepository::setConfig(__DIR__ . '/../config/deefy.db.ini');

$repo = DeefyRepository::getInstance();

$action = "";
if(isset($_GET['action'])) {
    $action = $_GET['action'];
}else if (isset($_POST['action'])) {
    $action = $_POST['action'];
}

$dispatcher = new \iutnc\deefy\dispatch\Dispatcher($action);
$dispatcher->run();



//////////////// TESTS //////////////////

//$playlists = $repo->findAllPlaylists();
//foreach ($playlists as $pl) {
//    print "playlist  : " . $pl->nom . ":". $pl->id . "\n";
//}
//
//
//$pl = new PlayList('test');
//$pl = $repo->saveEmptyPlaylist($pl);
//print "playlist  : " . $pl->nom . ":". $pl->id . "\n";

//$track = new PodcastTrack('test', 'test.mp3', 'auteur', '2021-01-01', 10, AudioTrack::CLASSIC_ROCK);
//$track = $repo->savePodcastTrack($track);
//print "track 2 : " . $track->titre . ":". get_class($track). "\n";
//$repo->addTrackToPlaylist($pl->id, $track->id);