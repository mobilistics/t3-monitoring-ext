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
        $data['typo3Version'] = TYPO3_version;

        // get installed extensions and version number
        $installedExtensions = $this->packageManager->getActivePackages();
        foreach ($installedExtensions as $package) {
            $packagePath = $package->getPackagePath();
            if (!strpos($packagePath, "sysext") && !strpos($packagePath, "typo3conf")) {
                continue;
            }

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
            $value['version'] = str_replace("v", "", strtolower($value['version']));

            if (!strpos($value['siteRelPath'], "sysext")) {
                // third party extensions
                $formattedData .= 'typo3_extension_state{extKey="' . $key . '" actual="'. $value['version'] .'"} ' . VersionUtility::convertVersionToInteger($value['version']) . PHP_EOL;
            } else {
                // core extensions
                $formattedData .= 'typo3_core_extension_state{extKey="' . $key . '" actual="'. $value['version'] .'"} ' . VersionUtility::convertVersionToInteger($value['version']) . PHP_EOL;
            }
        }

        return $formattedData;
    }
}
