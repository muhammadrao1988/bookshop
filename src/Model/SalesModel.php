<?php

namespace SalesDashboard\Model;

use SalesDashboard\Service\DbService;

class SalesModel
{
    private $db;

    public function __construct(DbService $database)
    {
        $this->db = $database->getConnection();
    }

    public function getSalesData($itemsPerPage, $offset, $filterCustomer = '', $filterProduct = '', $filterPrice = '')
    {
         $sql = "SELECT sales.id as sales_id, customers.customer_name, products.product_name, sales.product_price
            FROM sales
            INNER JOIN customers ON sales.customer_id = customers.id
            INNER JOIN products ON sales.product_id = products.id
            WHERE 1 = 1";

        $params = [];

        if (!empty($filterCustomer)) {
            $sql .= " AND customers.customer_name LIKE :filterCustomer";
            $params[':filterCustomer'] = $filterCustomer;
        }

        if (!empty($filterProduct)) {
            $sql .= " AND products.product_name LIKE :filterProduct";
            $params[':filterProduct'] = $filterProduct;
        }

        if (!empty($filterPrice)) {
            $sql .= " AND product.product_price <= :filterPrice";
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

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
            $params[':filterCustomer'] = $filterCustomer;
        }

        if (!empty($filterProduct)) {
            $sql .= " AND products.product_name LIKE :filterProduct";
            $params[':filterProduct'] = $filterProduct;
        }

        if (!empty($filterPrice)) {
            $sql .= " AND product.product_price <= :filterPrice";
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
}