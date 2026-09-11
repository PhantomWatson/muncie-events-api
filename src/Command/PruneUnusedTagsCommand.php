<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\Tag;
use App\Model\Table\TagsTable;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;

/**
 * Finds low-usage tags and interactively deletes them
 *
 * A tag is offered for deletion when it is associated with fewer than {@see self::MIN_EVENTS} events and none
 * of its associated events were created within the past year. Tags that have child tags are left alone, since
 * deleting them would also delete their whole subtree. Tags named in {@see self::EXEMPT_TAG_NAMES}, along with
 * all of their descendants, are never offered for deletion.
 */
class PruneUnusedTagsCommand extends Command
{
    /**
     * A tag is only considered for deletion if it has fewer than this many associated events
     *
     * @var int
     */
    public const int MIN_EVENTS = 2;

    /**
     * Tags with any of these names (matched case-insensitively), along with all of their descendants, are
     * never offered for deletion
     *
     * @var string[]
     */
    public const array EXEMPT_TAG_NAMES = [
        'delete',
        'musical genres',
        'musical instruments',
        'unlisted',
    ];

    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription(sprintf(
            'Find tags with fewer than %d associated events and no events created in the past year, then ask '
            . 'whether to delete each one.',
            self::MIN_EVENTS,
        ));

        return $parser;
    }

    /**
     * Prompts the user to delete each low-usage tag
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int The exit code
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        /** @var \App\Model\Table\TagsTable $tagsTable */
        $tagsTable = $this->fetchTable('Tags');

        $io->out('Searching for low-usage tags...');
        $exemptTagIds = $this->findExemptTagIds($tagsTable);
        $candidates = $this->findLowUsageTags($tagsTable, $exemptTagIds);
        if (!$candidates) {
            $io->success('No low-usage tags found.');

            return static::CODE_SUCCESS;
        }

        $parentIds = $this->findParentTagIds($tagsTable, array_map(fn(Tag $tag): int => $tag->id, $candidates));
        $io->out(sprintf('Found %d candidate tag(s).', count($candidates)));

        $deleted = 0;
        $kept = 0;
        $skipped = 0;
        foreach ($candidates as $tag) {
            // Parent tags don't get deleted
            if (in_array($tag->id, $parentIds, true)) {
                $skipped++;

                continue;
            }

            $eventCount = (int)$tag->get('event_count');
            $io->out();
            $io->out(sprintf('<info>%s</info> (#%d)', $tag->name, $tag->id));
            $io->out(sprintf(
                ' - %d associated event%s, none created in the past year',
                $eventCount,
                $eventCount === 1 ? '' : 's',
            ));

            if ($io->askChoice('Delete this tag?', ['y', 'n'], 'n') !== 'y') {
                $kept++;

                continue;
            }

            if ($tagsTable->delete($tag)) {
                $io->success(sprintf(' - Deleted "%s"', $tag->name));
                $deleted++;
            } else {
                $io->err(sprintf(' - Could not delete "%s"', $tag->name));
            }
        }

        $io->out();
        $io->out(sprintf('%d deleted, %d kept.', $deleted, $kept));
        if ($skipped > 0) {
            $io->out(sprintf('%d skipped for having child tags.', $skipped));
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Returns tags with fewer than self::MAX_EVENTS associated events and no associated event created in the
     * past year
     *
     * Each returned entity carries an additional `event_count` field.
     *
     * @param \App\Model\Table\TagsTable $tagsTable Tags table
     * @param int[] $exemptTagIds IDs of tags to exclude from the results
     * @return \App\Model\Entity\Tag[]
     */
    private function findLowUsageTags(TagsTable $tagsTable, array $exemptTagIds): array
    {
        $oneYearAgo = new DateTime('-1 year');
        $query = $tagsTable->find();
        if ($exemptTagIds) {
            $query->where(['Tags.id NOT IN' => $exemptTagIds]);
        }

        return $query
            ->select([
                'Tags.id',
                'Tags.name',
                'event_count' => $query->func()->count('Events.id'),
                'recent_event_count' => $query->func()->count(
                    $query->expr()
                        ->case()
                        ->when(['Events.created >=' => $oneYearAgo])
                        ->then(1),
                ),
            ])
            ->leftJoinWith('Events')
            ->groupBy(['Tags.id', 'Tags.name'])
            ->having([
                'event_count <' => self::MIN_EVENTS,
                'recent_event_count' => 0,
            ])
            ->orderBy(['Tags.name' => 'ASC'])
            ->all()
            ->toList();
    }

    /**
     * Returns the IDs of every tag named in self::EXEMPT_TAG_NAMES and every descendant of those tags
     *
     * @param \App\Model\Table\TagsTable $tagsTable Tags table
     * @return int[]
     */
    private function findExemptTagIds(TagsTable $tagsTable): array
    {
        $exemptIds = $tagsTable->find()
            ->select(['id'])
            ->where(['LOWER(Tags.name) IN' => self::EXEMPT_TAG_NAMES])
            ->all()
            ->extract('id')
            ->toList();

        $parentIds = $exemptIds;
        while ($parentIds) {
            $childIds = $tagsTable->find()
                ->select(['id'])
                ->where(['parent_id IN' => $parentIds])
                ->all()
                ->extract('id')
                ->toList();

            // array_diff also breaks out of any circular parent/child references
            $childIds = array_values(array_diff($childIds, $exemptIds));
            $exemptIds = array_merge($exemptIds, $childIds);
            $parentIds = $childIds;
        }

        return $exemptIds;
    }

    /**
     * Returns the IDs of the given tags that have at least one child tag
     *
     * @param \App\Model\Table\TagsTable $tagsTable Tags table
     * @param int[] $tagIds Tag IDs to check
     * @return int[]
     */
    private function findParentTagIds(TagsTable $tagsTable, array $tagIds): array
    {
        if (!$tagIds) {
            return [];
        }

        return $tagsTable->find()
            ->select(['parent_id'])
            ->where(['parent_id IN' => $tagIds])
            ->distinct(['parent_id'])
            ->all()
            ->extract('parent_id')
            ->toList();
    }
}
