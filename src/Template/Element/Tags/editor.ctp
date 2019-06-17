<?php
/**
 * @var AppView $this
 * @var array $authUser
 * @var array $selectedTags
 * @var Event $event
 */

use App\Model\Entity\Event;
use App\View\AppView;

$this->Html->script('tag_manager', ['block' => true]);
$this->Tag->setup('#available_tags', $event);
?>

<?php $this->Html->scriptStart(['block' => true]); ?>
$('#example_selectable_tag').tooltip().click(function(event) {
event.preventDefault();
});
TagManager.setupAutosuggest('#custom_tag_input');
<?php $this->Html->scriptEnd(); ?>

<div class="input" id="tag_editing">
    <div id="available_tags_container">
        <div id="available_tags"></div>
    </div>
    <div class="text-muted">
        Click <img src="/img/icons/menu-collapsed.png" alt="the collapse button"/> to expand groups.
        Click
        <a href="#" title="Selectable tags will appear in blue" id="example_selectable_tag">selectable tags</a>
        to select them.
    </div>

    <div id="selected_tags_container" style="display: none;">
        <span class="label">
            Selected tags:
        </span>
        <span id="selected_tags"></span>
        <div class="text-muted">
            Click on a tag to unselect it.
        </div>
    </div>

    <?php if ($authUser): ?>
        <div id="custom_tag_input_wrapper">
            <label for="custom_tag_input">
                Additional Tags
                <span id="tag_autosuggest_loading" style="display: none;">
                    <img src="/img/loading_small.gif" alt="Working..." title="Working..." style="vertical-align:top;"/>
                </span>
            </label>
            <?= $this->Form->control('customTags', [
                'label' => false,
                'class' => 'form-control',
                'id' => 'custom_tag_input'
            ]) ?>
            <div class="text-muted">
                Write out tags, separated by commas.
                <a href="#new_tag_rules" data-toggle="collapse">Rules for creating new tags</a>
            </div>
            <div id="new_tag_rules" class="alert alert-info collapse">
                <p>
                    Before entering new tags, please search for existing tags that describe your event. Once you start
                    typing, please select any appropriate suggestions that appear below the input field. Doing this will
                    make it more likely that your event will be linked to popular tags that are viewed by more visitors.
                </p>

                <p>
                    New tags must:
                </p>
                <ul>
                    <li>
                        be short, general descriptions that people might search for, describing what will take place at
                        the
                        event
                    </li>
                    <li>
                        be general enough to also apply to different events
                    </li>
                </ul>

                <p>
                    Must not:
                </p>
                <ul>
                    <li>
                        include punctuation, such as dashes, commas, slashes, periods, etc.
                    </li>
                    <li>
                        include profanity, email addresses, or website addresses
                    </li>
                    <li>
                        be the name of the location (having this as a tag would be redundant, since people can already
                        view
                        events by location)
                    </li>
                </ul>
            </div>
        </div>
    <?php endif ?>
</div>
