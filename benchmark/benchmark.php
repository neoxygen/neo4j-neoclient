<?php

require_once(__DIR__.'/../vendor/autoload.php');

use Neoxygen\NeoClient\ClientBuilder;

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

$outputfile = __DIR__.'/results.txt';
$output = '';

$output .= 'Benchmarking client instantation without cache, with result formatter enabled, 1000 runs'."\n";
$start = microtime(true);
$i = 0;
while ($i < 1000) {
    $client = ClientBuilder::create()
        ->setAutoFormatResponse(true)
        ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
        ->build();

    $i++;
}
$usage = convert(memory_get_peak_usage(true));
$end = microtime(true);
$diff = $end - $start;
$output .= sprintf('Runned in %s seconds, using %s memory', $diff, $usage);
$output .= PHP_EOL.'--------------------------'.PHP_EOL;
$client = null;
$i = null;

// ----- Client Instantation with DI cache

$output .= 'Benchmarking client instatation with cache enabled, 1000 runs'.PHP_EOL;

$start = microtime(true);
$i = 0;
while ($i < 1000) {
    $client = ClientBuilder::create()
        ->enableCache(__DIR__.'/../cache/')
        ->setAutoFormatResponse(true)
        ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
        ->build();

    $i++;
}
$usage = convert(memory_get_peak_usage(true));
$end = microtime(true);
$diff = $end - $start;
$output .= sprintf('Runned in %s seconds, using %s memory', $diff, $usage);
$output .= PHP_EOL.'--------------------------'.PHP_EOL;

$client = null;
$i = null;


// ---------- Running 1000 statements with immediate tx commits

$output .= 'Running 1000 statements with immediate tx commits'.PHP_EOL;

$client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
    ->setAutoFormatResponse(true)
    ->build();

$start = microtime(true);
for ($i=0; $i < 1000; $i++) {
    $q = 'CREATE (n:Benchmark {tx_id:{id}})';
    $p = ['id' => $i];
    $client->sendCypherQuery($q, $p);
}
$end = microtime(true);
$usage = convert(memory_get_peak_usage(true));
$diff = $end - $start;
$output .= sprintf('Runned in %s seconds, using %s memory', $diff, $usage).PHP_EOL;
$output .= '--------------------------'.PHP_EOL;

$client = null;
$i = null;
$p = null;
$q = null;

// ---------- Running 1000 statements with immediate tx commits

$output .= 'Running 1000 statements in one transaction commit, separate requests using the TransactionManager'.PHP_EOL;

$client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
    ->setAutoFormatResponse(true)
    ->build();

$start = microtime(true);
$tx = $client->createTransaction();
for ($i=0; $i < 1000; $i++) {
    $q = 'CREATE (n:Benchmark {tx_id:{id}})';
    $p = ['id' => $i];
    $tx->pushQuery($q, $p);
}
$tx->commit();
$end = microtime(true);
$usage = convert(memory_get_peak_usage(true));
$diff = $end - $start;
$output .= sprintf('Runned in %s seconds, using %s memory', $diff, $usage).PHP_EOL;
$output .= '--------------------------'.PHP_EOL;

$client = null;
$i = null;
$p = null;
$q = null;


// ---------- Running 1000 statements with immediate tx commits

$output .= 'Running 1000 statements in one transaction commit, same request'.PHP_EOL;

$client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
    //->setAutoFormatResponse(true)
    ->build();

$start = microtime(true);
$statements = [];
for ($i=0; $i < 1000; $i++) {
    $q = 'CREATE (n {tx_id:{id}})';
    $p = ['id' => $i];
    $st = [
        'statement' => $q,
        'parameters' => $p
    ];
    $statements[] = $st;
}
$client->sendMultiple($statements);
$end = microtime(true);
$usage = convert(memory_get_peak_usage(true));
$diff = $end - $start;
$output .= sprintf('Runned in %s seconds, using %s memory', $diff, $usage).PHP_EOL;
$output .= '--------------------------'.PHP_EOL;

$client = null;
$i = null;
$p = null;
$q = null;

// ----- Handling big graph response format

$output .= 'Handling big graph Response format, 1000 nodes with more edges'.PHP_EOL;
$client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
    ->setAutoFormatResponse(true)
    ->build();
$client->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');

$start = microtime(true);
$q = 'MATCH (n) OPTIONAL MATCH (n)-[r]-() RETURN r,n LIMIT 1000';
$r = $client->sendCypherQuery($q)->getResult();
$end = microtime(true);
$usage = convert(memory_get_peak_usage(true));
$diff = $end - $start;
$output .= sprintf('Runned in %s seconds, using %s memory', $diff, $usage).PHP_EOL;
$output .= '--------------------------'.PHP_EOL;


file_put_contents($outputfile, $output);
