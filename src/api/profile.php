<?php
// api/profile.php - Customer Profile & Settings API

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

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    if (isset($input['action']) && $input['action'] === 'change_password') {
        // Password Change
        if (empty($input['current_password']) || empty($input['new_password']) || empty($input['confirm_new_password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'All password fields are required']);
            exit;
        }

        if ($input['new_password'] !== $input['confirm_new_password']) {
            http_response_code(400);
            echo json_encode(['error' => 'New passwords do not match']);
            exit;
        }

        // Verify current password
        $customer = $customerModel->getById($customerId);
        if (!password_verify($input['current_password'], $customer['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Incorrect current password']);
            exit;
        }

        // Update password
        $result = $customerModel->update($customerId, ['password' => $input['new_password']]);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update password']);
        }

    } elseif (isset($input['action']) && $input['action'] === 'delete_account') {
        // Delete Account (Soft delete/Deactivate)
        // Check password for security
        if (empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Password required to delete account']);
            exit;
        }

        $customer = $customerModel->getById($customerId);
        if (!password_verify($input['password'], $customer['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Incorrect password']);
            exit;
        }

        // Soft delete - set is_active to 0
        $result = $customerModel->update($customerId, ['is_active' => 0]);
        if ($result) {
            session_destroy(); // Logout
            echo json_encode(['success' => true, 'message' => 'Account deactivated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to deactivate account']);
        }

    } else {
        // Profile Update
        $updateData = [];
        $allowedFields = ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = $input[$field];
            }
        }

        // Email update might require verification, skipping for now or handle separately
        // if (isset($input['email']) && $input['email'] !== $user['email']) { ... }

        if (empty($updateData)) {
            http_response_code(400);
            echo json_encode(['error' => 'No changes provided']);
            exit;
        }

        $result = $customerModel->update($customerId, $updateData);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update profile']);
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
