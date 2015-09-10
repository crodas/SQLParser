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

stmt(A) ::= begin(B).           { A = B; }
stmt(A) ::= commit(B).          { A = B; }
stmt(A) ::= rollback(B).        { A = B; }
stmt(A) ::= drop(B).            { A = B; }
stmt(A) ::= select(B).          { A = B; }
stmt(A) ::= insert(B).          { A = B; }
stmt(A) ::= update(B).          { A = B; }
stmt(A) ::= delete(B).          { A = B; }
stmt(A) ::= alter_table(B).     { A = B; }
stmt(A) ::= create_table(B).    { A = B; }
stmt(A) ::= create_view(B).     { A = B; }
stmt(A) ::= . { A = null; }

begin(A) ::= BEGIN transaction_keyword.             { A = new SQL\BeginTransaction; }
begin(A) ::= SAVEPOINT alpha(B).                    { A = new SQL\BeginTransaction(B); }
commit(A) ::= commit_keyword transaction_keyword.   { A = new SQL\CommitTransaction; }
commit(A) ::= RELEASE SAVEPOINT alpha(B).           { A = new SQL\CommitTransaction(B); }
rollback(A) ::= ROLLBACK transaction_keyword.       { A = new SQL\RollbackTransaction; }
rollback(A) ::= ROLLBACK TO alpha(B).               { A = new SQL\RollbackTransaction(B); }

transaction_keyword ::= TRANSACTION.
transaction_keyword ::= .

commit_keyword ::= COMMIT. 
commit_keyword ::= T_END. 


inner_select(A) ::= PAR_OPEN inner_select(B) PAR_CLOSE . { A = B;}
inner_select(A) ::= PAR_OPEN select(B) PAR_CLOSE . { A = B;}

/** Select */
select(A) ::= SELECT select_opts(MM) expr_list_as(L) from(X) joins(J) where(W) group_by(GG) order_by(O) limit(LL) .  { 
    A = new SQL\Select(L);
    if (X)  {
        foreach (X as $table) {
            A->from($table[0], $table[1]);
        }
    }
    if (MM) A->setOptions(MM);
    if (W)  A->where(W);
    if (J)  A->joins(J);
    if (O)  A->orderBy(O);
    if (GG) A->groupBy(GG[0], GG[1]);
    if (LL) A->limit(LL[0], LL[1]);
}

select_opts(A) ::= select_opts(B) select_mod(C) . { A = B; A[] = C; }
select_opts(A) ::= . { A = array(); }
select_mod(A) ::= ALL|DISTINCT|DISTINCTROW|HIGH_PRIORITY|STRAIGHT_JOIN|SQL_SMALL_RESULT|SQL_BIG_RESULT|SQL_CACHE|SQL_CALC_FOUND_ROWS|SQL_BUFFER_RESULT|SQL_NO_CACHE(X). { A = strtoupper(@X); }

from(A) ::= FROM table_list(X). { A = X; }
from(A) ::= .

table_list(A) ::= table_list(B) COMMA table_with_alias(C) . { A = B; A[] = C; }
table_list(A) ::= table_with_alias(B). { A = [B]; }

table_with_alias(A) ::= inner_select(B) T_AS alpha(Y) .   { A = [B, Y]; }
table_with_alias(A) ::= inner_select(B) alpha(Y) .        { A = [B, Y]; }
table_with_alias(A) ::= table_name(X) T_AS alpha(Y) .     { A = [X, Y]; }
table_with_alias(A) ::= table_name(X) alpha(Y) .          { A = [X, Y]; }
table_with_alias(A) ::= table_name(X).                    { A = [X, NULL]; }

joins(A) ::= joins(B) join(C). { A = B; A[] = C; } 
joins(A) ::= . { A = []; }

