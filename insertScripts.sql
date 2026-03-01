INSERT INTO IT (IT_FirstName, IT_LastName, IT_BirthDate, IT_Password)
VALUES ('Severus', 'Snape', '1960-01-09', 'aft3rA11Th1$tim3?');

SET @it_id = LAST_INSERT_ID();

INSERT INTO MANAGER (M_FirstName, M_LastName, M_BirthDate, M_Password, IT_ID)
VALUES ('Minerva', 'McGonagall', '1932-10-04', 'transfigurationRocks1$!', @it_id);

SET @manager_id = LAST_INSERT_ID();

INSERT IGNORE INTO LOCATION (StoreAisle, P_Type)
VALUES (2, 'Juice');
SET @location_id = (SELECT L_ID FROM LOCATION WHERE P_Type = 'Juice');


INSERT INTO STOCKER (S_FirstName, S_LastName, S_BirthDate, S_Password, M_ID, IT_ID)
VALUES ('Argus', 'Filch', '1944-03-16', 'mrs-Norris7#4', @manager_id, @it_id);

SET @stocker_id = LAST_INSERT_ID();

INSERT INTO PRODUCT (P_Cost, P_Weight, P_SaleCost, P_Name, P_Description, QuantityInStock, M_ID, L_ID)
VALUES (1.49, 20, 2.79, 'Pumpkin Juice', 'Juice of the pumpkin variety', 43, @manager_id, @location_id);

SET @product_id = LAST_INSERT_ID();

INSERT INTO STOCKS (S_ID, P_ID)
VALUES (@stocker_id, @product_id);
