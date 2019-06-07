SELECT * FROM `table1` WHERE `foo` = `bar` AND `foo` = "bar";
SELECT ID, CONCAT(NAME, " FROM ", DEPT) AS NAME, SALARY FROM employee;
SELECT * FROM employee WHERE dept = "TECHNOLOGY" OR salary >= 6000;
SELECT
following.id as fid
FROM following AS flow

INNER JOIN members AS mem ON mem.id = flow.user_id
INNER JOIN members_favorites as memfav ON memfav.userid = mem.id
INNER JOIN recipes AS re ON re.id = memfav.recipeid

where flow.follower_id = 1;
SELECT
   SQL_CALC_FOUND_ROWS a.* 
   FROM 
     articles AS a
       JOIN tags2articles AS ta  ON a.id=ta.idArticle
         JOIN tags AS t ON ta.idTag=t.id
         WHERE 
           t.id IN (12,13,16) 
    GROUP BY a.id
    HAVING
      COUNT(t.id)=3
      --COUNT(DISTINCT t.id)=3;
SELECT count(*) FROM t1 JOIN t2 USING (a);
SELECT count(*) FROM t1 JOIN t2;
SELECT 1, 2, 3;
SELECT DISTINCT 1, 2, 3;
SELECT ALL 1, 2, 3;
SELECT a, b, a||b FROM t1;
SELECT DISTINCT a, b, a||b FROM t1;
SELECT ALL a, b, a||b FROM t1;
SELECT 1, 2, 3 WHERE 2;
SELECT 1, 2, 3 WHERE 1;
SELECT 1, 2, 3 WHERE 0;
SELECT 1, 2, 3 WHERE NULL;
SELECT DISTINCT 1, 2, 3 WHERE 1;
SELECT ALL 1, 2, 3 WHERE 1;
SELECT a, b, a||b FROM t1 WHERE a!="x";
SELECT a, b, a||b FROM t1 WHERE a="x";
SELECT DISTINCT a, b, a||b FROM t1 WHERE a!="x";
SELECT ALL a, b, a||b FROM t1 WHERE a="x";
SELECT count(*), max(a) FROM t1 GROUP BY b;
SELECT count(*), max(a) FROM t1 GROUP BY b HAVING count(*)=1;
SELECT count(*), max(a) FROM t1 GROUP BY b HAVING count(*)=2;
SELECT DISTINCT count(*), max(a) FROM t1 GROUP BY b;
SELECT DISTINCT count(*), max(a) FROM t1 
GROUP BY b HAVING count(*)=1;
SELECT DISTINCT count(*), max(a) FROM t1 
GROUP BY b HAVING count(*)=2;
SELECT ALL count(*), max(a) FROM t1 GROUP BY b;
SELECT ALL count(*), max(a) FROM t1 
GROUP BY b HAVING count(*)=1;
SELECT ALL count(*), max(a) FROM t1 
GROUP BY b HAVING count(*)=2;
SELECT count(*), max(a) FROM t1 WHERE a="a" GROUP BY b;
SELECT count(*), max(a) FROM t1 
WHERE a="c" GROUP BY b HAVING count(*)=1;
SELECT count(*), max(a) FROM t1 
WHERE 0 GROUP BY b HAVING count(*)=2;
SELECT DISTINCT count(*), max(a) FROM t1 WHERE a<"c" GROUP BY b;
SELECT DISTINCT count(*), max(a) FROM t1 WHERE a>"a"
GROUP BY b HAVING count(*)=1;
SELECT DISTINCT count(*), max(a) FROM t1 WHERE 0
GROUP BY b HAVING count(*)=2;
SELECT ALL count(*), max(a) FROM t1 WHERE b>"one" GROUP BY b;
SELECT ALL count(*), max(a) FROM t1 WHERE a!="b"
GROUP BY b HAVING count(*)=1;
SELECT ALL count(*), max(a) FROM t1 
WHERE 0 GROUP BY b HAVING count(*)=2;
SELECT * FROM t1 a;
SELECT t1.* FROM t1 a;
SELECT "x"||a||"x" FROM t1;
SELECT "x"||a||"x" alias FROM t1;
SELECT "x"||a||"x" AS alias FROM t1;
SELECT t1.rowid FROM t1;
SELECT t1.rowid FROM t1,t2;
SELECT t1.rowid FROM t1,t2,t3;
SELECT t1.rowid FROM t1;
SELECT t1.rowid FROM t1 JOIN t2;
SELECT t1.rowid FROM t1 JOIN t2 JOIN t3;
SELECT t1.rowid FROM t1 NATURAL JOIN t3;
SELECT t1.rowid FROM t1 NATURAL LEFT OUTER JOIN t3;
SELECT t1.rowid FROM t1 NATURAL LEFT JOIN t3;
SELECT t1.rowid FROM t1 NATURAL INNER JOIN t3;
SELECT t1.rowid FROM t1 JOIN t3;
SELECT t1.rowid FROM t1 LEFT OUTER JOIN t3;
SELECT t1.rowid FROM t1 LEFT JOIN t3;
SELECT t1.rowid FROM t1 INNER JOIN t3;
SELECT b||a FROM t1 ORDER BY b||a;
SELECT b||a FROM t1 ORDER BY (b||a) ASC;
SELECT b||a FROM t1 ORDER BY (b||a) DESC;
SELECT * FROM t1 a;
SELECT * FROM t1 ORDER BY b;
SELECT * FROM t1 ORDER BY b, a;
SELECT * FROM t1 LIMIT 10;
SELECT * FROM t1 LIMIT 10 OFFSET 5;
SELECT * FROM t1 LIMIT 10, 5;
SELECT * FROM t1 ORDER BY a LIMIT 10;
SELECT * FROM t1 ORDER BY b LIMIT 10 OFFSET 5;
SELECT * FROM t1 ORDER BY a,b LIMIT 10, 5;
SELECT "abc"            abc;
SELECT "abc" WHERE NULL;
SELECT NULL;
SELECT count(*);
SELECT count(*) WHERE 0;
SELECT count(*) WHERE 1;
SELECT quote(x), quote(y) FROM xx;
SELECT count(*), count(x), count(y) FROM xx;
SELECT sum(x), sum(y) FROM xx;
SELECT * FROM t1, t2, t3;
SELECT * FROM x1 JOIN x2 LIMIT 1;
SELECT * FROM x2 JOIN x1 LIMIT 1;
SELECT * FROM x3 JOIN x2 LIMIT 1;
SELECT * FROM x2 JOIN x3 LIMIT 1;
SELECT * FROM x2 JOIN x3 ORDER BY +c, +f;
SELECT count(*) FROM x1 JOIN x2;
SELECT count(*) FROM x2 JOIN x3;
SELECT count(*) FROM x3 JOIN x1;
SELECT count(*) FROM x3 JOIN x3;
SELECT * FROM t1 INNER JOIN t2;
SELECT * FROM t1 AS y INNER JOIN t1 AS x;
SELECT * FROM t1 JOIN t2 ON (1);
SELECT * FROM t1 JOIN t2 ON (0);
SELECT * FROM t1 JOIN t2 ON (NULL);
SELECT * FROM t1 JOIN t2 ON ("abc");
SELECT * FROM t1 JOIN t2 ON ("1ab");
SELECT * FROM t1 JOIN t2 ON (0.9);
SELECT * FROM t1 JOIN t2 ON ("0.9");
SELECT * FROM t1 JOIN t2 ON (0.0);
SELECT t1.b, t2.b FROM t1 JOIN t2 ON (t1.a = t2.a);
SELECT t1.b, t2.b FROM t1 JOIN t2 ON (t1.a = "a");
SELECT t1.b, t2.b;
SELECT k FROM x1 LEFT JOIN x2 USING(k);
SELECT k FROM x1 LEFT JOIN x2 USING(k) WHERE x2.k;
SELECT k FROM x1 LEFT JOIN x2 USING(k) WHERE x2.k IS NULL;
SELECT k FROM x1 LEFT JOIN x2 USING(k) WHERE x2.k IS NOT NULL;
SELECT k FROM x1 NATURAL JOIN x2 WHERE x2.k;
SELECT k FROM x1 NATURAL JOIN x2 WHERE x2.k-3;
SELECT * FROM z1 LIMIT 1;
SELECT * FROM z1,z2 LIMIT 1;
SELECT z1.* FROM z1,z2 LIMIT 1;
SELECT z2.* FROM z1,z2 LIMIT 1;
SELECT z2.*, z1.* FROM z1,z2 LIMIT 1;
SELECT count(*), * FROM z1;
SELECT max(a), * FROM z1;
SELECT *, min(a) FROM z1;
SELECT *,* FROM z1,z2 LIMIT 1;
SELECT z1.*,z1.* FROM z2,z1 LIMIT 1;
SELECT * FROM z1;
SELECT * FROM z1 NATURAL JOIN z3;
SELECT z1.* FROM z1 NATURAL JOIN z3;
SELECT z3.* FROM z1 NATURAL JOIN z3;
SELECT z1.*, z3.* FROM z1 NATURAL JOIN z3;
SELECT 1, 2, z1.* FROM z1;
SELECT a, *, b, c FROM z1;
SELECT a, b FROM z1;
SELECT a IS NULL, b+1, * FROM z1;
SELECT 32*32, d||e FROM z2;
SELECT count(a), max(a), count(b), max(b) FROM z1;
SELECT count(*), max(1);
SELECT sum(b+1) FROM z1 NATURAL LEFT JOIN z3;
SELECT sum(b+2) FROM z1 NATURAL LEFT JOIN z3;
SELECT sum(b IS NOT NULL) FROM z1 NATURAL LEFT JOIN z3;
SELECT one, two, count(*) FROM a1;
SELECT one, two, count(*) FROM a1 WHERE one<3;
SELECT one, two, count(*) FROM a1 WHERE one>3;
SELECT *, count(*) FROM a1 JOIN a2;
SELECT *, sum(three) FROM a1 NATURAL JOIN a2;
SELECT *, sum(three) FROM a1 NATURAL JOIN a2;
SELECT group_concat(three, ""), a1.* FROM a1 NATURAL JOIN a2;
SELECT one, two, count(*) FROM a1 WHERE 0;
SELECT sum(two), * FROM a1, a2 WHERE three>5;
SELECT max(one) IS NULL, one IS NULL, two IS NULL FROM a1 WHERE two=7;
SELECT count(*) FROM a1;
SELECT count(*) FROM a1 WHERE 0;
SELECT count(*) FROM a1 WHERE 1;
SELECT max(a1.one)+min(two), a1.one, two, * FROM a1, a2 WHERE 1;
SELECT max(a1.one)+min(two), a1.one, two, * FROM a1, a2 WHERE 0;
SELECT group_concat(one), two FROM b1 GROUP BY two;
SELECT group_concat(one), sum(one) FROM b1 GROUP BY (one>4);
SELECT group_concat(one) FROM b1 GROUP BY (two>"o");
SELECT group_concat(one) FROM b1 GROUP BY (one=2 OR two="o");
SELECT group_concat(y) FROM b2 GROUP BY x;
SELECT count(*) FROM b2 GROUP BY CASE WHEN y<4 THEN NULL ELSE 0 END;
SELECT count(*) FROM b3 GROUP BY b;
SELECT count(*) FROM b3 GROUP BY a;
SELECT count(*) FROM b3 GROUP BY +b;
SELECT count(*) FROM b3 GROUP BY +a;
SELECT count(*) FROM b3 GROUP BY b||"";
SELECT count(*) FROM b3 GROUP BY a||"";
SELECT * FROM b3 GROUP BY count(*);
SELECT max(a) FROM b3 GROUP BY max(b);
SELECT group_concat(a) FROM b3 GROUP BY a, max(b);
SELECT up FROM c1 GROUP BY up HAVING count(*)>3;
SELECT up FROM c1 GROUP BY up HAVING sum(down)>16;
SELECT up FROM c1 GROUP BY up HAVING sum(down)<16;
SELECT up||down FROM c1 GROUP BY (down<5) HAVING max(down)<10;
SELECT up FROM c1 GROUP BY up HAVING down>10;
SELECT up FROM c1 GROUP BY up HAVING up="y";
SELECT i, j FROM c2 GROUP BY i>4 HAVING i>6;
SELECT sum(down) FROM c1 GROUP BY up;
SELECT sum(j), max(j) FROM c2 GROUP BY (i%3);
SELECT sum(j), max(j) FROM c2 GROUP BY (j%2);
SELECT 1+sum(j), max(j)+1 FROM c2 GROUP BY (j%2);
SELECT i, j FROM c2 GROUP BY i%2;
SELECT i, j FROM c2 GROUP BY i%2 HAVING j<30;
SELECT i, j FROM c2 GROUP BY i%2 HAVING j>30;
SELECT i, j FROM c2 GROUP BY i%2 HAVING j>30;
SELECT count(*), i, k FROM c2 NATURAL JOIN c3 GROUP BY substr(k, 1, 1);
SELECT i, j FROM c2 GROUP BY i%2;
SELECT i, j FROM c2 GROUP BY i;
SELECT i, j FROM c2 GROUP BY i HAVING i<5;
SELECT ALL a FROM h1;
SELECT DISTINCT a FROM h1;
SELECT ALL x FROM h2;
SELECT x FROM h2;
SELECT DISTINCT x FROM h2;
SELECT DISTINCT d FROM h3;
SELECT DISTINCT b FROM h1;
SELECT * FROM d1 ORDER BY x, y, z;
SELECT * FROM d1 ORDER BY x ASC, y ASC, z ASC;
SELECT * FROM d1 ORDER BY x DESC, y DESC, z DESC;
SELECT * FROM d1 ORDER BY x DESC, y ASC, z DESC;
SELECT * FROM d1 ORDER BY x DESC, y ASC, z ASC;
SELECT * FROM d1 ORDER BY x, y, z;
SELECT * FROM d1 ORDER BY x DESC, y, z DESC;
SELECT * FROM d1 ORDER BY x DESC, y, z;
SELECT * FROM d1 ORDER BY 1 ASC, 2 ASC, 3 ASC;
SELECT * FROM d1 ORDER BY 1 DESC, 2 DESC, 3 DESC;
SELECT * FROM d1 ORDER BY 1 DESC, 2 ASC, 3 DESC;
SELECT * FROM d1 ORDER BY 1 DESC, 2 ASC, 3 ASC;
SELECT * FROM d1 ORDER BY 1, 2, 3;
SELECT * FROM d1 ORDER BY 1 DESC, 2, 3 DESC;
SELECT * FROM d1 ORDER BY 1 DESC, 2, 3;
SELECT z, x FROM d1 ORDER BY 2;
SELECT z, x FROM d1 ORDER BY 1;
SELECT z+1 AS abc FROM d1 ORDER BY abc;
SELECT z+1 AS abc FROM d1 ORDER BY abc DESC;
SELECT z AS x, x AS z FROM d1 ORDER BY z;
SELECT z AS x, x AS z FROM d1 ORDER BY x;
SELECT * FROM d1 ORDER BY x+y+z;
SELECT * FROM d1 ORDER BY x*z;
SELECT * FROM d1 ORDER BY y*z;
SELECT a FROM d3 ORDER BY a;
SELECT a FROM d3 ORDER BY a DESC;
SELECT x FROM d4 ORDER BY 1;
SELECT x AS y FROM d4 ORDER BY y;
SELECT x||"" FROM d4 ORDER BY x;
SELECT x FROM d4 ORDER BY x||"";
SELECT b FROM f1 ORDER BY a LIMIT 5;
SELECT b FROM f1 ORDER BY a LIMIT 2+3;
SELECT b FROM f1 ORDER BY a LIMIT (SELECT a FROM f1 WHERE b = "e");
SELECT b FROM f1 ORDER BY a LIMIT 5.0;
SELECT b FROM f1 ORDER BY a LIMIT "5";
SELECT b FROM f1 ORDER BY a LIMIT "hello";
SELECT b FROM f1 ORDER BY a LIMIT NULL;
SELECT b FROM f1 ORDER BY a LIMIT 5.1;
SELECT b FROM f1 ORDER BY a LIMIT -1;
SELECT b FROM f1 ORDER BY a LIMIT length("abc")-100;
SELECT b FROM f1 ORDER BY a LIMIT 0;
SELECT b FROM f1 ORDER BY a DESC LIMIT 4;
SELECT b FROM f1 ORDER BY a DESC LIMIT 8;
SELECT b FROM f1 ORDER BY a DESC LIMIT "12.0";
SELECT b FROM f1 WHERE a>21 ORDER BY a LIMIT 10;
SELECT count(*) FROM f1 GROUP BY a/5 ORDER BY 1 LIMIT 10;
SELECT b FROM f1 ORDER BY a LIMIT 2 OFFSET "hello";
SELECT b FROM f1 ORDER BY a LIMIT 2 OFFSET NULL;
SELECT b FROM f1 ORDER BY a LIMIT 2 OFFSET 5.1;
SELECT b FROM f1 ORDER BY a 
LIMIT 2 OFFSET (SELECT group_concat(b) FROM f1);
SELECT b FROM f1 ORDER BY a LIMIT 10 OFFSET 5;
SELECT b FROM f1 ORDER BY a LIMIT 2+3 OFFSET 10;
SELECT b FROM f1 ORDER BY a 
LIMIT  (SELECT a FROM f1 WHERE b="j") 
OFFSET (SELECT a FROM f1 WHERE b="b");
SELECT b FROM f1 ORDER BY a LIMIT "5" OFFSET 3.0;
SELECT b FROM f1 ORDER BY a LIMIT "5" OFFSET 0;
SELECT b FROM f1 ORDER BY a LIMIT 0 OFFSET 10;
SELECT b FROM f1 ORDER BY a LIMIT 3 OFFSET "1"||"5";
SELECT b FROM f1 ORDER BY a LIMIT 10 OFFSET 20;
SELECT a FROM f1 ORDER BY a DESC LIMIT 100 OFFSET 18+4;
SELECT b FROM f1 ORDER BY a LIMIT 5 OFFSET -1;
SELECT b FROM f1 ORDER BY a LIMIT 5 OFFSET -500;
SELECT b FROM f1 ORDER BY a LIMIT 5 OFFSET 0;
SELECT b FROM f1 ORDER BY a LIMIT 5, 10;
SELECT b FROM f1 ORDER BY a LIMIT 10, 2+3;
SELECT b FROM f1 ORDER BY a 
LIMIT (SELECT a FROM f1 WHERE b="b"), (SELECT a FROM f1 WHERE b="j");
SELECT b FROM f1 ORDER BY a LIMIT 3.0, "5";
SELECT b FROM f1 ORDER BY a LIMIT 0, "5";
SELECT b FROM f1 ORDER BY a LIMIT 10, 0;
SELECT b FROM f1 ORDER BY a LIMIT "1"||"5", 3;
SELECT b FROM f1 ORDER BY a LIMIT 20, 10;
SELECT a FROM f1 ORDER BY a DESC LIMIT 18+4, 100;
SELECT b FROM f1 ORDER BY a LIMIT -1, 5;
SELECT b FROM f1 ORDER BY a LIMIT -500, 5;
SELECT b FROM f1 ORDER BY a LIMIT 0, 5;
select day(link_date) as day, count(*) from links where year(link_date) = $year and month(link_date) = $month group by day;
select day(link_published_date) as day, count(*) from links where year(link_published_date) = $year and month(link_published_date) = $month and link_status="published" group by day;
select day(link_date) as day, count(*) from links where year(link_date) = $year and month(link_date) = $month group by day;
SELECT UNIX_TIMESTAMP(post_date) FROM posts WHERE post_id in ($ids) ORDER BY post_id DESC LIMIT 1;
SELECT post_id FROM posts WHERE post_id in ($ids) ORDER BY post_id DESC LIMIT $rows;
SELECT
            s.*
                    FROM strings s
                            LEFT JOIN tstrings t ON (s.id = t.stringid)
            WHERE isnull(t.text)
            ORDER BY len(s.text) DESC;
