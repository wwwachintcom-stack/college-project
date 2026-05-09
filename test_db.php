<?php
require_once __DIR__ . '/config/env.php';

$uri = env('MONGO_URI', '');
echo "URI: " . substr($uri, 0, 50) . "...\n";
echo "Extension version: " . phpversion('mongodb') . "\n";

// Extension 2.x uses MongoDB\Driver directly — no library needed
try {
    $manager = new MongoDB\Driver\Manager($uri . '&tlsInsecure=true', [], []);

    // Test ping
    $cmd = new MongoDB\Driver\Command(['ping' => 1]);
    $result = $manager->executeCommand('admin', $cmd);
    $res = current($result->toArray());
    echo "✅ Connected! Ping: " . ($res->ok ?? 0) . "\n";

    // Test insert
    $bulk = new MongoDB\Driver\BulkWrite();
    $id = $bulk->insert(['test' => true, 'time' => new MongoDB\BSON\UTCDateTime()]);
    $manager->executeBulkWrite(env('MONGO_DB','medicare_db') . '.test_api', $bulk);
    echo "✅ Insert OK: $id\n";

    // Test find
    $query = new MongoDB\Driver\Query(['test' => true], ['limit' => 1]);
    $cursor = $manager->executeQuery(env('MONGO_DB','medicare_db') . '.test_api', $query);
    $docs = $cursor->toArray();
    echo "✅ Find OK: " . count($docs) . " doc(s)\n";

    // Test update
    $bulk2 = new MongoDB\Driver\BulkWrite();
    $bulk2->update(['test' => true], ['$set' => ['updated' => true]]);
    $manager->executeBulkWrite(env('MONGO_DB','medicare_db') . '.test_api', $bulk2);
    echo "✅ Update OK\n";

    // Test delete
    $bulk3 = new MongoDB\Driver\BulkWrite();
    $bulk3->delete(['test' => true]);
    $manager->executeBulkWrite(env('MONGO_DB','medicare_db') . '.test_api', $bulk3);
    echo "✅ Delete OK\n";

    echo "\n🎉 All tests passed! MongoDB Atlas connected.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
