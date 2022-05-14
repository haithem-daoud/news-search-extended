<?php

namespace T3hd\NewsSearch\Domain\Model\Dto;

/**
 * Extend News Domain Model
 * 
 * Author : Haithem Daoud
 * 2022-15-04
 *
 */
class Search extends \GeorgRinger\News\Domain\Model\Dto\Search
{
    /**
     * Basic search word
     *
     * @var string
     */
    protected $year = '';

    /**
     * Get the year
     *
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * Set year
     *
     * @param string $year
     */
    public function setYear(string $year): void
    {
        $this->year = $year;
    }
}
