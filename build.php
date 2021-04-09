<?php

require __DIR__ . '/vendor/autoload.php';

use SQLParser\Lexer;

$constants = '';

foreach (Lexer::getKeywords() as $code => $id) {
    $constants .= 'const T_' . strtoupper($code) . ' = ' . var_export($code, true) . ";\n\n";
}

$code = '<?php

namespace SQL;

class ReservedWords
{
    ' . $constants . '
    public static $words = ' . var_export(Lexer::getKeywords(), true) . ';
}';


file_put_contents(__DIR__ . '/src/SQL/ReservedWords.php', $code);
