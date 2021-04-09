<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015-2021 César Rodas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * -
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * -
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace SQLParser;

use RuntimeException;
use SQLParser_Parser as P;

class Lexer
{
    public $token;
    public $value;

    protected static $keywords = [];

    protected static $symbols = [
        '--' => 'comment',
        '"' => P::T_STRING1,
        "'" => P::T_STRING2,
        '`' => P::T_STRING2,
        '+' => P::T_PLUS,
        '-' => P::T_MINUS,
        '*' => P::T_TIMES,
        '/' => P::T_DIV,
        '%' => P::T_MOD,
        '>' => P::T_GT,
        '<' => P::T_GT,
        '(' => P::T_PAR_OPEN,
        ')' => P::T_PAR_CLOSE,
        ';' => P::T_SEMICOLON,
        '=' => P::T_EQ,
        '?' => P::T_QUESTION,
        '$' => P::T_DOLLAR,
        ':' => P::T_COLON,
        '.' => P::T_DOT,
        ',' => P::T_COMMA,
        '||' => P::T_OR,
        '&&' => P::T_AND,
        '<>' => P::T_NE,
        '!=' => P::T_NE,
        '>=' => P::T_GE,
        '<=' => P::T_LE,
    ];

    protected $offset = 0;

    protected $len;
    private $data;
    private $N;
    private $line;

    public function __construct(string $data)
    {
        $this->data = trim($data);
        $this->len = \strlen($this->data);
        $this->N = 0;
        $this->line = 1;
    }

    public static function getKeywords()
    {
        return self::$keywords;
    }

    public static function initKeywordsTable()
    {
        $ignoredKeywords = array_combine(self::$symbols, self::$symbols);
        unset($ignoredKeywords[P::T_OR], $ignoredKeywords[P::T_AND]);

        $keywords = [];
        $reflection = new \ReflectionClass(P::class);
        foreach ($reflection->getConstants() as $name => $value) {
            if ('T' === $name[0] && '_' === $name[1] && !($ignoredKeywords[$value] ?? false)) {
                $keyword = strtolower(substr($name, 2));
                $keywords[$keyword] = $value;
            }
        }

        ksort($keywords);

        self::$keywords = $keywords;
    }

    public function hasNextToken(): bool
    {
        $i = &$this->offset;

        while ($i < $this->len && ctype_space($this->data[$i])) {
            ++$i;
        }

        if ($i >= $this->len) {
            return false;
        }

        switch ($this->data[$i]) {
        case '0': case '1': case '2': case '3': case '4':
        case '5': case '6': case '7': case '8': case '9':
            $value = $this->getNumber();
            $token = P::NUMBER;

            break;

        case '"': case "'": case '`':
            switch ($this->data[$i]) {
            case '`':
                $token = P::T_COLUMN;

                break;

            case '"':
                $token = P::T_STRING1;

                break;

            case "'":
                $token = P::T_STRING2;

                break;
            }
            $stopAt = $this->data[$i];
            $start = ++$i;

            for (; $i < $this->len && $this->data[$i] !== $stopAt; ++$i) {
                if ('\\' === $this->data[$i]) {
                    // escape
                    ++$i;
                }
            }

            $value = stripslashes(substr($this->data, $start, $i - $start));
            ++$i;

            break;

        default:
            for ($e = 2; $e >= 1; --$e) {
                $value = substr($this->data, $i, $e);
                $token = self::$symbols[$value] ?? false;
                if ($token) {
                    $i += \strlen($value);

                    break;
                }
            }

            if ('comment' === $token) {
                $i -= 2;

                $end = strpos($this->data, "\n", $i);
                $value = $end ? substr($this->data, $i, $end - $i) : substr($this->data, $i);
                $i += \strlen($value);
            }

            if (!$token) {
                $value = $this->getKeyword();
                $token = self::$keywords[strtolower($value)] ?? P::ALPHA;
            }
        }

        $this->value = $value;
        $this->token = $token;

        return true;
    }

    protected function getNumber(): string
    {
        $i = &$this->offset;
        if (!ctype_digit($this->data[$i])) {
            throw new RuntimeException("Unexpected {$this->data[$i]}");
        }

        $start = $i;

        while ($i < $this->len && (ctype_digit($this->data[$i]) || '.' === $this->data[$i])) {
            ++$i;
        }

        $value = substr($this->data, $start, $i - $start);

        if (!is_numeric($value)) {
            throw new \RuntimeException("{$value} is not a valid number");
        }

        return $value;
    }

    protected function getKeyword(): string
    {
        $i = &$this->offset;
        if (!ctype_alpha($this->data[$i]) && '_' !== $this->data[$i]) {
            throw new RuntimeException("Unexpected {$this->data[$i]}");
        }

        $start = $i;

        while ($i < $this->len && (ctype_alnum($this->data[$i]) || '_' === $this->data[$i])) {
            ++$i;
        }

        return substr($this->data, $start, $i - $start);
    }
}

Lexer::initKeywordsTable();
