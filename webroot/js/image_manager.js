var ImageManager = {
    userId: null,
    eventImgBaseUrl: '',

    setupManager: function () {
        $('#selected_images').sortable({
            placeholder: 'ui-state-highlight'
        });

        $('#image_select_toggler').click(function (event) {
            event.preventDefault();
            ImageManager.toggleUploadedImages();
        });

        ImageManager.hidePreselectedImages();
    },

    getSelectionContainer: function (imageId) {
        return $('<li id="selectedimage_' + imageId + '" data-image-id="' + imageId + '" class="row"></li>');
    },

    getLinkedImage: function (imageId, filename) {
        var fullUrl = this.eventImgBaseUrl + 'full/' + filename;
        var tinyUrl = this.eventImgBaseUrl + 'tiny/' + filename;
        return $(
            '<a href="' + fullUrl + '" rel="popup" id="thumbnail_link_' + imageId + '">' +
            '<img src="' + tinyUrl + '" class="selected_image" alt="Uploaded image" />' +
            '</a>'
        );
    },

    getCaptionFieldLabel: function (imageId) {
        return $('<label for="caption-image-' + imageId + '" class="sr-only">Caption</label>');
    },

    getCaptionField: function (imageId) {
        return $(
            '<input class="form-control" type="text" name="data[Image][' + imageId + ']" ' +
            'id="caption-image-' + imageId + '" placeholder="Enter a caption for this image" value="" />'
        );
    },

    getRemoveButton: function () {
        return $('<button type="button" class="remove btn btn-danger" title="Remove"></button>')
            .append('<i class="fas fa-times"></i>')
            .append('<span class="sr-only">Remove</span>')
            .click(function () {
                var container = $(this).parent('li');
                ImageManager.unselectImage(container);
            });
    },

    addHiddenListedImage: function (imageId, filename) {
        var link = $(
            '<a href="#" id="listed_image_' + imageId + '" data-image-id="' + imageId + '"' +
            ' data-image-filename="' + filename + '"></a>'
        );
        var url = this.eventImgBaseUrl + 'tiny/' + filename;
        link.html('<img src="' + url + '" alt="Uploaded image" />');
        link.click(function (event) {
            event.preventDefault();
            var imageId = $(this).data('imageId');
            ImageManager.selectListedImage(imageId);
        });
        link.hide();
        $('#image_select_container').prepend(link);
    },

    populateSelectionContainer: function (selectionContainer, imageId, filename) {
        var leftCol = $('<div class="col-md-2"></div>')
            .append(ImageManager.getLinkedImage(imageId, filename));
        var rightCol = $('<div class="col-md-10"></div>')
            .append(ImageManager.getCaptionFieldLabel(imageId))
            .append(ImageManager.getCaptionField(imageId));
        selectionContainer
            .append(leftCol)
            .append(rightCol)
            .append(ImageManager.getRemoveButton(imageId))
            .appendTo($('#selected_images'));
    },

    afterSelection: function (imageId) {
        $('#no_images_selected').hide();
        $('#thumbnail_link_' + imageId).magnificPopup({
            closeBtnInside: true,
            type: 'image',
            fixedContentPos: false,
            fixedBgPos: true,
            midClick: true,
            removalDelay: 300,
            mainClass: 'my-mfp-zoom-in'
        });

        $('#selected_images').sortable('refresh');
    },

    unselectImage: function (container) {
        var imageId = container.data('imageId');
        var listedImage = $('#listed_image_' + imageId);
        var remove_selection = function () {
            container.slideUp(300, function () {
                container.remove();
                if ($('#selected_images li').length === 0) {
                    $('#no_images_selected').show();
                }
            });
        };
        if (listedImage.length === 0) {
            remove_selection();
            return;
        }
        if ($('#image_select_container').is(':visible')) {
            listedImage.fadeIn(300);
            var options = {
                to: '#listed_image_' + imageId,
                className: 'ui-effects-transfer'
            };
            container.effect('transfer', options, 300, remove_selection);
        } else {
            remove_selection();
            listedImage.show();
        }
    },

    selectListedImage: function (imageId) {
        var listedImage = $('#listed_image_' + imageId);
        var filename = listedImage.data('imageFilename');
        if (listedImage.length === 0 || !filename) {
            return ImageManager.selectUnlistedImage(imageId);
        }
        var selectionContainer = ImageManager.getSelectionContainer(imageId);
        selectionContainer.fadeTo(0, 0);
        selectionContainer.hide();
        ImageManager.populateSelectionContainer(selectionContainer, imageId, filename);
        selectionContainer.slideDown(200, function () {
            selectionContainer.fadeTo(200, 1);
            var options = {
                to: '#selectedimage_' + imageId,
                className: 'ui-effects-transfer'
            };
            var callback = function () {
                listedImage.fadeOut(200);
            };
            listedImage.effect('transfer', options, 400, callback);
            ImageManager.afterSelection(imageId);
        });
    },

    selectUnlistedImage: function (imageId) {
        // Add an empty container with a loading icon
        var selectionContainer = ImageManager.getSelectionContainer(imageId);
        selectionContainer
            .hide()
            .addClass('loading')
            .appendTo($('#selected_images'))
            .fadeIn(300);

        $.ajax({
            url: '/images/filename/' + imageId + '.json',
            success: function (data) {
                if (!data) {
                    alert('There was an error selecting an image (image not found).');
                    $('#selectedimage_' + imageId).remove();
                } else {
                    selectionContainer.removeClass('loading');
                    ImageManager.populateSelectionContainer(selectionContainer, imageId, data.filename);
                    ImageManager.afterSelection(imageId);
                    ImageManager.addHiddenListedImage(imageId, data.filename);
                }
            },
            error: function () {
                alert('There was an error selecting an image.');
            }
        });
    },

    setupUpload: function (params) {
        this.eventImgBaseUrl = params.eventImgBaseUrl;
        this.userId = params.userId;

        $('#image_upload_button').uploadifive({
            uploadScript: '/images/upload',
            checkScript: '/images/file-exists',
            onCheck: false,
            fileSizeLimit: params.filesize_limit,
            buttonText: 'Upload a new image',
            buttonClass: 'btn btn-secondary',
            formData: {
                user_id: params.userId,
                event_id: params.eventId
            },
            onUploadComplete: function (file, data) {
                console.log(file);
                console.log(params);
                console.log(data);

                var intRegex = /^\d+$/;

                // If the image's ID is returned
                if (intRegex.test(data)) {
                    var imageId = data;
                    ImageManager.selectUnlistedImage(imageId);
                }
            },
            'onError': function (errorType, files) {
                alert('There was an error uploading that file: ' + file.xhr.responseText);
            },
            'onQueueComplete': function () {
                this.uploadifive('clearQueue');
            }
        });
    },

    // Hide preselected images in the collection of selectable images
    hidePreselectedImages: function () {
        $('#selected_images').find('li').each(function () {
            var li = $(this);
            var imageId = li.data('imageId');
            var listedImage = $('#listed_image_' + imageId);
            if (listedImage.length !== 0) {
                listedImage.hide();
            }
            li.find('a.remove').click(function (event) {
                event.preventDefault();
                var container = $(this).parent('li');
                ImageManager.unselectImage(container);
            });
        });
    },

    toggleUploadedImages: function () {
        if ($('#image_select_toggler').hasClass('loading')) {
            return;
        }

        var container = $('#image_select_container');
        if (container.children().length === 0) {
            ImageManager.loadUploadedImages();
            return;
        }

        container.slideToggle();
    },

    loadUploadedImages: function () {
        var container = $('#image_select_container');
        var link = $('#image_select_toggler');
        $.ajax({
            url: '/images/user-images/' + ImageManager.userId,
            beforeSend: function () {
                link.addClass('loading');
            },
            complete: function () {
                link.removeClass('loading');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
                var error = $(
                    '<div class="alert alert-danger">' +
                    'There was an error loading your uploaded images. ' +
                    'Please try again or contact an administrator for assistance.' +
                    '</div>'
                );
                error.hide();
                container.after(error);
            },
            success: function (data) {
                container.hide();
                container.html(data);
                container.slideDown();
                ImageManager.addHiddenUploadedImages();
                container.find('button').click(function (event) {
                    event.preventDefault();
                    var imageId = $(this).data('imageId');
                    ImageManager.selectListedImage(imageId);
                });
            }
        });
    },

    /**
     * Looks for selected images that aren't in the uploaded images list
     * (which might happen if the current user is an admin editing someone
     * else's event) and adds them to the uploaded images list. This allows
     * such a user to unselect and then reselect such images. */
    addHiddenUploadedImages: function () {
        var container = $('#image_select_container');
        $('#selected_images li').each(function () {
            var imageId = $(this).data('imageId');
            var filename = $(this).find('img.selected_image').attr('src').split('/').pop();

            // The Calendar helper does not show image thumbnails if the file is not found
            if (typeof filename == 'undefined') {
                return;
            }

            var linkedImage = $(
                '<a href="#" id="listed_image_' + imageId + '" data-image-id="' + imageId + '" ' +
                'data-image-filename="' + filename + '"></a>'
            );
            var url = this.eventImgBaseUrl + 'tiny/' + filename;
            linkedImage.html('<img src="' + url + '" alt="Uploaded image" />');
            linkedImage.hide();
            container.append(linkedImage);
        });
    }
};
