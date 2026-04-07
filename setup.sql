CREATE DATABASE IF NOT EXISTS enterprise_manager;
USE enterprise_manager;

CREATE TABLE IF NOT EXISTS user (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS customer (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerName VARCHAR(100) NOT NULL,
    Address VARCHAR(255),
    ContactNumber VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS supplier (
    SupplierID INT AUTO_INCREMENT PRIMARY KEY,
    SupplierName VARCHAR(100) NOT NULL,
    Address VARCHAR(255),
    ContactNo VARCHAR(20),
    Status ENUM('Active','Inactive') DEFAULT 'Active'
);

CREATE TABLE IF NOT EXISTS product (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    ProductName VARCHAR(100) NOT NULL,
    SellingPrice DECIMAL(10,2) NOT NULL,
    SupplierID INT,
    FOREIGN KEY (SupplierID) REFERENCES supplier(SupplierID)
);

CREATE TABLE IF NOT EXISTS material (
    MaterialID INT AUTO_INCREMENT PRIMARY KEY,
    MaterialName VARCHAR(100) NOT NULL,
    PricePerUnit DECIMAL(10,2) NOT NULL,
    SupplierID INT,
    FOREIGN KEY (SupplierID) REFERENCES supplier(SupplierID)
);

CREATE TABLE IF NOT EXISTS jobrole (
    JobRoleID INT AUTO_INCREMENT PRIMARY KEY,
    JobType VARCHAR(100) NOT NULL,
    WagePerDay DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS project (
    ProjectID INT AUTO_INCREMENT PRIMARY KEY,
    ProjectName VARCHAR(100) NOT NULL,
    ProjectDate DATE NOT NULL,
    TransportCost DECIMAL(10,2) DEFAULT 0,
    ProjectValue DECIMAL(10,2) NOT NULL,
    CustomerID INT,
    FOREIGN KEY (CustomerID) REFERENCES customer(CustomerID)
);

CREATE TABLE IF NOT EXISTS productmaterial (
    ProductID INT,
    MaterialID INT,
    QuantityNeeded DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (ProductID, MaterialID),
    FOREIGN KEY (ProductID) REFERENCES product(ProductID),
    FOREIGN KEY (MaterialID) REFERENCES material(MaterialID)
);

CREATE TABLE IF NOT EXISTS projectproduct (
    ProjectID INT,
    ProductID INT,
    Quantity INT NOT NULL,
    PRIMARY KEY (ProjectID, ProductID),
    FOREIGN KEY (ProjectID) REFERENCES project(ProjectID),
    FOREIGN KEY (ProductID) REFERENCES product(ProductID)
);

CREATE TABLE IF NOT EXISTS projectlabour (
    ProjectID INT,
    JobRoleID INT,
    NumWorkers INT NOT NULL,
    NumDays INT NOT NULL,
    PRIMARY KEY (ProjectID, JobRoleID),
    FOREIGN KEY (ProjectID) REFERENCES project(ProjectID),
    FOREIGN KEY (JobRoleID) REFERENCES jobrole(JobRoleID)
);
