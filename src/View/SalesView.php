<?php

namespace SalesDashboard\View;

class SalesView
{
    public function render($salesData, $paginationLinks, $totalPrice, $filterCustomer, $filterProduct, $filterPrice)
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sales Dashboard</title>
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    font-family: Arial, sans-serif;
                }

                h1 {
                    color: #333;
                }

                form {
                    margin-bottom: 20px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }

                table, th, td {
                    border: 1px solid #ddd;
                }

                th, td {
                    padding: 12px;
                    text-align: left;
                }

                th {
                    background-color: #f2f2f2;
                }

                tr:hover {
                    background-color: #f5f5f5;
                }

                div.total {
                    margin-top: 10px;
                    font-weight: bold;
                }

                div.pagination {
                    margin-top: 10px;
                }
            </style>
        </head>

        <body>

        <div class="container">

            <h1 class="mt-4">Sales Dashboard</h1>

            <!-- Filter Form -->
            <form id="filterForm" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label for="customer" class="form-label">Customer:</label>
                        <input type="text" name="customer" class="form-control" value="<?= $filterCustomer ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="product" class="form-label">Product:</label>
                        <input type="text" name="product" class="form-control" value="<?= $filterProduct ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="text" name="price" class="form-control" value="<?= $filterPrice ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-secondary reset_btn">Reset Filters</button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <a href="import_json.php" class="btn btn-success">Import JSON</a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Display Sales Data Table -->
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Customer Name</th>
                    <th>Sale Date</th>
                    <th>Product Name</th>
                    <th>Product Price</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($salesData as $row) : ?>
                    <tr>
                        <td><?= $row['sales_id'] ?></td>
                        <td><?= $row['customer_name'] ?></td>
                        <td><?= $row['ordered_at'] ?></td>
                        <td><?= $row['product_name'] ?></td>
                        <td><?= $row['product_price'] ?></td>
                    </tr>
                <?php endforeach; ?>
                    <tr>
                        <td colspan="4" style="text-align: right"><b>Total:</b></td>
                        <td><b><?= $totalPrice ?></b></td>
                    </tr>
                </tbody>
            </table>


            <!-- Display Pagination Links -->
            <nav aria-label="...">
                <ul class="pagination">
                    <?php foreach ($paginationLinks as $link) : ?>
                        <?php if ($link['isCurrent']) : ?>
                            <li class="page-item">
                                <a class="page-link active" href="#"><?= $link['page'] ?></a>
                            </li>
                        <?php else : ?>
                            <li class="page-item">
                                <a href="<?= $link['url'] ?>" class="page-link"><?= $link['page'] ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </ul>
            </nav>
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script>
            $(document).ready(function (){
                $(".reset_btn").click(function (){
                    $("#filterForm .form-control").each(function (){
                        $(this).val("");
                    })
                    document.getElementById('filterForm').submit();
                })
            });
        </script>

        </body>

        </html>
        <?php
    }

    public function renderImportJson(){?>

        <!-- import_json_view.php -->
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Import JSON Data</title>
            <!-- Bootstrap CSS link -->
            <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
        <div class="container mt-5">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">Import JSON Data</h1>
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <input type="hidden" name="import_json" value="1">
                            <label for="jsonFile">Choose a JSON file:</label>
                            <input type="file" class="form-control-file" name="jsonFile" id="jsonFile" accept=".json" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Import</button>
                        <br>
                        <br>
                        <a href="index.php"> <- Back to Home</a>
                    </form>
                </div>
            </div>
        </div>
      </body>
        </html>
    <?php }
}
