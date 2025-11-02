<?php
require_once 'connect.php';

class table_policy {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Get policy by type
     * @param string $policy_type - 'term' or 'policy'
     * @return array
     */
    public function getPolicyByType($policy_type) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM policy WHERE policy_type = ?");
            $stmt->execute([$policy_type]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'success' => true,
                    'data' => $result,
                    'message' => 'Policy retrieved successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Policy not found'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all policies
     * @return array
     */
    public function getAllPolicies() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM policy ORDER BY policy_type");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $results,
                'message' => 'Policies retrieved successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update policy text
     * @param string $policy_type - 'term' or 'policy'
     * @param string $policy_text - New policy text
     * @return array
     */
    public function updatePolicy($policy_type, $policy_text) {
        try {
            // Check if policy type exists
            $checkStmt = $this->conn->prepare("SELECT policy_id FROM policy WHERE policy_type = ?");
            $checkStmt->execute([$policy_type]);
            
            if (!$checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Policy type not found'
                ];
            }

            // Update policy text
            $stmt = $this->conn->prepare("UPDATE policy SET policy_text = ? WHERE policy_type = ?");
            $result = $stmt->execute([$policy_text, $policy_type]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Policy updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update policy'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if policy type is valid
     * @param string $policy_type
     * @return bool
     */
    public function isValidPolicyType($policy_type) {
        $validTypes = ['term', 'policy', 'cookie', 'about'];
        return in_array($policy_type, $validTypes);
    }

    /**
     * Initialize default policies if not exist
     * @return array
     */
    public function initializeDefaultPolicies() {
        try {
            // Check if policies exist
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM policy");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                // Insert default policies
                $defaultPolicies = [
                    ['term', 'เงื่อนไขการใช้บริการเริ่มต้น'],
                    ['policy', 'นโยบายความเป็นส่วนตัวเริ่มต้น'],
                    ['cookie', 'นโยบายคุกกี้เริ่มต้น'],
                    ['about', 'ข้อมูลเกี่ยวกับเราเริ่มต้น']
                ];
                
                $insertStmt = $this->conn->prepare("INSERT INTO policy (policy_type, policy_text) VALUES (?, ?)");
                
                foreach ($defaultPolicies as $policy) {
                    $insertStmt->execute($policy);
                }
                
                return [
                    'success' => true,
                    'message' => 'Default policies initialized'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Policies already exist'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function __destruct() {
        $this->db = null;
        $this->conn = null;
    }
}
?>