select * from subs where visible order by id asc;
select * from subs where visible order by id asc;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
SELECT UNIX_TIMESTAMP(annotation_time) as time, UNIX_TIMESTAMP(annotation_expire) as expire, annotation_text as text FROM annotations WHERE annotation_key = "sub_preferences_0" and (annotation_expire is null or annotation_expire > now());
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select subs.* from subs, subs_copy where dst = 0 and id = src;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select `count` from counts where `key` = "0.published" and date > date_sub(now(), interval 7200 second);
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
SELECT UNIX_TIMESTAMP(annotation_time) as time, UNIX_TIMESTAMP(annotation_expire) as expire, annotation_text as text FROM annotations WHERE annotation_key = "top-actives-mnm" and (annotation_expire is null or annotation_expire > now());
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select link_id, counter*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.5/172800) as value from links, link_clicks, sub_statuses where sub_statuses.id = 0 AND link_id = link AND status="published" and date > "2015-08-22 22:18:00" and link_clicks.id = link order by value desc limit 5;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select link_id, (link_votes-link_negatives*2)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.8/129600) as value from links, sub_statuses where id = 0 AND link_id = link AND status="published" and date > "2015-08-23 10:18:00" order by value desc limit 5;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select sum(counter) as total_count, sum(counter*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.5/172800)) as value, blog_url from links, link_clicks, blogs, sub_statuses where sub_statuses.id = 0 AND link_id = link AND date > "2015-08-22 22:18:00" and status="published" and link_blog = blog_id AND link_clicks.id = link group by link_blog order by value desc limit 10;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select comment_id, comment_order, user_id, user_login, user_avatar, link_id, link_uri, link_title, link_comments, comment_karma*(1-(1440469080-unix_timestamp(comment_date))*0.7/43000) as value, link_negatives/link_votes as rel from comments, links, users, sub_statuses where id = 0 AND status in ("published", "queued") AND link_id = link AND date > "2015-08-22 22:18:00" and comment_date > "2015-08-24 08:24:00" and LENGTH(comment_content) > 32 and link_negatives/link_votes < 0.5  and comment_karma > 50 and comment_link_id = link and comment_user_id = user_id and user_level != "disabled" order by value desc limit 10;
select link from sub_statuses, subs, links where date > date_sub(now(), interval 48 hour) and status = "published" and sub_statuses.id = origen and subs.id = sub_statuses.id and owner > 0 and not nsfw and link_id = link order by date desc limit 10;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select link_tags FROM links, sub_statuses WHERE id = 0 AND link_id = link and link_date > "2015-08-22 22:18:00" and link_status = "published";
SELECT UNIX_TIMESTAMP(annotation_time) as time, UNIX_TIMESTAMP(annotation_expire) as expire, annotation_text as text FROM annotations WHERE annotation_key = "top-link-mnm" and (annotation_expire is null or annotation_expire > now());
SELECT SQL_CACHE count(*) FROM sub_statuses  WHERE sub_statuses.id = 0 AND status="published";
select * from subs where visible order by id asc;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
SELECT UNIX_TIMESTAMP(annotation_time) as time, UNIX_TIMESTAMP(annotation_expire) as expire, annotation_text as text FROM annotations WHERE annotation_key = "sub_preferences_0" and (annotation_expire is null or annotation_expire > now());
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select subs.* from subs, subs_copy where dst = 0 and id = src;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select `count` from counts where `key` = "0.published" and date > date_sub(now(), interval 7200 second);
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
SELECT UNIX_TIMESTAMP(annotation_time) as time, UNIX_TIMESTAMP(annotation_expire) as expire, annotation_text as text FROM annotations WHERE annotation_key = "top-actives-mnm" and (annotation_expire is null or annotation_expire > now());
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select link_id, counter*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.5/172800) as value from links, link_clicks, sub_statuses where sub_statuses.id = 0 AND link_id = link AND status="published" and date > "2015-08-22 22:18:00" and link_clicks.id = link order by value desc limit 5;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select link_id, (link_votes-link_negatives*2)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.8/129600) as value from links, sub_statuses where id = 0 AND link_id = link AND status="published" and date > "2015-08-23 10:18:00" order by value desc limit 5;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select sum(counter) as total_count, sum(counter*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.5/172800)) as value, blog_url from links, link_clicks, blogs, sub_statuses where sub_statuses.id = 0 AND link_id = link AND date > "2015-08-22 22:18:00" and status="published" and link_blog = blog_id AND link_clicks.id = link group by link_blog order by value desc limit 10;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select comment_id, comment_order, user_id, user_login, user_avatar, link_id, link_uri, link_title, link_comments, comment_karma*(1-(1440469080-unix_timestamp(comment_date))*0.7/43000) as value, link_negatives/link_votes as rel from comments, links, users, sub_statuses where id = 0 AND status in ("published", "queued") AND link_id = link AND date > "2015-08-22 22:18:00" and comment_date > "2015-08-24 08:24:00" and LENGTH(comment_content) > 32 and link_negatives/link_votes < 0.5  and comment_karma > 50 and comment_link_id = link and comment_user_id = user_id and user_level != "disabled" order by value desc limit 10;
select link from sub_statuses, subs, links where date > date_sub(now(), interval 48 hour) and status = "published" and sub_statuses.id = origen and subs.id = sub_statuses.id and owner > 0 and not nsfw and link_id = link order by date desc limit 10;
SELECT subs.*, media.id as media_id, media.size as media_size, media.dim1 as media_dim1, media.dim2 as media_dim2,
			media.extension as media_extension, UNIX_TIMESTAMP(media.date) as media_date
			FROM subs
			LEFT JOIN media ON (media.type="sub_logo" and media.id = subs.id and media.version = 0) where subs.name = "mnm";
