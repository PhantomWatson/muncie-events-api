<?php
namespace App\Test\TestCase\Command;

use App\Model\Table\TagsTable;
use App\Test\Fixture\TagsFixture;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Command\PruneUnusedTagsCommand Test Case
 *
 * @uses \App\Command\PruneUnusedTagsCommand
 */
class PruneUnusedTagsCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;
    use LocatorAwareTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public array $fixtures = [
        'app.Events',
        'app.Users',
        'app.Categories',
        'app.EventSeries',
        'app.Images',
        'app.Tags',
        'app.EventsImages',
        'app.EventsTags',
    ];

    /**
     * Answering "n" to every prompt keeps every tag
     *
     * @return void
     */
    public function testKeepsTagsWhenDeclined(): void
    {
        $this->exec('prune_unused_tags', array_fill(0, 20, 'n'));

        $this->assertExitSuccess();
        $this->assertOutputContains(TagsFixture::TAG_NAME_ALTERNATE);
        $this->assertOutputContains(TagsFixture::TAG_NAME_CHILD);

        $expectedTagIds = [
            TagsFixture::TAG_WITH_EVENT,
            TagsFixture::TAG_WITH_DIFFERENT_EVENT,
            TagsFixture::TAG_ID_CHILD,
            TagsFixture::TAG_ID_UNLISTED,
        ];
        $tags = $this->getTableLocator()->get('Tags');
        foreach ($expectedTagIds as $tagId) {
            $this->assertTrue($tags->exists(['id' => $tagId]), "Tag #$tagId should not have been deleted");
        }
    }

    /**
     * A tag that still has child tags is never offered for deletion
     *
     * @return void
     */
    public function testSkipsTagsWithChildren(): void
    {
        $this->exec('prune_unused_tags', array_fill(0, 20, 'n'));

        $this->assertExitSuccess();
        $this->assertOutputNotContains(sprintf('%s (#%d)', TagsFixture::TAG_NAME, TagsFixture::TAG_WITH_EVENT));
        $this->assertOutputContains('skipped for having child tags');
    }

    /**
     * Tags named in EXEMPT_TAG_NAMES, and their descendants, are never offered for deletion
     *
     * @return void
     */
    public function testExemptsProtectedTagsAndDescendants(): void
    {
        // Answer "y" to everything so nothing is spared for lack of confirmation
        $this->exec('prune_unused_tags', array_fill(0, 20, 'y'));

        $this->assertExitSuccess();

        // The exempt group is matched case-insensitively, and its child is exempt for being a descendant
        $this->assertOutputNotContains(TagsFixture::TAG_NAME_EXEMPT_GROUP);
        $this->assertOutputNotContains(TagsFixture::TAG_NAME_EXEMPT_DESCENDANT);
        $this->assertOutputNotContains(sprintf('delete (#%d)', TagsTable::DELETE_GROUP_ID));
        $this->assertOutputNotContains(sprintf('unlisted (#%d)', TagsTable::UNLISTED_GROUP_ID));

        $exemptTagIds = [
            TagsFixture::TAG_ID_EXEMPT_GROUP,
            TagsFixture::TAG_ID_EXEMPT_DESCENDANT,
            TagsTable::DELETE_GROUP_ID,
            TagsTable::UNLISTED_GROUP_ID,
        ];
        $tags = $this->getTableLocator()->get('Tags');
        foreach ($exemptTagIds as $tagId) {
            $this->assertTrue($tags->exists(['id' => $tagId]), "Exempt tag #$tagId should not have been deleted");
        }
    }

    /**
     * Answering "y" deletes the tag and its events_tags rows
     *
     * @return void
     */
    public function testDeletesTagWhenConfirmed(): void
    {
        // "another tag" sorts first among the candidates, so the leading "y" targets it
        $this->exec('prune_unused_tags', array_merge(['y'], array_fill(0, 20, 'n')));

        $this->assertExitSuccess();

        $tags = $this->getTableLocator()->get('Tags');
        $this->assertFalse(
            $tags->exists(['id' => TagsFixture::TAG_WITH_DIFFERENT_EVENT]),
            'The confirmed tag should have been deleted',
        );
        $this->assertTrue($tags->exists(['id' => TagsFixture::TAG_WITH_EVENT]));
        $this->assertTrue($tags->exists(['id' => TagsFixture::TAG_ID_UNLISTED]));

        $eventsTags = $this->getTableLocator()->get('EventsTags');
        $this->assertSame(
            0,
            $eventsTags->find()->where(['tag_id' => TagsFixture::TAG_WITH_DIFFERENT_EVENT])->count(),
            'The join rows for the deleted tag should have been removed',
        );
    }
}
