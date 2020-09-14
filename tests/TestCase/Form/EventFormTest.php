<?php

namespace App\Test\TestCase\Form;

use App\Form\EventForm;
use Cake\TestSuite\TestCase;

/**
 * App\Form\EventForm Test Case
 */
class EventFormTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Form\EventForm
     */
    public $Event;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Event = new EventForm();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Event);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
