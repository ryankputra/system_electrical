<?php
// Lightweight stubs for PhpSpreadsheet classes so static analyzers
// don't report undefined types when the library isn't installed.

if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
    eval('namespace PhpOffice\\PhpSpreadsheet; class Spreadsheet {}');
}

if (!class_exists('PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx')) {
    eval('namespace PhpOffice\\PhpSpreadsheet\\Writer; class Xlsx { public function __construct($spreadsheet = null) {} public function save($path) {} }');
}

if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
    eval('namespace PhpOffice\\PhpSpreadsheet; class IOFactory { public static function load($file) { return null; } }');
}

if (!class_exists('PhpOffice\\PhpSpreadsheet\\Style\\Fill')) {
    eval('namespace PhpOffice\\PhpSpreadsheet\\Style; class Fill { const FILL_SOLID = 1; }');
}
