(function ($) {
	"use strict";
	$(function () {
		$(document).ready(function() {
            function media_upload(button) {
                $('body').on('click', button, function(e) {
                    e.preventDefault();
                    var $button = $(this);
                    var file_frame;

                    if (file_frame) {
                        file_frame.open();
                        return;
                    }

                    file_frame = wp.media.frames.file_frame = wp.media({
                        multiple: false,
                        library: {
                            type: 'image'
                        }
                    });

                    // When an image is selected, run a callback.
                    file_frame.on( 'select', function() {
                        var attachment = file_frame.state().get('selection').first().toJSON();
                        var image_url;
                        if(attachment.sizes.thumbnail){
                            image_url = attachment.sizes.thumbnail.url;
                        }else{
                            image_url = attachment.url;
                        }
                        $button.closest('div').find('.media-delete').show();
                        $button.closest('div').find('.image-id').val(attachment.id);
                        $button.closest('div').find('.image-preview').html("<img src=\""+image_url+"\">");
                    });

                    file_frame.open();
                    return false;
                });
            }
            media_upload('.media-select');
            function media_remove_about(button) {
                $('body').on('click', button, function(e) {
                    e.preventDefault();
                    $(this).closest('div').find('.image-id').val('');
                    $(this).closest('div').find('.image-preview').html('');
                	$(this).hide();
                    return false;
                });
            }
            media_remove_about('.media-delete');
        });
	});
}(jQuery));