<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Shell\Helper\ProgressHelper;

/**
 * Copies image files from a previous version (e.g. CakePHP 3) to the current version of the website
 *
 * Depends on the migrateFilesFromDir config value being set
 */
class MigrateImageFilesCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $cake3DirName = Configure::read('migrateFilesFromDir');
        if (!$cake3DirName) {
            $io->out('migrateFilesFromDir not configured');
            return;
        }

        $cake3ImgDir = dirname(ROOT) . DS . $cake3DirName . DS . 'webroot' . DS . 'img' . DS . 'events';
        if (!is_dir($cake3ImgDir)) {
            $io->out('cake3ImgDir not found: ' . $cake3ImgDir);
            return;
        }

        $cake4ImgDir = ROOT . DS . 'webroot' . DS . 'img' . DS . 'events';
        if (!is_dir($cake4ImgDir) && !mkdir($cake4ImgDir. 0755)) {
            $io->out('cake4ImgDir not found and couldn\'t be created: ' . $cake4ImgDir);
            return;
        }

        $subdirNames = ['full', 'small', 'tiny'];
        foreach ($subdirNames as $subdirName) {
            $io->out("Processing $subdirName images...");
            $sourceSubdir = $cake3ImgDir . DS . $subdirName;
            $destinationSubdir = $cake4ImgDir . DS . $subdirName;

            if (!is_dir($destinationSubdir)) {
                mkdir($destinationSubdir, 0755);
            }

            $files = scandir($sourceSubdir);
            $count = count($files) - 2;
            $io->out('- ' . number_format($count) . ' images found');
            /** @var ProgressHelper $progress */
            $progress = $io->helper('Progress');
            $progress->init([
                'total' => $count,
                'width' => 20,
            ]);

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if (!file_exists($destinationSubdir . DS . $file)) {
                    copy($sourceSubdir. DS . $file, $destinationSubdir . DS . $file);
                }
                $progress->increment(1);
                $progress->draw();
            }
            $io->out();
            $io->out('- Done');
            $io->out();
        }

        $io->success('Finished');
    }
}
