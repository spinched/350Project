INSERT INTO "MANAGER" (M_FirstName, M_LastName, M_BirthDate, M_Password)
VALUES ('Minerva', 'McGonagall', '1932-10-04', 'transfigurationRocks1$!')

INSERT INTO "STOCKER" (S_FirstName, S_LastName, S_BirthDate, S_Password, M_ID, IT_ID)
VALUES ('Argus', 'Filch', '1944-03-16', 'mrs-Norris7#4')

INSERT INTO "IT" (IT_FirstName, IT_LastName, IT_BirthDate, IT_Password)
VALUES ('Severus', 'Snape', '1960-01-09', 'aft3rA11Th1$tim3?')

INSERT INTO "LOCATION" (StoreAisle, P_Type)
VALUES (2, 'Meat')

INSERT INTO "PRODUCT" (P_Cost, P_Weight, P_SaleCost, P_Name, P_Description, QuantityInStock)
VALUES (1.49, 20, 2.79, 'Pumpkin Juice', 'Juice of the pumpkin variety', 43)
