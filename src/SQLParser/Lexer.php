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


    private $_yy_state = 1;
    private $_yy_stack = array();

    function yylex()
    {
        return $this->{'yylex' . $this->_yy_state}();
    }

    function yypushstate($state)
    {
        array_push($this->_yy_stack, $this->_yy_state);
        $this->_yy_state = $state;
    }

    function yypopstate()
    {
        $this->_yy_state = array_pop($this->_yy_stack);
    }

    function yybegin($state)
    {
        $this->_yy_state = $state;
    }



    function yylex1()
    {
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        do {
            $rules = array(
                '/\G[ \t\n]+/i ',
                '/\G\"/i ',
                '/\G\'/i ',
                '/\G`/i ',
                '/\G--[^\n]+/i ',
                '/\Gwhen/i ',
                '/\Gunsigned/i ',
                '/\Gcase/i ',
                '/\Gcreate/i ',
                '/\Gthen/i ',
                '/\Gdefault/i ',
                '/\Gelse/i ',
                '/\Gmodify/i ',
                '/\Gautoincrement/i ',
                '/\Gauto_increment/i ',
                '/\Gcollate/i ',
                '/\Gend/i ',
                '/\Gnull/i ',
                '/\Gselect/i ',
                '/\Ggroup/i ',
                '/\Ginsert/i ',
                '/\Gupdate/i ',
                '/\Gdelete/i ',
                '/\Ginto/i ',
                '/\Gleft/i ',
                '/\Gright/i ',
                '/\Ginner/i ',
                '/\Gjoin/i ',
                '/\Gfrom/i ',
                '/\Glimit/i ',
                '/\Gdelete/i ',
                '/\Goffset/i ',
                '/\Gvalues/i ',
                '/\Gset/i ',
                '/\Gdrop/i ',
                '/\Gtable/i ',
                '/\Gnot/i ',
                '/\G>=/i ',
                '/\G<=/i ',
                '/\G%/i ',
                '/\G\//i ',
                '/\G>/i ',
                '/\G</i ',
                '/\G\\(/i ',
                '/\G\\)/i ',
                '/\G;/i ',
                '/\G\\*/i ',
                '/\G\\+/i ',
                '/\G-/i ',
                '/\G=/i ',
                '/\G\\?/i ',
                '/\G\\$/i ',
                '/\G:/i ',
                '/\G\\./i ',
                '/\G,/i ',
                '/\Gon/i ',
                '/\Gduplicate/i ',
                '/\Gin/i ',
                '/\Gall/i ',
                '/\Gdistinct/i ',
                '/\Gnatural/i ',
                '/\Gouter/i ',
                '/\Gusing/i ',
                '/\Ginterval/i ',
                '/\Ghaving/i ',
                '/\Gwhere/i ',
                '/\Gview/i ',
                '/\Glike/i ',
                '/\Gorder/i ',
                '/\Gprimary/i ',
                '/\Gcolumn/i ',
                '/\Gfirst/i ',
                '/\Gafter/i ',
                '/\Gchange/i ',
                '/\Gindex/i ',
                '/\Gadd/i ',
                '/\Galter/i ',
                '/\Gunique/i ',
                '/\Gkey/i ',
                '/\Gdesc/i ',
                '/\Gasc/i ',
                '/\Gby/i ',
                '/\Gand/i ',
                '/\Gor/i ',
                '/\Gis/i ',
                '/\G\\|\\|/i ',
                '/\G!=/i ',
                '/\Gbegin/i ',
                '/\Gtransaction/i ',
                '/\Gcommit/i ',
                '/\Grollback/i ',
                '/\Gsavepoint/i ',
                '/\Grelease/i ',
                '/\Gto/i ',
                '/\Gas/i ',
                '/\Grename/i ',
                '/\G[0-9]+(\\.[0-9]+)?|0x[0-9a-fA-F]+/i ',
                '/\Gsql_cache/i ',
                '/\Gsql_calc_found_rows/i ',
                '/\Gsql_no_cache/i ',
                '/\Ghigh_priority/i ',
                '/\Gstraight_join/i ',
                '/\Gsql_small_result/i ',
                '/\Gsql_big_result/i ',
                '/\Gsql_buffer_result/i ',
                '/\G[a-z_][a-z0-9_]*/i ',
            );
            $match = false;
            foreach ($rules as $index => $rule) {
                if (preg_match($rule, substr($this->data, $this->N), $yymatches)) {
                    if ($match) {
                        if (strlen($yymatches[0]) > strlen($match[0][0])) {
                            $match = array($yymatches, $index); // matches, token
                        }
                    } else {
                        $match = array($yymatches, $index);
                    }
                }
            }
            if (!$match) {
                throw new Exception('Unexpected input at line ' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            $this->token = $match[1];
            $this->value = $match[0][0];
            $yysubmatches = $match[0];
            array_shift($yysubmatches);
            if (!$yysubmatches) {
                $yysubmatches = array();
            }
            $r = $this->{'yy_r1_' . $this->token}($yysubmatches);
            if ($r === null) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                // accept this token
                return true;
            } elseif ($r === true) {
                // we have changed state
                // process this token in the new state
                return $this->yylex();
            } elseif ($r === false) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                if ($this->N >= strlen($this->data)) {
                    return false; // end of input
                }
                // skip this token
                continue;
            } else {
                $yy_yymore_patterns = array_slice($rules, $this->token, true);
                // yymore is needed
                do {
                    if (!isset($yy_yymore_patterns[$this->token])) {
                        throw new Exception('cannot do yymore for the last token');
                    }
                    $match = false;
                    foreach ($yy_yymore_patterns[$this->token] as $index => $rule) {
                        if (preg_match('/' . $rule . '/i',
                                $this->data, $yymatches, null, $this->N)) {
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            if ($match) {
                                if (strlen($yymatches[0]) > strlen($match[0][0])) {
                                    $match = array($yymatches, $index); // matches, token
                                }
                            } else {
                                $match = array($yymatches, $index);
                            }
                        }
                    }
                    if (!$match) {
                        throw new Exception('Unexpected input at line ' . $this->line .
                            ': ' . $this->data[$this->N]);
                    }
                    $this->token = $match[1];
                    $this->value = $match[0][0];
                    $yysubmatches = $match[0];
                    array_shift($yysubmatches);
                    if (!$yysubmatches) {
                        $yysubmatches = array();
                    }
                    $this->line = substr_count($this->value, "\n");
                    $r = $this->{'yy_r1_' . $this->token}();
                } while ($r !== null || !$r);
                if ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } else {
                    // accept
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    return true;
                }
            }
        } while (true);

    } // end function


    const YYINITIAL = 1;
    function yy_r1_0($yy_subpatterns)
    {

    return false;
      }
    function yy_r1_1($yy_subpatterns)
    {

      $this->yybegin(self::INSTRING1);
      $this->_string = '';
      $this->N++;
      return true;
      }
    function yy_r1_2($yy_subpatterns)
    {

      $this->yybegin(self::INSTRING2);
      $this->N++;
      $this->_string = '';
      return true;
      }
    function yy_r1_3($yy_subpatterns)
    {

      $this->yybegin(self::INSTRING3);
      $this->_string = '';
      $this->N++;
      return true;
      }
    function yy_r1_4($yy_subpatterns)
    {
 $this->token = "comment";     }
    function yy_r1_5($yy_subpatterns)
    {
 $this->token = P::WHEN;     }
    function yy_r1_6($yy_subpatterns)
    {
 $this->token = P::T_UNSIGNED;     }
    function yy_r1_7($yy_subpatterns)
    {
 $this->token = P::T_CASE;     }
    function yy_r1_8($yy_subpatterns)
    {
 $this->token = P::CREATE;     }
    function yy_r1_9($yy_subpatterns)
    {
 $this->token = P::THEN;     }
    function yy_r1_10($yy_subpatterns)
    {
 $this->token = P::T_DEFAULT;     }
    function yy_r1_11($yy_subpatterns)
    {
 $this->token = P::T_ELSE;     }
    function yy_r1_12($yy_subpatterns)
    {
 $this->token = P::MODIFY;     }
    function yy_r1_13($yy_subpatterns)
    {
 $this->token = P::AUTO_INCREMENT;     }
    function yy_r1_14($yy_subpatterns)
    {
 $this->token = P::AUTO_INCREMENT;     }
    function yy_r1_15($yy_subpatterns)
    {
 $this->token = P::COLLATE;     }
    function yy_r1_16($yy_subpatterns)
    {
 $this->token = P::T_END;     }
    function yy_r1_17($yy_subpatterns)
    {
 $this->token = P::T_NULL;     }
    function yy_r1_18($yy_subpatterns)
    {
 $this->token = P::SELECT;     }
    function yy_r1_19($yy_subpatterns)
    {
 $this->token = P::GROUP;      }
    function yy_r1_20($yy_subpatterns)
    {
 $this->token = P::INSERT;     }
    function yy_r1_21($yy_subpatterns)
    {
 $this->token = P::UPDATE;     }
    function yy_r1_22($yy_subpatterns)
    {
 $this->token = P::DELETE;     }
    function yy_r1_23($yy_subpatterns)
    {
 $this->token = P::INTO;     }
    function yy_r1_24($yy_subpatterns)
    {
 $this->token = P::LEFT;     }
    function yy_r1_25($yy_subpatterns)
    {
 $this->token = P::RIGHT;     }
    function yy_r1_26($yy_subpatterns)
    {
 $this->token = P::INNER;     }
    function yy_r1_27($yy_subpatterns)
    {
 $this->token = P::JOIN;     }
    function yy_r1_28($yy_subpatterns)
    {
 $this->token = P::FROM;       }
    function yy_r1_29($yy_subpatterns)
    {
 $this->token = P::LIMIT;      }
    function yy_r1_30($yy_subpatterns)
    {
 $this->token = P::DELETE;     }
    function yy_r1_31($yy_subpatterns)
    {
 $this->token = P::OFFSET;     }
    function yy_r1_32($yy_subpatterns)
    {
 $this->token = P::VALUES;     }
    function yy_r1_33($yy_subpatterns)
    {
 $this->token = P::SET;     }
    function yy_r1_34($yy_subpatterns)
    {
 $this->token = P::DROP;     }
    function yy_r1_35($yy_subpatterns)
    {
 $this->token = P::TABLE;     }
    function yy_r1_36($yy_subpatterns)
    {
 $this->token = P::T_NOT;     }
    function yy_r1_37($yy_subpatterns)
    {
 $this->token = P::T_GE;     }
    function yy_r1_38($yy_subpatterns)
    {
 $this->token = P::T_LE;     }
    function yy_r1_39($yy_subpatterns)
    {
 $this->token = P::T_MOD;     }
    function yy_r1_40($yy_subpatterns)
    {
 $this->token = P::T_DIV;     }
    function yy_r1_41($yy_subpatterns)
    {
 $this->token = P::T_GT;     }
    function yy_r1_42($yy_subpatterns)
    {
 $this->token = P::T_LT;     }
    function yy_r1_43($yy_subpatterns)
    {
 $this->token = P::PAR_OPEN;     }
    function yy_r1_44($yy_subpatterns)
    {
 $this->token = P::PAR_CLOSE;     }
    function yy_r1_45($yy_subpatterns)
    {
 $this->token = P::SEMICOLON;     }
    function yy_r1_46($yy_subpatterns)
    {
 $this->token = P::T_TIMES;     }
    function yy_r1_47($yy_subpatterns)
    {
 $this->token = P::T_PLUS;     }
    function yy_r1_48($yy_subpatterns)
    {
 $this->token = P::T_MINUS;     }
    function yy_r1_49($yy_subpatterns)
    {
 $this->token = P::T_EQ;     }
    function yy_r1_50($yy_subpatterns)
    {
 $this->token = P::QUESTION;     }
    function yy_r1_51($yy_subpatterns)
    {
 $this->token = P::T_DOLLAR;     }
    function yy_r1_52($yy_subpatterns)
    {
 $this->token = P::T_COLON;     }
    function yy_r1_53($yy_subpatterns)
    {
 $this->token = P::T_DOT;     }
    function yy_r1_54($yy_subpatterns)
    {
 $this->token = P::COMMA;     }
    function yy_r1_55($yy_subpatterns)
    {
 $this->token = P::ON;     }
    function yy_r1_56($yy_subpatterns)
    {
 $this->token = P::DUPLICATE;     }
    function yy_r1_57($yy_subpatterns)
    {
 $this->token = P::T_IN;     }
    function yy_r1_58($yy_subpatterns)
    {
 $this->token = P::ALL;     }
    function yy_r1_59($yy_subpatterns)
    {
 $this->token = P::DISTINCT;     }
    function yy_r1_60($yy_subpatterns)
    {
 $this->token = P::NATURAL;     }
    function yy_r1_61($yy_subpatterns)
    {
 $this->token = P::OUTER;     }
    function yy_r1_62($yy_subpatterns)
    {
 $this->token = P::USING;     }
    function yy_r1_63($yy_subpatterns)
    {
 $this->token = P::INTERVAL;     }
    function yy_r1_64($yy_subpatterns)
    {
 $this->token = P::HAVING;     }
    function yy_r1_65($yy_subpatterns)
    {
 $this->token = P::WHERE;     }
    function yy_r1_66($yy_subpatterns)
    {
 $this->token = P::VIEW;     }
    function yy_r1_67($yy_subpatterns)
    {
 $this->token = P::T_LIKE;     }
    function yy_r1_68($yy_subpatterns)
    {
 $this->token = P::ORDER;     }
    function yy_r1_69($yy_subpatterns)
    {
 $this->token = P::PRIMARY;     }
    function yy_r1_70($yy_subpatterns)
    {
 $this->token = P::T_COLUMN;     }
    function yy_r1_71($yy_subpatterns)
    {
 $this->token = P::T_FIRST ;     }
    function yy_r1_72($yy_subpatterns)
    {
 $this->token = P::T_AFTER;     }
    function yy_r1_73($yy_subpatterns)
    {
 $this->token = P::CHANGE;     }
    function yy_r1_74($yy_subpatterns)
    {
 $this->token = P::INDEX;     }
    function yy_r1_75($yy_subpatterns)
    {
 $this->token = P::ADD;     }
    function yy_r1_76($yy_subpatterns)
    {
 $this->token = P::ALTER;     }
    function yy_r1_77($yy_subpatterns)
    {
 $this->token = P::UNIQUE;     }
    function yy_r1_78($yy_subpatterns)
    {
 $this->token = P::KEY;     }
    function yy_r1_79($yy_subpatterns)
    {
 $this->token = P::DESC;     }
    function yy_r1_80($yy_subpatterns)
    {
 $this->token = P::ASC;     }
    function yy_r1_81($yy_subpatterns)
    {
 $this->token = P::BY;     }
    function yy_r1_82($yy_subpatterns)
    {
 $this->token = P::T_AND;     }
    function yy_r1_83($yy_subpatterns)
    {
 $this->token = P::T_OR;     }
    function yy_r1_84($yy_subpatterns)
    {
 $this->token = P::T_IS;     }
    function yy_r1_85($yy_subpatterns)
    {
 $this->token = P::T_OR;     }
    function yy_r1_86($yy_subpatterns)
    {
 $this->token = P::T_NE;     }
    function yy_r1_87($yy_subpatterns)
    {
 $this->token = P::BEGIN;     }
    function yy_r1_88($yy_subpatterns)
    {
 $this->token = P::TRANSACTION;     }
    function yy_r1_89($yy_subpatterns)
    {
 $this->token = P::COMMIT;     }
    function yy_r1_90($yy_subpatterns)
    {
 $this->token = P::ROLLBACK;     }
    function yy_r1_91($yy_subpatterns)
    {
 $this->token = P::SAVEPOINT;     }
    function yy_r1_92($yy_subpatterns)
    {
 $this->token = P::RELEASE;     }
    function yy_r1_93($yy_subpatterns)
    {
 $this->token = P::TO;     }
    function yy_r1_94($yy_subpatterns)
    {
 $this->token = P::T_AS;     }
    function yy_r1_95($yy_subpatterns)
    {
 $this->token = P::RENAME;     }
    function yy_r1_96($yy_subpatterns)
    {
 $this->token = P::NUMBER;     }
    function yy_r1_97($yy_subpatterns)
    {
 $this->token = P::SQL_CACHE;     }
    function yy_r1_98($yy_subpatterns)
    {
 $this->token = P::SQL_CALC_FOUND_ROWS;     }
    function yy_r1_99($yy_subpatterns)
    {
 $this->token = P::SQL_NO_CACHE;     }
    function yy_r1_100($yy_subpatterns)
    {
 $this->token = P::HIGH_PRIORITY;     }
    function yy_r1_101($yy_subpatterns)
    {
 $this->token = P::STRAIGHT_JOIN;     }
    function yy_r1_102($yy_subpatterns)
    {
 $this->token = P::SQL_BIG_RESULT;     }
    function yy_r1_103($yy_subpatterns)
    {
 $this->token = P::SQL_BIG_RESULT;     }
    function yy_r1_104($yy_subpatterns)
    {
 $this->token = P::SQL_BUFFER_RESULT;     }
    function yy_r1_105($yy_subpatterns)
    {
 $this->token = P::ALPHA;     }


    function yylex2()
    {
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        do {
            $rules = array(
                '/\G\"/i ',
                '/\G\\\\/i ',
                '/\G[^\"\\\\]+/i ',
            );
            $match = false;
            foreach ($rules as $index => $rule) {
                if (preg_match($rule, substr($this->data, $this->N), $yymatches)) {
                    if ($match) {
                        if (strlen($yymatches[0]) > strlen($match[0][0])) {
                            $match = array($yymatches, $index); // matches, token
                        }
                    } else {
                        $match = array($yymatches, $index);
                    }
                }
            }
            if (!$match) {
                throw new Exception('Unexpected input at line ' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            $this->token = $match[1];
            $this->value = $match[0][0];
            $yysubmatches = $match[0];
            array_shift($yysubmatches);
            if (!$yysubmatches) {
                $yysubmatches = array();
            }
            $r = $this->{'yy_r2_' . $this->token}($yysubmatches);
            if ($r === null) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                // accept this token
                return true;
            } elseif ($r === true) {
                // we have changed state
                // process this token in the new state
                return $this->yylex();
            } elseif ($r === false) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                if ($this->N >= strlen($this->data)) {
                    return false; // end of input
                }
                // skip this token
                continue;
            } else {
                $yy_yymore_patterns = array_slice($rules, $this->token, true);
                // yymore is needed
                do {
                    if (!isset($yy_yymore_patterns[$this->token])) {
                        throw new Exception('cannot do yymore for the last token');
                    }
                    $match = false;
                    foreach ($yy_yymore_patterns[$this->token] as $index => $rule) {
                        if (preg_match('/' . $rule . '/i',
                                $this->data, $yymatches, null, $this->N)) {
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            if ($match) {
                                if (strlen($yymatches[0]) > strlen($match[0][0])) {
                                    $match = array($yymatches, $index); // matches, token
                                }
                            } else {
                                $match = array($yymatches, $index);
                            }
                        }
                    }
                    if (!$match) {
                        throw new Exception('Unexpected input at line ' . $this->line .
                            ': ' . $this->data[$this->N]);
                    }
                    $this->token = $match[1];
                    $this->value = $match[0][0];
                    $yysubmatches = $match[0];
                    array_shift($yysubmatches);
                    if (!$yysubmatches) {
                        $yysubmatches = array();
                    }
                    $this->line = substr_count($this->value, "\n");
                    $r = $this->{'yy_r2_' . $this->token}();
                } while ($r !== null || !$r);
                if ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } else {
                    // accept
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    return true;
                }
            }
        } while (true);

    } // end function


    const INSTRING1 = 2;
    function yy_r2_0($yy_subpatterns)
    {

    $this->value = $this->_string;
    $this->token = P::T_STRING;
    $this->N -= strlen($this->_string) - 1;
    $this->_string = '';
    $this->yybegin(self::YYINITIAL);
      }
    function yy_r2_1($yy_subpatterns)
    {

    $this->yybegin(self::INESCAPE1);
    $this->N++;
    return true;
      }
    function yy_r2_2($yy_subpatterns)
    {

    $this->_string .= $this->value;
    return false;
      }


    function yylex3()
    {
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        do {
            $rules = array(
                '/\G\'/i ',
                '/\G\\\\/i ',
                '/\G[^\'\\\\]+/i ',
            );
            $match = false;
            foreach ($rules as $index => $rule) {
                if (preg_match($rule, substr($this->data, $this->N), $yymatches)) {
                    if ($match) {
                        if (strlen($yymatches[0]) > strlen($match[0][0])) {
                            $match = array($yymatches, $index); // matches, token
                        }
                    } else {
                        $match = array($yymatches, $index);
                    }
                }
            }
            if (!$match) {
                throw new Exception('Unexpected input at line ' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            $this->token = $match[1];
            $this->value = $match[0][0];
            $yysubmatches = $match[0];
            array_shift($yysubmatches);
            if (!$yysubmatches) {
                $yysubmatches = array();
            }
            $r = $this->{'yy_r3_' . $this->token}($yysubmatches);
            if ($r === null) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                // accept this token
                return true;
            } elseif ($r === true) {
                // we have changed state
                // process this token in the new state
                return $this->yylex();
            } elseif ($r === false) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                if ($this->N >= strlen($this->data)) {
                    return false; // end of input
                }
                // skip this token
                continue;
            } else {
                $yy_yymore_patterns = array_slice($rules, $this->token, true);
                // yymore is needed
                do {
                    if (!isset($yy_yymore_patterns[$this->token])) {
                        throw new Exception('cannot do yymore for the last token');
                    }
                    $match = false;
                    foreach ($yy_yymore_patterns[$this->token] as $index => $rule) {
                        if (preg_match('/' . $rule . '/i',
                                $this->data, $yymatches, null, $this->N)) {
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            if ($match) {
                                if (strlen($yymatches[0]) > strlen($match[0][0])) {
                                    $match = array($yymatches, $index); // matches, token
                                }
                            } else {
                                $match = array($yymatches, $index);
                            }
                        }
                    }
                    if (!$match) {
                        throw new Exception('Unexpected input at line ' . $this->line .
                            ': ' . $this->data[$this->N]);
                    }
                    $this->token = $match[1];
                    $this->value = $match[0][0];
                    $yysubmatches = $match[0];
                    array_shift($yysubmatches);
                    if (!$yysubmatches) {
                        $yysubmatches = array();
                    }
                    $this->line = substr_count($this->value, "\n");
                    $r = $this->{'yy_r3_' . $this->token}();
                } while ($r !== null || !$r);
                if ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } else {
                    // accept
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    return true;
                }
            }
        } while (true);

    } // end function


    const INSTRING2 = 3;
    function yy_r3_0($yy_subpatterns)
    {

    $this->value = $this->_string;
    $this->token = P::T_STRING;
    $this->N -= strlen($this->_string) - 1;
    $this->_string = '';
    $this->yybegin(self::YYINITIAL);
      }
    function yy_r3_1($yy_subpatterns)
    {

    $this->yybegin(self::INESCAPE2);
    $this->N++;
    return true;
      }
    function yy_r3_2($yy_subpatterns)
    {

    $this->_string .= $this->value;
    return false;
      }


    function yylex4()
    {
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        do {
            $rules = array(
                '/\G`/i ',
                '/\G\\\\/i ',
                '/\G[^`\\\\]+/i ',
            );
            $match = false;
            foreach ($rules as $index => $rule) {
                if (preg_match($rule, substr($this->data, $this->N), $yymatches)) {
                    if ($match) {
                        if (strlen($yymatches[0]) > strlen($match[0][0])) {
                            $match = array($yymatches, $index); // matches, token
                        }
                    } else {
                        $match = array($yymatches, $index);
                    }
                }
            }
            if (!$match) {
                throw new Exception('Unexpected input at line ' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            $this->token = $match[1];
            $this->value = $match[0][0];
            $yysubmatches = $match[0];
            array_shift($yysubmatches);
            if (!$yysubmatches) {
                $yysubmatches = array();
            }
            $r = $this->{'yy_r4_' . $this->token}($yysubmatches);
            if ($r === null) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                // accept this token
                return true;
            } elseif ($r === true) {
                // we have changed state
                // process this token in the new state
                return $this->yylex();
            } elseif ($r === false) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                if ($this->N >= strlen($this->data)) {
                    return false; // end of input
                }
                // skip this token
                continue;
            } else {
                $yy_yymore_patterns = array_slice($rules, $this->token, true);
                // yymore is needed
                do {
                    if (!isset($yy_yymore_patterns[$this->token])) {
                        throw new Exception('cannot do yymore for the last token');
                    }
                    $match = false;
                    foreach ($yy_yymore_patterns[$this->token] as $index => $rule) {
                        if (preg_match('/' . $rule . '/i',
                                $this->data, $yymatches, null, $this->N)) {
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            if ($match) {
                                if (strlen($yymatches[0]) > strlen($match[0][0])) {
                                    $match = array($yymatches, $index); // matches, token
                                }
                            } else {
                                $match = array($yymatches, $index);
                            }
                        }
                    }
                    if (!$match) {
                        throw new Exception('Unexpected input at line ' . $this->line .
                            ': ' . $this->data[$this->N]);
                    }
                    $this->token = $match[1];
                    $this->value = $match[0][0];
                    $yysubmatches = $match[0];
                    array_shift($yysubmatches);
                    if (!$yysubmatches) {
                        $yysubmatches = array();
                    }
                    $this->line = substr_count($this->value, "\n");
                    $r = $this->{'yy_r4_' . $this->token}();
                } while ($r !== null || !$r);
                if ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } else {
                    // accept
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    return true;
                }
            }
        } while (true);

    } // end function


    const INSTRING3 = 4;
    function yy_r4_0($yy_subpatterns)
    {

    $this->value = $this->_string;
    $this->token = P::COLUMN;
    $this->N -= strlen($this->_string) - 1;
    $this->_string = '';
    $this->yybegin(self::YYINITIAL);
      }
    function yy_r4_1($yy_subpatterns)
    {

    $this->yybegin(self::INESCAPE3);
    $this->N++;
    return true;
      }
    function yy_r4_2($yy_subpatterns)
    {

    $this->_string .= $this->value;
    return false;
      }



    function yylex5()
    {
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        do {
            $rules = array(
                '/\G./i ',
            );
            $match = false;
            foreach ($rules as $index => $rule) {
                if (preg_match($rule, substr($this->data, $this->N), $yymatches)) {
                    if ($match) {
                        if (strlen($yymatches[0]) > strlen($match[0][0])) {
                            $match = array($yymatches, $index); // matches, token
                        }
                    } else {
                        $match = array($yymatches, $index);
                    }
                }
            }
            if (!$match) {
                throw new Exception('Unexpected input at line ' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            $this->token = $match[1];
            $this->value = $match[0][0];
            $yysubmatches = $match[0];
            array_shift($yysubmatches);
            if (!$yysubmatches) {
                $yysubmatches = array();
            }
            $r = $this->{'yy_r5_' . $this->token}($yysubmatches);
            if ($r === null) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                // accept this token
                return true;
            } elseif ($r === true) {
                // we have changed state
                // process this token in the new state
                return $this->yylex();
            } elseif ($r === false) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                if ($this->N >= strlen($this->data)) {
                    return false; // end of input
                }
                // skip this token
                continue;
            } else {
                $yy_yymore_patterns = array_slice($rules, $this->token, true);
                // yymore is needed
                do {
                    if (!isset($yy_yymore_patterns[$this->token])) {
                        throw new Exception('cannot do yymore for the last token');
                    }
                    $match = false;
                    foreach ($yy_yymore_patterns[$this->token] as $index => $rule) {
                        if (preg_match('/' . $rule . '/i',
                                $this->data, $yymatches, null, $this->N)) {
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            if ($match) {
                                if (strlen($yymatches[0]) > strlen($match[0][0])) {
                                    $match = array($yymatches, $index); // matches, token
                                }
                            } else {
                                $match = array($yymatches, $index);
                            }
                        }
                    }
                    if (!$match) {
                        throw new Exception('Unexpected input at line ' . $this->line .
                            ': ' . $this->data[$this->N]);
                    }
                    $this->token = $match[1];
                    $this->value = $match[0][0];
                    $yysubmatches = $match[0];
                    array_shift($yysubmatches);
                    if (!$yysubmatches) {
                        $yysubmatches = array();
                    }
                    $this->line = substr_count($this->value, "\n");
                    $r = $this->{'yy_r5_' . $this->token}();
                } while ($r !== null || !$r);
                if ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } else {
                    // accept
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    return true;
                }
            }
        } while (true);

    } // end function


    const INESCAPE1 = 5;
    function yy_r5_0($yy_subpatterns)
    {

    $this->yybegin(self::INSTRING1);
    $this->_string .= $this->value;
    $this->N++;
    }



    function yylex6()
    {
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        do {
            $rules = array(
                '/\G./i ',
            );
            $match = false;
            foreach ($rules as $index => $rule) {
                if (preg_match($rule, substr($this->data, $this->N), $yymatches)) {
                    if ($match) {
                        if (strlen($yymatches[0]) > strlen($match[0][0])) {
                            $match = array($yymatches, $index); // matches, token
                        }
                    } else {
                        $match = array($yymatches, $index);
                    }
                }
            }
            if (!$match) {
                throw new Exception('Unexpected input at line ' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            $this->token = $match[1];
            $this->value = $match[0][0];
            $yysubmatches = $match[0];
            array_shift($yysubmatches);
            if (!$yysubmatches) {
                $yysubmatches = array();
            }
            $r = $this->{'yy_r6_' . $this->token}($yysubmatches);
            if ($r === null) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                // accept this token
                return true;
            } elseif ($r === true) {
                // we have changed state
                // process this token in the new state
                return $this->yylex();
            } elseif ($r === false) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                if ($this->N >= strlen($this->data)) {
                    return false; // end of input
                }
                // skip this token
                continue;
            } else {
                $yy_yymore_patterns = array_slice($rules, $this->token, true);
                // yymore is needed
                do {
                    if (!isset($yy_yymore_patterns[$this->token])) {
                        throw new Exception('cannot do yymore for the last token');
                    }
                    $match = false;
                    foreach ($yy_yymore_patterns[$this->token] as $index => $rule) {
                        if (preg_match('/' . $rule . '/i',
                                $this->data, $yymatches, null, $this->N)) {
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            if ($match) {
                                if (strlen($yymatches[0]) > strlen($match[0][0])) {
                                    $match = array($yymatches, $index); // matches, token
                                }
                            } else {
                                $match = array($yymatches, $index);
                            }
                        }
                    }
                    if (!$match) {
                        throw new Exception('Unexpected input at line ' . $this->line .
                            ': ' . $this->data[$this->N]);
                    }
                    $this->token = $match[1];
                    $this->value = $match[0][0];
                    $yysubmatches = $match[0];
                    array_shift($yysubmatches);
                    if (!$yysubmatches) {
                        $yysubmatches = array();
                    }
                    $this->line = substr_count($this->value, "\n");
                    $r = $this->{'yy_r6_' . $this->token}();
                } while ($r !== null || !$r);
                if ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } else {
                    // accept
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    return true;
                }
            }
        } while (true);

    } // end function


    const INESCAPE2 = 6;
    function yy_r6_0($yy_subpatterns)
    {

    $this->yybegin(self::INSTRING2);
    $this->_string .= $this->value;
    $this->N++;
    }


    function yylex7()
    {
        if ($this->N >= strlen($this->data)) {
            return false; // end of input
        }
        do {
            $rules = array(
                '/\G./i ',
            );
            $match = false;
            foreach ($rules as $index => $rule) {
                if (preg_match($rule, substr($this->data, $this->N), $yymatches)) {
                    if ($match) {
                        if (strlen($yymatches[0]) > strlen($match[0][0])) {
                            $match = array($yymatches, $index); // matches, token
                        }
                    } else {
                        $match = array($yymatches, $index);
                    }
                }
            }
            if (!$match) {
                throw new Exception('Unexpected input at line ' . $this->line .
                    ': ' . $this->data[$this->N]);
            }
            $this->token = $match[1];
            $this->value = $match[0][0];
            $yysubmatches = $match[0];
            array_shift($yysubmatches);
            if (!$yysubmatches) {
                $yysubmatches = array();
            }
            $r = $this->{'yy_r7_' . $this->token}($yysubmatches);
            if ($r === null) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                // accept this token
                return true;
            } elseif ($r === true) {
                // we have changed state
                // process this token in the new state
                return $this->yylex();
            } elseif ($r === false) {
                $this->N += strlen($this->value);
                $this->line += substr_count($this->value, "\n");
                if ($this->N >= strlen($this->data)) {
                    return false; // end of input
                }
                // skip this token
                continue;
            } else {
                $yy_yymore_patterns = array_slice($rules, $this->token, true);
                // yymore is needed
                do {
                    if (!isset($yy_yymore_patterns[$this->token])) {
                        throw new Exception('cannot do yymore for the last token');
                    }
                    $match = false;
                    foreach ($yy_yymore_patterns[$this->token] as $index => $rule) {
                        if (preg_match('/' . $rule . '/i',
                                $this->data, $yymatches, null, $this->N)) {
                            $yymatches = array_filter($yymatches, 'strlen'); // remove empty sub-patterns
                            if ($match) {
                                if (strlen($yymatches[0]) > strlen($match[0][0])) {
                                    $match = array($yymatches, $index); // matches, token
                                }
                            } else {
                                $match = array($yymatches, $index);
                            }
                        }
                    }
                    if (!$match) {
                        throw new Exception('Unexpected input at line ' . $this->line .
                            ': ' . $this->data[$this->N]);
                    }
                    $this->token = $match[1];
                    $this->value = $match[0][0];
                    $yysubmatches = $match[0];
                    array_shift($yysubmatches);
                    if (!$yysubmatches) {
                        $yysubmatches = array();
                    }
                    $this->line = substr_count($this->value, "\n");
                    $r = $this->{'yy_r7_' . $this->token}();
                } while ($r !== null || !$r);
                if ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } else {
                    // accept
                    $this->N += strlen($this->value);
                    $this->line += substr_count($this->value, "\n");
                    return true;
                }
            }
        } while (true);

    } // end function


    const INESCAPE3 = 7;
    function yy_r7_0($yy_subpatterns)
    {

    $this->yybegin(self::INSTRING3);
    $this->_string .= $this->value;
    $this->N++;
    }


}
