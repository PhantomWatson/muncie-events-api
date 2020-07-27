class AdminTagManager {
    constructor() {
        this.setupTagManager();
    }

    setupTagManager() {
        $(() => {
            // Tabs
            $('#tag_management_tabs').tabs();

            // Arranger
            this.setupTagArranger();

            // Empty trash function
            $('#tab-remove a').click(function (event) {
                event.preventDefault();
                $.ajax({
                    url: this.href,
                    success: function (data) {
                        $('<p class="alert alert-success">' + data.message + '</p>').prependTo('#tab-remove .results');
                    },
                    error: function () {
                        alert('The server returned an error.');
                    }
                });
            });

            const tagAutocompleteFields = document.querySelectorAll('.search_field');
            tagAutocompleteFields.forEach((tagAcField) => {
                if (typeof tagAcField.id === 'undefined' || tagAcField.id === '') {
                    tagAcField.id = 'tag-search-' + Math.floor(Math.random() * 9999);
                }
                const resultsContainerId = 'tag-search-results-' + Math.floor(Math.random() * 9999);

                new autoComplete({
                    data: {
                        src: async () => {
                            const query = tagAcField.value.trim();
                            const source = await fetch(`/admin/tags/autocomplete.json?term=${query}`);
                            const apiResponse = await source.json();
                            const data = apiResponse.hasOwnProperty('tags') ? apiResponse.tags : null;
                            if (!data) {
                                return [];
                            }
                            let tagSuggestions = [];
                            let tagName;
                            for (let i = 0; i < data.length; i++) {
                                tagName = data[i].name;
                                tagSuggestions.push(tagName);
                            }

                            return tagSuggestions;
                        },
                        cache: false
                    },
                    selector: '#' + tagAcField.id,       // Input field selector              | (Optional)
                    threshold: 3,                        // Min. Chars length to start Engine | (Optional)
                    debounce: 300,                       // Post duration for engine to start | (Optional)
                    resultsList: {                       // Rendered results list object      | (Optional)
                        render: true,

                        // Make results automatically close upon clicking anywhere on the page
                        container: source => {
                            source.setAttribute('id', resultsContainerId);
                            tagAcField.addEventListener('autoComplete', function (event) {
                                function hideSearchResults() {
                                    const searchResults = document.getElementById(resultsContainerId);
                                    while (searchResults.firstChild) {
                                        searchResults.removeChild(searchResults.firstChild);
                                    }
                                    document.removeEventListener('click', hideSearchResults);
                                }

                                document.addEventListener('click', hideSearchResults);
                            });
                        },

                        destination: tagAcField,
                        position: 'afterend',
                        element: 'ul'
                    },
                    searchEngine: function (query, record) {
                        return record;
                    },
                    maxResults: 10,                        // Max. number of rendered results | (Optional)
                    highlight: true,                       // Highlight matching results      | (Optional)
                    resultItem: {                          // Rendered result item            | (Optional)
                        content: (data, source) => {
                            source.innerHTML = data.match;
                        },
                        element: 'li'
                    },
                    noResults: () => {                     // Action script on noResults      | (Optional)
                        const result = document.createElement('li');
                        result.setAttribute('class', 'no_result autoComplete_result');
                        result.setAttribute('tabindex', '1');
                        result.innerHTML = 'No Results';
                        document.getElementById(resultsContainerId).appendChild(result);
                    },
                    onSelection: feedback => {             // Action script onSelection event | (Optional)
                        tagAcField.value = feedback.selection.value;
                    }
                });
            });

            // Find
            $('#tag_search_form').submit(function (event) {
                event.preventDefault();
                $.ajax({
                    url: '/admin/tags/trace/' + $(this).find('.search_field').val(),
                    success: function (data) {
                        $('#trace_results').html(data);
                    },
                    error: function () {
                        alert('The server returned an error.');
                    }
                });
            });

            // Edit
            $('#tag_edit_search_form').submit(function (event) {
                event.preventDefault();
                $.ajax({
                    url: '/admin/tags/edit/' + $(this).find('.search_field').val(),
                    success: function (data) {
                        $('#edit_results').html(data);

                        // Set up resulting form (if any) to load results in same div
                        $('#edit_results form').ajaxForm({
                            target: '#edit_results'
                        });
                    }
                });
            });

            // Remove
            $('#tag_remove_form').submit(function (event) {
                event.preventDefault();
                const tagName = $('#tag_remove_field').val();
                $.ajax({
                    url: '/admin/tags/remove/' + tagName,
                    success: function (data) {
                        $('<div>' + data + '</div>').prependTo('#tab-remove .results');
                    },
                    error: function () {
                        alert('The server returned an error.');
                    }
                });
            });

            // Add
            $('#tab-add form').ajaxForm({
                target: '#add_results',
                beforeSend: function () {
                    $('#add_results').empty();
                    $('#tab-add input[type=submit]').attr('disabled', 'disabled');
                },
                complete: function () {
                    $('#tab-add input[type=submit]').removeAttr('disabled');
                }
            });
        });
    }

    setupTagArranger() {
        Ext.BLANK_IMAGE_URL = '/ext-2.0.1/resources/images/default/s.gif';
        Ext.onReady(function () {
            const getnodesUrl = '/admin/tags/get_nodes/';
            const reorderUrl = '/admin/tags/reorder.json';
            const reparentUrl = '/admin/tags/reparent.json';
            const Tree = Ext.tree;
            const tree = new Tree.TreePanel({
                el: 'tree-div',
                autoScroll: true,
                animate: true,
                enableDD: true,
                containerScroll: true,
                rootVisible: true,
                loader: new Ext.tree.TreeLoader({
                    dataUrl: getnodesUrl,
                    preloadChildren: true
                })
            });
            const root = new Tree.AsyncTreeNode({
                text: 'Tags',
                draggable: false,
                id: 'root'
            });
            tree.setRootNode(root);
            let oldPosition = null;
            let oldNextSibling = null;
            tree.on('startdrag', function (tree, node, event) {
                oldPosition = node.parentNode.indexOf(node);
                oldNextSibling = node.nextSibling;
            });
            tree.on('movenode', function (tree, node, oldParent, newParent, position) {
                let params;
                let url;
                if (oldParent == newParent) {
                    url = reorderUrl;
                    params = {
                        node: node.id,
                        delta: position - oldPosition
                    };
                } else {
                    url = reparentUrl;
                    params = {
                        node: node.id,
                        parent: newParent.id,
                        position: position
                    };
                }
                // we disable tree interaction until we've heard a response from the server
                // this prevents concurrent requests which could yield unusual results
                tree.disable();
                Ext.Ajax.request({
                    url: url,
                    params: params,
                    success: function (response, request) {
                        const responseTextJson = JSON.parse(response.responseText);
                        const success = responseTextJson.hasOwnProperty('success') ? responseTextJson.success : false;
                        if (!success){
                            request.failure();
                        } else {
                            tree.enable();
                        }
                    },
                    failure: function () {
                        console.log('failure callback');
                        // we move the node back to where it was beforehand and
                        // we suspendEvents() so that we don't get stuck in a possible infinite loop
                        tree.suspendEvents();
                        oldParent.appendChild(node);
                        if (oldNextSibling) {
                            oldParent.insertBefore(node, oldNextSibling);
                        }
                        tree.resumeEvents();
                        tree.enable();
                        alert('Error: Your changes could not be saved');
                    }
                });
            });

            tree.render();
            root.expand();
        });
    }
}
