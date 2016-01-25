<?php
/*
   The MIT License (MIT)

   Copyright (c) 2015 César Rodas

   Permission is hereby granted, free of charge, to any person obtaining a copy
   of this software and associated documentation files (the "Software"), to deal
   in the Software without restriction, including without limitation the rights
   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   copies of the Software, and to permit persons to whom the Software is
   furnished to do so, subject to the following conditions:

   The above copyright notice and this permission notice shall be included in
   all copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   THE SOFTWARE.
*/

namespace SQLParser;

use SQLParser_Parser as P;
use Exception;

class Lexer
{
    private $data;
    private $N;
    public $token;
    public $value;
    private $line;

    function __construct($data) {
        $this->data = trim($data);
        $this->N    = 0;
        $this->line = 1;
    }

/*!lex2php

%input $this->data
%counter $this->N
%token $this->token
%value $this->value
%line $this->line
%caseinsensitive 1
%matchlongest 1


whitespace = /[ \t\n]+/

comment = /\-\-[^\n]+/
number  = /[0-9]+(\.[0-9]+)?|0x[0-9a-fA-F]+/

string1 = /SINGLE_QUOTE[^SINGLE_QUOTE\\]*(?:\\.[^SINGLE_QUOTE\\]*)*SINGLE_QUOTE(?=[ \t\r]*[\n#;])/
string2 = /"[^"\\]*(?:\\.[^"\\]*)*"(?=[ \t\r]*[\n#;])/
string3 = /`[^`\\]*(?:\\.[^`\\]*)*`(?=[ \t\r]*[\n#;])/
alpha   = /[a-z_][a-z0-9_]*/

*/
/*!lex2php
  %statename YYINITIAL
  whitespace {
    return false;
  }
  comment   { $this->token = "comment"; }
  "when"    { $this->token = P::WHEN; }
  "unsigned" { $this->token = P::T_UNSIGNED; }
  "case"    { $this->token = P::T_CASE; }
  "create"  { $this->token = P::CREATE; }
  "then"    { $this->token = P::THEN; }
  "default" { $this->token = P::T_DEFAULT; }
  "else"    { $this->token = P::T_ELSE; }
  "modify"  { $this->token = P::MODIFY; }
  "auto_increment" { $this->token = P::AUTO_INCREMENT; }
  "collate" { $this->token = P::COLLATE; }
  "end"     { $this->token = P::T_END; }
  "null"    { $this->token = P::T_NULL; }
  "select"  { $this->token = P::SELECT; }
  "group"   { $this->token = P::GROUP;  }
  "insert"  { $this->token = P::INSERT; }
  "update"  { $this->token = P::UPDATE; }
  "delete"  { $this->token = P::DELETE; }
  "into"    { $this->token = P::INTO; }
  "left"    { $this->token = P::LEFT; }
  "right"   { $this->token = P::RIGHT; }
  "inner"   { $this->token = P::INNER; }
  "join"    { $this->token = P::JOIN; }
  "from"    { $this->token = P::FROM;   }
  "limit"   { $this->token = P::LIMIT;  }
  "delete"  { $this->token = P::DELETE; }
  "offset"  { $this->token = P::OFFSET; }
  "values"  { $this->token = P::VALUES; }
  "set"     { $this->token = P::SET; }
  "drop"    { $this->token = P::DROP; }
  "table"   { $this->token = P::TABLE; }
  "not"     { $this->token = P::T_NOT; }
  ">="      { $this->token = P::T_GE; }
  "<="      { $this->token = P::T_LE; }
  "%"       { $this->token = P::T_MOD; }
  "/"       { $this->token = P::T_DIV; }
  ">"       { $this->token = P::T_GT; }
  "<"       { $this->token = P::T_LT; }
  "("       { $this->token = P::PAR_OPEN; }
  ")"       { $this->token = P::PAR_CLOSE; }
  ";"       { $this->token = P::SEMICOLON; }
  "*"       { $this->token = P::T_TIMES; }
  "+"       { $this->token = P::T_PLUS; }
  "-"       { $this->token = P::T_MINUS; }
  "="       { $this->token = P::T_EQ; }
  "?"       { $this->token = P::QUESTION; }
  "$"       { $this->token = P::T_DOLLAR; }
  ":"       { $this->token = P::T_COLON; }
  "."       { $this->token = P::T_DOT; }
  ","       { $this->token = P::COMMA; }
  "on"      { $this->token = P::ON; }
  "duplicate" { $this->token = P::DUPLICATE; }
  "in"      { $this->token = P::T_IN; }
  "all"     { $this->token = P::ALL; }
  "distinct"    { $this->token = P::DISTINCT; }
  "natural"     { $this->token = P::NATURAL; }
  "outer"       { $this->token = P::OUTER; }
  "using"       { $this->token = P::USING; }
  "interval"    { $this->token = P::INTERVAL; }
  "having"  { $this->token = P::HAVING; }
  "where"   { $this->token = P::WHERE; }
  "view"    { $this->token = P::VIEW; }
  "like"    { $this->token = P::T_LIKE; }
  "order"   { $this->token = P::ORDER; }
  "primary" { $this->token = P::PRIMARY; }
  "column"  { $this->token = P::T_COLUMN; }
  "first"   { $this->token = P::T_FIRST ; }
  "after"   { $this->token = P::T_AFTER; }
  "change"  { $this->token = P::CHANGE; }
  "index"   { $this->token = P::INDEX; }
  "add"     { $this->token = P::ADD; }
  "alter"   { $this->token = P::ALTER; }
  "unique"  { $this->token = P::UNIQUE; }
  "key"     { $this->token = P::KEY; }
  "desc"    { $this->token = P::DESC; }
  "asc"     { $this->token = P::ASC; }
  "by"      { $this->token = P::BY; }
  "and"     { $this->token = P::T_AND; }
  "or"      { $this->token = P::T_OR; }
  "is"      { $this->token = P::T_IS; }
  "||"      { $this->token = P::T_OR; }
  "!="      { $this->token = P::T_NE; }
  "begin"   { $this->token = P::BEGIN; }
  "transaction" { $this->token = P::TRANSACTION; }
  "commit"      { $this->token = P::COMMIT; }
  "rollback"    { $this->token = P::ROLLBACK; }
  "savepoint"   { $this->token = P::SAVEPOINT; }
  "release"     { $this->token = P::RELEASE; }
  "to"          { $this->token = P::TO; }
  "as"      { $this->token = P::T_AS; }
  number    { $this->token = P::NUMBER; }
  string1   { $this->token = P::T_STRING; }
  string2   { $this->token = P::T_STRING; }
  string3   { $this->token = P::COLUMN; }

  // MySQL stuff
  "sql_cache"       { $this->token = P::SQL_CACHE; }
  "sql_calc_found_rows" { $this->token = P::SQL_CALC_FOUND_ROWS; }
  "sql_no_cache"    { $this->token = P::SQL_NO_CACHE; }
  "high_priority"   { $this->token = P::HIGH_PRIORITY; }
  "straight_join"   { $this->token = P::STRAIGHT_JOIN; }
  "sql_small_result"  { $this->token = P::SQL_BIG_RESULT; }
  "sql_big_result"  { $this->token = P::SQL_BIG_RESULT; }
  "sql_buffer_result"   { $this->token = P::SQL_BUFFER_RESULT; }

  // Alpha-texts
  alpha     { $this->token = P::ALPHA; }
*/

}
