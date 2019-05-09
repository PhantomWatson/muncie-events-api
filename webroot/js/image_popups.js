var muncieEventsImagePopups = {
	// Contains IDs of processed links
	singles_processed: [],
	
	// Contains the shared rel values of processed groups
	groups_processed: [],

	prepare: function() {
		$('a[rel*="popup"]').each(function () {
			var link = $(this);
			var rel = link.attr('rel');
			
			// Single image
			if (rel === 'popup') {
				muncieEventsImagePopups.prepareSingle(link);
				
			// Group together all images with this value for rel
			} else if (rel.indexOf('popup[') === 0) {
				muncieEventsImagePopups.prepareGroup(link);
			}
		});
	},
	
	prepareSingle: function(link) {
		var id = this.getLinkId(link);
		
		// Skip a link that's already been processed
		if (this.singles_processed.indexOf(id) !== -1) {
			return;
		}
		
		var options = {
			closeBtnInside: true,
			type: 'image',
			fixedContentPos: false,
			fixedBgPos: true,
			midClick: true,
			removalDelay: 300,
			mainClass: 'my-mfp-zoom-in'
		};
		options.key = 'single_image';
		options.key += (link.attr('title') ? '_with_caption' : '_no_caption');
		link.magnificPopup(options);
		this.singles_processed.push(id);
	},
	
	prepareGroup: function(link) {
		var rel = link.attr('rel');
		
		// Skip a group that's already been processed
		if (this.groups_processed.indexOf(rel) !== -1) {
			return;
		}
		
		// Process as a single image if this group has only one item
		if ($('a[rel="' + rel + '"]').length < 2) {
			this.prepareSingle(link);
		}
		
		var options = {
			closeBtnInside: true,
			type: 'image',
			fixedContentPos: false,
			fixedBgPos: true,
			midClick: true,
			removalDelay: 300,
			mainClass: 'my-mfp-zoom-in'
		};
		options.key = 'multiple_images';
		options.delegate = 'a[rel="' + rel + '"]';
		options.gallery = {
			enabled: true,
			navigateByImgClick: true,
			preload: [0,2]
		};
		link.parent().magnificPopup(options);
		this.groups_processed.push(rel);
	},
	
	getLinkId: function(link) {
		var id = link.attr('id');
		if (id) {
			return id;
		}
		id = 'popup_link_' + this.singles_processed.length;
		link.attr('id', id);

		return id;
	}
};