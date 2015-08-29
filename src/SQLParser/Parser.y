%name SQLParser_
%include {
use SQLParser\Stmt;
}

%declare_class { class SQLParser_Parser }
%include_class {
    public $body = array();
}

%syntax_error {
    $expect = array();
    foreach ($this->yy_get_expected_tokens($yymajor) as $token) {
        $expect[] = self::$yyTokenName[$token];
    }
    throw new RuntimeException('Unexpected ' . $this->tokenName($yymajor) . '(' . $TOKEN. ') Expecting ' . implode(",", $expect));
}

%right T_NOT.
%left T_AND.
%left T_OR.
%left T_QUESTION T_COLON.
%nonassoc T_EQ T_LIKE T_NE.
%nonassoc T_GT T_GE T_LT T_LE.
%nonassoc T_IN.
%left T_PLUS T_MINUS T_CONCAT.
%left T_TIMES T_DIV T_MOD.
%left T_PIPE T_BITWISE T_FILTER_PIPE.

query ::= stmts(A). { $this->body = A; }

stmts(A) ::= stmts(B) SEMICOLON stmt(C) . { A = B; A[] = C; }
stmts(A) ::= stmt(C) .  { A = [C];  }

stmt(A) ::= PAR_OPEN stmt(B) PAR_CLOSE . { A = B; }

stmt(A) ::= drop(B).            { A = B; }
stmt(A) ::= select(B).          { A = B; }
stmt(A) ::= insert(B).          { A = B; }
stmt(A) ::= update(B).          { A = B; }
stmt(A) ::= delete(B).          { A = B; }
stmt(A) ::= alter_table(B).     { A = B; }
stmt(A) ::= create_table(B).    { A = B; }
stmt(A) ::= create_view(B).     { A = B; }
stmt(A) ::= . { A = null; }

inner_select(A) ::= PAR_OPEN inner_select(B) PAR_CLOSE . { A = B;}
inner_select(A) ::= PAR_OPEN select(B) PAR_CLOSE . { A = B;}

/** Select */
select(A) ::= SELECT select_opts(MM) expr_list_as(L) from(X) joins(J) where(W) group_by(GG) order_by(O) limit(LL) .  { 
    A = new SQLParser\Select(L);
    if (MM) A->setOptions(MM);
    if (X)  A->from(X);
    if (W)  A->where(W);
    if (J)  A->joins(J);
    if (O)  A->orderBy(O);
    if (GG) A->groupBy(GG[0], GG[1]);
    if (LL) A->limit(LL);
}

select_opts(A) ::= select_opts(B) select_mod(C) . { A = B; A[] = C; }
select_opts(A) ::= . { A = array(); }
select_mod(A) ::= ALL|DISTINCT|DISTINCTROW|HIGH_PRIORITY|STRAIGHT_JOIN|SQL_SMALL_RESULT|SQL_BIG_RESULT|SQL_CACHE|SQL_CALC_FOUND_ROWS|SQL_BUFFER_RESULT|SQL_NO_CACHE(X). { A = strtoupper(@X); }

from(A) ::= FROM table_list(X). { A = X; }
from(A) ::= .

table_list(A) ::= table_list(B) COMMA table_with_alias(C) . { A = B; A[] = C; }
table_list(A) ::= table_with_alias(B). { A = [B]; }

table_with_alias(A) ::= inner_select(B) T_AS alpha(Y) .   { A = new Stmt\Table(B, Y); }
table_with_alias(A) ::= inner_select(B) alpha(Y) .        { A = new Stmt\Table(B, Y); }
table_with_alias(A) ::= table_name(X) T_AS alpha(Y) .     { A = X->setAlias(Y); }
table_with_alias(A) ::= table_name(X) alpha(Y) .          { A = X->setAlias(Y); }
table_with_alias(A) ::= table_name(X).                    { A = X; }

joins(A) ::= joins(B) join(C). { A = B; A[] = C; } 
joins(A) ::= . { A = []; }

join(A) ::= join_type(B) JOIN table_with_alias(C) join_condition(D). {
    A = B->setTable(C); 
    if (D[0]) {
        A->{D[0]}(D[1]);
    }
}

join_type(A) ::= join_prefix(B) INNER.                      { A = new Stmt\Join('INNER', B); }
join_type(A) ::= join_prefix(B) LEFT join_postfix(C).       { A = new Stmt\Join('LEFT', B, C); }
join_type(A) ::= join_prefix(B) RIGHT join_postfix(C).      { A = new Stmt\Join('RIGHT', B, C); }
join_type(A) ::= join_prefix(B).                            { A = new Stmt\Join('INNER', B); }

join_prefix(A) ::= NATURAL. { A = 'NATURAL'; }
join_prefix(A) ::= . { A = ''; }
join_postfix(A) ::= OUTER . { A = 'OUTER'; }
join_postfix(A) ::= . { A = ''; }

join_condition(A) ::= ON expr(B).           { A = ['ON', B]; }
join_condition(A) ::= USING columns(B) .    { A = ['USING', B]; }
join_condition(A) ::= USING PAR_OPEN columns(B) PAR_CLOSE .    { A =['USING',  B]; }
join_condition(A) ::= . { A = NULL; }

