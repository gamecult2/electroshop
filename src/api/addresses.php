<?php
// api/addresses.php - Customer Addresses API

header('Content-Type: application/json');
require_once '../includes/init.php';
require_once '../models/Customer.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$customerId = get_current_user_id();
$customerModel = new Customer();

$method = $_SERVER['REQUEST_METHOD'];

// Parse JSON input if available
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $address = $customerModel->getAddressById($_GET['id'], $customerId);
            if ($address) {
                echo json_encode(['success' => true, 'address' => $address]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Address not found']);
            }
        } else {
            $addresses = $customerModel->getCustomerAddresses($customerId);
            echo json_encode(['success' => true, 'addresses' => $addresses]);
        }
        break;

    case 'POST':
        // Add new address
        if (empty($input['wilaya']) || empty($input['daira']) || empty($input['commune']) || 
            empty($input['street_address']) || empty($input['phone_number'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $result = $customerModel->addAddress($customerId, $input);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => t('address_added')]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add address']);
        }
        break;

    case 'PUT':
        // Update existing address or set default
        if (isset($input['action']) && $input['action'] === 'set_default') {
            if (empty($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Address ID required']);
                exit;
            }
            
            $result = $customerModel->setDefaultAddress($input['id'], $customerId);
            if ($result) {
                echo json_encode(['success' => true, 'message' => t('address_updated')]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update address']);
            }
        } else {
            // Regular update
            if (empty($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Address ID required']);
                exit;
            }

            $result = $customerModel->updateAddress($input['id'], $customerId, $input);
            if ($result) {
                echo json_encode(['success' => true, 'message' => t('address_updated')]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update address']);
            }
        }
        break;

    case 'DELETE':
        // Delete address
        if (empty($input['id'])) {
            // Also check query param if not in body (standard for DELETE)
            $input['id'] = $_GET['id'] ?? null;
        }
        
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Address ID required']);
            exit;
        }

        $result = $customerModel->deleteAddress($input['id'], $customerId);
        if ($result) {
            echo json_encode(['success' => true, 'message' => t('address_removed')]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete address']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
