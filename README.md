Problem Statement:
=
Imagine your friend - the owner of a small bookshop - asks you for a simple representation of his latest sales.
He provides you with a simple plain JSON export file.

What do you need to do?:
- Design a database scheme for optimized storage
- Read the JSON data and save it to the database using PHP
- Create a simple page with filters for customer, product and price
- Output the filtered results in a table below the filters
- Add a last row for the total price of all filtered entries

Additional information:
The shop system changed the timezone of the date of the sale.
Prior to version 1.0.17+60 it was Europe/Berlin.
Afterwards it is UTC. Please provide a tested class for the version comparison.

Environment:
PHP 7, MySQL / MariaDB


Coding Summary & Solution:
=
- The environment is PHP 7 and My-SQL
- In this project, I adopted the PSR-4 autoloading standard, ensuring efficient class loading through Composer.
- Following the MVC architectural pattern, organized the code into models, views, and controllers, enhancing maintainability.
- The version comparison functionality accommodated changes in timezone formats, addressing versions like "1.0.17+60" and "1.1.3" seamlessly. (File location bookshop/src/Service/VersionHandlerService..php
- Utilizing PDO for secure database interactions, a Database class facilitated connection handling.

Steps to run the Project:
- Extract the attached zip in your server directory
- Go to bookshop/src/Service/DbService.php and add DB credentials
- If you are using localhost go to the browser and run http://localhost/bookshop
- If you are using a virtual host or subdomain go to the browser and run {example.xzy}. The domain will be according to your created one.
