<?php

require __DIR__ . '/vendor/autoload.php';

use SQLParser\Lexer;

$constants = '';
$array     = '';

foreach (Lexer::getKeywords() as $code => $id) {
    $array     .= 'self::T_' . strtoupper($code) . " => true,\n";
    $constants .= 'const T_' . strtoupper($code) . ' = ' . var_export($code, true) . ";\n\n";
}

$code = '<?php

namespace SQL;

class ReservedWords
{
    ' . $constants ."
    public static \$words = [\n" . $array . '];
}';


file_put_contents(__DIR__ . '/src/SQL/ReservedWords.php', $code);
