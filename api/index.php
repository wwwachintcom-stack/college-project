<?php
/**
 * MediCare REST API
 * Base URL: http://localhost:8000/api/
 *
 * Routes:
 *   GET    /api/users
 *   GET    /api/users/{id}
 *   POST   /api/users
 *   PUT    /api/users/{id}
 *   PATCH  /api/users/{id}
 *   DELETE /api/users/{id}
 *
 *   (same for: appointments, doctors, prescriptions, bills, waiting_room, notifications)
 */

require_once __DIR__ . '/../config/config.php';

// ── CORS Headers ──────────────────────────────────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); exit;
}

// ── Auth Middleware ───────────────────────────────────────────────────────────
function apiAuth(): array {
    $headers = getallheaders();
    $apiKey  = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? ($_GET['api_key'] ?? '');

    // Session-based auth (for browser requests)
    if (isLoggedIn()) return me();

    // API Key auth
    if (!empty($apiKey)) {
        $user = col('users')->findOne(['api_key' => $apiKey, 'is_active' => true]);
        if ($user) return $user;
    }

    apiError('Unauthorized. Pass X-API-Key header or login first.', 401);
}

function requireApiRole(array $user, string|array $roles): void {
    if (!in_array($user['role'], (array)$roles)) {
        apiError('Forbidden. Required role: ' . implode(' or ', (array)$roles), 403);
    }
}

