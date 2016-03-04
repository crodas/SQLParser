phplemon src/SQLParser/Parser.y
plex src/SQLParser/Lexer.lex > /dev/null
sed -i bak "s/SINGLE_QUOTE/\\\\'/g" src/SQLParser/Lexer.php
FILE=src/SQL/ReservedWords.php
echo "<?php return array(" > $FILE
cat src/SQLParser/Lexer.lex | egrep -oE '^\s*"([a-z]+)"' | sed 's/$/ => 1,/' >> $FILE
echo ");" >> $FILE
php build.php
