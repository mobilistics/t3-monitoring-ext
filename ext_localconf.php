<?php

use MobilisticsGmbH\PrometheusMonitoring\MetricsExporter;
if(!defined('TYPO3') && !defined('TYPO3_MODE')) {
    die();
}

call_user_func(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['prometheus_exporter_metrics']
        = MetricsExporter::class . '::export';
});
