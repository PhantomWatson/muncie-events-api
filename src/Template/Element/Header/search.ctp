<?php

use Cake\Routing\Router;

$this->Html->scriptBlock('setupSearch();', ['block' => true]);
?>
<img src="/img/loading_small_dark.gif" id="search_autocomplete_loading" alt="Loading..."/>
<form class="form-inline my-2 my-lg-0" id="EventSearchForm"
      action="<?= Router::url(['controller' => 'Events', 'action' => 'search']) ?>">
    <div class="input-group">
        <input class="form-control mr-2 my-2 my-sm-0" type="search" placeholder="Search events"
               aria-label="Search events" name="filter" id="header-search"/>
        <div class="input-group-append btn-group">
            <button type="submit" class="btn btn-light my-2 my-sm-0 d-none d-xl-inline">
                Search
            </button>
            <button type="submit" class="btn btn-light my-2 my-sm-0 d-xl-none">
                <span class="fas fa-search"></span>
            </button>
            <button id="search_options_toggler" class="dropdown-toggle btn btn-light my-2 my-sm-0"
                    type="button"
                    data-toggle="collapse" aria-haspopup="true" aria-expanded="false"
                    data-target="#search_options">
                <span class="caret"></span>
                <span class="sr-only">Search options</span>
            </button>
            <div id="search_options" class="collapse" aria-labelledby="search_options_toggler">
                <div>
                    <label class="sr-only" for="direction">
                        Direction of events
                    </label>
                    <?= $this->Form->control('direction', [
                        'options' => [
                            'upcoming' => 'Upcoming',
                            'past' => 'Past Events',
                            'all' => 'All Events'
                        ],
                        'default' => 'upcoming',
                        'type' => 'radio',
                        'label' => false,
                        'legend' => false
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</form>
