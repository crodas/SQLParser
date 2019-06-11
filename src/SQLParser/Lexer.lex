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

alpha   = /[a-z_][a-z0-9_]*/
STRINGCONTENTS1 = /[^"\\]+/
STRINGCONTENTS2 = /[^SINGLE_QUOTE\\]+/
STRINGCONTENTS3 = /[^`\\]+/
ESCAPEDTHING1 = @"|\\@
ESCAPEDTHING2 = @SINGLE_QUOTE|\\@
ESCAPEDTHING3 = @`|\\@
ANYTHINGELSE = /./

*/
/*!lex2php
  %statename YYINITIAL
  whitespace {
    return false;
  }


  "\"" {
      $this->yybegin(self::INSTRING1);
      $this->_string = '';
      $this->N++;
      return true;
  }

  "SINGLE_QUOTE" {
      $this->yybegin(self::INSTRING2);
      $this->N++;
      $this->_string = '';
      return true;
  }

  "`" {
      $this->yybegin(self::INSTRING3);
      $this->_string = '';
      $this->N++;
      return true;
  }

  comment   { $this->token = "comment"; }
  "when"    { $this->token = P::T_WHEN; }
  "unsigned" { $this->token = P::T_UNSIGNED; }
  "between"  { $this->token = P::T_BETWEEN; }
  "case"    { $this->token = P::T_CASE; }
  "create"  { $this->token = P::T_CREATE; }
  "then"    { $this->token = P::T_THEN; }
  "default" { $this->token = P::T_DEFAULT; }
  "else"    { $this->token = P::T_ELSE; }
  "modify"  { $this->token = P::T_MODIFY; }
  "autoincrement" { $this->token = P::T_AUTO_INCREMENT; }
  "auto_increment" { $this->token = P::T_AUTO_INCREMENT; }
  "collate" { $this->token = P::T_COLLATE; }
  "end"     { $this->token = P::T_END; }
  "null"    { $this->token = P::T_NULL; }
  "select"  { $this->token = P::T_SELECT; }
  "group"   { $this->token = P::T_GROUP;  }
  "insert"  { $this->token = P::T_INSERT; }
  "update"  { $this->token = P::T_UPDATE; }
  "delete"  { $this->token = P::T_DELETE; }
  "into"    { $this->token = P::T_INTO; }
  "left"    { $this->token = P::T_LEFT; }
  "right"   { $this->token = P::T_RIGHT; }
  "inner"   { $this->token = P::T_INNER; }
  "join"    { $this->token = P::T_JOIN; }
  "from"    { $this->token = P::T_FROM;   }
  "limit"   { $this->token = P::T_LIMIT;  }
  "offset"  { $this->token = P::T_OFFSET; }
  "values"  { $this->token = P::T_VALUES; }
  "set"     { $this->token = P::T_SET; }
  "drop"    { $this->token = P::T_DROP; }
  "table"   { $this->token = P::T_TABLE; }
  "not"     { $this->token = P::T_NOT; }
  ">="      { $this->token = P::T_GE; }
  "<="      { $this->token = P::T_LE; }
  "%"       { $this->token = P::T_MOD; }
  "/"       { $this->token = P::T_DIV; }
  ">"       { $this->token = P::T_GT; }
  "<"       { $this->token = P::T_LT; }
  "("       { $this->token = P::T_PAR_OPEN; }
  ")"       { $this->token = P::T_PAR_CLOSE; }
  ";"       { $this->token = P::T_SEMICOLON; }
  "*"       { $this->token = P::T_TIMES; }
  "+"       { $this->token = P::T_PLUS; }
  "-"       { $this->token = P::T_MINUS; }
  "="       { $this->token = P::T_EQ; }
  "?"       { $this->token = P::T_QUESTION; }
  "$"       { $this->token = P::T_DOLLAR; }
  ":"       { $this->token = P::T_COLON; }
  "."       { $this->token = P::T_DOT; }
  ","       { $this->token = P::T_COMMA; }
  "on"      { $this->token = P::T_ON; }
  "duplicate" { $this->token = P::T_DUPLICATE; }
  "in"      { $this->token = P::T_IN; }
  "all"     { $this->token = P::T_ALL; }
  "distinct"    { $this->token = P::T_DISTINCT; }
  "natural"     { $this->token = P::T_NATURAL; }
  "outer"       { $this->token = P::T_OUTER; }
  "using"       { $this->token = P::T_USING; }
  "interval"    { $this->token = P::T_INTERVAL; }
  "having"  { $this->token = P::T_HAVING; }
  "where"   { $this->token = P::T_WHERE; }
  "view"    { $this->token = P::T_VIEW; }
  "like"    { $this->token = P::T_LIKE; }
  "glob"    { $this->token = P::T_GLOB; }
  "order"   { $this->token = P::T_ORDER; }
  "primary" { $this->token = P::T_PRIMARY; }
  "column"  { $this->token = P::T_COLUMN; }
  "first"   { $this->token = P::T_FIRST ; }
  "after"   { $this->token = P::T_AFTER; }
  "change"  { $this->token = P::T_CHANGE; }
  "binary"  { $this->token = P::T_BINARY; }
  "index"   { $this->token = P::T_INDEX; }
  "add"     { $this->token = P::T_ADD; }
  "alter"   { $this->token = P::T_ALTER; }
  "unique"  { $this->token = P::T_UNIQUE; }
  "key"     { $this->token = P::T_KEY; }
  "desc"    { $this->token = P::T_DESC; }
  "asc"     { $this->token = P::T_ASC; }
  "by"      { $this->token = P::T_BY; }
  "and"     { $this->token = P::T_AND; }
  "or"      { $this->token = P::T_OR; }
  "is"      { $this->token = P::T_IS; }
  "||"      { $this->token = P::T_OR; }
  "!="      { $this->token = P::T_NE; }
  "begin"   { $this->token = P::T_BEGIN; }
  "work"        { $this->token = P::T_WORK; }
  "transaction" { $this->token = P::T_TRANSACTION; }
  "commit"      { $this->token = P::T_COMMIT; }
  "rollback"    { $this->token = P::T_ROLLBACK; }
  "savepoint"   { $this->token = P::T_SAVEPOINT; }
  "release"     { $this->token = P::T_RELEASE; }
  "to"          { $this->token = P::T_TO; }
  "as"      { $this->token = P::T_AS; }
  "rename"  { $this->token = P::T_RENAME; }
  number    { $this->token = P::T_NUMBER; }

  // MySQL stuff
  "sql_cache"       { $this->token = P::T_SQL_CACHE; }
  "sql_calc_found_rows" { $this->token = P::T_SQL_CALC_FOUND_ROWS; }
  "sql_no_cache"    { $this->token = P::T_SQL_NO_CACHE; }
  "high_priority"   { $this->token = P::T_HIGH_PRIORITY; }
  "straight_join"   { $this->token = P::T_STRAIGHT_JOIN; }
  "sql_small_result"  { $this->token = P::T_SQL_BIG_RESULT; }
  "sql_big_result"  { $this->token = P::T_SQL_BIG_RESULT; }
  "sql_buffer_result"   { $this->token = P::T_SQL_BUFFER_RESULT; }

  // Alpha-texts
  alpha     { $this->token = P::T_ALPHA; }
*/
/*!lex2php
  %statename INSTRING1
  "\"" {
    $this->value = $this->_string;
    $this->token = P::T_STRING1;
    $this->N -= strlen($this->_string) - 1;
    $this->_string = '';
    $this->yybegin(self::YYINITIAL);
  }
  "\\" {
    $this->yybegin(self::INESCAPE1);
    $this->N++;
    return true;
  }
  STRINGCONTENTS1 {
    $this->_string .= $this->value;
    return false;
  }
*/
/*!lex2php
  %statename INSTRING2
  "SINGLE_QUOTE" {
    $this->value = $this->_string;
    $this->token = P::T_STRING2;
    $this->N -= strlen($this->_string) - 1;
    $this->_string = '';
    $this->yybegin(self::YYINITIAL);
  }
  "\\" {
    $this->yybegin(self::INESCAPE2);
    $this->N++;
    return true;
  }
  STRINGCONTENTS2 {
    $this->_string .= $this->value;
    return false;
  }
*/
/*!lex2php
  %statename INSTRING3
  "`" {
    $this->value = $this->_string;
    $this->token = P::T_COLUMN;
    $this->N -= strlen($this->_string) - 1;
    $this->_string = '';
    $this->yybegin(self::YYINITIAL);
  }
  "\\" {
    $this->yybegin(self::INESCAPE3);
    $this->N++;
    return true;
  }
  STRINGCONTENTS3 {
    $this->_string .= $this->value;
    return false;
  }
*/

/*!lex2php
%statename INESCAPE1
ANYTHINGELSE {
    $this->yybegin(self::INSTRING1);
    $this->_string .= $this->value;
}
*/

/*!lex2php
%statename INESCAPE2
ANYTHINGELSE {
    $this->yybegin(self::INSTRING2);
    $this->_string .= $this->value;
}
*/
/*!lex2php
%statename INESCAPE3
ANYTHINGELSE {
    $this->yybegin(self::INSTRING3);
    $this->_string .= $this->value;
}
*/

}
