<?php

namespace Pulq\Agavi\Validator;

class PaginationValidator extends \AgaviValidator
{
    const ITEMS_PER_PAGE = 'items_per_page';
    const PAGE = 'page';

    protected function validate()
    {
        $page_argument = $this->getParameter('page_argument', static::PAGE);
        $items_argument = $this->getParameter('items_argument', static::ITEMS_PER_PAGE);

        $items_per_page = (int)$this->getData($items_argument);
        $page = (int)$this->getData($page_argument);

        $page = ($page > 0) ? $page : 1;
        $items_per_page = ($items_per_page > 0) ? $items_per_page : null;

        $this->export($page, 'page');

        if ($items_per_page !== null) {
            $this->export($items_per_page, 'items_per_page');
        }

        return true;
    }

}
