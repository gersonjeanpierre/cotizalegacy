--- Actualizar el metro lineal a 40 de vinilo impresion
UPDATE Precios SET precio_unitario=40 WHERE id_precio = 37
UPDATE Precios SET cantidad_min=1 WHERE id_precio = 37
UPDATE Precios SET precio_unitario=35 WHERE id_precio = 38
UPDATE Precios SET cantidad_min=19 WHERE id_precio = 38

-- Actualizar el metro lineal a 5 de laminado para vinilo
SELECT * FROM OpcionExtra WHERE descripcion = 'Laminado'

UPDATE OpcionExtra SET precio_opcion = 5 WHERE descripcion = 'Laminado'

-- FINAL  VINIL CHINO
UPDATE OpcionExtra SET precio_opcion=0 WHERE id_opcion = 16
UPDATE OpcionExtra SET precio_opcion=0 WHERE id_opcion = 17
UPDATE OpcionExtra SET precio_opcion=0 WHERE id_opcion = 24
UPDATE OpcionExtra SET precio_opcion=0 WHERE id_opcion = 25
-- FINAL  VINIL ARCLAD
UPDATE OpcionExtra SET precio_opcion=10 WHERE id_opcion = 22
UPDATE OpcionExtra SET precio_opcion=10 WHERE id_opcion = 23
UPDATE OpcionExtra SET precio_opcion=10 WHERE id_opcion = 26
UPDATE OpcionExtra SET precio_opcion=10 WHERE id_opcion = 27

-- IMPRENTERO CHINO
UPDATE OpcionExtra SET precio_opcion=0 WHERE id_opcion = 32
UPDATE OpcionExtra SET precio_opcion=0 WHERE id_opcion = 33
UPDATE OpcionExtra SET precio_opcion=0 WHERE id_opcion = 36
UPDATE OpcionExtra SET precio_opcion=0 WHERE id_opcion = 37
-- IMPRENTERO ARCLAD
UPDATE OpcionExtra SET precio_opcion=5 WHERE id_opcion = 34
UPDATE OpcionExtra SET precio_opcion=5 WHERE id_opcion = 35
UPDATE OpcionExtra SET precio_opcion=5 WHERE id_opcion = 38
UPDATE OpcionExtra SET precio_opcion=5 WHERE id_opcion = 39

-- AGREGAR LAMINADOS MATES BRILLANTES CHINO ARCLAD
INSERT INTO OpcionExtra VALUES ('Laminado Mate Chino',2,5,'final');
INSERT INTO OpcionExtra VALUES ('Laminado Brillo Chino',2,5,'final');
INSERT INTO OpcionExtra VALUES ('Laminado Mate Chino',2,5,'imprentero');
INSERT INTO OpcionExtra VALUES ('Laminado Brillo Chino',2,5,'imprentero');
INSERT INTO OpcionExtra VALUES ('Laminado Mate Arclad',2,10,'final');
INSERT INTO OpcionExtra VALUES ('Laminado Brillo Arclad',2,10,'final');
INSERT INTO OpcionExtra VALUES ('Laminado Mate Arclad',2,10,'imprentero');
INSERT INTO OpcionExtra VALUES ('Laminado Brillo Arclad',2,10,'imprentero');

--AGREGAR CELTEX
INSERT INTO OpcionExtra VALUES ('Celtex 3mm',2,37,'final');
INSERT INTO OpcionExtra VALUES ('Celtex 5mm',2,50,'final');
INSERT INTO OpcionExtra VALUES ('Celtex 3mm',2,37,'imprentero');
INSERT INTO OpcionExtra VALUES ('Celtex 5mm',2,50,'imprentero');
INSERT INTO OpcionExtra VALUES ('Foam 3mm',2,34,'final');
INSERT INTO OpcionExtra VALUES ('Foam 5mm',2,49,'final');
INSERT INTO OpcionExtra VALUES ('Foam 3mm',2,34,'imprentero');
INSERT INTO OpcionExtra VALUES ('Foam 5mm',2,49,'imprentero');














