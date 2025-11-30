<?php
require_once(__DIR__ . '/../settings/db_class.php');

class Migration extends db_connection
{
    public function create_table()
    {
        $sql = "CREATE TABLE IF NOT EXISTS saved_venues (
            saved_id INT(11) NOT NULL AUTO_INCREMENT,
            customer_id INT(11) NOT NULL,
            venue_id INT(11) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (saved_id),
            UNIQUE KEY unique_save (customer_id, venue_id),
            FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
            FOREIGN KEY (venue_id) REFERENCES venue(venue_id) ON DELETE CASCADE
        )";

        return $this->query($sql);
    }
}

$migration = new Migration();
if ($migration->create_table()) {
    echo "Table 'saved_venues' created successfully.";
} else {
    echo "Failed to create table.";
}
?>