join(A) ::= join_type(B) JOIN table_with_alias(C) join_condition(D). {
    A = B->setTable(C[0], C[1]); 
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

order_by_fields(A) ::= order_by_fields(B) COMMA order_by_field(C) . { A = B; A[] = C; }
order_by_fields(A) ::= order_by_field(B) . { A = [B]; }

order_by_field(A) ::= expr(X) DESC|ASC(Y) . { A = new Stmt\Expr(strtoupper(@Y), X); }
order_by_field(A) ::= expr(X) . { A = new Stmt\Expr("ASC", X); }

limit(A) ::= LIMIT expr(B) OFFSET expr(C).  { A = [B, C]; }
limit(A) ::= LIMIT expr(C) COMMA expr(B).   { A = [B, C]; }
limit(A) ::= LIMIT expr(B).{ A = [B, NULL]; }
limit(A) ::= . { A = NULL; }

group_by(A) ::= GROUP BY expr_list_par_optional(B) . { A = [B, null]; }
group_by(A) ::= GROUP BY expr_list_par_optional(B) HAVING expr(C). { A = [B, C]; }
group_by(A) ::= .

insert(A) ::= insert_stmt(X) select(S).                 { A = X; X->values(S); }
insert(A) ::= insert_stmt(X) inner_select(S)    .       { A = X; X->values(S); }
insert(A) ::= insert_stmt(X) VALUES expr_list_par_many(L).   { A = X; X->values(L); }
insert(A) ::= insert_stmt(X) set_expr(S). { 
    A = X; 
    $keys   = [];
    $values = [];
    foreach (S->getExprs() as $field) {
        $member = $field->getMembers();
        $keys[]   = $member[0];
        $values[] = $member[1];
    }
    X->values([$values])->fields($keys);
}

drop(A) ::= DROP TABLE table_list(X). {
    A = new SQL\Drop('TABLE', X);
}

delete(A) ::= DELETE FROM table_with_alias(T) where(W) order_by(O) limit(L). {
    A = new SQL\Delete(T[0], T[1]);
    if (W) A->where(W);
    if (O) A->orderBy(O);
    if (L) A->limit(L[0], L[1]);
}

update(A) ::= UPDATE table_list(B) joins(JJ) set_expr(S) where(W) order_by(O) limit(LL). {
    A = new SQL\Update(B, S);
    if (JJ) A->joins(JJ);
    if (W)  A->where(W);
    if (O) A->orderBy(O);
    if (LL) A->limit(LL[0], LL[1]);
}

insert_stmt(A) ::= INSERT|REPLACE(X) INTO insert_table(T). { 
    A = new SQL\Insert(@X);
    A->into(T[0])->fields(T[1]); }
insert_stmt(A) ::= INSERT|REPLACE(X) insert_table(T). { 
    A = new SQL\Insert(@X);
    A->into(T[0]); 
}

insert_table(A) ::= table_name(B) . { A = [B, []];}
insert_table(A) ::= table_name(B) PAR_OPEN columns(L) PAR_CLOSE.  { A = [B, L]; }

set_expr(A) ::= SET set_expr_values(X). { A = X; }
set_expr_values(A) ::= set_expr_values(B) COMMA assign(C) . { A = B->addTerm(C); }
set_expr_values(A) ::= assign(C) .      { A = new Stmt\ExprList(C); }
assign(A) ::= term_colname(B) T_EQ expr(X) . { 
    A = new Stmt\Expr("=", B, X); 
}

create_view(A) ::= CREATE VIEW colname(N) T_AS select(S). {
    A = new SQL\View(N, S);
}

create_table(A) ::= CREATE TABLE alpha(N) PAR_OPEN create_fields(X) PAR_CLOSE table_opts(O) . {
    A = new SQL\Table(N, X, O);
}

table_opts(A) ::= table_opts(B) table_opt(C). { A = array_merge(B, C); }
table_opts(A) ::= . { A = array(); }

table_opt(A) ::= table_key(B) T_EQ term(C) . {  
    A[implode(" ", B)] = C->getMember(0); 
}

table_key(A) ::= table_key(B) alpha(C). { A = B; A[] = C; }
table_key(A) ::= alpha(B). { A = [B]; }

create_fields(A) ::= create_fields(B) COMMA create_column(C). { A = B; A[] = C; }
create_fields(A) ::= create_column(C) . { A = array(C); }

create_column(A) ::= PRIMARY KEY expr_list_par(X). {
    A = ['primary', X];
}
create_column(A) ::= UNIQUE KEY alpha(C) expr_list_par(X). {
    A = ['unique', C, X];
}
create_column(A) ::= KEY alpha(C) expr_list_par(X). {
    A = ['key', C, X];
}

create_column(A) ::= alpha(B) data_type(C) column_mods(X) . { 
    A = new Stmt\Column(B, C[0], C[1]);
    foreach (X as $setting) {
        if (is_array($setting)) {
            A->{$setting[0]}($setting[1]);
        } else {
            A->$setting();
        }
    }
}

data_type(A) ::= alpha(B) . {
    A = [B, NULL];
}

data_type(A) ::= alpha(B) PAR_OPEN NUMBER(X) PAR_CLOSE .{
    A = [B, X];
}

column_mods(A) ::= column_mods(B) column_mod(C). { A = B; A[] = C; }
column_mods(A) ::= . { A = []; }

column_mod(A) ::= T_DEFAULT expr(C).  { A = ['defaultValue', C]; }
column_mod(A) ::= COLLATE expr(C).  { A = ['collate', C]; }
column_mod(A) ::= PRIMARY KEY.      { A = 'primaryKey'; }
column_mod(A) ::= T_NOT T_NULL.     { A = 'notNull'; }
column_mod(A) ::= AUTO_INCREMENT.   { A = 'autoincrement'; }

/** Expression */
expr(A) ::= expr(B) T_AND expr(C). { A = new Stmt\Expr('and', B, C); }
expr(A) ::= expr(B) T_OR expr(C). { A = new Stmt\Expr('or', B, C); }
expr(A) ::= T_NOT expr(C). { A = new Stmt\Expr('not', C); }
expr(A) ::= PAR_OPEN expr(B) PAR_CLOSE.    { A = new Stmt\Expr('expr', B); }
expr(A) ::= term_select(B) . { A = B; }
expr(A) ::= expr(B) T_EQ|T_LIKE|T_NE|T_GT|T_GE|T_LT|T_LE(X) expr(C). { A = new Stmt\Expr(@X, B, C); }
expr(A) ::= expr(B) T_IS T_NOT null(C). { A = new Stmt\Expr("!=", B, C); }
expr(A) ::= expr(B) T_IS null(C). { A = new Stmt\Expr("=", B, C); }
expr(A) ::= expr(B) T_PLUS|T_MINUS|T_TIMES|T_DIV|T_MOD(X) expr(C). { A = new Stmt\Expr(@X, B, C); }
expr(A) ::= expr(B) in(Y) term_select(X).       { A = new Stmt\Expr(Y, B, X); }
expr(A) ::= expr(B) in(Y) expr_list_par(X).     { A = new Stmt\Expr(Y, B, new Stmt\Expr('expr', X)); }
expr(A) ::= case(B) . { A = B; }
expr(A) ::= term(B) . { A = B; }

in(A) ::= T_NOT T_IN. { A = 'nin'; }
in(A) ::= T_IN. { A = 'in'; }

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
term(A) ::= T_STRING(B).                { A = new Stmt\Expr('value', trim(B, "'\"")); }
term(A) ::= alpha(B).                   { A = new Stmt\Expr('column', B); }
term(A) ::= term_colname(B).            { A = B; }
term_select(A)  ::= inner_select(B).    { A = new Stmt\Expr('expr', B); }
term_colname(A) ::= colname(B) .                { 
    if (B instanceof Stmt\VariablePlaceholder) {
        A = B;
    } else if (is_array(B)) {
        A = new Stmt\Expr('column', B[0], B[1]); 
    } else {
        A = new Stmt\Expr('column', B);
    }
}

null(A) ::= T_NULL.        { A = new Stmt\Expr('value', NULL);}

function_call(A) ::= ALPHA(C) expr_list_par_or_null(D) . { A = new Stmt\Expr('CALL', C, D); }

columns(A) ::= columns(B) COMMA alpha(C) . { A = B->addTerm(C); }
columns(A) ::= alpha(B) . { A = new Stmt\ExprList(B); }

expr_list_par_or_null (A) ::= expr_list_par(X).    { A = X; }
expr_list_par_or_null (A) ::= PAR_OPEN PAR_CLOSE.  { A = new Stmt\ExprList(); }

expr_list_par_optional (A) ::= expr_list_par(X).    { A = X; }
expr_list_par_optional (A) ::= expr_list(X).        { A = X; }

expr_list_par_many(A) ::= expr_list_par_many(B) COMMA expr_list_par(C) . { A = B; A[] = C; }
expr_list_par_many(A) ::= expr_list_par(C) . { A = [C]; }

expr_list_par(A) ::= PAR_OPEN expr_list(X) PAR_CLOSE. { A = X; }
expr_list(A) ::= expr_list(B) COMMA expr(C). { A = B->addTerm(C); }
expr_list(A) ::= expr(B). { A = new Stmt\ExprList(B); }
/* expr_list(A) ::= . { A = new Stmt\ExprList ; } */

expr_list_as(A) ::= expr_list_as(B) COMMA expr_as(C). { A = B; A[] = C; }
expr_list_as(A) ::= expr_as(B). { A = [B]; }

expr_as(A) ::= expr(B) . { A = [B]; }
expr_as(A) ::= expr(B) T_AS alpha(C) .  { A = [B, C]; }
expr_as(A) ::= expr(B) alpha(C) .       { A = [B, C]; }

table_name(A) ::= colname(B) . { A = B; }

colname(A) ::= alpha(B) T_DOT alpha_or_all(C).        { A = [B, C]; }
colname(A) ::= alpha_or_all(B).                       { A = B; }
colname(A) ::= variable(B).                     { A = B; }

alpha(A) ::= T_DEFAULT(X) .     { A = @X; }
alpha(A) ::= INTERVAL(X) .      { A = @X; }
alpha(A) ::= AUTO_INCREMENT(X). { A = @X; }
alpha(A) ::= ALPHA(B).          { A = B; }
alpha(A) ::= COLUMN(B).         { A = trim(B, "` \r\n\t"); }

alpha_or_all(A) ::= alpha(X).     { A = X; }
alpha_or_all(A) ::= T_TIMES.      { A = new Stmt\Expr("ALL"); }

variable(A) ::= QUESTION. { A = new Stmt\VariablePlaceholder; }
variable(A) ::= T_DOLLAR|T_COLON ALPHA(X). { A = new Stmt\VariablePlaceholder(X); }
