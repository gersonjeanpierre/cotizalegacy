

--- Actualizar el metro lineal a 40 de vinilo impresion
UPDATE Precios SET precio_unitario=40 WHERE id_producto=2


-- Actualizar el metro lineal a 5 de laminado para vinilo
SELECT * FROM OpcionExtra WHERE descripcion = 'Laminado'

UPDATE OpcionExtra SET precio_opcion = 5 WHERE descripcion = 'Laminado'