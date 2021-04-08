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

%left T_OR.
%left T_AND.
%right T_NOT.
%left T_QUESTION T_COLON.
%nonassoc T_EQ T_LIKE T_GLOB T_NE.
%nonassoc T_GT T_GE T_LT T_LE .
%nonassoc T_IN.
%left T_PLUS T_MINUS T_CONCAT.
%left T_TIMES T_DIV T_MOD.
%left T_PIPE T_BITWISE T_FILTER_PIPE.

query ::= stmts(A). { $this->body = A; }

stmts(A) ::= stmts(B) T_SEMICOLON stmt(C) . { A = B; A[] = C; }
stmts(A) ::= stmt(C) .  { A = [C];  }

stmt(A) ::= T_PAR_OPEN stmt(B) T_PAR_CLOSE . { A = B; }

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
stmt(A) ::= create_index(B).    { A = B; }
stmt(A) ::= create_view(B).     { A = B; }
stmt(A) ::= . { A = null; }

begin(A) ::= T_BEGIN transaction_keyword.             { A = new SQL\BeginTransaction; }
begin(A) ::= T_SAVEPOINT alpha(B).                    { A = new SQL\BeginTransaction(B); }
commit(A) ::= commit_keyword transaction_keyword.   { A = new SQL\CommitTransaction; }
commit(A) ::= T_RELEASE T_SAVEPOINT alpha(B).           { A = new SQL\CommitTransaction(B); }
rollback(A) ::= T_ROLLBACK transaction_keyword.       { A = new SQL\RollbackTransaction; }
rollback(A) ::= T_ROLLBACK T_TO alpha(B).               { A = new SQL\RollbackTransaction(B); }

transaction_keyword ::= T_TRANSACTION|T_WORK.
transaction_keyword ::= .

commit_keyword ::= T_COMMIT.
commit_keyword ::= T_END.


inner_select(A) ::= T_PAR_OPEN inner_select(B) T_PAR_CLOSE . { A = B;}
inner_select(A) ::= T_PAR_OPEN select(B) T_PAR_CLOSE . { A = B;}

alter_table(A) ::= T_ALTER T_TABLE table_name(X) alter_operation(Y). { A = Y->setTableName(X); }

alter_operation(A) ::= T_DROP T_PRIMARY T_KEY . { A = new SQL\AlterTable\DropPrimaryKey; }
alter_operation(A) ::= T_DROP T_KEY|T_INDEX colname(Y) . { A = new SQL\AlterTable\DropIndex(Y); }
alter_operation(A) ::= alter_change(X) T_SET T_DEFAULT expr(V) . { A = new SQL\AlterTable\SetDefault(X, V); }
alter_operation(A) ::= alter_change(X) T_DROP T_DEFAULT. { A = new SQL\AlterTable\SetDefault(X, NULL); }
alter_operation(A) ::= alter_change(X) create_column(Y) after(B). { A = new SQL\AlterTable\ChangeColumn(X, Y, B); }
alter_operation(A) ::= T_MODIFY create_column(Y) after(B) . { A = new SQL\AlterTable\ChangeColumn(Y->getName(), Y, B); }
alter_operation(A) ::= T_ADD optional_column create_column(Y) after(X). { A = new SQL\AlterTable\AddColumn(Y, X); }
alter_operation(A) ::= T_DROP optional_column colname(X) . { A = new SQL\AlterTable\DropColumn(X); }
alter_operation(A) ::= T_RENAME to colname(X) . { A = new SQL\AlterTable\RenameTable(X); }
alter_operation(A) ::= T_RENAME T_KEY|T_INDEX colname(F) T_TO colname(X) . { A = new SQL\AlterTable\RenameIndex(F, X); }
alter_operation(A) ::= T_ADD index_type(B) T_KEY|T_INDEX colname(C) index_list(X) . { A = new SQL\AlterTable\AddIndex(B, C, X); }

