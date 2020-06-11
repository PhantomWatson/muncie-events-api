var TagManager = {
    tagAutoSuggestionRequest: null,

    /**
     * @param data An array of tag objects
     * @param container $('#container_id')
     * @returns
     */
    createTagList: function (data, container) {
        var list = $('<ul></ul>');
        for (var i = 0; i < data.length; i++) {
            var tagId = data[i].id;
            var tagName = data[i].name;
            var children = data[i].children;
            var hasChildren = (children.length > 0);
            var isSelectable = data[i].selectable;
            var listItem = $('<li id="available_tag_li_' + tagId + '"></li>');
            var row = $('<div class="single_row"></div>');
            listItem.append(row);
            list.append(listItem);

            if (isSelectable) {
                var tagLink = $('<a href="#" class="available_tag" title="Click to select" id="available_tag_' + tagId + '"></a>');
                tagLink.append(tagName);
                (function (tag_id) {
                    tagLink.click(function (event) {
                        event.preventDefault();
                        var link = $(this);
                        var tagName = link.html();
                        var listItem = link.parents('li').first();
                        TagManager.selectTag(tag_id, tagName, listItem);
                    });
                })(tagId);
                tagName = tagLink;
            }

            // Bullet point
            if (hasChildren) {
                var collapsedIcon = $('<a href="#" title="Click to expand/collapse"></a>');
                collapsedIcon.append(
                    '<img src="/img/icons/menu-collapsed.png" class="expand_collapse" alt="Un-collapse this menu" />'
                );
                (function (children) {
                    collapsedIcon.click(function (event) {
                        event.preventDefault();
                        var icon = $(this);
                        var iconContainer = icon.parent('div');
                        var childrenContainer = iconContainer.next('.children');

                        // Populate list if it is empty
                        if (childrenContainer.is(':empty')) {
                            TagManager.createTagList(children, childrenContainer);
                        }

                        // Open/close
                        childrenContainer.slideToggle(200, function () {
                            var iconImage = icon.children('img.expand_collapse');
                            if (childrenContainer.is(':visible')) {
                                iconImage.prop('src', '/img/icons/menu-expanded.png');
                                iconImage.prop('alt', 'Collapse this menu');
                            } else {
                                iconImage.prop('src', '/img/icons/menu-collapsed.png');
                                iconImage.prop('alt', 'Un-collapse this menu');
                            }
                        });
                    });
                })(children);

                row.append(collapsedIcon);
            } else {
                row.append('<img src="/img/icons/menu-leaf.png" class="leaf" alt="tag" />');
            }

            row.append(tagName);

            // Tag and submenu
            if (hasChildren) {
                var childrenContainer = $('<div style="display: none;" class="children"></div>');
                row.after(childrenContainer);
            }

            // If tag has been selected
            if (isSelectable && this.tagIsSelected(tagId)) {
                tagName.addClass('selected');
                if (!hasChildren) {
                    listItem.hide();
                }
            }
        }
        container.append(list);
    },

    tagIsSelected: function (tagId) {
        var selectedTags = $('#selected_tags').find('a');
        for (var i = 0; i < selectedTags.length; i++) {
            var tag = $(selectedTags[i]);
            if (tag.data('tag_id') === tagId) {
                return true;
            }
        }
        return false;
    },

    preselectTags: function (selectedTags) {
        if (selectedTags.length === 0) {
            return;
        }
        $('#selected_tags_container').show();
        for (var i = 0; i < selectedTags.length; i++) {
            TagManager.selectTag(selectedTags[i].id, selectedTags[i].name);
        }
    },

    unselectTag: function (tagId, unselectLink) {
        var availableTagListItem = $('#available_tag_li_' + tagId);

        // If available tag has not yet been loaded, then simply remove the selected tag
        if (availableTagListItem.length === 0) {
            unselectLink.remove();
            if ($('#selected_tags').children().length === 0) {
                $('#selected_tags_container').slideUp(200);
            }
            return;
        }

        // Remove 'selected' class from available tag
        var availableLink = $('#available_tag_' + tagId);
        if (availableLink.hasClass('selected')) {
            availableLink.removeClass('selected');
        }

        var removeLink = function () {
            unselectLink.fadeOut(200, function () {
                unselectLink.remove();
                if ($('#selected_tags').children().length === 0) {
                    $('#selected_tags_container').slideUp(200);
                }
            });
        };

        availableTagListItem.slideDown(200);

        // If available tag is not visible, then no transfer effect
        if (availableLink.is(':visible')) {
            var options = {
                to: '#available_tag_' + tagId,
                className: 'ui-effects-transfer'
            };
            unselectLink.effect('transfer', options, 200, removeLink);
        } else {
            removeLink();
        }
    },

    selectTag: function (tagId, tagName, availableTagListItem) {
        var selectedContainer = $('#selected_tags_container');
        if (!selectedContainer.is(':visible')) {
            selectedContainer.slideDown(200);
        }

        // Do not add tag if it is already selected
        if (this.tagIsSelected(tagId)) {
            return;
        }

        // Add tag
        var listItem = $(
            '<a href="#" title="Click to remove" data-tag-id="' + tagId + '" id="selected_tag_' + tagId + '"></a>'
        );
        listItem.append(tagName);
        listItem.append('<input type="hidden" name="tags[_ids][]" value="' + tagId + '" />');
        listItem.click(function (event) {
            event.preventDefault();
            var unselectLink = $(this);
            var tagId = unselectLink.data('tag_id');
            TagManager.unselectTag(tagId, unselectLink);
        });
        listItem.hide();
        $('#selected_tags').append(listItem);
        listItem.fadeIn(200);

        // If available tag has not yet been loaded, then return
        availableTagListItem = $('#available_tag_li_' + tagId);
        if (availableTagListItem.length === 0) {
            return;
        }

        // Hide/update link to add tag
        var link = $('#available_tag_' + tagId);
        var options = {
            to: '#selected_tag_' + tagId,
            className: 'ui-effects-transfer'
        };
        var callback = function () {
            link.addClass('selected');
            var hasChildren = (availableTagListItem.children('div.children').length !== 0);
            if (!hasChildren) {
                availableTagListItem.slideUp(200);
            }
        };
        link.effect('transfer', options, 200, callback);
    },

    setupAutosuggest: function (selector) {
        $(selector).bind('keydown', function (event) {
            if (event.keyCode === $.ui.keyCode.TAB && $(this).data('autocomplete').menu.active) {
                event.preventDefault();
            }
        }).autocomplete({
            source: function (request, response) {
                if (TagManager.tagAutoSuggestionRequest) {
                    TagManager.tagAutoSuggestionRequest.abort();
                }

                TagManager.tagAutoSuggestionRequest = $.ajax({
                    url: 'https://api.muncieevents.com/v1/tags/autocomplete',
                    dataType: 'json',
                    data: {
                        term: extractLast(request.term)
                    },
                    success: function (apiResponse) {
                        const data = apiResponse.data;
                        let tagSuggestions = [];
                        let tagName;
                        for (let i = 0; i < data.length; i++) {
                            tagName = data[i].attributes.name;
                            tagSuggestions.push(tagName);
                        }
                        response(tagSuggestions);
                    }
                });
            },
            delay: 0,
            search: function () {
                var term = extractLast(this.value);
                if (term.length < 2) {
                    return false;
                }
                $(selector).siblings('img.loading').show();
            },
            response: function () {
                $(selector).siblings('img.loading').hide();
                TagManager.tagAutoSuggestionRequest = null;
            },
            focus: function () {
                return false;
            },
            select: function (event, ui) {
                var tagName = ui.item.label;
                var terms = split(this.value);
                terms.pop();
                terms.push(tagName);

                // Add placeholder to get the comma-and-space at the end
                terms.push('');

                this.value = terms.join(', ');
                return false;
            }
        });
    }
};
