<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Versioning\VersionState;

class LocalizationController extends \TYPO3\CMS\Backend\Controller\Page\LocalizationController
{
    /**
     * @codeCoverageIgnore
     */
    public function getRecordLocalizeSummary(
        ServerRequestInterface $request,
        ResponseInterface $response = null
    ): ResponseInterface {
        $params = $request->getQueryParams();
        if (!isset($params['pageId'], $params['destLanguageId'], $params['languageId'])) {
            return new JsonResponse(null, 400);
        }

        $pageId = (int)$params['pageId'];
        $destLanguageId = (int)$params['destLanguageId'];
        $languageId = (int)$params['languageId'];

        $records = [];
        $result = $this->localizationRepository->getRecordsToCopyDatabaseResult(
            $pageId,
            $destLanguageId,
            $languageId,
            '*'
        );

        $columns = $this->getPageColumns($pageId);

        while ($row = $result->fetch()) {
            BackendUtility::workspaceOL('tt_content', $row, -99, false);
            if (!$row || VersionState::cast($row['t3ver_state'])->equals(VersionState::DELETE_PLACEHOLDER)) {
                continue;
            }
            $colPos = $row['colPos'];
            if (!isset($records[$colPos])) {
                $records[$colPos] = [];
            }
            if ($colPos >= ColumnNumberUtility::MULTIPLIER && !isset($columns['columns'][$colPos])) {
                $columns[$colPos] = 'Unknown';
                $columns['columnList'][] = (string) $colPos;
            }
            $records[$colPos][] = [
                'icon' => $this->iconFactory->getIconForRecord('tt_content', $row, Icon::SIZE_SMALL)->render(),
                'title' => $row[$GLOBALS['TCA']['tt_content']['ctrl']['label']],
                'uid' => $row['uid']
            ];
        }

        $columns['columnList'] = array_values(array_unique($columns['columnList'] ?? []));

        $payload = [
            'records' => $records,
            'columns' => $columns,
        ];
        return (new JsonResponse())->setPayload($payload);
    }
}
