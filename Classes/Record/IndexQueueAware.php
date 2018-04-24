<?php
namespace StrIo\SolrIndexingMonitor\Record;

use ApacheSolrForTypo3\Solr\IndexQueue\RecordMonitor;
use ApacheSolrForTypo3\Solr\Util as SolrUtil;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IndexQueueAware extends RecordMonitor implements FormDataProviderInterface
{
    public function addData(array $result): array
    {
        $recordTable = $result['tableName'];
        $recordPid = $result['effectivePid'];
        $recordUid = $result['vanillaUid'];
        $configurationPageId = $this->getConfigurationPageId($recordTable, $recordPid, $recordUid);

        if ($configurationPageId > 0) {
            $solrConfiguration = SolrUtil::getSolrConfigurationFromPageId($configurationPageId);
            $isMonitoredRecord = $solrConfiguration->getIndexQueueIsMonitoredTable($recordTable);

            if ($isMonitoredRecord && $this->indexQueue->containsItem($recordTable, $recordUid)) {
                $indexQueueItem = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
                    '*',
                    'tx_solr_indexqueue_item',
                    'item_type = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(
                        $recordTable,
                        'tx_solr_indexqueue_item'
                    ) .
                    ' AND item_uid = ' . (int)$recordUid
                );

                if (!empty($indexQueueItem)) {
                    $indexed = date('d.m.Y H:i:s', $indexQueueItem['indexed']);

                    if ($indexQueueItem['indexed'] > 0 && $indexQueueItem['changed'] > $indexQueueItem['indexed']) {
                        $message = GeneralUtility::makeInstance(
                            FlashMessage::class,
                            'The solr indexing of this record is pending. Last time indexed: ' . $indexed,
                            'Indexing pending',
                            FlashMessage::INFO
                        );
                    }

                    if ($indexQueueItem['errors'] !== '') {
                        $message = GeneralUtility::makeInstance(
                            FlashMessage::class,
                            substr($indexQueueItem['errors'], 0, 500) . '... see Module "Index Queue" > "Indexing Errors"',
                            'Indexing error',
                            FlashMessage::ERROR
                        );
                    }

                    if ($message instanceof FlashMessage) {
                        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                        $messageQueue->addMessage($message);
                    }
                }
            }
        }

        return $result;
    }
}
