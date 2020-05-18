<?php
namespace App\Model\Filter;

use Search\Model\Filter\FilterCollection;

class EventsCollection extends FilterCollection
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->add('q', 'Search.Like', [
            'field' => ['title', 'description', 'location'],

            // Automatically add wildcards before and after a search term
            'before' => true,
            'after' => true,
        ]);
    }
}
