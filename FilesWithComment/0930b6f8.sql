-- ============================================================
-- setup.sql
-- Purpose: Creates the entire database schema for the
--          Enterprise Manager application.
-- Run this file once to initialise the database before
--          launching the PHP application.
-- ============================================================

-- Create the database only if it does not already exist
CREATE DATABASE IF NOT EXISTS enterprise_manager;

-- Switch the active database to enterprise_manager
USE enterprise_manager;

-- ------------------------------------------------------------
-- Table: user
-- Stores login credentials for all application users.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user (
    UserID   INT AUTO_INCREMENT PRIMARY KEY, -- Auto-incrementing unique ID for each user
    Name     VARCHAR(100) NOT NULL,          -- Full display name of the user
    Username VARCHAR(50)  NOT NULL UNIQUE,   -- Login username; must be unique across all users
    Password VARCHAR(255) NOT NULL           -- Bcrypt-hashed password string (never plain-text)
);

-- ------------------------------------------------------------
-- Table: customer
-- Stores details of clients who commission projects.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS customer (
    CustomerID    INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each customer
    CustomerName  VARCHAR(100) NOT NULL,          -- Full name or company name of the customer
    Address       VARCHAR(255),                   -- Physical or postal address (optional)
    ContactNumber VARCHAR(20)                     -- Phone or mobile number (optional)
);

-- ------------------------------------------------------------
-- Table: supplier
-- Stores vendors that supply products and/or materials.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS supplier (
    SupplierID   INT AUTO_INCREMENT PRIMARY KEY,       -- Unique identifier for each supplier
    SupplierName VARCHAR(100) NOT NULL,                 -- Name of the supplier company or individual
    Address      VARCHAR(255),                          -- Physical address of the supplier (optional)
    ContactNo    VARCHAR(20),                           -- Contact phone number (optional)
    Status       ENUM('Active','Inactive') DEFAULT 'Active' -- Whether the supplier is currently active
);

-- ------------------------------------------------------------
-- Table: product
-- Stores finished goods or services that can be sold/assigned
-- to projects.  Each product is optionally linked to a supplier.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product (
    ProductID    INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each product
    ProductName  VARCHAR(100) NOT NULL,          -- Descriptive name of the product
    SellingPrice DECIMAL(10,2) NOT NULL,         -- Price at which the product is sold (2 decimal places)
    SupplierID   INT,                            -- Foreign key linking to the supplier of this product
    FOREIGN KEY (SupplierID) REFERENCES supplier(SupplierID) -- Enforces referential integrity with supplier table
);

-- ------------------------------------------------------------
-- Table: material
-- Stores raw materials used in manufacturing products.
-- Each material is optionally sourced from a supplier.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS material (
    MaterialID   INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each material
    MaterialName VARCHAR(100) NOT NULL,          -- Descriptive name of the material
    PricePerUnit DECIMAL(10,2) NOT NULL,         -- Cost of one unit of this material
    SupplierID   INT,                            -- Foreign key linking to the supplying vendor
    FOREIGN KEY (SupplierID) REFERENCES supplier(SupplierID) -- Enforces referential integrity with supplier table
);

-- ------------------------------------------------------------
-- Table: jobrole
-- Stores types of labour roles (e.g. Electrician, Plumber)
-- along with their daily wage rate.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS jobrole (
    JobRoleID  INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each job role
    JobType    VARCHAR(100) NOT NULL,          -- Name / title of the job role
    WagePerDay DECIMAL(10,2) NOT NULL          -- Daily wage paid to workers in this role
);

-- ------------------------------------------------------------
-- Table: project
-- Stores individual construction / service projects.
-- Each project is linked to one customer.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS project (
    ProjectID     INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each project
    ProjectName   VARCHAR(100) NOT NULL,          -- Descriptive name of the project
    ProjectDate   DATE NOT NULL,                  -- Date the project is scheduled or was completed
    TransportCost DECIMAL(10,2) DEFAULT 0,        -- Transport/logistics cost for the project (defaults to 0)
    ProjectValue  DECIMAL(10,2) NOT NULL,         -- Total revenue value of the project
    CustomerID    INT,                            -- Foreign key linking to the customer who owns this project
    FOREIGN KEY (CustomerID) REFERENCES customer(CustomerID) -- Enforces referential integrity with customer table
);

-- ------------------------------------------------------------
-- Table: productmaterial  (junction / bridge table)
-- Links products to the raw materials they require.
-- A product can need many materials; a material can be used
-- in many products  (many-to-many relationship).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS productmaterial (
    ProductID      INT,                -- Foreign key: which product this row belongs to
    MaterialID     INT,                -- Foreign key: which material is required
    QuantityNeeded DECIMAL(10,2) NOT NULL, -- How many units of this material the product needs
    PRIMARY KEY (ProductID, MaterialID),   -- Composite primary key prevents duplicate product-material pairs
    FOREIGN KEY (ProductID)  REFERENCES product(ProductID),  -- Links back to the product table
    FOREIGN KEY (MaterialID) REFERENCES material(MaterialID) -- Links back to the material table
);

-- ------------------------------------------------------------
-- Table: projectproduct  (junction / bridge table)
-- Links projects to the products assigned to them.
-- A project can include many products; a product can appear
-- in many projects  (many-to-many relationship).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projectproduct (
    ProjectID INT,         -- Foreign key: which project this row belongs to
    ProductID INT,         -- Foreign key: which product is included
    Quantity  INT NOT NULL, -- Number of units of this product used in the project
    PRIMARY KEY (ProjectID, ProductID),     -- Composite PK prevents duplicate project-product pairs
    FOREIGN KEY (ProjectID) REFERENCES project(ProjectID), -- Links back to the project table
    FOREIGN KEY (ProductID) REFERENCES product(ProductID)  -- Links back to the product table
);

-- ------------------------------------------------------------
-- Table: projectlabour  (junction / bridge table)
-- Links projects to the labour roles assigned to them.
-- Tracks how many workers of each type and for how many days.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projectlabour (
    ProjectID  INT,         -- Foreign key: which project this row belongs to
    JobRoleID  INT,         -- Foreign key: which job role is assigned
    NumWorkers INT NOT NULL, -- Number of workers of this role on the project
    NumDays    INT NOT NULL, -- Number of days those workers are employed on the project
    PRIMARY KEY (ProjectID, JobRoleID),       -- Composite PK prevents duplicate project-role pairs
    FOREIGN KEY (ProjectID) REFERENCES project(ProjectID),  -- Links back to the project table
    FOREIGN KEY (JobRoleID) REFERENCES jobrole(JobRoleID)   -- Links back to the jobrole table
);
