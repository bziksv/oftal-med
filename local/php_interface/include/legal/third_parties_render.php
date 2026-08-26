<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once __DIR__ . '/legal_helpers.php';

function oftalMedLegalThirdPartiesData(): array
{
    static $data = null;
    if ($data === null) {
        $data = include __DIR__ . '/third_parties_data.php';
    }

    return $data;
}

function oftalMedLegalRenderThirdPartyServiceName(array $service): string
{
    $name = legal_var($service['name']);
    if (!empty($service['inn'])) {
        $name .= ' (ИНН ' . legal_var($service['inn']) . ')';
    }

    return $name;
}

function oftalMedLegalRenderThirdPartyRecommendationLine(array $block, ?array $service = null): string
{
    $links = [];
    foreach ($block['urls'] as $url) {
        $links[] = '<a href="' . legal_h($url) . '" target="_blank" rel="noopener">'
            . legal_var($url) . '</a>';
    }

    $line = implode(', ', $links) . ' — ' . legal_var($block['text']);
    if ($service !== null) {
        $line = oftalMedLegalRenderThirdPartyServiceName($service) . ' — ' . $line;
    }

    return $line;
}

function oftalMedLegalRenderThirdPartyUrlListItems(): string
{
    $html = '';
    foreach (oftalMedLegalThirdPartiesData()['services'] as $service) {
        if (empty($service['recommendation'])) {
            continue;
        }
        foreach ($service['recommendation'] as $block) {
            $html .= '<li>' . oftalMedLegalRenderThirdPartyRecommendationLine($block, $service) . ";</li>\n        ";
        }
    }

    return $html;
}
