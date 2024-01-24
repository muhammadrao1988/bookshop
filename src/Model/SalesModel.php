<?php

namespace SalesDashboard\Model;

use SalesDashboard\Service\DbService;
use SalesDashboard\Service\VersionHandlerService;

class SalesModel
{
    private $db;

    public function __construct(DbService $database)
    {
        $this->db = $database->getConnection();
    }

    public function getSalesData($itemsPerPage, $offset, $filterCustomer = '', $filterProduct = '', $filterPrice = '')
    {
        try {
            $sql = "SELECT sales.id as sales_id, customers.customer_name, products.product_name, sales.product_price,sales.ordered_at
            FROM sales
            INNER JOIN customers ON sales.customer_id = customers.id
            INNER JOIN products ON sales.product_id = products.id
            WHERE 1 = 1";
            $params = [];
            if (!empty($filterCustomer)) {
                $sql .= " AND customers.customer_name LIKE :filterCustomer";
                $params[':filterCustomer'] = '%' . $filterCustomer . '%'; // Adding wildcards for LIKE query
            }

            if (!empty($filterProduct)) {
                $sql .= " AND products.product_name LIKE :filterProduct";
                $params[':filterProduct'] = '%' . $filterProduct . '%'; // Adding wildcards for LIKE query
            }

            if (!empty($filterPrice)) {
                $sql .= " AND products.product_price <= :filterPrice";
                $params[':filterPrice'] = $filterPrice;
            }

            $sql .= " LIMIT :offset, :itemsPerPage";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $param => $value) {
                $stmt->bindParam($param, $value);
            }

            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->bindParam(':itemsPerPage', $itemsPerPage, \PDO::PARAM_INT);

            $stmt->execute();

            // Print the SQL query
            //echo "SQL Query: " . $stmt->queryString;

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }catch (\PDOException $e) {
            // Check if the error message indicates a missing table
            if (strpos($e->getMessage(), 'Base table or view not found') !== false) {
                // Redirect to import_json.php
                header('Location: import_json.php');
                exit();
            } else {
                // Handle other types of errors
                echo "An error occurred: " . $e->getMessage();
            }
        }

    }

    public function getTotalPages($itemsPerPage, $filterCustomer = '', $filterProduct = '', $filterPrice = '')
    {
        $sql = "SELECT COUNT(*) as total
            FROM sales
            INNER JOIN customers ON sales.customer_id = customers.id
            INNER JOIN products ON sales.product_id = products.id
            WHERE 1 = 1";

        $params = [];

        if (!empty($filterCustomer)) {
            $sql .= " AND customers.customer_name LIKE :filterCustomer";
            $params[':filterCustomer'] = '%' . $filterCustomer . '%'; // Adding wildcards for LIKE query
        }

        if (!empty($filterProduct)) {
            $sql .= " AND products.product_name LIKE :filterProduct";
            $params[':filterProduct'] = '%' . $filterProduct . '%'; // Adding wildcards for LIKE query
        }

        if (!empty($filterPrice)) {
            $sql .= " AND products.product_price <= :filterPrice";
            $params[':filterPrice'] = $filterPrice;
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $param => $value) {
            $stmt->bindParam($param, $value);
        }

        $stmt->execute();

        $total = $stmt->fetchColumn();

        return ceil($total / $itemsPerPage);
    }

    private function tableExists($tableName)
    {
        $sql = "SHOW TABLES LIKE :table_name";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':table_name', $tableName, \PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function truncateCreateTables()
    {
        if ($this->tableExists('products')) {
            $this->truncateProductsTable();
        } else {
            $this->createProductsTable();
        }

        if ($this->tableExists('customers')) {
            $this->truncateCustomersTable();
        } else {
            $this->createCustomersTable();
        }

        if ($this->tableExists('sales')) {
            $this->truncateSalesTable();
        } else {
            $this->createSalesTable();
        }
    }

    public function createProductsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS products (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    product_name VARCHAR(161) COLLATE utf8mb4_0900_ai_ci,
                    product_price DOUBLE(16, 2)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    public function createCustomersTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS customers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_name VARCHAR(161) COLLATE utf8mb4_0900_ai_ci,
                    customer_email VARCHAR(161) COLLATE utf8mb4_0900_ai_ci
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    public function createSalesTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS sales (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    product_id INT,
                    product_price DOUBLE(16, 2),
                    customer_id INT,
                    ordered_at DATETIME,
                    FOREIGN KEY (product_id) REFERENCES products(id),
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    public function truncateProductsTable()
    {
        $sql = "TRUNCATE TABLE products";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    public function truncateCustomersTable()
    {
        $sql = "TRUNCATE TABLE customers";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    public function truncateSalesTable()
    {
        $sql = "TRUNCATE TABLE sales";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    public function saveSalesData($jsonData)
    {
        $this->truncateCreateTables();


        foreach ($jsonData as $saleData) {
            // Insert or update product and get product ID
            $productId = $this->saveProduct($saleData['product_name'], $saleData['product_price']);

            // Insert or update customer and get customer ID
            $customerId = $this->saveCustomer($saleData['customer_name'], $saleData['customer_mail']);

            // Convert sale date to UTC based on version
            $saleDate = $this->convertToUTC($saleData['sale_date'], $saleData['version']);

            // Insert sales data
            $this->saveSale($productId, $saleData['product_price'], $customerId, $saleDate);
        }
    }

    private function saveProduct($productName, $productPrice)
    {
        // Check if the product already exists
        $existingProductId = $this->getProductId($productName);

        if ($existingProductId) {
            return $existingProductId; // Product already exists, return its ID
        }

        // Product doesn't exist, insert a new record
        $sql = "INSERT INTO products (product_name, product_price)
                VALUES (:product_name, :product_price)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':product_name', $productName);
        $stmt->bindParam(':product_price', $productPrice);
        $stmt->execute();

        return $this->db->lastInsertId(); // Return the ID of the newly inserted product
    }

    private function getProductId($productName)
    {
        $sql = "SELECT id FROM products WHERE product_name = :product_name";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':product_name', $productName, \PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? $result['id'] : null;
    }

    private function saveCustomer($customerName, $customerEmail)
    {
        // Check if the customer email already exists
        $existingCustomerId = $this->getCustomerIdByEmail($customerEmail);

        if ($existingCustomerId) {
            return $existingCustomerId; // Customer with the same email already exists, return its ID
        }

        // Customer with the same email doesn't exist, insert a new record
        $sql = "INSERT INTO customers (customer_name, customer_email)
                VALUES (:customer_name, :customer_email)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':customer_name', $customerName);
        $stmt->bindParam(':customer_email', $customerEmail);
        $stmt->execute();

        return $this->db->lastInsertId(); // Return the ID of the newly inserted customer
    }

    private function getCustomerIdByEmail($customerEmail)
    {
        $sql = "SELECT id FROM customers WHERE customer_email = :customer_email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':customer_email', $customerEmail, \PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? $result['id'] : null;
    }

    private function saveSale($productId, $productPrice, $customerId, $orderedAt)
    {
        $sql = "INSERT INTO sales (product_id, product_price, customer_id, ordered_at)
                VALUES (:product_id, :product_price, :customer_id, :ordered_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':product_price', $productPrice);
        $stmt->bindParam(':customer_id', $customerId);
        $stmt->bindParam(':ordered_at', $orderedAt);
        $stmt->execute();
    }

    private function convertToUTC($dateTime, $version)
    {
        // Check if the version is greater than or equal to the threshold version
        $thresholdVersion = '1.0.17+60';
        $compareResult = VersionHandlerService::compareVersions($version, $thresholdVersion);

        // Determine the source timezone based on the comparison result
        $sourceTimezone = ($compareResult >= 0) ? VersionHandlerService::TIMEZONE_NEW_VERSION : VersionHandlerService::TIMEZONE_OLD_VERSION;

        // Create DateTime object with the provided date and source timezone
        $dateTimeObject = new \DateTime($dateTime, new \DateTimeZone($sourceTimezone));

        // Convert to UTC timezone
        $dateTimeObject->setTimezone(new \DateTimeZone(VersionHandlerService::TIMEZONE_NEW_VERSION));

        // Return the formatted UTC date
        return $dateTimeObject->format('Y-m-d H:i:s');
    }

}