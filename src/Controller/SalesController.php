<?php

namespace SalesDashboard\Controller;
use SalesDashboard\Service\DbService;
use SalesDashboard\Service\PaginatorService;
use SalesDashboard\Model\SalesModel;
use SalesDashboard\View\SalesView;

class SalesController
{
    private $model;
    private $view;

    public function __construct()
    {
        // Create instances of Database, SalesModel, and SalesView
        $database = new DbService();
        $this->model = new SalesModel($database);
        $this->view = new SalesView();
    }

    public function index()
    {
        $filterCustomer = $_GET['customer'] ?? '';
        $filterProduct = $_GET['product'] ?? '';
        $filterPrice = $_GET['price'] ?? '';

        // Pagination parameters
        $itemsPerPage = 5;
        $currentPage = $_GET['page'] ?? 1;

        // Fetch sales data and total pages from the model
        $salesData = $this->model->getSalesData($itemsPerPage, ($currentPage - 1) * $itemsPerPage, $filterCustomer, $filterProduct, $filterPrice);
        $totalPages = $this->model->getTotalPages($itemsPerPage, $filterCustomer, $filterProduct, $filterPrice);

        // paginator class to generate pagination links
        $paginator = new PaginatorService($itemsPerPage);
        $paginationLinks = $paginator->generatePaginationLinks($totalPages);

        // Render the view with the fetched data and pagination links
        $this->view->render($salesData, $paginationLinks, $this->calculateTotalPrice($salesData), $filterCustomer, $filterProduct, $filterPrice);
    }

    private function calculateTotalPrice($salesData)
    {
        // Calculate the total price based on the sales data
        $totalPrice = 0;
        foreach ($salesData as $sale) {
            $totalPrice += $sale['product_price'];
        }

        return $totalPrice;
    }
}