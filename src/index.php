<?php

namespace MyNamespace;

require_once __DIR__ . '/../vendor/autoload.php';

echo (new DateGetter())->get_date();
