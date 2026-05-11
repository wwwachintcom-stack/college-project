<?php
/**
 * Vercel Entry Point — All requests come here
 * Routes to correct PHP file based on URI
 */

// Set base path for includes
define('BASE_PATH', __DIR__ . '/..');

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$uri = rtrim($uri, '/') ?: '/';

// Remove .php extension if present
$uri = preg_replace('/\.php$/', '', $uri);

// Route map
$routes = [
    '/'                    => '/index.php',
    '/auth/login'          => '/auth/login.php',
    '/auth/logout'         => '/auth/logout.php',
    '/auth/register'       => '/auth/register.php',

    '/patient/dashboard'   => '/patient/dashboard.php',
    '/patient/book'        => '/patient/book.php',
    '/patient/appointments'=> '/patient/appointments.php',
    '/patient/prescriptions'=> '/patient/prescriptions.php',
    '/patient/waiting_room'=> '/patient/waiting_room.php',
    '/patient/profile'     => '/patient/profile.php',

    '/doctor/dashboard'    => '/doctor/dashboard.php',
    '/doctor/appointments' => '/doctor/appointments.php',
    '/doctor/patients'     => '/doctor/patients.php',
    '/doctor/prescriptions'=> '/doctor/prescriptions.php',
    '/doctor/waiting_room' => '/doctor/waiting_room.php',
    '/doctor/profile'      => '/doctor/profile.php',

    '/reception/dashboard' => '/reception/dashboard.php',
    '/reception/appointments'=> '/reception/appointments.php',
    '/reception/walkin'    => '/reception/walkin.php',
    '/reception/checkin'   => '/reception/checkin.php',
    '/reception/billing'   => '/reception/billing.php',
    '/reception/waiting_room'=> '/reception/waiting_room.php',
    '/reception/patients'  => '/reception/patients.php',

    '/admin/dashboard'     => '/admin/dashboard.php',
    '/admin/users'         => '/admin/users.php',
    '/admin/doctors'       => '/admin/doctors.php',
    '/admin/appointments'  => '/admin/appointments.php',
    '/admin/billing'       => '/admin/billing.php',
    '/admin/reports'       => '/admin/reports.php',
    '/admin/settings'      => '/admin/settings.php',
];

// API routes
if (str_starts_with($uri, '/api')) {
    // Handle REST API calls
    require_once BASE_PATH . '/config/config.php';

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204); exit;
    }

    // Strip /api prefix and route
    $apiUri = substr($uri, 4) ?: '/';
    $parts  = array_values(array_filter(explode('/', trim($apiUri, '/'))));
    $resource = $parts[0] ?? null;
    $id       = $parts[1] ?? null;

    $allowed = ['users','appointments','doctors','prescriptions','bills','waiting_room','notifications'];

    if (!$resource) {
        http_response_code(200);
        echo json_encode(['success'=>true,'message'=>'MediCare API','version'=>'1.0','endpoints'=>array_map(fn($r)=>"/api/$r",$allowed)]);
        exit;
    }

    if (!in_array($resource, $allowed)) {
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>"Resource '$resource' not found"]);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];

    function apiOk(mixed $data, int $code=200, string $msg='Success'): never {
        http_response_code($code);
        echo json_encode(['success'=>true,'message'=>$msg,'data'=>$data],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        exit;
    }
    function apiErr(string $msg, int $code=400): never {
        http_response_code($code);
        echo json_encode(['success'=>false,'message'=>$msg],JSON_PRETTY_PRINT);
        exit;
    }
    function cleanDoc(mixed $doc): mixed {
        if (!$doc) return null;
        if (is_array($doc)) {
            if (isset($doc['_id'])) $doc['_id'] = oid($doc['_id']);
            unset($doc['password']);
        }
        return $doc;
    }

    match(true) {
        $method==='GET'    && !$id => (function() use($resource) {
            $f=[]; $o=['limit'=>min((int)($_GET['limit']??20),100)];
            if(!empty($_GET['role']))   $f['role']=$_GET['role'];
            if(!empty($_GET['status'])) $f['status']=$_GET['status'];
            $docs=array_map('cleanDoc',col($resource)->find($f,$o));
            apiOk(['count'=>count($docs),'limit'=>$o['limit'],$resource=>$docs]);
        })(),
        $method==='GET'    &&  $id => (function() use($resource,$id) {
            $doc=col($resource)->findOne(['_id'=>toOid($id)]);
            if(!$doc) apiErr("Not found",404);
            apiOk(cleanDoc($doc));
        })(),
        $method==='POST'   && !$id => (function() use($resource,$body) {
            if(empty($body)) apiErr('Empty body');
            if($resource==='users'&&!empty($body['password'])) $body['password']=password_hash($body['password'],PASSWORD_DEFAULT);
            $body['created_at']=now();
            $r=col($resource)->insertOne($body);
            $id=oid($r->insertedId);
            apiOk(cleanDoc(col($resource)->findOne(['_id'=>toOid($id)])),201,'Created');
        })(),
        $method==='PUT'    &&  $id => (function() use($resource,$id,$body) {
            if(!col($resource)->findOne(['_id'=>toOid($id)])) apiErr("Not found",404);
            if($resource==='users'&&!empty($body['password'])) $body['password']=password_hash($body['password'],PASSWORD_DEFAULT);
            $body['updated_at']=now(); unset($body['_id'],$body['created_at']);
            col($resource)->updateOne(['_id'=>toOid($id)],['$set'=>$body]);
            apiOk(cleanDoc(col($resource)->findOne(['_id'=>toOid($id)])));
        })(),
        $method==='PATCH'  &&  $id => (function() use($resource,$id,$body) {
            if(!col($resource)->findOne(['_id'=>toOid($id)])) apiErr("Not found",404);
            if($resource==='users'&&!empty($body['password'])) $body['password']=password_hash($body['password'],PASSWORD_DEFAULT);
            $body['updated_at']=now(); unset($body['_id'],$body['created_at']);
            col($resource)->updateOne(['_id'=>toOid($id)],['$set'=>$body]);
            apiOk(cleanDoc(col($resource)->findOne(['_id'=>toOid($id)])));
        })(),
        $method==='DELETE' &&  $id => (function() use($resource,$id) {
            if(!col($resource)->findOne(['_id'=>toOid($id)])) apiErr("Not found",404);
            col($resource)->deleteOne(['_id'=>toOid($id)]);
            apiOk(['id'=>$id],'Deleted');
        })(),
        default => apiErr("Method not allowed",405),
    };
    exit;
}

// Web routes
$file = $routes[$uri] ?? null;

if (!$file) {
    // Try with trailing variations
    foreach ($routes as $route => $target) {
        if (str_starts_with($uri, $route)) { $file = $target; break; }
    }
}

if ($file && file_exists(BASE_PATH . $file)) {
    // Fix relative paths for includes inside PHP files
    chdir(BASE_PATH . dirname($file));
    require BASE_PATH . $file;
    exit;
}

// 404
http_response_code(404);
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title>
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f8fafc}
.b{text-align:center}.b h2{color:#1e293b}.b p{color:#64748b}.b a{color:#2563eb}</style>
</head><body><div class="b"><h2>404 — Not Found</h2><p>' . htmlspecialchars($uri) . '</p><a href="/">← Home</a></div></body></html>';
