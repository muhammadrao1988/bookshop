<?php

namespace SalesDashboard\Service;

class VersionHandlerService
{
    const TIMEZONE_OLD_VERSION = 'Europe/Berlin';
    const TIMEZONE_NEW_VERSION = 'UTC';

    public static function compareVersions($version1, $version2)
    {
        // Extract the version numbers and build arrays for comparison
        $parts1 = explode('+', $version1);
        $versionNumber1 = $parts1[0];
        $buildNumber1 = isset($parts1[1]) ? (int)$parts1[1] : 0;

        $parts2 = explode('+', $version2);
        $versionNumber2 = $parts2[0];
        $buildNumber2 = isset($parts2[1]) ? (int)$parts2[1] : 0;

        // Compare version numbers
        $versionComparison = version_compare($versionNumber1, $versionNumber2);

        // If versions are equal, compare build numbers
        if ($versionComparison === 0) {
            return $buildNumber1 <=> $buildNumber2;
        }

        return $versionComparison;
    }

    public static function convertSaleDate($saleDate, $version)
    {
        // Compare the version with the threshold version
        $thresholdVersion = '1.0.17+60';
        $compareResult = self::compareVersions($version, $thresholdVersion);

        // Convert sale date based on the comparison result
        $timezone = ($compareResult >= 0) ? self::TIMEZONE_NEW_VERSION : self::TIMEZONE_OLD_VERSION;

        $dateTime = new \DateTime($saleDate, new \DateTimeZone($timezone));
        $dateTime->setTimezone(new \DateTimeZone(self::TIMEZONE_NEW_VERSION));

        return $dateTime->format('Y-m-d H:i:s');
    }
}