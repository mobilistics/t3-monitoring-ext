<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "prometheus_monitoring"
 *
 * Auto generated 2021-08-20
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/
// @phpstan-ignore-next-line
$EM_CONF[$_EXTKEY] = [
    'title' => 'TYPO3 Prometheus Monitoring',
    'description' => 'Export prometheus compatible metrics of a TYPO3 instance',
    'category' => 'misc',
    'author' => 'Mobilistics GmbH',
    'author_email' => 'info@mobilistics.de',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-11.5.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