where(A) ::= WHERE expr(B) . { A = B; }
where(A) ::= . { A = NULL; }

order_by(A) ::= ORDER BY order_by_fields(B) . { A = B; }
order_by(A) ::= . { A = NULL; }

order_by_fields(A) ::= order_by_fields(B) COMMA order_by_field(C) . { A = B->addTerm(C); }
order_by_fields(A) ::= order_by_field(B) . { A = new Stmt\ExprList(B); }

order_by_field(A) ::= expr(X) DESC|ASC(Y) . { A = new Stmt\Expr(strtoupper(@Y), X); }
order_by_field(A) ::= expr(X) . { A = new Stmt\Expr("DESC", X); }

limit(A) ::= LIMIT expr(B) OFFSET expr(C).  { A = new Stmt\ExprList(B, C); }
limit(A) ::= LIMIT expr(B) COMMA expr(C).   { A = new Stmt\ExprList(B, C); }
limit(A) ::= LIMIT expr(B).{ A = new Stmt\ExprList(B); }
limit(A) ::= . { A = NULL; }

group_by(A) ::= GROUP BY expr_list_par_optional(B) . { A = [B, null]; }
group_by(A) ::= GROUP BY expr_list_par_optional(B) HAVING expr(C). { A = [B, C]; }
group_by(A) ::= .

insert(A) ::= insert_stmt(X) select(S).                 { A = X; X->values(S); }
insert(A) ::= insert_stmt(X) inner_select(S)    .       { A = X; X->values(S); }
insert(A) ::= insert_stmt(X) VALUES expr_list_par_many(L).   { A = X; X->values(L); }
insert(A) ::= insert_stmt(X) set_expr(S). { 
    A = X; 
    $keys   = new Stmt\ExprList;
    $values = new Stmt\ExprList;
    foreach (S->getTerms() as $field) {
        $member = $field->getMembers();
        $keys->addTerm($member[0]);
        $values->addTerm($member[1]);
    }
    X->values($values)->fields($keys);
}

drop(A) ::= DROP TABLE table_list(X). {
    A = new SQLParser\Drop('TABLE', X);
}

delete(A) ::= DELETE FROM table_with_alias(T) where(W) order_by(O) limit(L). {
    A = new SQLParser\Delete(T);
    if (W) A->where(W);
    if (O) A->orderBy(O);
    if (L) A->limit(L);
}

update(A) ::= UPDATE table_list(B) joins(JJ) set_expr(S) where(W) order_by(O) limit(LL). {
    A = new SQLParser\Update(B, S);
    if (JJ) A->joins(JJ);
    if (W)  A->where(W);
    if (O) A->orderBy(O);
    if (LL) A->limit(LL);
}

insert_stmt(A) ::= INSERT|REPLACE(X) INTO insert_table(T). { 
    A = new SQLParser\Insert(@X);
    A->table(T[0])->fields(T[1]); }
insert_stmt(A) ::= INSERT|REPLACE(X) insert_table(T). { 
    A = new SQLParser\Insert(@X);
    A->table(T[0]); 
}

insert_table(A) ::= table_name(B) . { A = [B, []];}
insert_table(A) ::= table_name(B) PAR_OPEN columns(L) PAR_CLOSE.  { A = [B, L]; }

set_expr(A) ::= SET set_expr_values(X). { A = X; }
set_expr_values(A) ::= set_expr_values(B) COMMA assign(C) . { A = B->addTerm(C); }
set_expr_values(A) ::= assign(C) .      { A = new Stmt\ExprList(C); }
assign(A) ::= colname(B) T_EQ expr(X) . { A = new Stmt\Expr("=", B, X); }

create_view(A) ::= CREATE VIEW colname(N) T_AS select(S). {
    A = new SQLParser\View(N, S);
}

create_table(A) ::= CREATE TABLE colname(N) PAR_OPEN create_fields(X) PAR_CLOSE.

create_fields(A) ::= colname(B) data_type(C) column_mod(D) . { 
}

data_type(A) ::= colname(B) . {
    A = new Stmt\DataType(@B);
}

data_type(A) ::= colname(B) PAR_OPEN NUMBER(X) PAR_CLOSE .{
    A = new Stmt\DataType(@B, X);
}

column_mod(A) ::= T_NOT NULL.

/** Expression */
expr(A) ::= expr(B) T_AND expr(C). { A = new Stmt\Expr('and', B, C); }
expr(A) ::= expr(B) T_OR expr(C). { A = new Stmt\Expr('or', B, C); }
expr(A) ::= T_NOT expr(C). { A = new Stmt\Expr('not', C); }
expr(A) ::= PAR_OPEN expr(B) PAR_CLOSE.    { A = new Stmt\Expr('expr', B); }
expr(A) ::= inner_select(B) . { A = new Stmt\Expr('expr', B); }
expr(A) ::= expr(B) T_EQ|T_LIKE|T_NE|T_GT|T_GE|T_LT|T_LE(X) expr(C). { A = new Stmt\Expr(@X, B, C); }
expr(A) ::= expr(B) T_IS T_NOT null(C). { A = new Stmt\Expr("!=", B, C); }
expr(A) ::= expr(B) T_IS null(C). { A = new Stmt\Expr("=", B, C); }
expr(A) ::= expr(B) T_PLUS|T_MINUS|T_TIMES|T_DIV|T_MOD(X) expr(C). { A = new Stmt\Expr(@X, B, C); }
expr(A) ::= colname(B) T_IN inner_select(X).    { A = new Stmt\Expr('in', B, X); }
expr(A) ::= colname(B) T_IN expr_list_par(X).   { A = new Stmt\Expr('in', B, X); }
expr(A) ::= case(B) . { A = B; }
expr(A) ::= term(B) . { A = B; }

