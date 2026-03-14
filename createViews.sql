CREATE OR REPLACE VIEW landing_page AS
SELECT PRODUCT.P_Name, PRODUCT.P_Description, PRODUCT.P_Cost, 
  PRODUCT.P_SaleCost, PRODUCT.QuantityInStock, 
  LOCATION.StoreAisle
FROM PRODUCT
JOIN LOCATION ON LOCATION.L_ID = PRODUCT.L_ID
WHERE PRODUCT.P_SaleCost IS NOT NULL;
  
CREATE OR REPLACE VIEW indiv_product_page AS 
SELECT PRODUCT.P_ID, PRODUCT.P_Name, PRODUCT.P_Description, PRODUCT.P_Cost, 
  PRODUCT.P_SaleCost, PRODUCT.P_Weight, PRODUCT.QuantityInStock, 
  LOCATION.StoreAisle, LOCATION.P_Type AS ProductCategory
FROM PRODUCT
JOIN LOCATION ON LOCATION.L_ID = PRODUCT.L_ID;

CREATE OR REPLACE VIEW stocking_management_page AS
SELECT 
  PRODUCT.P_ID, 
  PRODUCT.P_Name, 
  PRODUCT.QuantityInStock, 
  PRODUCT.P_Cost, 
  PRODUCT.P_SaleCost, 
  PRODUCT.P_CostPerOunce,
  PRODUCT.P_SaleCostPerOunce,
  LOCATION.StoreAisle, 
  LOCATION.P_Type AS ProductCategory
FROM PRODUCT
JOIN LOCATION ON PRODUCT.L_ID = LOCATION.L_ID;

CREATE OR REPLACE VIEW manager_management_page AS
SELECT STOCKER.S_FirstName, STOCKER.S_LastName, STOCKER.S_BirthDate, STOCKER.M_ID,
  MANAGER.M_FirstName, MANAGER.M_LastName
FROM STOCKER
JOIN MANAGER ON STOCKER.M_ID = MANAGER.M_ID;

CREATE OR REPLACE VIEW it_view AS
SELECT 
  IT_ID AS EmployeeID,
  IT_FirstName AS FirstName,
  IT_LastName AS LastName,
  IT_BirthDate AS BirthDate,
  'IT' AS Role,
  NULL AS ManagerID
FROM IT
UNION ALL
SELECT 
  M_ID AS EmployeeID,
  M_FirstName AS FirstName,
  M_LastName AS LastName,
  M_BirthDate AS BirthDate,
  'Manager' AS Role,
  NULL AS ManagerID
FROM MANAGER
UNION ALL
SELECT 
  S_ID AS EmployeeID,
  S_FirstName AS FirstName,
  S_LastName AS LastName,
  S_BirthDate AS BirthDate,
  'Stocker' AS Role,
  M_ID AS ManagerID
FROM STOCKER;
