<?php

namespace SalesDashboard\Controller;
use SalesDashboard\Service\DbService;
use SalesDashboard\Service\JsonImporterService;
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
        $itemsPerPage = 25;
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

    public function importJson(){

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["import_json"])) {
            if (isset($_FILES["jsonFile"])) {
                $jsonFile = $_FILES["jsonFile"];
                $jsonImporter = new JsonImporterService(__DIR__ . '/uploads');
                // Import JSON data
                $resultMessage = $jsonImporter->importJson($jsonFile);
                if ($resultMessage["success"]) {
                    // Use the SalesModel function to save the sales data

                    $this->model->saveSalesData(json_decode($resultMessage['data'],true));
                    echo "<div class='text-center mt-5'>JSON Data successfully imported. Please <a href='index.php'> click here </a> to view report.</div>";
                } else {
                    echo "<div class='text-center mt-5'>Import failed. " . $resultMessage["message"].'</div>';
                }

            } else {
                echo "Invalid file upload.";
            }
        }
        $this->view->renderImportJson();
    }
}