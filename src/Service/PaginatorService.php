<?php

namespace SalesDashboard\Service;

class PaginatorService
{
    private $currentPage;
    private $itemsPerPage;

    public function __construct($itemsPerPage = 10)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $this->getCurrentPage();
    }

    public function getCurrentPage()
    {
        return isset($_GET['page']) ? (int)$_GET['page'] : 1;
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    public function calculateOffset()
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function generatePaginationLinks($totalPages)
    {
        $links = [];

        for ($i = 1; $i <= $totalPages; $i++) {
            $links[] = [
                'page' => $i,
                'url' => $this->generatePageUrl($i),
                'isCurrent' => $i == $this->currentPage,
            ];
        }

        return $links;
    }

    private function generatePageUrl($page)
    {
        $queryString = $_SERVER['QUERY_STRING'];
        parse_str($queryString, $queryParams);

        $queryParams['page'] = $page;

        $url = strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($queryParams);

        return $url;
    }

}