// ── Response Helpers ──────────────────────────────────────────────────────────
function apiSuccess(mixed $data, int $code = 200, string $message = 'Success'): never {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function apiError(string $message, int $code = 400, array $errors = []): never {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'errors'  => $errors,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function getBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

function cleanDoc(mixed $doc): mixed {
    if (!$doc) return null;
    if (is_array($doc)) {
        // Convert _id to string
        if (isset($doc['_id'])) $doc['_id'] = oid($doc['_id']);
        // Remove password
        unset($doc['password']);
        // Recurse
        foreach ($doc as $k => $v) {
            if (is_array($v)) $doc[$k] = cleanDoc($v);
        }
    }
    return $doc;
}

// ── Router ────────────────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = preg_replace('#^/api/?#', '', $uri);
$parts  = array_filter(explode('/', trim($uri, '/')));
$parts  = array_values($parts);

$resource = $parts[0] ?? null;
$id       = $parts[1] ?? null;

// Allowed collections
$allowed = ['users','appointments','doctors','prescriptions','bills','waiting_room','notifications'];

if (!$resource) {
    apiSuccess([
        'name'      => 'MediCare REST API',
        'version'   => '1.0',
        'endpoints' => array_map(fn($r) => "/api/$r", $allowed),
        'methods'   => ['GET','POST','PUT','PATCH','DELETE'],
    ]);
}

if (!in_array($resource, $allowed)) {
    apiError("Resource '$resource' not found. Allowed: " . implode(', ', $allowed), 404);
}

// ── Route to handler ──────────────────────────────────────────────────────────
match(true) {
    $method === 'GET'    && !$id => handleGetAll($resource),
    $method === 'GET'    &&  $id => handleGetOne($resource, $id),
    $method === 'POST'   && !$id => handlePost($resource),
    $method === 'PUT'    &&  $id => handlePut($resource, $id),
    $method === 'PATCH'  &&  $id => handlePatch($resource, $id),
    $method === 'DELETE' &&  $id => handleDelete($resource, $id),
    default => apiError("Method $method not allowed for this route.", 405),
};

// ── Handlers ──────────────────────────────────────────────────────────────────

/** GET /api/{resource} — List all */
function handleGetAll(string $resource): never {
    $filter  = [];
    $options = [];

    // Filters from query string
    if (!empty($_GET['role']))   $filter['role']   = $_GET['role'];
    if (!empty($_GET['status'])) $filter['status'] = $_GET['status'];
    if (!empty($_GET['search'])) {
        $filter['$or'] = [
            ['name'  => ['$regex' => $_GET['search'], '$options' => 'i']],
            ['email' => ['$regex' => $_GET['search'], '$options' => 'i']],
        ];
    }

    // Pagination
    $limit = min((int)($_GET['limit'] ?? 20), 100);
    $options['limit'] = $limit;

    $docs = col($resource)->find($filter, $options);
    $docs = array_map('cleanDoc', $docs);

    apiSuccess([
        'count'  => count($docs),
        'limit'  => $limit,
        $resource => $docs,
    ]);
}

/** GET /api/{resource}/{id} — Get one */
function handleGetOne(string $resource, string $id): never {
    $doc = col($resource)->findOne(['_id' => toOid($id)]);
    if (!$doc) apiError("$resource with id '$id' not found.", 404);
    apiSuccess(cleanDoc($doc));
}

/** POST /api/{resource} — Create */
function handlePost(string $resource): never {
    $body = getBody();
    if (empty($body)) apiError('Request body is empty or invalid JSON.', 400);

    // Validation per resource
    $errors = validateResource($resource, $body, 'create');
    if (!empty($errors)) apiError('Validation failed.', 422, $errors);

    // Hash password for users
    if ($resource === 'users' && !empty($body['password'])) {
        $body['password'] = password_hash($body['password'], PASSWORD_DEFAULT);
    }

    $body['created_at'] = now();
    $result = col($resource)->insertOne($body);
    $id     = oid($result->insertedId ?? $result->getInsertedId());

    $created = col($resource)->findOne(['_id' => toOid($id)]);
    apiSuccess(cleanDoc($created), 201, ucfirst($resource) . ' created successfully.');
}

/** PUT /api/{resource}/{id} — Full replace */
function handlePut(string $resource, string $id): never {
    $doc = col($resource)->findOne(['_id' => toOid($id)]);
    if (!$doc) apiError("$resource with id '$id' not found.", 404);

    $body = getBody();
    if (empty($body)) apiError('Request body is empty or invalid JSON.', 400);

    $errors = validateResource($resource, $body, 'update');
    if (!empty($errors)) apiError('Validation failed.', 422, $errors);

    if ($resource === 'users' && !empty($body['password'])) {
        $body['password'] = password_hash($body['password'], PASSWORD_DEFAULT);
    }

    $body['updated_at'] = now();
    unset($body['_id'], $body['created_at']);

    col($resource)->updateOne(['_id' => toOid($id)], ['$set' => $body]);
    $updated = col($resource)->findOne(['_id' => toOid($id)]);
    apiSuccess(cleanDoc($updated), 200, ucfirst($resource) . ' updated successfully.');
}

/** PATCH /api/{resource}/{id} — Partial update */
function handlePatch(string $resource, string $id): never {
    $doc = col($resource)->findOne(['_id' => toOid($id)]);
    if (!$doc) apiError("$resource with id '$id' not found.", 404);

    $body = getBody();
    if (empty($body)) apiError('Request body is empty or invalid JSON.', 400);

    if ($resource === 'users' && !empty($body['password'])) {
        $body['password'] = password_hash($body['password'], PASSWORD_DEFAULT);
    }

    $body['updated_at'] = now();
    unset($body['_id'], $body['created_at']);

    col($resource)->updateOne(['_id' => toOid($id)], ['$set' => $body]);
    $updated = col($resource)->findOne(['_id' => toOid($id)]);
    apiSuccess(cleanDoc($updated), 200, ucfirst($resource) . ' patched successfully.');
}

/** DELETE /api/{resource}/{id} — Delete */
function handleDelete(string $resource, string $id): never {
    $doc = col($resource)->findOne(['_id' => toOid($id)]);
    if (!$doc) apiError("$resource with id '$id' not found.", 404);

    col($resource)->deleteOne(['_id' => toOid($id)]);
    apiSuccess(['id' => $id], 200, ucfirst($resource) . ' deleted successfully.');
}

// ── Validation ────────────────────────────────────────────────────────────────
function validateResource(string $resource, array $data, string $mode): array {
    $errors = [];

    if ($resource === 'users') {
        if ($mode === 'create') {
            if (empty($data['name']))     $errors['name']     = 'Name is required.';
            if (empty($data['email']))    $errors['email']    = 'Email is required.';
            if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email.';
            if (empty($data['password'])) $errors['password'] = 'Password is required.';
            if (strlen($data['password'] ?? '') < 6) $errors['password'] = 'Min 6 characters.';
            if (!in_array($data['role'] ?? 'patient', ['patient','doctor','reception','admin'])) {
                $errors['role'] = 'Invalid role.';
            }
            // Check duplicate email
            if (empty($errors['email'])) {
                $exists = col('users')->findOne(['email' => $data['email']]);
                if ($exists) $errors['email'] = 'Email already registered.';
            }
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }
    }

    if ($resource === 'appointments') {
        if ($mode === 'create') {
            if (empty($data['patient_id']))       $errors['patient_id']       = 'Patient ID required.';
            if (empty($data['doctor_id']))        $errors['doctor_id']        = 'Doctor ID required.';
            if (empty($data['appointment_date'])) $errors['appointment_date'] = 'Date required.';
            if (empty($data['appointment_time'])) $errors['appointment_time'] = 'Time required.';
        }
        if (!empty($data['status']) && !in_array($data['status'], ['pending','confirmed','in_progress','completed','cancelled'])) {
            $errors['status'] = 'Invalid status.';
        }
    }

    if ($resource === 'bills') {
        if ($mode === 'create' && empty($data['patient_id'])) {
            $errors['patient_id'] = 'Patient ID required.';
        }
        if (!empty($data['payment_status']) && !in_array($data['payment_status'], ['pending','partial','paid'])) {
            $errors['payment_status'] = 'Invalid payment status.';
        }
    }

    return $errors;
}
