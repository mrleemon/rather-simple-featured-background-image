/*
 * calls the wordpress media manager and saves image url to input field
 */
(function () {

	document.addEventListener('DOMContentLoaded', function (e) {

		// Instantiates the variable that holds the media library frame.
		var background_image_frame;

		/**
		 * Opens the wordpress media manager frame and sets actions for 
		 * when the user makes their selections
		 */
		document.querySelectorAll('#fbi-set-image, #fbi-update-image').forEach(function (item) {
			item.addEventListener('click', function (e) {
				e.preventDefault();

				// If the frame already exists, re-open it.
				if (background_image_frame) {
					background_image_frame.open();
					return;
				}

				// Sets up the media library frame.
				background_image_frame = wp.media.frames.background_image_frame = wp.media({
					title: meta_image.title,
					button: { text: meta_image.button },
					library: { type: 'image' }
				});

				// Runs when an image is selected.
				background_image_frame.on('select', function () {

					// Grabs the attachment selection and creates a JSON representation of the model.
					var media_attachment = background_image_frame.state().get('selection').first().toJSON();

					// Displays a thumbnail of the selected image.
					document.querySelector('#fbi-thumbnail').classList.remove('hide');
					document.querySelector('#fbi-thumbnail img').setAttribute('src', media_attachment.url);

					// Sends the attachment URL to our custom image input field.
					document.querySelector('#fbi-image').value = media_attachment.url;

					// Hide/Show appropriate links.
					document.querySelector('#fbi-set-image').classList.add('hide');
					document.querySelector('#fbi-image-desc').classList.remove('hide');
					document.querySelector('#fbi-remove-image').classList.remove('hide');
				});

				// Opens the media library frame.
				background_image_frame.open();
			});
		});

		/**
		 * Removes thumbnail and displays "Add new" link
		 */
		var elRemove = document.querySelector('#fbi-remove-image');
		if (elRemove) {
			elRemove.addEventListener('click', function (e) {
				e.preventDefault();

				// Hides the thumbnail of the selected image.
				document.querySelector('#fbi-thumbnail').classList.add('hide');
				document.querySelector('#fbi-thumbnail img').setAttribute('src', '');

				// Remove the attachment URL in our custom image input field.
				document.querySelector('#fbi-image').value = '';

				// Hide/Show appropriate links.
				document.querySelector('#fbi-set-image').classList.remove('hide');
				document.querySelector('#fbi-image-desc').classList.add('hide');
				document.querySelector('#fbi-remove-image').classList.add('hide');
			});
		}

	});

})();