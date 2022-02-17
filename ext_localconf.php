<?php

if(!defined('TYPO3') && !defined('TYPO3_MODE')) {
    die();
}

call_user_func(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['prometheus_exporter_metrics']
        = \MobilisticsGmbH\PrometheusMonitoring\MetricsExporter::class . '::export';
});
