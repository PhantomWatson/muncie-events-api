<?php
namespace App\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;

class ApiCallsListener implements EventListenerInterface
{
    /**
     * implementedEvents() method
     *
     * @return array
     */
    public function implementedEvents()
    {
        return ['apiCall' => 'recordApiCall'];
    }

    /**
     * Passes the event name and metadata to ActivityRecordsTable::add()
     *
     * @param \Cake\Event\Event $event Event
     * @param array $meta Array of metadata (userId, communityId, etc.)
     * @return void
     */
    public function recordApiCall(Event $event, array $meta = null)
    {
        $apiCallsTable = TableRegistry::get('ApiCalls');
        $apiCall = $apiCallsTable->newEntity([
            'user_id' => $meta['userId'],
            'url' => $meta['url']
        ]);
        $apiCallsTable->save($apiCall);
    }
}