case(A) ::= T_CASE case_options(X) T_END . { 
    X = array_merge(['CASE'], X);
    A = new Stmt\Expr(X);
}
case(A) ::= T_CASE case_options(X) T_ELSE expr(Y) T_END. { 
    X = array_merge(['CASE'], X, [Y]);
    A = new Stmt\Expr(X);
}

case_options(A) ::= case_options(X) WHEN expr(B) THEN expr(C) . { A = X; X[] = new Stmt\Expr("WHEN", B, C); }
case_options(A) ::= WHEN expr(B) THEN expr(C) . { A = array(new Stmt\Expr("WHEN", B, C)); }

term(A) ::= INTERVAL expr(C) ALPHA(X).  { A = new Stmt\Expr('timeinterval', C, X); }
term(A) ::= T_PLUS term(B).             { A = new Stmt\Expr('value', B); }
term(A) ::= T_MINUS NUMBER(B).          { A = new Stmt\Expr('value', -1 * B); }
term(A) ::= NUMBER(B).                  { A = new Stmt\Expr('value', 0+B); }
term(A) ::= null(B).                    { A = B; }
term(A) ::= function_call(B) .          { A = B; }
term(A) ::= alpha(B).                   { A = B; }
term(A) ::= colname(B) .                { A = new Stmt\Expr('value', B); }

null(A) ::= T_NULL.        { A = new Stmt\Expr('value', NULL);}

function_call(A) ::= ALPHA(C) expr_list_par_or_null(D) . { A = new Stmt\Expr('CALL', C, D); }

columns(A) ::= columns(B) COMMA alpha(C) . { A = B->addTerm(C); }
columns(A) ::= alpha(B) . { A = new Stmt\ExprList(B); }

expr_list_par_or_null (A) ::= expr_list_par(X).    { A = X; }
expr_list_par_or_null (A) ::= PAR_OPEN PAR_CLOSE.  { A = new Stmt\Expr('EMPTY', ''); }

expr_list_par_optional (A) ::= expr_list_par(X).    { A = X; }
expr_list_par_optional (A) ::= expr_list(X).        { A = X; }

expr_list_par_many(A) ::= expr_list_par_many(B) COMMA expr_list_par(C) . { A = B->addTerm(C); }
expr_list_par_many(A) ::= expr_list_par(C) . { A = new Stmt\ExprList(C); }

expr_list_par(A) ::= PAR_OPEN expr_list(X) PAR_CLOSE. { A = X; }
expr_list(A) ::= expr_list(B) COMMA expr(C). { A = B->addTerm(C); }
expr_list(A) ::= expr(B). { A = new Stmt\ExprList(B); }
/* expr_list(A) ::= . { A = new Stmt\ExprList ; } */

expr_list_as(A) ::= expr_list_as(B) COMMA expr_as(C). { A = B->addTerm(C); }
expr_list_as(A) ::= expr_as(B). { A = new Stmt\ExprList(B); }

expr_as(A) ::= expr(B) . { A = B; }
expr_as(A) ::= expr(B) T_AS alpha(C) .  { A = new Stmt\Expr('ALIAS', B, C); }
expr_as(A) ::= expr(B) alpha(C) .       { A = new Stmt\Expr('ALIAS', B, C); }

table_name(A) ::= colname(B) . { A = new Stmt\Table(B); }

colname(A) ::= alpha(B) T_DOT xalpha(C).        { A = new Stmt\ColumnName(B, C); }
colname(A) ::= xalpha(B).                       { A = new Stmt\ColumnName(B) ; }
colname(A) ::= variable(B).                     { A = B; }

alpha(A) ::= INTERVAL(X) .      { A = new Stmt\Expr('VALUE', @X); }
alpha(A) ::= T_STRING(B).       { A = new Stmt\Expr('VALUE', stripslashes(trim(B, "\r\t\n \"'"))); }
alpha(A) ::= ALPHA(B).          { A = new Stmt\Alpha(B); }
alpha(A) ::= COLUMN(B).         { A = new Stmt\Alpha(trim(B, "` \r\n\t")); }

xalpha(A) ::= alpha(X).     { A = X; }
xalpha(A) ::= T_TIMES.      { A = new Stmt\All; }

variable(A) ::= QUESTION. { A = new Stmt\VariablePlaceholder; }
variable(A) ::= T_DOLLAR|T_COLON ALPHA(X). { A = new Stmt\VariablePlaceholder(X); }
