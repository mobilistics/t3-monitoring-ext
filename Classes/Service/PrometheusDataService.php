<?php

declare(strict_types=1);

namespace MobilisticsGmbH\PrometheusMonitoring\Service;

/*
 *
 * This file is part of the "${EXTENSION_NAME}" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 *  (c) 2022 Sebastian Richter <s.richter@raphael-gmbh.de>, Raphael GmbH
 *
 */

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use MobilisticsGmbH\PrometheusMonitoring\Utilities\VersionUtility;

class PrometheusDataService
{
    /**
     * @var PackageManager
     */
    protected $packageManager;

    public function __construct()
    {
        $this->packageManager = GeneralUtility::makeInstance(PackageManager::class);
    }

    /**
     * @return string
     */
    public function getPrometheusData(): string
    {
        $data = [];
        // get current TYPO3 version
        // @phpstan-ignore-next-line
        $TYPO3_version = new Typo3Version();

        $data['typo3Version'] =   $TYPO3_version->getVersion();

        // get installed extensions and version number
        $installedExtensions = $this->packageManager->getActivePackages();

        foreach ($installedExtensions as $package) {
            $extKey = $package->getPackageKey();
            $data['extensions'][$extKey]['siteRelPath'] = $package->getPackagePath();
            $data['extensions'][$extKey]['version'] = $package->getPackageMetaData()->getVersion();
        }

        return $this->getPrometheusFormattedData($data);
    }

    /**
     * @param array $dataToFormat
     * @return string
     */
    private function getPrometheusFormattedData(array $dataToFormat): string
    {
        $formattedData = 'typo3_version_state{actual="' . $dataToFormat['typo3Version'] . '"} ' . VersionUtility::convertVersionToInteger($dataToFormat['typo3Version']) . PHP_EOL;
        foreach ($dataToFormat['extensions'] as $key => $value) {

            // cleanup version number
            if(substr((string) $value['version'],-1,1)  == 'v' || substr((string) $value['version'],0,1) == 'v') {
                $value['version'] = str_replace("v", "", strtolower((string) $value['version']));
            }

            if (!strpos((string) $value['siteRelPath'], "sysext")) {
                // third party extensions
                $formattedData .= 'typo3_extension_state{extKey="' . $key . '", actual="'. $value['version'] .'"} ' . VersionUtility::convertVersionToInteger($value['version']) . PHP_EOL;
            } else {
                // core extensions
                $formattedData .= 'typo3_core_extension_state{extKey="' . $key . '", actual="'. $value['version'] .'"} ' . VersionUtility::convertVersionToInteger($value['version']) . PHP_EOL;
            }
        }

        // Add static metrics
        $formattedData .= $this->getPhpVersionMetric();

        return $formattedData;
    }

    private function getPhpVersionMetric(): string
    {
        $phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;

        return sprintf('php_info{version="%s"} 1', $phpVersion) . PHP_EOL;
    }
}
