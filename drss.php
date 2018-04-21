<?php

function get_link_to_file($filename) {
    $url = $_SERVER['REQUEST_URI']; //returns the current URL
    $parts = explode('/',$url);
    $path = "http://". $_SERVER['SERVER_NAME'];

    for ($i = 0; $i < count($parts) - 1; $i++) {
     $path .= $parts[$i] . "/";
    }

    $path .= $filename;

    return $path;
}


require_once("drssconf.php");

$xml = new SimpleXMLElement("<rss version='2.0'/>");
$channel = $xml->addChild("channel");
$channel->addChild("title", DRSS_TITLE);
$channel->addChild("link", DRSS_LINK);
$channel->addChild("description", DRSS_DESCRIPTION);
$channel->addChild("managingEditor", DRSS_MANAGING_EDITOR);
$channel->addChild("pubDate", DRSS_PUB_DATE);

$di = new RecursiveDirectoryIterator(DRSS_DIR,RecursiveDirectoryIterator::SKIP_DOTS);
$it = new RecursiveIteratorIterator($di);

$filenames = array();

foreach($it as $file) {
    array_push($filenames, pathinfo($file, PATHINFO_DIRNAME) . "\\" . pathinfo($file, PATHINFO_BASENAME));
}

$items = array();

foreach($filenames as $file) {
    $meta = get_meta_tags($file);

    $link = get_link_to_file($file);

    if (isset($meta["drss:title"]) && isset($meta["drss:description"]) && isset($meta["drss:author"]) && isset($meta["drss:published"])) {
        $item = array(
            "title" => $meta["drss:title"],
            "link" => $link,
            "description" => $meta["drss:description"],
            "author" => $meta["drss:author"],
            "pubDate" => $meta["drss:published"],
        );
        array_push($items, $item);
    }
}

foreach ($items as $item) {
    $xml_item = $channel->addChild("item");
    $xml_item->addChild("title", $item["title"]);
    $xml_item->addChild("link", $item["link"]);
    $xml_item->addChild("author", $item["author"]);
    $xml_item->addChild("description", $item["description"]);
    $xml_item->addChild("pubDate", $item["pubDate"]);
}

echo $xml->asXML();