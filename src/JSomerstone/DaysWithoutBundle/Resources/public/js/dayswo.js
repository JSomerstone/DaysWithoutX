$(function() {

    /**
     * Delete-counter-functionality
     */
    $('a.delete-link').click(function(){
        var link = $(this),
            counter = link.attr('counter'),
            owner = link.attr('owner'),
            confirmed = confirm('You´re about to remove a counter - this cannot be undone. Are you sure?');

        if (confirmed)
        {
            $.post( "/delete/" + counter + "/" + owner, function( data ) {
                var response = jQuery.parseJSON(data);
                console.log(response);
                if (response.redirection)
                {
                    window.location = response.redirection;
                }
                else if ( response.success )
                {
                    dwo.message.info(response.message);
                }
                else {
                    dwo.message.error(response.message);
                }
            });
        }
    });

    dwo = {
        message : {
            info : function(msg)
            {
                $('#notification-container').html(
                    dwo.message.render('alert-info', '', msg)
                );
            },
            warning : function(msg)
            {
                $('#notification-container').html(
                    dwo.message.render('alert-warning', 'Notice:', msg)
                );
            },
            error: function(msg)
            {
                $('#notification-container').html(
                    dwo.message.render('alert-danger', 'Error:', msg)
                );
            },
            render : function(msgClass, msgHeadline, msg)
            {
                return "<div class=\"alert " + msgClass + "\"> \
                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button> \
                            <strong>" + msgHeadline + "</strong> " + msg + " \
                        </div>";
            }

        }
    }
});
