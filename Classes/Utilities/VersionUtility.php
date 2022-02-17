<?php

namespace MobilisticsGmbH\PrometheusMonitoring\Utilities;

/*
 * This file is part of the TYPO3 extension prometheus_exporter.
 *
 * (c) Mobilistics GmbH <info@mobilistics.de>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

class VersionUtility
{
    /**
     * @param string $versionNumber
     * @return int
     */
    public static function convertVersionToInteger($versionNumber)
    {
        $versionParts = explode('.', $versionNumber);
        $version = $versionParts[0];
        for ($i = 1; $i < 3; ++$i) {
            if (!empty($versionParts[$i])) {
                $version .= str_pad((string)(int)$versionParts[$i], 3, '0', STR_PAD_LEFT);
            } else {
                $version .= '000';
            }
        }

        return (int)$version;
    }

    /**
     * Returns the three part version number (string) from an integer, eg 4012003 -> '4.12.3'
     *
     * @param string $versionInteger
     * @return string
     */
    public static function convertIntegerToVersionNumber($versionInteger)
    {
        $versionString = str_pad($versionInteger, 9, '0', STR_PAD_LEFT);
        $parts = [
            substr($versionString, 0, 3),
            substr($versionString, 3, 3),
            substr($versionString, 6, 3)
        ];
        return (int)$parts[0] . '.' . (int)$parts[1] . '.' . (int)$parts[2];
    }
}
