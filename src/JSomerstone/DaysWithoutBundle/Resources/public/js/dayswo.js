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
                $( ".result" ).html( data );
            });
        }
    });
});
