<?php

namespace MobilisticsGmbH\PrometheusMonitoring;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use MobilisticsGmbH\PrometheusMonitoring\Utilities\VersionUtility;
use MobilisticsGmbH\PrometheusMonitoring\Service\PrometheusDataService;

class MetricsExporter extends ActionController
{
    public function main()
    {
        // check if secret key is given and correct
        if (!$this->checkIfSecretKeyIsGiven($_GET)) {
            header(' ', true, 404);die();
        }

        // get prometheus data
        $prometheusDataService = GeneralUtility::makeInstance(PrometheusDataService::class);
        $data = $prometheusDataService->getPrometheusData();

        // set response
        header('Content-Type', 'text/plain; charset=utf-8');
        echo nl2br($data);die();
    }

    /**
     * @param array $queryParams
     * @return bool
     */
    protected function checkIfSecretKeyIsGiven(array $queryParams)
    {
        $settings = $this->getSettings();
        // secret
        if (!empty($settings['secret']) && strlen($settings['secret']) >= 32) {
            $secret = $queryParams['secret'] ? $queryParams['secret'] : '';
            if ($secret !== $settings['secret']) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        $configuration = [];
        try {
            $isVersion9Up = VersionUtility::convertVersionToInteger(TYPO3_version) >= 9000000;
            if ($isVersion9Up) {
                $extensionConfiguration = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class);
                $configuration = $extensionConfiguration->get('prometheus_monitoring');
            } elseif (array_key_exists('prometheus_monitoring', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'])) {
                $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['prometheus_monitoring']);
            }
        } catch (\Exception $exception) {
            // do nothing
        }

        return $configuration;
    }
}

$eid = GeneralUtility::makeInstance('MobilisticsGmbH\PrometheusMonitoring\MetricsExporter');
echo $eid->main();
