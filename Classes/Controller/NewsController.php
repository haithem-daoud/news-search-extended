<?php

namespace T3hd\NewsSearch\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;

use GeorgRinger\NumberedPagination\NumberedPagination;
use GeorgRinger\News\Domain\Model\Dto\Search;
use GeorgRinger\News\Event\NewsSearchResultActionEvent;
use GeorgRinger\News\Event\NewsSearchFormActionEvent;

/**
 * Extend News Controller
 * 
 * Author : Haithem Daoud
 * 2022-15-04
 *
 */
class NewsController extends \GeorgRinger\News\Controller\NewsController
{
    /**
     * Extend News Search Form
     *
     * @param \GeorgRinger\News\Domain\Model\Dto\Search $search
     * @param array $overwriteDemand
     *
     * @return void
     */
    public function searchFormAction(
        Search $search = null,
        array $overwriteDemand = []
    ): void {
        $demand = $this->createDemandObjectFromSettings($this->settings);
        $demand->setActionAndClass(__METHOD__, __CLASS__);

        if ($this->settings['disableOverrideDemand'] != 1 && $overwriteDemand !== null) {
            $demand = $this->overwriteDemandObject($demand, $overwriteDemand);
        }

        $statistics = $this->newsRepository->countByDate($demand);

        if (is_null($search)) {
            $search = GeneralUtility::makeInstance(Search::class);
        }

        if($this->request->hasArgument('year') && $this->request->hasArgument('subject')) {
            $search->setSubject($this->request->getArgument('subject'));
            $year = $this->request->hasArgument('year') ? $this->request->getArgument('year') : '';
            $search->setYear($year);
        }

        if($search->getSubject()) {
            $demand->setSearch($search);
        }

        $assignedValues = [
            'data' => $statistics,
            'search' => $search,
            'overwriteDemand' => $overwriteDemand,
            'demand' => $demand,
            'settings' => $this->settings,
            'year' => $year ?? null
        ];

        $event = $this->eventDispatcher->dispatch(new NewsSearchFormActionEvent($this, $assignedValues));

        $this->view->assignMultiple($event->getAssignedValues());
    }

    /**
     * Display news search result
     *
     * @param \GeorgRinger\News\Domain\Model\Dto\Search $search
     * @param array $overwriteDemand
     *
     * @return void
     */
    public function searchResultAction(
        Search $search = null,
        array $overwriteDemand = []
    ): void {

         # for handling year select ajax request
         if ($this->request->hasArgument('search') && $this->request->getArgument('search')['year']) {
            if ($this->request->hasArgument('settings')) {
                $object = json_decode($this->request->getArgument('settings'));
                $this->settings = $this->stdToArray($object);
            }
        }

        $demand = $this->createDemandObjectFromSettings($this->settings);
        $demand->setActionAndClass(__METHOD__, __CLASS__);

        if ($this->settings['disableOverrideDemand'] != 1 && $overwriteDemand !== null) {
            $demand = $this->overwriteDemandObject($demand, $overwriteDemand);
        }

        if (!is_null($search)) {
            $this->initializeSearchParams($search);
        }

        /**
         *  Check for existing search parameters
         *  If so re-initialize search class and re-pass parameters
         */
        if($this->request->hasArgument('subject') || $this->request->hasArgument('year')) {
            $search = GeneralUtility::makeInstance(Search::class);
            $this->initializeSearchParams($search);
            $search->setSubject($this->request->hasArgument('subject') ? $this->request->getArgument('subject') : '');
            $year = $this->request->hasArgument('year') ? $this->request->getArgument('year') : '';
            $search->setYear($year);
        }

        if($search) {
            $demand->setSearch($search);
        }

        $newsRecords = $this->newsRepository->findDemanded($demand);

        // pagination
        $paginationConfiguration = $this->settings['list']['paginate'] ?? [];
        $itemsPerPage = (int)($paginationConfiguration['itemsPerPage'] ?: 10);
        $maximumNumberOfLinks = (int)($paginationConfiguration['maximumNumberOfLinks'] ?? 0);

        $currentPage = $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1;

        $paginator = GeneralUtility::makeInstance(QueryResultPaginator::class, $newsRecords, $currentPage, $itemsPerPage);
        $paginationClass = $paginationConfiguration['class'] ?? SimplePagination::class;
        if ($paginationClass === NumberedPagination::class && $maximumNumberOfLinks && class_exists(NumberedPagination::class)) {
            $pagination = GeneralUtility::makeInstance(NumberedPagination::class, $paginator, $maximumNumberOfLinks);
        } else {
            $pagination = GeneralUtility::makeInstance(SimplePagination::class, $paginator);
        }

        $assignedValues = [
            'news' => $newsRecords,
            'overwriteDemand' => $overwriteDemand,
            'search' => $search,
            'demand' => $demand,
            'settings' => $this->settings,
            'pagination' => [
                'currentPage' => $currentPage,
                'paginator' => $paginator,
                'pagination' => $pagination,
            ],
        ];

        $event = $this->eventDispatcher->dispatch(new NewsSearchResultActionEvent($this, $assignedValues));

        $this->view->assignMultiple($event->getAssignedValues());
    }

    /**
     * Convert Object to array
     *
     * @param $obj
     * @return array
     */
    protected function stdToArray($obj){
        $reaged = (array)$obj;
        foreach($reaged as $key => &$field){
          if(is_object($field))$field = $this->stdToArray($field);
        }
        return $reaged;
    }

    /**
     * Initialize params for the search query
     * 
     * @param array $search
     */
    protected function initializeSearchParams($search) {
        $search->setFields($this->settings['search']['fields']);
        $search->setDateField($this->settings['dateField']);
        $search->setSplitSubjectWords((bool)$this->settings['search']['splitSearchWord']);
    }
}
