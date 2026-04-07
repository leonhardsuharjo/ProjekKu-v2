Main planning sheet for ProjekKu V2 

General picture of project = 
    a php local app that acts as project manager where its users can enter 

Things to be noted here = 
    - Main Pages 
    - Main Database tables
    - App workflow (what the app will look like)

Home page = index.php 

Main challenges = 
    - phpmyadmin supports their own specified folder location where the operation is conducted (therefore this git folder acts as duplicate of the OG files used in ops)
      - location = C:/xampp/htdocs/[enterprise_managerNAME]/ 

File Mapping (list of files) = 
    - setup.sql #temp file to store sql code -- used to form the database 
    - db.php #mysqli connection,, included by every page
        DONE
    - nav.php #for html only nav BAR, included inside each <body> of every page 
        DONE
    - style.css #Minimal CSS — tables, forms, nav, buttons, insight colors
    - login.php #to start Session
        DONE
    - register.php # register form and save password through HASH
        DONE
    - logout.php # destroy session and redirect to login 
        DONE
    - index.php # starting point -- HOME
        DONE
    - customers.php
        DONE 
    - suppliers.php
        DONE
    - products.php
        DONE
    - materials.php
    - jobroles.php
    - projects.php
    - productmaterials.php # N:N page to link materials to a product
    - project_products.php # N to N page to assign products to project 
    - project_labour.php # Report — calculates MaterialCost + LabourCost via SQL subqueries, computes GrossProfit live, NO STORED derived values -- no specific database schema for this page 
