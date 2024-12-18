<?php

declare(strict_types=1);

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

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use MobilisticsGmbH\PrometheusMonitoring\Utilities\VersionUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use MobilisticsGmbH\PrometheusMonitoring\Service\PrometheusDataService;

class MetricsExporter
{
    public function __construct(
        private PrometheusDataService $prometheusDataService,
        private ExtensionConfiguration $extensionConfiguration,
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function export(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response();
        $settings = $this->getSettings();

        $access = false;
        $method = 'hmac';

        if ((!empty($settings['secret']) && strlen((string) $settings['secret']) >= 32)) {
            if (!isset($settings['unsecure']) || $settings['unsecure'] != 1) {
                $header = getallheaders();
                if (is_array($header) && array_key_exists('Hmac', $header)) {
                    $hmac_header = $header['Hmac'];
                    $body = file_get_contents('php://input');

                    if (hash_equals($hmac_header, hash_hmac('sha256', $body, (string) $settings['secret']))) {
                        $access = true;
                    }
                }
            } else {
                // check if secret key is given and correct
                if (!$this->checkIfSecretKeyIsGiven($request->getQueryParams())) {
                    return $response->withStatus(404);
                }

                $access = true;
                $method = 'get';
            }

            if ($access) {
                // get prometheus data
                $data = $this->prometheusDataService->getPrometheusData();

                if ($method == 'get') {
                    $response = $response->withHeader('Content-Type', 'text/plain; charset=utf-8');
                } else {
                    $response = $response->withHeader('Content-Type', 'text/plain; charset=utf-8')
                        ->withAddedHeader('HMAC', hash_hmac('sha256', $data, (string) $settings['secret']));
                }

                $response->getBody()->write($data);
                return $response;
            }
        }

        return $response->withStatus(404);
    }

    /**
     * @param array $queryParams
     * @return bool
     */
    protected function checkIfSecretKeyIsGiven(array $queryParams): bool
    {
        $settings = $this->getSettings();
        // secret
        if (!empty($settings['secret']) && strlen((string) $settings['secret']) >= 32) {
            $secret = $queryParams['secret'] ?? '';
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
    protected function getSettings(): array
    {
        $configuration = [];
        try {
            $configuration = $this->extensionConfiguration->get('prometheus_monitoring');
        } catch (\Exception $exception) {
            // do nothing
        }

        return $configuration;
    }
}
