<?php

class PulqPaginationValidator extends AgaviValidator
{
    const ITEMS_PER_PAGE = 'items_per_page';
    const PAGE = 'page';

    protected function validate()
    {
        $items_per_page = (int)$this->getData(static::ITEMS_PER_PAGE);
        $page = (int)$this->getData(static::PAGE);

        $page = ($page > 0) ? $page : 1;
        $items_per_page = ($items_per_page > 0) ? $items_per_page : null;

        $this->export($page, 'page');
        $this->export($items_per_page, 'items_per_page');

        return true;
    }
    
}
