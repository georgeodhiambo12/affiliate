class OrderUpdater
{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function updateOrderStatus($transaction_track_id) {
        $approved = 'yes';
        $sql = "SELECT * FROM orders WHERE transcation_track_id = '$transaction_track_id' AND approved = '$approved'";
        $result = $this->conn->query($sql);

        if ($row = $result->fetch_assoc()) {
            $referral_code = $row['referral_code'];
            $user_id = $row['user'];

            // Update the status in invitations_affiliate to "yes"
            $updateInvitationSql = "UPDATE invitations_affiliate SET status = 'yes' WHERE referral_code = '$referral_code'";
            $this->conn->query($updateInvitationSql);

            // Insert or update supplier details in suppliers_affiliate table
            $sql_check_supplier = "SELECT * FROM suppliers_affiliate WHERE email = (SELECT email FROM users WHERE id = '$user_id')";
            $result_check_supplier = $this->conn->query($sql_check_supplier);

            if ($result_check_supplier->num_rows == 0) {
                $supplier_query = "SELECT * FROM users WHERE id = '$user_id'";
                $supplier_result = $this->conn->query($supplier_query);
                $supplier_row = $supplier_result->fetch_assoc();

                $insertSupplierSql = "INSERT INTO suppliers_affiliate (name, email, phone, affiliate_id, referral_code, status, subscription_plan, subscription_amount) 
                                      VALUES ('{$supplier_row['name']}', '{$supplier_row['email']}', '{$supplier_row['phone']}', 
                                              (SELECT affiliate_id FROM invitations_affiliate WHERE referral_code = '$referral_code'), 
                                              '$referral_code', 'active', 'Premium', '{$row['total']}')";
                $this->conn->query($insertSupplierSql);
            } else {
                // If the supplier exists, update their status and subscription
                $updateSupplierSql = "UPDATE suppliers_affiliate 
                                      SET status = 'active', subscription_amount = '{$row['total']}' 
                                      WHERE email = (SELECT email FROM users WHERE id = '$user_id')";
                $this->conn->query($updateSupplierSql);
            }

            // Record the sale in the sales_affiliate table
            $insertSaleSql = "INSERT INTO sales_affiliate (affiliate_id, sale_amount, commission_earned, sale_date) 
                              VALUES ((SELECT affiliate_id FROM invitations_affiliate WHERE referral_code = '$referral_code'), 
                                      '{$row['total']}', '{$row['total']} * 0.10', NOW())";
            $this->conn->query($insertSaleSql);

            return true;
        } else {
            // If not approved, ensure status is "no" in invitations_affiliate
            $updateInvitationSql = "UPDATE invitations_affiliate SET status = 'no' WHERE referral_code = (SELECT referral_code FROM orders WHERE transcation_track_id = '$transaction_track_id')";
            $this->conn->query($updateInvitationSql);
            return false;
        }
    }
}
