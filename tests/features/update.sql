UPDATE T SET C1 = 1 WHERE C2 = "a";
update urls set worker = "" where finished = 0 and worker not in ("later", "");
UPDATE T SET C1 = 1 WHERE C2 = "a";
UPDATE T SET C1 = C1 + 1,D1= (C1*3)+9/C3 WHERE C2 = "a";
UPDATE T SET C1 = "text" || C1 WHERE C2 = "a";
UPDATE T1 SET C1 = 2 WHERE C2 IN (SELECT C3 FROM T2 WHERE C4 = 0);
UPDATE T SET C1 = 1, C2 = 2;
UPDATE T SET A = 1 WHERE C1 = 1 AND C2 = 2;
UPDATE business AS b INNER JOIN business_geocode AS g ON b.business_id = g.business_id
SET b.mapx = g.latitude,
      b.mapy = g.longitude
      WHERE  (b.mapx = "" or b.mapx = 0) and
        g.latitude > 0;
UPDATE t SET id = id + 1;
UPDATE t SET id = id + 1 ORDER BY id DESC;
UPDATE items,month SET items.price=month.price
WHERE items.id=month.id;
UPDATE weather
    SET temp_hi = temp_hi - 2,  temp_lo = temp_lo - 2
        WHERE date > "1994-11-28";
UPDATE branches SET balance = balance + 100.00
    WHERE name NOT IN (SELECT branch_name FROM accounts WHERE name = "Bob");
UPDATE branches SET balance = balance + 100.00
    WHERE name = (SELECT branch_name FROM accounts WHERE name = "Bob");
