DROP TABLE t_old;
DELETE FROM t1;
DELETE FROM t1 WHERE t1.id IS NULL;
DELETE FROM t1 WHERE t1.id IS NOT NULL;
DELETE FROM t1 WHERE isnull(t1.id);
DELETE FROM tblRecipe WHERE categoryID in ( SELECT categoryID FROM tblSubCategories INNER JOIN tblRecipe ON (tblSubCategories.categoryID = tblRecipe.categoryID) WHERE tblRecipe.categoryID = 9); 
DELETE FROM tblRecipe WHERE categoryID in ( SELECT categoryID FROM tblSubCategories INNER JOIN tblRecipe ON (tblSubCategories.categoryID = tblRecipe.categoryID)) AND tblRecipe.categoryID = 9; 
