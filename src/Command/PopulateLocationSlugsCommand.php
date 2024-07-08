<?php
namespace App\Command;

use App\Model\Entity\Event;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Cake\Shell\Helper\ProgressHelper;

/**
 * PopulateLocationSlugs command
 *
 * @property ConsoleIo $io
 */
class PopulateLocationSlugsCommand extends Command
{

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/3.0/en/console-and-shells/commands.html#defining-arguments-and-options
     *
     * @param ConsoleOptionParser $parser The parser to be defined
     * @return ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Sets the location_slug field for every event
     *
     * Intended to be run once, after the location_slug field is added, but can be run at any time to overwrite
     * potentially incorrect slugs with newly-generated slugs
     *
     * @param Arguments $args The command arguments.
     * @param ConsoleIo $io The console io
     * @return void
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('Collecting events...');
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $events = $eventsTable
            ->find()
            ->select(['id', 'location']);
        $count = $events->count();
        $io->out(sprintf(' - %s events found', number_format($count)));

        $io->out();
        $io->out('Setting location slugs...');
        /** @var ProgressHelper $progress */
        $progress = $io->helper('Progress');
        $progress->init([
            'total' => $count,
            'width' => 40,
        ]);
        $progress->draw();
        foreach ($events as $event) {
            /** @var Event $event */
            $event->setLocationSlug();

            if (!$eventsTable->save($event)) {
                $io->err('Error updating event. Details:');
                $io->out(print_r($event->getErrors(), true));
                $this->abort();
            }

            $progress->increment(1);
            $progress->draw();
        }

        $io->out();
        $io->success('Done');
    }
}
