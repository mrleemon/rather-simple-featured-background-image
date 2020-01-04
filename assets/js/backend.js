/*
 * calls the wordpress media manager and saves image url to input field
 */
jQuery( document ).ready( function( $ ) {

    // Instantiates the variable that holds the media library frame.
    var background_image_frame;

    /**
     * Opens the wordpress media manager frame and sets actions for 
     * when the user makes their selections
     */
    $( '#fbi-set-image, #fbi-update-image' ).click( function( e ) {
 
        // Prevents the default action from occuring.
        e.preventDefault();

        // If the frame already exists, re-open it.
        if ( background_image_frame ) {
            background_image_frame.open();
            return;
        }

        // Sets up the media library frame
        background_image_frame = wp.media.frames.background_image_frame = wp.media({
            title: meta_image.title,
            button: { text: meta_image.button },
            library: { type: 'image' }
        } );

        // Runs when an image is selected.
        background_image_frame.on('select', function() {
            
            // Grabs the attachment selection and creates a JSON representation of the model.
            var media_attachment = background_image_frame.state().get( 'selection' ).first().toJSON();

            // displays a thumbnail of the selected image
            $( '#fbi-thumbnail' ).removeClass( 'hide' );
			$( '#fbi-thumbnail img' ).attr( 'src', media_attachment.url );

            // Sends the attachment URL to our custom image input field.
            $( '#fbi-image' ).val( media_attachment.url );

            // hide / show appropriate links
            $( '#fbi-set-image' ).addClass( 'hide' );
            $( '#fbi-image-desc' ).removeClass( 'hide' );
            $( '#fbi-remove-image' ).removeClass( 'hide' );
        } );
 
        // Opens the media library frame.
        background_image_frame.open();
    } );

	/**
	* removes thumbnail and displays "add new" link
	*/
	$( '#fbi-remove-image' ).click( function( e ) {
		e.preventDefault();
		$( '#fbi-thumbnail' ).addClass( 'hide' );
		$( '#fbi-thumbnail img' ).attr( 'src', '' );
		$( '#fbi-image' ).val( '' );
		$( '#fbi-set-image' ).removeClass( 'hide' );
		$( '#fbi-image-desc' ).addClass( 'hide' );
		$( '#fbi-remove-image' ).addClass( 'hide' );
	} );

} );