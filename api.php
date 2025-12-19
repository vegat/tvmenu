<?php
header('Content-Type: application/json; charset=utf-8');

$dataFile = __DIR__ . '/data/menu.json';

function load_data($file)
{
    if (!file_exists($file)) {
        $default = [
            'updated_at' => microtime(true),
            'items' => []
        ];
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $default;
    }

    $raw = file_get_contents($file);
    $data = json_decode($raw, true);
    if (!$data) {
        $data = [
            'updated_at' => microtime(true),
            'items' => []
        ];
    }

    return $data;
}

function save_data($file, $data)
{
    $data['updated_at'] = microtime(true);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function normalize_price($price)
{
    $raw = trim((string)$price);
    $numeric = preg_replace('/[^0-9.,]/', '', $raw);
    $numeric = str_replace(',', '.', $numeric);
    $numeric = $numeric === '' ? '0' : $numeric;
    $number = is_numeric($numeric) ? (float)$numeric : 0;
    $display = floor($number) == $number ? number_format($number, 0, '.', '') : rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    return $display . ' PLN';
}

function respond($payload, $code = 200)
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($method === 'GET' && $action === 'health') {
    respond(['status' => 'ok']);
}

if ($method === 'GET' && $action === 'menu') {
    $since = isset($_GET['since']) ? floatval($_GET['since']) : null;
    $wait = isset($_GET['wait']) && $_GET['wait'] == '1';
    $start = microtime(true);
    $timeout = 20; // seconds

    while ($wait && $since !== null && (microtime(true) - $start) < $timeout) {
        clearstatcache();
        $current = file_exists($dataFile) ? filemtime($dataFile) : microtime(true);
        if ($current > $since) {
            break;
        }
        usleep(400000);
    }

    $data = load_data($dataFile);
    $activeItems = array_values(array_filter($data['items'], fn($item) => $item['active']));
    respond([
        'updated_at' => $data['updated_at'],
        'items' => $activeItems
    ]);
}

// For non-GET requests we expect JSON
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'list':
        $data = load_data($dataFile);
        respond($data);
        break;
    case 'add':
        $name = trim($input['name'] ?? '');
        $price = trim($input['price'] ?? '');
        $description = trim($input['description'] ?? '');
        if ($name === '' || $price === '') {
            respond(['error' => 'Nazwa i cena są wymagane.'], 400);
        }

        $data = load_data($dataFile);
        $id = uniqid('item_', true);
        $data['items'][] = [
            'id' => $id,
            'name' => $name,
            'price' => normalize_price($price),
            'description' => $description,
            'active' => true
        ];
        save_data($dataFile, $data);
        respond(['message' => 'Dodano pozycję', 'id' => $id]);
        break;
    case 'toggle':
        $id = $input['id'] ?? null;
        $active = (bool)($input['active'] ?? false);
        if (!$id) {
            respond(['error' => 'Brak identyfikatora.'], 400);
        }
        $data = load_data($dataFile);
        foreach ($data['items'] as &$item) {
            if ($item['id'] === $id) {
                $item['active'] = $active;
                save_data($dataFile, $data);
                respond(['message' => 'Zaktualizowano status']);
            }
        }
        respond(['error' => 'Nie znaleziono pozycji.'], 404);
        break;
    case 'delete':
        $id = $input['id'] ?? null;
        if (!$id) {
            respond(['error' => 'Brak identyfikatora.'], 400);
        }
        $data = load_data($dataFile);
        $before = count($data['items']);
        $data['items'] = array_values(array_filter($data['items'], fn($item) => $item['id'] !== $id));
        if (count($data['items']) === $before) {
            respond(['error' => 'Nie znaleziono pozycji.'], 404);
        }
        save_data($dataFile, $data);
        respond(['message' => 'Usunięto pozycję']);
        break;
    default:
        respond(['error' => 'Nieobsługiwane działanie.'], 400);
}