select link_tags FROM links, sub_statuses WHERE id = 0 AND link_id = link and link_date > "2015-08-22 22:18:00" and link_status = "published";
SELECT UNIX_TIMESTAMP(annotation_time) as time, UNIX_TIMESTAMP(annotation_expire) as expire, annotation_text as text FROM annotations WHERE annotation_key = "top-link-mnm" and (annotation_expire is null or annotation_expire > now());
SELECT SQL_CACHE count(*) FROM sub_statuses  WHERE sub_statuses.id = 0 AND status="published";
SELECT 
DISTINCT
SQL_BUFFER_RESULT
t.id,
    t.tag, 
    c.title AS Category
    FROM
    tags2Articles t2a 
    INNER JOIN tags t ON t.id = t2a.idTag
    INNER JOIN categories c ON t.tagCategory = c.id
    INNER JOIN (
            SELECT
            a.id 
            FROM 
            articles AS a
            JOIN tags2articles AS ta  ON a.id=ta.idArticle
            JOIN tags AS tsub ON ta.idTag=tsub.id
            WHERE 
            tsub.id IN (12,13,16) 
            GROUP BY a.id
            HAVING COUNT(tsub.id)=3 
            ) asub ON t2a.idArticle = asub.id;
SELECT city, (temp_hi+temp_lo)/2 AS temp_avg, date FROM weather;
SELECT * FROM weather
    WHERE city = "San Francisco" AND prcp > 0.0;
