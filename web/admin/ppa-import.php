<?php
/**
 * Created by PhpStorm.
 * User: chrigu
 * Date: 18/02/15
 * Time: 23:25
 */

$datacollectionFolder = "../../data/modules/datacollection/";

$content = file_get_contents("http://api.pingpongapp.ch/datacollection/");
$data = json_decode($content, true);

foreach ($data as $index => $value) {
    $filename = "import_" . $index;

    $image = $value['Gallery_plupload'];
    $imageinfo = pathinfo($image['url']);
    $imagenameLocal = $filename . "_image_" . $image['width'] . "x" . $image['height'] . "." . $imageinfo['extension'];
    $imagepathLocal = $datacollectionFolder . $imagenameLocal;
    if (!file_exists($imagepathLocal))
        file_put_contents($imagepathLocal, file_get_contents($image['url']));

    $location = $value['location'];
    $location = str_replace(" ", ", ", $location);
    $content = array(
        'image' => $imagenameLocal,
        'title' => $value['titel'],
        'location' => $location,
        'description' => $value['description'],
        'rating' => $value['rating'],
        'created_at' => time(),
        'updated_at' => time(),
    );
    $datafilepath = $datacollectionFolder . $filename . '.json';
    if (!file_exists($datafilepath))
        file_put_contents($datafilepath, json_encode($content));
}

echo "<pre>";
var_dump($data);