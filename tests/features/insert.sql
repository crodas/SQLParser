INSERT INTO Table_1 (column_3, column_1) VALUES ('1996-12-31', 20);
INSERT INTO Table_1 VALUES (20, 'GOODBYE', '1996-12-31');
INSERT INTO Sailboat(ID,Manufacture, Model, Length, Beam, Price) VALUES(2,'Pacific Seacraft', 'Dana 24', '24 feet 0 inches','8 feet 2 inches', 50000.00);
INSERT INTO Sailboat(Manufacture, Model, Length, Beam, Price) VALUES('Cal Jenson', 'Cal 40', '40 feet 0 inches','10 feet 2 inches', 39500.00);
INSERT INTO Sailboat VALUES('Catalina', 'Catalina 27', '26 feet 11 inches','9 feet 2 inches', 6499.00);
INSERT INTO Sailboat VALUES(2,'Mumm', 'Mumm 30', '30 feet 0 inches','8 feet 2 inches', 37000.00);
INSERT INTO Sailboat (Manufacture, Model, Length, Beam, Price) VALUES ('Catalina', 'Catalina 26', '35 feet 11 inches','10 feet 2 inches', 42499.00), ('Santana', 'Santana 30', '30 feet 0 inches', '9 feet 9 inches',17000.00), ('Cal Marine', 'Cal 25 Mark I', '25 feet o inches', '8 feet 0 inches', 4585.00);
INSERT INTO SailboatDataToImport VALUES('Pearson','27','27 feet 2 inches','8 feet',9000.00),('Aquarius','23','23 feet','7 feet',3000.00),('Vanguard','17','17 feet 3 inches','6 feet 4 inches',5000.00);
INSERT INTO Sailboat SELECT Manufacture, Model, Length, Beam, Price FROM SailboatDataToImport;
INSERT INTO employee (emp_no, fname, lname, officeno)
   VALUES (3022, "John", "Smith", 2101);
INSERT INTO OrdersArchive (order_id, order_date, ship_name) 
   SELECT order_id, order_date, ship_name FROM Orders 
         WHERE order_date >= (DATE()-30);
INSERT INTO weather VALUES ('San Francisco', 46, 50, 0.25, '1994-11-27');
INSERT INTO cities VALUES ('San Francisco', '(-194.0, 53.0)');
INSERT INTO weather (city, temp_lo, temp_hi, prcp, date)
        VALUES ('San Francisco', 43, 57, 0.0, '1994-11-29');
INSERT INTO weather (date, city, temp_hi, temp_lo)
        VALUES ('1994-11-29', 'Hayward', 54, 37);

