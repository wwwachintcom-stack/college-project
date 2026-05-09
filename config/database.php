<?php
/**
 * MediCare — MongoDB Atlas Connection
 * Uses PHP MongoDB Extension 2.x (MongoDB\Driver\*)
 */
require_once __DIR__ . '/env.php';

if (!extension_loaded('mongodb')) {
    die('<pre style="padding:20px;background:#1a1a2e;color:#f87171">
❌ PHP MongoDB extension not loaded!
Add to C:\php\php.ini:  extension=php_mongodb.dll
Then restart the server.
</pre>');
}

$_MONGO_URI = env('MONGO_URI', '');
$_MONGO_DB  = env('MONGO_DB', 'medicare_db');

if (empty($_MONGO_URI) || str_contains($_MONGO_URI, '<username>')) {
    _showSetupGuide();
}

// ── Singleton Manager ─────────────────────────────────────────────────────────
function getManager(): MongoDB\Driver\Manager {
    static $m = null;
    if ($m !== null) return $m;
    global $_MONGO_URI;
    try {
        $m = new MongoDB\Driver\Manager($_MONGO_URI);
        return $m;
    } catch (Exception $e) {
        die('<pre style="padding:20px;background:#1a1a2e;color:#f87171">❌ MongoDB connection failed: ' . $e->getMessage() . '</pre>');
    }
}

// ── Collection factory ────────────────────────────────────────────────────────
function col(string $name): MCollection {
    global $_MONGO_DB;
    return new MCollection($_MONGO_DB, $name);
}

// ── MCollection class ─────────────────────────────────────────────────────────
class MCollection {
    private string $ns;

    public function __construct(
        private string $db,
        private string $name
    ) {
        $this->ns = "$db.$name";
    }

    // ── READ ──────────────────────────────────────────────────────────────────

    public function findOne(array $filter = [], array $options = []): ?array {
        $opts = array_merge(['limit' => 1], $options);
        $docs = $this->find($filter, $opts);
        return $docs[0] ?? null;
    }

    public function find(array $filter = [], array $options = []): array {
        try {
            $qOpts = [];
            if (!empty($options['sort']))       $qOpts['sort']       = (object)$options['sort'];
            if (!empty($options['limit']))      $qOpts['limit']      = (int)$options['limit'];
            if (!empty($options['skip']))       $qOpts['skip']       = (int)$options['skip'];
            if (!empty($options['projection'])) $qOpts['projection'] = (object)$options['projection'];

            $query  = new MongoDB\Driver\Query(empty($filter) ? [] : $filter, $qOpts);
            $cursor = getManager()->executeQuery($this->ns, $query);
            $cursor->setTypeMap(['root'=>'array','document'=>'array','array'=>'array']);
            return $cursor->toArray();
        } catch (Exception $e) {
            return [];
        }
    }

    public function aggregate(array $pipeline): array {
        try {
            $cmd    = new MongoDB\Driver\Command([
                'aggregate' => $this->name,
                'pipeline'  => $pipeline,
                'cursor'    => new stdClass,
            ]);
            $cursor = getManager()->executeCommand($this->db, $cmd);
            $cursor->setTypeMap(['root'=>'array','document'=>'array','array'=>'array']);
            return $cursor->toArray();
        } catch (Exception $e) {
            return [];
        }
    }

    public function countDocuments(array $filter = []): int {
        try {
            $cmd    = new MongoDB\Driver\Command([
                'count' => $this->name,
                'query' => empty($filter) ? new stdClass : $filter,
            ]);
            $result = getManager()->executeCommand($this->db, $cmd);
            $result->setTypeMap(['root'=>'array','document'=>'array','array'=>'array']);
            $res    = current($result->toArray());
            return (int)($res['n'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    public function distinct(string $field, array $filter = []): array {
        try {
            $cmd    = new MongoDB\Driver\Command([
                'distinct' => $this->name,
                'key'      => $field,
                'query'    => empty($filter) ? new stdClass : $filter,
            ]);
            $result = getManager()->executeCommand($this->db, $cmd);
            $result->setTypeMap(['root'=>'array','document'=>'array','array'=>'array']);
            $res    = current($result->toArray());
            return $res['values'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    // ── WRITE ─────────────────────────────────────────────────────────────────

    public function insertOne(array $doc): object {
        if (!isset($doc['created_at'])) $doc['created_at'] = now();
        $bulk = new MongoDB\Driver\BulkWrite(['ordered' => true]);
        $id   = $bulk->insert($doc);
        getManager()->executeBulkWrite($this->ns, $bulk);
        $idStr = (string)$id;
        return (object)['insertedId' => $idStr, 'getInsertedId' => fn() => $idStr];
    }

    public function insertMany(array $docs): object {
        $bulk = new MongoDB\Driver\BulkWrite(['ordered' => true]);
        $ids  = [];
        foreach ($docs as $doc) {
            if (!isset($doc['created_at'])) $doc['created_at'] = now();
            $id    = $bulk->insert($doc);
            $ids[] = (string)$id;
        }
        getManager()->executeBulkWrite($this->ns, $bulk);
        return (object)['insertedIds' => $ids, 'getInsertedIds' => fn() => $ids];
    }

    public function updateOne(array $filter, array $update): void {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update($filter, $update, ['multi' => false, 'upsert' => false]);
        getManager()->executeBulkWrite($this->ns, $bulk);
    }

    public function updateMany(array $filter, array $update): void {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update($filter, $update, ['multi' => true, 'upsert' => false]);
        getManager()->executeBulkWrite($this->ns, $bulk);
    }

    public function deleteOne(array $filter): void {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->delete($filter, ['limit' => 1]);
        getManager()->executeBulkWrite($this->ns, $bulk);
    }

    public function deleteMany(array $filter = []): void {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->delete(empty($filter) ? [] : $filter, ['limit' => 0]);
        getManager()->executeBulkWrite($this->ns, $bulk);
    }

    public function drop(): void {
        try {
            $cmd = new MongoDB\Driver\Command(['drop' => $this->name]);
            getManager()->executeCommand($this->db, $cmd);
        } catch (Exception $e) {}
    }
}

// ── Helper functions ──────────────────────────────────────────────────────────

function oid(mixed $id): string {
    if ($id instanceof MongoDB\BSON\ObjectId) return (string)$id;
    if (is_array($id) && isset($id['$oid'])) return $id['$oid'];
    return (string)$id;
}

function toOid(mixed $id): ?MongoDB\BSON\ObjectId {
    try { return new MongoDB\BSON\ObjectId((string)$id); }
    catch (Exception) { return null; }
}

function toArr(mixed $data): array {
    if (is_array($data)) return array_values($data);
    try { return iterator_to_array($data, false); } catch (Exception) { return []; }
}

function now(): MongoDB\BSON\UTCDateTime {
    return new MongoDB\BSON\UTCDateTime();
}

function mDate(string $d): MongoDB\BSON\UTCDateTime {
    return new MongoDB\BSON\UTCDateTime(strtotime($d) * 1000);
}

function today(): MongoDB\BSON\UTCDateTime {
    return new MongoDB\BSON\UTCDateTime(strtotime('today') * 1000);
}

function tomorrow(): MongoDB\BSON\UTCDateTime {
    return new MongoDB\BSON\UTCDateTime(strtotime('tomorrow') * 1000);
}

function fmtDate(mixed $d, string $fmt = 'd M Y'): string {
    if (!$d) return '—';
    if ($d instanceof MongoDB\BSON\UTCDateTime) return date($fmt, $d->toDateTime()->getTimestamp());
    if (is_array($d) && isset($d['$date']['$numberLong'])) return date($fmt, (int)($d['$date']['$numberLong'] / 1000));
    if (is_string($d)) return date($fmt, strtotime($d));
    return '—';
}

function badgeClass(string $s): string {
    return match($s) {
        'confirmed','paid','completed','done','active' => 'badge-success',
        'pending','partial'                            => 'badge-warning',
        'in_progress','waiting'                        => 'badge-info',
        'cancelled','left','inactive'                  => 'badge-danger',
        default                                        => 'badge-gray',
    };
}

function statusLabel(string $s): string {
    return ucfirst(str_replace('_', ' ', $s));
}

function nextInvoice(): string {
    return 'INV-' . date('Y') . '-' . str_pad(col('bills')->countDocuments() + 1, 4, '0', STR_PAD_LEFT);
}

function _showSetupGuide(): never {
    die('<!DOCTYPE html><html><head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Inter,sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
    .box{background:#1e293b;border-radius:16px;padding:40px;max-width:580px;width:100%;border:1px solid #334155}
    h2{color:#f87171;font-size:20px;margin-bottom:8px}
    p{color:#94a3b8;font-size:14px;margin-bottom:24px}
    .step{font-size:13px;line-height:1.8;color:#cbd5e1;margin-bottom:12px}
    pre{background:#020617;border-radius:8px;padding:14px;font-size:12px;color:#86efac;margin-top:10px;border:1px solid #1e3a5f}
    a{color:#60a5fa}
    </style></head><body><div class="box">
    <h2>⚙️ MongoDB Atlas Setup Required</h2>
    <p>Open <strong>medicare/.env</strong> and set your connection string:</p>
    <div class="step">
        1. Go to <a href="https://cloud.mongodb.com" target="_blank">cloud.mongodb.com</a><br>
        2. Your Cluster → <strong>Connect</strong> → <strong>Drivers</strong><br>
        3. Copy the connection string, replace &lt;password&gt;<br>
        4. If password has special chars (@, #, $), encode them: @ → %40
    </div>
    <pre>MONGO_URI=mongodb+srv://username:password@cluster0.xxxxx.mongodb.net/?appName=Cluster0
MONGO_DB=medicare_db</pre>
    </div></body></html>');
}
