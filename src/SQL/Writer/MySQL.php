<?php

namespace SQL\Writer;

use SQL\Writer;

class MySQL extends Writer
{
    public function escape($value)
    {
        return "`$value`";
    }
}
