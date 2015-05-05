<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 10/04/15
 * Time: 23:50
 */

$v = filter_input(INPUT_GET, 'v');
$url = filter_input(INPUT_GET, 'url');
if ($url) {
    $urlData = parse_url($url, PHP_URL_QUERY);
    $urlQueryParams = array();
    parse_str($urlData, $urlQueryParams);
    if (array_key_exists('v', $urlQueryParams))
        $v = $urlQueryParams['v'];
}

// http://forum.videohelp.com/threads/294962-ripping-videos-from-youtube-in-high-quality
// http://keepvid.com/?url=https%3A%2F%2Fyoutu.be%2FhppfnQdckVQ
// https://www.youtube.com/watch?v=hppfnQdckVQ
// http://gdata.youtube.com/feeds/api/videos/rUDm2xatms4
// http://r18---sn-5hnezn7s.googlevideo.com/videoplayback?source=youtube&sparams=dur,id,initcwndbps,ip,ipbits,itag,mime,mm,ms,mv,pl,ratebypass,source,upn,expire&mv=m&initcwndbps=5301250&ipbits=0&ms=au&mt=1428705431&sver=3&id=o-AKCOggdNy7qOlSgZ5Ns3iUs-Q3mNwhE9pNjvTmbu4I32&dur=102.307&expire=1428727072&pl=38&signature=149BD46BA8EC6911FF45E45A294A7760C85236A0.858E3086AACC31C011043BAC2D54B77736C7453F&fexp=900720,907263,932627,932631,934954,9408292,9408347,9408412,9408708,9408919,946008,947243,948124,948703,951703,952612,957201,961404,961406&key=yt5&ip=2001:1af8:4700:a022:1::d98c&mm=31&mime=video/mp4&upn=9D2ogjLFK9w&ratebypass=yes&itag=22&title=Jati+%7C+Aaron+Hampshire


// vimeo sample:
// https://vimeo.com/123004750
// http://en.savefrom.net/?rmode=false
// https://pdlvimeocdn-a.akamaihd.net/86723/638/349055885.mp4?token2=1428707791_320fa1b088c6a27bdf8af27621dd5446&aksessionid=5535823af49855af

$feedUrl = "http://gdata.youtube.com/feeds/api/videos?q=$v&format=1&alt=json";
$response = file_get_contents($feedUrl);
$data = json_decode($response, true);
$rtspUrl = "";

try {
    $rtspUrl = $data['feed']['entry'][0]['media$group']['media$content'][1]['url'];
} catch (Exception $e) {
    //
}

echo json_encode(array(
    'success' => true,
    'v' => $v,
    'rtsp' => $rtspUrl
));

