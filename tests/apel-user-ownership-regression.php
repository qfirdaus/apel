<?php
declare(strict_types=1);

require_once __DIR__ . '/../public/extensions/project/apel-user-ownership.php';

$failures = [];
$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};

$assert(apel_user_identifier(['matrik' => ' 2260123 ']) === '2260123', 'matrik normalization failed');
$assert(apel_user_identifier(['f_loginID' => ' apel-01 ']) === 'APEL-01', 'login normalization failed');
$assert(apel_user_identifier([], ['identifier' => ' abc ']) === 'ABC', 'context identifier precedence failed');
$assert(apel_user_identifier([]) === '', 'empty identifier must stay empty');

if ($failures !== []) {
    fwrite(STDERR, "APEL ownership regression FAILED\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "APEL ownership regression PASS\n";

