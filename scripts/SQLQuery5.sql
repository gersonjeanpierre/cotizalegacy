CREATE DATABASE Cotizacion;
USE Cotizacion;

CREATE TABLE Producto (
  id_producto INT IDENTITY(1,1) PRIMARY KEY,
  nombre VARCHAR(100)
);
INSERT INTO Producto VALUES ('Gigantografia');
INSERT INTO Producto VALUES ('Tomatodo');
INSERT INTO Producto VALUES ('Lapicero');


USE Cotizacion;
CREATE TABLE OpcionExtra (
	id_opcion INT IDENTITY(1,1) PRIMARY KEY,
	descripcion VARCHAR(100),
	id_producto INT,
	FOREIGN KEY (id_producto) REFERENCES Producto(id_producto)
);

INSERT INTO OpcionExtra VALUES('serigrafeado',2);
INSERT INTO OpcionExtra VALUES('serigrafeado',3);
INSERT INTO OpcionExtra VALUES('UV Full Color',2);
INSERT INTO OpcionExtra VALUES('UV Full Color',3);
INSERT INTO OpcionExtra VALUES('UV DTF',2);

CREATE TABLE Precios (
 id_precio INT IDENTITY (1,1) PRIMARY KEY,
 id_producto INT,
 id_opcion INT,
 cantidad_min INT,
 cantidad_max INT,
 precio_unitario FLOAT,
 FOREIGN KEY (id_producto) REFERENCES Producto(id_producto),
 FOREIGN KEY (id_opcion) REFERENCES OpcionExtra(id_opcion)
);
INSERT INTO OpcionExtra VALUES ('Termosellado',1),('Pita y Tubo',1),('Ojales',1),('Marco',1);

INSERT INTO Precios (id_producto, id_opcion, cantidad_min, cantidad_max, precio_unitario) VALUES 
(1, 7, 1,1,8.00);
( 2, 2, 1, 50, 12.00),   -- Precio de Tomatodo UV Full Color hasta 50 unidades
( 2, 2, 51, 100, 10.00); -- Precio de Tomatodo UV Full Color de 51 a 100 unidades
(4, 2, 3, 1, 100, 10.00),  -- Precio de Tomatodo UV DTF
(5, 3, 1, 1, 100, 1.50),   -- Precio de Lapicero serigrafeado
(6, 3, 4, 1, 100, 2.00);   -- Precio de Lapicero UV Full Color

INSERT INTO OpcionExtra VALUES ('termosellado',2);

CREATE TABLE Cliente (
 id_cliente INT IDENTITY(1,1) PRIMARY KEY,
 nombre VARCHAR(60),
 razon_social VARCHAR(60),
 ruc VARCHAR(30),
 celular INT,
 correo varchar (50),
 fecha_registro DATETIME DEFAULT GETDATE()
);
 
CREATE TABLE RegistroCotizacion (
id_registro INT IDENTITY(1,1) PRIMARY KEY,
id_cliente INT,
fecha DATETIME DEFAULT GETDATE(),
FOREIGN KEY (id_cliente) REFERENCES Cliente(id_cliente)
);

CREATE TABLE DetalleCotizacion (
id_detalle INT IDENTITY(1,1) PRIMARY KEY,
id_registro INT,
id_producto INT,
cantidad INT,
precio_total DECIMAL(10,2),
FOREIGN KEY (id_producto) REFERENCES Producto(id_producto),
FOREIGN KEY (id_registro) REFERENCES RegistroCotizacion(id_registro)
);

ALTER TABLE OpcionExtra ADD precio_opcion DECIMAL(10,2);

UPDATE OpcionExtra SET precio_opcion=2.5 WHERE id_opcion=5;
UPDATE OpcionExtra SET precio_opcion=5.0 WHERE id_opcion=6;

CREATE TABLE OpcionDetalle (
    id_opcion_detalle INT IDENTITY(1,1) PRIMARY KEY,
    id_detalle INT NOT NULL,
    id_opcion INT NOT NULL,
    FOREIGN KEY (id_detalle) REFERENCES DetalleCotizacion(id_detalle),
    FOREIGN KEY (id_opcion) REFERENCES OpcionExtra(id_opcion)
);


UPDATE Precios SET precio_unitario=17 WHERE id_precio=3;
UPDATE Precios SET precio_unitario=15 WHERE id_precio=4;
UPDATE Precios SET cantidad_min=7, cantidad_max=18 WHERE id_precio=4;
UPDATE Precios SET cantidad_min=19, cantidad_max=100, precio_unitario=13 WHERE id_precio=5;
UPDATE Precios SET cantidad_min=7,cantidad_max=18, precio_unitario=15 WHERE id_precio=7;



UPDATE Precios SET cantidad_max=6 WHERE id_precio=3;
UPDATE Precios SET cantidad_max=6 WHERE id_precio=3;


UPDATE Precios SET precio_unitario=15 WHERE id_precio=3;
UPDATE OpcionExtra SET descripcion='Termosellado' WHERE id_opcion=9;

	
		SELECT oe.id_opcion,p.nombre, oe.descripcion 
		FROM Producto p
		JOIN OpcionExtra oe ON oe.id_producto=p.id_producto
		ORDER BY oe.id_opcion;

		UPDATE oe
		SET oe.descripcion='Serigrafiado'
		FROM OpcionExtra oe
		JOIN Producto p ON oe.id_producto=p.id_producto
		WHERE oe.id_opcion=1;

		SELECT * FROM OpcionExtra
		ORDER BY id_producto;

		UPDATE OpcionExtra SET precio_opcion=1.00 WHERE id_opcion=1;

		INSERT INTO Precios VALUES (3,1,1,20,2);
		INSERT INTO Precios VALUES (3,1,21,50,1.7);


-- todas mis tablas actuales 
SELECT * fROM OpcionDetalle;
SELECT * FROM Producto;
SELECT * FROM Precios;
SELECT * FROM OpcionExtra;
SELECT * FROM Cliente;
SELECT * FROM RegistroCotizacion;
SELECT * FROM DetalleCotizacion;

UPDATE OpcionExtra SET precio_opcion=1.00 WHERE id_opcion=12;
--CREAR EL FILTRO PARA TIPO DE CLIENTE

ALTER TABLE Precios ADD Tipo_cliente VARCHAR(20);
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=3; 
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=4; 
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=5; 
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=6; 
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=7;
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=13;
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=14;
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=15;
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=16;
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=17;
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=18;
UPDATE Precios SET Tipo_cliente='final' WHERE id_precio=19;

ALTER TABLE OpcionExtra ADD Tipo_cliente VARCHAR(20);

UPDATE OpcionExtra SET Tipo_cliente='final' WHERE id_opcion=5; 
UPDATE OpcionExtra SET Tipo_cliente='final' WHERE id_opcion=6; 
UPDATE OpcionExtra SET Tipo_cliente='final' WHERE id_opcion=7; 
UPDATE OpcionExtra SET Tipo_cliente='final' WHERE id_opcion=8; 


ALTER TABLE Precios ALTER COLUMN precio_unitario DECIMAL(6,2);

SELECT * FROM Precios ORDER BY id_producto;
SELECT * FROM OpcionExtra;

INSERT INTO OpcionExtra VALUES ('Termosellado',1,1.00,'imprentero');
INSERT INTO OpcionExtra VALUES ('Pita y Tubo',1,2.50,'imprentero');
INSERT INTO OpcionExtra VALUES ('Ojales',1,0.50,'imprentero');
INSERT INTO OpcionExtra VALUES ('Marco',1,2.50,'imprentero');

