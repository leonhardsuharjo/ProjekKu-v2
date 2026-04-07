Main planning sheet for ProjekKu V2 

General picture of project = 
    a php local app that acts as project manager where its users can enter 

Things to be noted here = 
    - Main Pages 
    - Main Database tables
    - App workflow (what the app will look like)

Main challenges = 
    - phpmyadmin supports a specified folder where the operation is conducted (therefore this git folder acts as duplicate of the OG files used in ops)
    - 

File Mapping (list of files) = 
    - setup.sql #temp file to store sql code -- used to form the database 
    - db.php
    - nav.php #for html only nav bar, included inside each <body> of every page 
    - style.css
    - login.php
    - register.php
    - logout.php
    - index.php
    - customers.php
    - suppliers.php
    - products.php
    - materials.php
    - jobroles.php
    - projects.php
    - productmaterials.php # N:N page to link materials to a product
    - project_products.php # N to N page to assign products to project 
    - project_labour.php # Report — calculates MaterialCost + LabourCost via SQL subqueries, computes GrossProfit live, NO STORED derived values -- no specific database schema for this page 
