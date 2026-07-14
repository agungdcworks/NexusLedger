<?php
// NexusLedger - REST API
// Contains: API vulnerabilities (auth bypass, IDOR, mass assignment)

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? ($_GET['api_key'] ?? '');

// Weak API authentication
function api_auth($key) {
    global $mysqli;
    if (empty($key)) return false;

    if (SECURITY_LEVEL === 'low') {
        // Accept "demo" key or any query that returns a result
        if ($key === 'demo') return ['id' => 2, 'username' => 'john.doe', 'role' => 'user'];
        // SQL injection in API key check
        $result = $mysqli->query("SELECT id, username, role FROM users WHERE session_token='$key' LIMIT 1");
        return $result && $result->num_rows > 0 ? $result->fetch_assoc() : false;
    } elseif (SECURITY_LEVEL === 'high') {
        $stmt = $GLOBALS['pdo']->prepare("SELECT id, username, role FROM api_tokens WHERE token=? LIMIT 1");
        $stmt->execute([$key]);
        $tok = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tok) {
            $result = $mysqli->query("SELECT id, username, role FROM users WHERE id={$tok['user_id']}");
            return $result->fetch_assoc();
        }
        return false;
    }
    // Impossible: proper HMAC token validation
    return false;
}

// Routing
switch ($endpoint) {
    case 'health':
        echo json_encode(['status' => 'ok', 'timestamp' => date('c'), 'version' => '3.2.1']);
        break;

    case 'users':
        $user = api_auth($api_key);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            break;
        }

        if ($method === 'GET') {
            $uid = $_GET['id'] ?? $user['id'];
            // IDOR: no check if uid belongs to authenticated user
            $result = $mysqli->query("SELECT id, username, email, full_name, role, balance, currency FROM users WHERE id=$uid");
            $data = $result ? $result->fetch_assoc() : null;
            if ($data) {
                // Mass Assignment: expose all fields including sensitive
                echo json_encode(['success' => true, 'user' => $data]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
            }
        }
        break;

    case 'transactions':
        $user = api_auth($api_key);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            break;
        }

        if ($method === 'GET') {
            $uid = $_GET['user_id'] ?? $user['id'];
            // IDOR: can view any user's transactions
            $result = $mysqli->query("SELECT * FROM transactions WHERE user_id=$uid ORDER BY created_at DESC LIMIT 50");
            $txns = [];
            while ($row = $result->fetch_assoc()) $txns[] = $row;
            echo json_encode(['success' => true, 'count' => count($txns), 'transactions' => $txns]);
        } elseif ($method === 'POST') {
            // Mass Assignment: accept all fields
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $to = $input['to_account'] ?? '';
            $amt = $input['amount'] ?? 0;
            $desc = $input['description'] ?? '';
            $ref = 'API-' . strtoupper(substr(md5(time()), 0, 8));
            $mysqli->query("INSERT INTO transactions (user_id, from_account, to_account, amount, type, status, description, ref_number) VALUES ({$user['id']}, 'ACC-{$user['id']}001', '$to', $amt, 'transfer', 'completed', '$desc', '$ref')");
            echo json_encode(['success' => true, 'ref' => $ref]);
        }
        break;

    case 'orders':
        // Endpoint without auth requirement (missing auth check)
        if ($method === 'GET') {
            $oid = $_GET['id'] ?? 0;
            $result = $mysqli->query("SELECT * FROM transactions WHERE id=$oid");
            $data = $result ? $result->fetch_assoc() : null;
            echo json_encode($data ? ['success' => true, 'order' => $data] : ['error' => 'Not found']);
        }
        break;

    default:
        // API documentation
        echo json_encode([
            'service' => 'NexusLedger API v3',
            'endpoints' => [
                '/api/?endpoint=health' => 'GET - Health check',
                '/api/?endpoint=users&id={id}' => 'GET - User info (requires X-API-Key)',
                '/api/?endpoint=transactions' => 'GET/POST - Transactions (requires X-API-Key)',
                '/api/?endpoint=orders&id={id}' => 'GET - Order lookup (public)',
            ],
            'auth' => 'X-API-Key header or api_key parameter',
            'demo_key' => 'demo'
        ]);
        break;
}
