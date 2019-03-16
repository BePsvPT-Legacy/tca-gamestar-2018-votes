<?php

require_once __DIR__.'/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

$client = new Client([
  'base_uri' => 'https://script.google.com',
  'headers' => [
    'Accept-Language' => 'en-US,en;q=0.9',
    'DNT' => '1',
    'Upgrade-Insecure-Requests' => '1',
    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3628.0 Safari/537.36',
  ],
]);

$sheets = [
  'Form Responses 1', 'Recently', 'Verified', 'Uniqued',
  'Counts', 'Awards', 'Players', 'Draw', 'Winners',
];

$promises = array_combine($sheets, array_map(function ($sheet) use ($client) {
  return $client->getAsync('/macros/s/AKfycbyGBlT3a_nIiSDGUEK1Y9F0xuVUAs4wm13EBBTUCCWfDZkK6Yc/exec', [
    'query' => [
      'id' => '1Xr9U7-5L847qGeIxO6fDr_4hrmHdtNqYHg4fPnOP10I',
      'sheet' => $sheet,
    ],
  ]);
}, $sheets));

$results = Promise\settle($promises)->wait();

foreach ($results as $key => $response) {
  $path = sprintf('%s/data/%s/%s.json', __DIR__, $key, date('Y-m-d-H-i-s'));

  if (!is_dir(dirname($path))) {
    mkdir(dirname($path), 0777, true);
  }

  file_put_contents($path, $response['value']->getBody()->getContents());
}
