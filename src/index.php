<?php
declare(strict_types=1);

use iutnc\nrv\repository\NrvRepository;

session_start();

require_once '../vendor/autoload.php';

NrvRepository::setConfig(__DIR__ . '/../config/nrv.db.ini');

$repo = NrvRepository::getInstance();

$action = "";
if(isset($_GET['action'])) {
    $action = $_GET['action'];
}else if (isset($_POST['action'])) {
    $action = $_POST['action'];
}

$dispatcher = new \iutnc\nrv\dispatch\Dispatcher($action);
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