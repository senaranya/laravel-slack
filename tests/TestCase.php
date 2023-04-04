<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public static function areArraysSame(array $array1, array $array2): bool
    {
        return self::arrayDiffRecursive($array1, $array2) === [];
    }

    private static function arrayDiffRecursive(array $array1, array $array2): array
    {
        $aReturn = [];

        foreach ($array1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $array2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = self::arrayDiffRecursive($mValue, $array2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                }
                elseif ($mValue !== $array2[$mKey]) {
                    $aReturn[$mKey] = $mValue;
                }
            }
            else {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }
}