SELECT * FROM weather
    ORDER BY city;
SELECT * FROM weather
    ORDER BY city, temp_lo;
SELECT * FROM weather
    ORDER BY city, temp_lo;
SELECT DISTINCT city
FROM weather
ORDER BY city;
SELECT *
FROM weather, cities
WHERE city = name;
SELECT weather.city, weather.temp_lo, weather.temp_hi,
       weather.prcp, weather.date, cities.location
       FROM weather, cities
        WHERE cities.name = weather.city;
SELECT * FROM weather INNER JOIN cities ON (weather.city = cities.name);
SELECT * FROM weather LEFT OUTER JOIN cities ON (weather.city = cities.name);
SELECT W1.city, W1.temp_lo AS low, W1.temp_hi AS high,
       W2.city, W2.temp_lo AS low, W2.temp_hi AS high
       FROM weather W1, weather W2
       WHERE W1.temp_lo < W2.temp_lo
       AND W1.temp_hi > W2.temp_hi;
SELECT *
FROM weather w, cities c
WHERE w.city = c.name;
SELECT city FROM weather
    WHERE temp_lo = (SELECT max(temp_lo) FROM weather);
SELECT city, max(temp_lo)
        FROM weather
            GROUP BY city;
SELECT city, max(temp_lo)
        FROM weather
            GROUP BY city;
SELECT city, max(temp_lo)
    FROM weather
    WHERE city LIKE "S%"
    GROUP BY city
    HAVING max(temp_lo) < 40;
select * from (select count(*) as total, hostname from urls group by hostname) as x order by total desc limit 10;
select * from urls where finished = 0 and worker in ("later", "yyy");
select * from urls where finished = 0 and worker not in ("later", "xxx");
SELECT * From urls where foo = :limit;
select * from database_name.urls;

SELECT CustomerName, City, Country
FROM Customers
ORDER BY
(CASE
    WHEN City IS NULL THEN Country
    ELSE City
END);
