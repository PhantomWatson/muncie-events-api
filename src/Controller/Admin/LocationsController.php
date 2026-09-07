<?php
namespace App\Controller\Admin;

use App\Model\Entity\Event;

/**
 * Locations Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 */
class LocationsController extends AdminController
{
    /**
     * "Manage locations" page
     *
     * @return void
     */
    public function index(): void
    {
        $this->set([
            'pageTitle' => 'Manage Locations',
            'locationNames' => $this->Events->getUniqueLocationNames(),
        ]);
    }

    /**
     * Renames every event at the target location to the destination location, and redirects back to the
     * management page
     *
     * @return \Cake\Http\Response
     */
    public function merge()
    {
        $this->request->allowMethod('post');
        $redirectTo = ['action' => 'index'];

        $targetLocation = trim((string)$this->request->getData('target_location'));
        $destinationLocation = trim((string)$this->request->getData('destination_location'));

        if ($targetLocation === '') {
            $this->Flash->error('No target location was selected.');

            return $this->redirect($redirectTo);
        }
        if ($destinationLocation === '') {
            $this->Flash->error('No destination location was selected.');

            return $this->redirect($redirectTo);
        }
        if ($targetLocation === $destinationLocation) {
            $this->Flash->error("Cannot merge \"$targetLocation\" into itself.");

            return $this->redirect($redirectTo);
        }
        if (in_array(Event::VIRTUAL_LOCATION, [$targetLocation, $destinationLocation], true)) {
            $this->Flash->error('"' . Event::VIRTUAL_LOCATION . '" cannot be part of a location merge.');

            return $this->redirect($redirectTo);
        }

        $locationNames = $this->Events->getUniqueLocationNames();
        if (!in_array($targetLocation, $locationNames, true)) {
            $this->Flash->error("The location \"$targetLocation\" could not be found.");

            return $this->redirect($redirectTo);
        }
        if (!in_array($destinationLocation, $locationNames, true)) {
            $this->Flash->error("The location \"$destinationLocation\" could not be found.");

            return $this->redirect($redirectTo);
        }

        $count = $this->Events->mergeLocations($targetLocation, $destinationLocation);
        $this->Flash->success(sprintf(
            'Updated %d event%s from "%s" to "%s".',
            $count,
            $count == 1 ? '' : 's',
            $targetLocation,
            $destinationLocation
        ));

        return $this->redirect($redirectTo);
    }
}
