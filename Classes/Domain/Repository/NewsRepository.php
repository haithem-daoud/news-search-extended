<?php

namespace T3hd\NewsSearch\Domain\Repository;

use GeorgRinger\News\Domain\Model\DemandInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Extend News Repository
 * 
 * Author : Haithem Daoud
 * 2022-15-04
 *
 */
class NewsRepository extends \GeorgRinger\News\Domain\Repository\NewsRepository
{
    /**
     * Get the search constraints
     *
     * @param QueryInterface $query
     * @param DemandInterface $demand
     * @return array
     * @throws \UnexpectedValueException
     */
    protected function getSearchConstraints(QueryInterface $query, DemandInterface $demand): array
    {
        $constraints = [];
        if ($demand->getSearch() === null) {
            return $constraints;
        }

        /* @var $searchObject \GeorgRinger\News\Domain\Model\Dto\Search */
        $searchObject = $demand->getSearch();
        $searchSubject = $searchObject->getSubject();
        if (!empty($searchSubject)) {
            $queryBuilder = $this->getQueryBuilder('tx_news_domain_model_news');

            $searchFields = GeneralUtility::trimExplode(',', $searchObject->getFields(), true);
            $searchConstraints = [];

            if (count($searchFields) === 0) {
                throw new \UnexpectedValueException('No search fields defined', 1318497755);
            }
            $searchSubjectSplitted = str_getcsv($searchSubject, ' ');
            if ($searchObject->isSplitSubjectWords()) {
                foreach ($searchFields as $field) {
                    $subConstraints = [];
                    foreach ($searchSubjectSplitted as $searchSubjectSplittedPart) {
                        $searchSubjectSplittedPart = trim($searchSubjectSplittedPart);
                        if ($searchSubjectSplittedPart) {
                            $subConstraints[] = $query->like($field, '%' . $searchSubjectSplittedPart . '%');
                        }
                    }
                    $searchConstraints[] = $query->logicalAnd($subConstraints);
                }
                if (count($searchConstraints)) {
                    $constraints[] = $query->logicalOr($searchConstraints);
                }
            } else {
                if (!empty($searchSubject)) {
                    foreach ($searchFields as $field) {
                        $searchConstraints[] = $query->like($field, '%' . $searchSubject . '%');
                    }
                }
                if (count($searchConstraints)) {
                    $constraints[] = $query->logicalOr($searchConstraints);
                }
            }
        }

        $minimumDate = strtotime($searchObject->getMinimumDate());
        if ($minimumDate) {
            $field = $searchObject->getDateField();
            if (empty($field)) {
                throw new \UnexpectedValueException('No date field is defined', 1396348732);
            }
            $constraints[] = $query->greaterThanOrEqual($field, $minimumDate);
        }
        $maximumDate = strtotime($searchObject->getMaximumDate());
        if ($maximumDate) {
            $field = $searchObject->getDateField();
            if (empty($field)) {
                throw new \UnexpectedValueException('No date field is defined', 1396348733);
            }
            $maximumDate += 86400;
            $constraints[] = $query->lessThanOrEqual($field, $maximumDate);
        }

        $year = $searchObject->getYear();
        if ($year && $year!='all') {
            $field = $searchObject->getDateField();
            if (empty($field)) {
                throw new \UnexpectedValueException('No date field is defined', 1396348733);
            }

            $begin = mktime(0, 0, 0, 1, 1, $year);
            $end = mktime(23, 59, 59, 12, 31, $year);

            $constraints[] = $query->logicalAnd([
                $query->greaterThanOrEqual($field, $begin),
                $query->lessThanOrEqual($field, $end)
            ]);
        }

        return $constraints;
    }
}
