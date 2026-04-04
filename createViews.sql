CREATE OR REPLACE VIEW all_products AS
SELECT 
  PRODUCT.P_ID,
  PRODUCT.P_Name,
  PRODUCT.P_Description,
  PRODUCT.P_Cost, 
  PRODUCT.P_SaleCost,
  PRODUCT.P_Weight,
  PRODUCT.QuantityInStock, 
  LOCATION.StoreAisle,
  LOCATION.P_Type
FROM PRODUCT
JOIN LOCATION ON PRODUCT.L_ID = LOCATION.L_ID;

CREATE OR REPLACE VIEW products_on_sale AS
SELECT
  PRODUCT.P_ID,
  PRODUCT.P_Name,
  PRODUCT.P_Description,
  PRODUCT.P_Cost,
  PRODUCT.P_Weight,
  PRODUCT.P_SaleCost,
  PRODUCT.QuantityInStock, 
  LOCATION.StoreAisle,
  LOCATION.P_Type
FROM PRODUCT
JOIN LOCATION ON LOCATION.L_ID = PRODUCT.L_ID
WHERE PRODUCT.P_SaleCost IS NOT NULL;

CREATE OR REPLACE VIEW all_stockers AS
SELECT
  STOCKER.S_ID,
  STOCKER.S_FirstName,
  STOCKER.S_LastName,
  STOCKER.S_BirthDate,
  STOCKER.M_ID,
  MANAGER.M_FirstName,
  MANAGER.M_LastName,
  'Stocker' AS Role
FROM STOCKER
JOIN MANAGER ON STOCKER.M_ID = MANAGER.M_ID;

CREATE OR REPLACE VIEW all_employees AS
SELECT 
  IT_ID AS EmployeeID,
  IT_FirstName AS FirstName,
  IT_LastName AS LastName,
  IT_BirthDate AS BirthDate,
  IT_Password AS Password,
  'IT' AS Role,
  NULL AS ManagerID
FROM IT
UNION ALL
SELECT 
  M_ID AS EmployeeID,
  M_FirstName AS FirstName,
  M_LastName AS LastName,
  M_BirthDate AS BirthDate,
  M_Password AS Password,
  'Manager' AS Role,
  NULL AS ManagerID
FROM MANAGER
UNION ALL
SELECT 
  S_ID AS EmployeeID,
  S_FirstName AS FirstName,
  S_LastName AS LastName,
  S_BirthDate AS BirthDate,
  S_Password AS Password,
  'Stocker' AS Role,
  M_ID AS ManagerID
FROM STOCKER;