create_index(A) ::= T_CREATE index_type(B) T_INDEX colname(C) T_ON colname(T) index_list(X) . {
    A = new SQL\AlterTable\AddIndex(B, C, X);
    A->setTableName(T);
}

index_type(A) ::= T_UNIQUE . { A = 'UNIQUE'; }
index_type(A) ::= . { A = ''; }

to ::= T_TO|T_AS .
to ::= .

alter_change(A) ::= T_CHANGE optional_column colname(X) . { A = X; }

optional_column ::= T_COLUMN .
optional_column ::= .

after(A) ::= T_FIRST . { A = TRUE; }
after(A) ::= T_AFTER colname(Y) . { A = Y; }
after(A) ::= .


/** Select */
select(A) ::= T_SELECT select_opts(MM) expr_list_as(L) from(X) joins(J) where(W) group_by(GG) order_by(O) limit(LL) .  {
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
select_mod(A) ::= T_ALL|T_DISTINCT|DISTINCTROW|HIGH_PRIORITY|STRAIGHT_JOIN|SQL_SMALL_RESULT|SQL_BIG_RESULT|SQL_CACHE|SQL_CALC_FOUND_ROWS|SQL_BUFFER_RESULT|SQL_NO_CACHE(X). { A = strtoupper(X); }

from(A) ::= T_FROM table_list(X). { A = X; }
from(A) ::= .

table_list(A) ::= table_list(B) T_COMMA table_with_alias(C) . { A = B; A[] = C; }
table_list(A) ::= table_with_alias(B). { A = [B]; }

table_with_alias(A) ::= inner_select(B) T_AS alpha(Y) .   { A = [B, Y]; }
table_with_alias(A) ::= inner_select(B) alpha(Y) .        { A = [B, Y]; }
table_with_alias(A) ::= table_name(X) T_AS alpha(Y) .     { A = [X, Y]; }
table_with_alias(A) ::= table_name(X) alpha(Y) .          { A = [X, Y]; }
table_with_alias(A) ::= table_name(X).                    { A = [X, NULL]; }

joins(A) ::= joins(B) join(C). { A = B; A[] = C; }
joins(A) ::= . { A = []; }

join(A) ::= join_type(B) T_JOIN table_with_alias(C) join_condition(D). {
    A = B->setTable(C[0], C[1]);
    if (is_array(D) && D[0]) {
        A->{D[0]}(D[1]);
    }
}

join_type(A) ::= join_prefix(B) T_INNER.                      { A = new Stmt\Join('INNER', B); }
join_type(A) ::= join_prefix(B) T_LEFT join_postfix(C).       { A = new Stmt\Join('LEFT', B, C); }
join_type(A) ::= join_prefix(B) T_RT_IGHT join_postfix(C).      { A = new Stmt\Join('RIGHT', B, C); }
join_type(A) ::= join_prefix(B).                            { A = new Stmt\Join('INNER', B); }

join_prefix(A) ::= T_NATURAL. { A = 'NATURAL'; }
join_prefix(A) ::= . { A = ''; }
join_postfix(A) ::= T_OUTER . { A = 'OUTER'; }
join_postfix(A) ::= . { A = ''; }

join_condition(A) ::= T_ON expr(B).           { A = ['ON', B]; }
join_condition(A) ::= T_USING columns(B) .    { A = ['USING', B]; }
join_condition(A) ::= T_USING T_PAR_OPEN columns(B) T_PAR_CLOSE .    { A =['USING',  B]; }
join_condition(A) ::= . { A = NULL; }

where(A) ::= T_WHERE expr(B) . { A = B; }
where(A) ::= . { A = NULL; }

order_by(A) ::= T_ORDER T_BY order_by_fields(B) . {
    A = new Stmt\ExprList;
    A->setExprs(B);
}
order_by(A) ::= . { A = NULL; }

order_by_fields(A) ::= order_by_fields(B) T_COMMA order_by_field(C) . { A = B; A[] = C; }
order_by_fields(A) ::= order_by_field(B) . { A = [B]; }

order_by_field(A) ::= expr(X) T_DESC|T_ASC(Y) . {
    A = new Stmt\Expr(strtoupper(Y), X);
}
order_by_field(A) ::= expr(X) . { A = new Stmt\Expr("ASC", X); }

limit(A) ::= T_LIMIT expr(B) T_OFFSET expr(C).  { A = [B, C]; }
limit(A) ::= T_LIMIT expr(C) T_COMMA expr(B).   { A = [B, C]; }
limit(A) ::= T_LIMIT expr(B).{ A = [B, NULL]; }
limit(A) ::= . { A = NULL; }

group_by(A) ::= T_GROUP T_BY expr_list_par_optional(B) . { A = [B, null]; }
group_by(A) ::= T_GROUP T_BY expr_list_par_optional(B) T_HAVING expr(C). { A = [B, C]; }
group_by(A) ::= .

insert(A) ::= insert_stmt(X) select(S).                 { A = X; X->values(S); }
insert(A) ::= insert_stmt(X) inner_select(S)    .       { A = X; X->values(S); }
insert(A) ::= insert_stmt(X) T_VALUES expr_list_par_many(L) on_dup(DU).   {
    A = X; X->values(L);
    if (DU) A->onDuplicate(DU);
}
insert(A) ::= insert_stmt(X) set_expr(S) on_dup(DU). {
    A = X;
    $keys   = new Stmt\ExprList;
    $values = [];
    foreach (S->getExprs() as $field) {
        $member = $field->getMembers();
        $keys->addTerm($member[0]);
        $values[] = $member[1];
    }
    X->values([$values])->fields($keys);
    if (DU) A->onDuplicate(DU);
}

drop(A) ::= T_DROP T_TABLE table_list(X). {
    A = new SQL\Drop('TABLE', X);
}

delete(A) ::= T_DELETE T_FROM table_with_alias(T) where(W) order_by(O) limit(L). {
    A = new SQL\Delete(T[0], T[1]);
    if (W) A->where(W);
    if (O) A->orderBy(O);
    if (L) A->limit(L[0], L[1]);
}

update(A) ::= T_UPDATE table_list(B) joins(JJ) set_expr(S) where(W) order_by(O) limit(LL). {
    A = new SQL\Update(B, S);
    if (JJ) A->joins(JJ);
    if (W)  A->where(W);
    if (O) A->orderBy(O);
    if (LL) A->limit(LL[0], LL[1]);
}

insert_stmt(A) ::= T_INSERT|REPLACE(X) T_INTO insert_table(T). {
    A = new SQL\Insert(X);
    A->into(T[0]);
    if (T[1]) A->fields(T[1]);
}
insert_stmt(A) ::= T_INSERT|REPLACE(X) insert_table(T). {
    A = new SQL\Insert(X);
    A->into(T[0]);
    if (T[1]) A->fields(T[1]);
}

insert_table(A) ::= table_name(B) . { A = [B, null];}
insert_table(A) ::= table_name(B) T_PAR_OPEN columns(L) T_PAR_CLOSE.  { A = [B, L]; }

on_dup(A) ::= T_ON T_DUPLICATE T_KEY T_UPDATE set_expr_values(X) . { A = X; }
on_dup(A) ::= . { A = NULL; }


set_expr(A) ::= T_SET set_expr_values(X). { A = X; }
set_expr_values(A) ::= set_expr_values(B) T_COMMA assign(C) . { A = B->addTerm(C); }
set_expr_values(A) ::= assign(C) .      { A = new Stmt\ExprList(C); }
assign(A) ::= term_colname(B) T_EQ expr(X) . {
    A = new Stmt\Expr("=", B, X);
}

create_view(A) ::= T_CREATE T_VIEW colname(N) T_AS select(S). {
    A = new SQL\View(N, S);
}

create_table(A) ::= T_CREATE T_TABLE colname(N) T_PAR_OPEN create_fields(X) T_PAR_CLOSE table_opts(O) . {
    A = new SQL\Table(N, X, O);
}

table_opts(A) ::= table_opts(B) table_opt(C). { A = array_merge(B, C); }
table_opts(A) ::= . { A = array(); }

table_opt(A) ::= table_key(B) T_EQ term(C) . {
    A[implode(" ", B)] = C->getMember(0);
}

table_key(A) ::= table_key(B) alpha(C). { A = B; A[] = C; }
table_key(A) ::= alpha(B). { A = [B]; }

create_fields(A) ::= create_fields(B) T_COMMA create_column(C). { A = B; A[] = C; }
create_fields(A) ::= create_column(C) . { A = array(C); }

create_column(A) ::= T_PRIMARY T_KEY index_list(X). {
    A = ['primary', X];
}
create_column(A) ::= T_UNIQUE T_KEY colname(C) index_list(X). {
    A = ['unique', C, X];
}
create_column(A) ::= T_KEY colname(C) index_list(X). {
    A = ['key', C, X];
}

index_list(A) ::= T_PAR_OPEN indexes(B) T_PAR_CLOSE . { A = B; }
indexes(A) ::= indexes(B) T_COMMA index_col_name(C)  . { A = B->addTerm(C); }
indexes(A) ::= index_col_name(B) . { A = new Stmt\ExprList(B); }

index_col_name(A) ::= term_colname(B) length(C) order(D) . {
    A = new Stmt\Expr('INDEX', B, C, D);
}

order(Y)  ::= T_DESC|T_ASC(X) . { Y = strtoupper(X); }
order(Y)  ::= . { Y = NULL; }
length(A) ::= T_PAR_OPEN T_NUMBER(B) T_PAR_CLOSE . { A = B; }
length(A) ::= . { A = NULL; }

create_column(A) ::= colname(B) data_type(C) column_mods(X) . {
    A = new Stmt\Column(B, C[0], C[1], C[2]);
    foreach (X as $setting) {
        if (is_array($setting)) {
            A->{$setting[0]}($setting[1]);
        } else {
            A->$setting();
        }
    }
}

data_type(A) ::= alpha(B) unsigned(Y) . {
    A = [B, NULL, Y];
}

data_type(A) ::= alpha(B) T_PAR_OPEN T_NUMBER(X) T_PAR_CLOSE unsigned(Y) .{
    A = [B, X, Y];
}

data_type(A) ::= alpha(B) T_PAR_OPEN T_NUMBER(X) T_PAR_CLOSE unsigned(Y) .{
    A = [B, X, Y];
}

unsigned(A) ::= . { A = ''; }
unsigned(A) ::= T_UNSIGNED(B) . { A = B; }

column_mods(A) ::= column_mods(B) column_mod(C). { A = B; A[] = C; }
column_mods(A) ::= . { A = []; }

column_mod(A) ::= T_DEFAULT term(C).    { A = ['defaultValue', C]; }
column_mod(A) ::= T_COLLATE term(C).      { A = ['collate', C]; }
column_mod(A) ::= T_PRIMARY T_KEY.          { A = 'primaryKey'; }
column_mod(A) ::= T_NOT T_NULL.         {    A = 'notNull'; }
column_mod(A) ::= T_AUTO_INCREMENT.       { A = 'autoincrement'; }

/** Expression */
expr(A) ::= expr(B) T_AND expr(C). { A = new Stmt\Expr('and', B, C); }
expr(A) ::= expr(B) T_OR expr(C). { A = new Stmt\Expr('or', B, C); }
expr(A) ::= T_NOT expr(C). {
    if (C->getType() === 'IS NULL') {
        $parts = C->getMembers();
        A = new Stmt\Expr('IS NOT NULL', $parts[0]);
        return;
    }
    A = new Stmt\Expr('not', C);
}
expr(A) ::= T_PAR_OPEN expr(B) T_PAR_CLOSE.    { A = new Stmt\Expr('expr', B); }
expr(A) ::= term_select(B) . { A = B; }
expr(A) ::= expr(B) T_EQ|T_NE|T_GT|T_GE|T_LT|T_LE(X) expr(C). {
    $members = B->getMembers();
    if  (B->getType() === 'VALUE' && count($members) === 2&& $members[1] == 2) {
        B = new Stmt\Expr('COLUMN', $members[0]);
    }
    A = new Stmt\Expr(X, B, C);
}
expr(A) ::= expr(B) T_IS T_NOT null(C). { A = new Stmt\Expr("IS NOT NULL", B); }
expr(A) ::= expr(B) T_IS null(C). { A = new Stmt\Expr("IS NULL", B); }
expr(A) ::= expr(B) T_PLUS|T_MINUS|T_TIMES|T_DIV|T_MOD(X) expr(C). { A = new Stmt\Expr(X, B, C); }
expr(A) ::= expr(B) T_NOT T_BETWEEN expr(C) T_AND expr(D) . {
    A = new Stmt\Expr('not between', B, C, D);
}
expr(A) ::= expr(B) T_BETWEEN expr(C) T_AND expr(D) . {
    A = new Stmt\Expr('between', B, C, D);
}
expr(A) ::= expr(B) negable(X) expr(C). {
    $members = B->getMembers();
    if  (B->getType() === 'VALUE' && count($members) === 2&& $members[1] == 2) {
        B = new Stmt\Expr('COLUMN', $members[0]);
    }
    A = new Stmt\Expr(X, B, C);
}
expr(A) ::= expr(B) in(Y) term_select(X).       { A = new Stmt\Expr(Y, B, X); }
expr(A) ::= expr(B) in(Y) expr_list_par(X).     { A = new Stmt\Expr(Y, B, new Stmt\Expr('expr', X)); }
expr(A) ::= case(B) . { A = B; }
expr(A) ::= term(B) . { A = B; }

in(A) ::= T_IN . { A = 'IN'; }
in(A) ::= T_NOT T_IN. { A = 'NOT IN'; }

negable(A) ::= T_NOT negable_expr(B) . { A = 'NOT ' . B; }
negable(A) ::= negable_expr(B) . { A = B; }

negable_expr(A) ::= T_IS   . { A = 'IS'; }
negable_expr(A) ::= T_LIKE . { A = 'LIKE'; }
negable_expr(A) ::= T_LIKE T_BINARY . { A = 'GLOB'; }
negable_expr(A) ::= T_GLOB. { A = 'GLOB'; }


case(A) ::= T_CASE case_options(X) T_END . {
    X = array_merge(['CASE'], X);
    A = new Stmt\Expr(X);
}
case(A) ::= T_CASE case_options(X) T_ELSE expr(Y) T_END. {
    X = array_merge(['CASE'], X, [Y]);
    A = new Stmt\Expr(X);
}

case_options(A) ::= case_options(X) T_WHEN expr(B) T_THEN expr(C) . { A = X; X[] = new Stmt\Expr("WHEN", B, C); }
case_options(A) ::= T_WHEN expr(B) T_THEN expr(C) . { A = array(new Stmt\Expr("WHEN", B, C)); }

term(A) ::= T_INTERVAL expr(C) ALPHA(X).  { A = new Stmt\Expr('timeinterval', C, X); }
term(A) ::= T_PLUS term(B).             { A = new Stmt\Expr('value', B); }
term(A) ::= T_MINUS T_NUMBER(B).          { A = new Stmt\Expr('value', -1 * B); }
term(A) ::= T_NUMBER(B).                  { A = new Stmt\Expr('value', 0+B); }
term(A) ::= null(B).                    { A = B; }
term(A) ::= function_call(B) .          { A = B; }
term(A) ::= T_STRING1(B).                { A = new Stmt\Expr('value', trim(B, "'\""), 1); }
term(A) ::= T_STRING2(B).                { A = new Stmt\Expr('value', trim(B, "'\""), 2); }
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

function_call(A) ::= ALPHA(C) expr_list_par_or_null(D) . {
    if (strtolower(C) === 'isnull') {
        $parts = D->getExprs();
        if (!empty($parts[0]) && $parts[0]->getType() === 'COLUMN') {
            // This is a "isnull" function call, we must convert
            // `isnull(col)` to `col IS NULL` (which is the correct
            // SQL-standard way of representing that statement)
            A = new Stmt\Expr('IS NULL', $parts[0]);
            return;
        }
    }
    A = new Stmt\Expr('CALL', C, D);
}

columns(A) ::= columns(B) T_COMMA alpha(C) . { A = B->addTerm(C); }
columns(A) ::= alpha(B) . { A = new Stmt\ExprList(B); }

expr_list_par_or_null (A) ::= expr_list_par(X).    { A = X; }
expr_list_par_or_null (A) ::= T_PAR_OPEN T_PAR_CLOSE.  { A = new Stmt\ExprList(); }

expr_list_par_optional (A) ::= expr_list_par(X).    { A = X; }
expr_list_par_optional (A) ::= expr_list(X).        { A = X; }

expr_list_par_many(A) ::= expr_list_par_many(B) T_COMMA expr_list_par(C) . { A = B; A[] = C; }
expr_list_par_many(A) ::= expr_list_par(C) . { A = [C]; }

expr_list_par(A) ::= T_PAR_OPEN expr_list(X) T_PAR_CLOSE. { A = X; }
expr_list(A) ::= expr_list(B) T_COMMA expr(C). { A = B->addTerm(C); }
expr_list(A) ::= expr(B). { A = new Stmt\ExprList(B); }
/* expr_list(A) ::= . { A = new Stmt\ExprList ; } */

expr_list_as(A) ::= expr_list_as(B) T_COMMA expr_as(C). { A = B; A[] = C; }
expr_list_as(A) ::= expr_as(B). { A = [B]; }

expr_as(A) ::= expr(B) . { A = [B]; }
expr_as(A) ::= expr(B) T_AS alpha(C) .  { A = [B, C]; }
expr_as(A) ::= expr(B) alpha(C) .       { A = [B, C]; }

table_name(A) ::= colname(B) . { A = B; }

colname(A) ::= alpha(B) T_DOT alpha_or_all(C).  { A = [B, C]; }
colname(A) ::= alpha_or_all(B).                 { A = B; }
colname(A) ::= T_STRING1(B).                     { A = B; }
colname(A) ::= T_STRING2(B).                     { A = B; }
colname(A) ::= variable(B).                     { A = B; }

alpha(A) ::= T_DEFAULT(X) .     { A = X; }
alpha(A) ::= T_COLLATE(X) .       { A = X; }
alpha(A) ::= INTERVAL(X) .      { A = X; }
alpha(A) ::= T_AUTO_INCREMENT(X). { A = X; }
alpha(A) ::= ALPHA(B).          { A = B; }
alpha(A) ::= COLUMN(B).         { A = trim(B, "` \r\n\t"); }

alpha_or_all(A) ::= alpha(X).     { A = X; }
alpha_or_all(A) ::= T_TIMES.      { A = new Stmt\Expr("ALL"); }

variable(A) ::= T_QUESTION. { A = new Stmt\VariablePlaceholder; }
variable(A) ::= T_DOLLAR|T_COLON variable_name(X). { A = new Stmt\VariablePlaceholder(X); }

variable_name(A) ::= ALPHA(X) . { A = X; }
variable_name(A) ::= T_LIMIT|T_INSERT|T_UPDATE|T_FROM|T_SELECT|T_COLLATE|T_AUTO_INCREMENT|T_DEFAULT|PRIMARY|T_OFFSET|T_KEY(X) . { A = X; }
