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

    },
    convertTimestamp : function (dateString)
    {
        var postDate = new Date(dateString),
            now = new Date(),
            /*dif = now - postDate + (postDate.getTimezoneOffset() * 1000 * 60), // ms*/
            dif = now - postDate, // ms
            s   = Math.floor(dif/1000),
            m   = Math.floor(s/60),
            h   = Math.floor(m/60),
            d   = Math.floor(h/24),
            w   = Math.floor(d/7),
            M   = now.getMonth() - postDate.getMonth(),
            y   = new Date(dif).getFullYear() - 1970,
            t   = ["year","month","week", "day","hour","minute","second"],
            a   = [y,M,w,d,h,m,s];

        for(var i in a)
        {
            if(a[i])
            {
                a=a[i];
                t=t[i];
                break;
            }
        }

        if (a == 1 && t == 'day')
        {
            return 'yesterday';
        }
        else if (a == 1 && t == 'hour')
        {
            return 'hour ago';
        }
        else if (a == 1 && t == 'minute')
        {
            return 'just now';
        }
        else
        {
            return ( a==1 ? "last " + t : a +" "+ t +"s ago") ;
        }
    }
}

$(function() {

    /**
     * Delete-counter-functionality
     */
    $('a.delete-link').click(function()
    {
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

    $('span.timestamp').html(function()
    {
        return dwo.convertTimestamp(this.innerHTML);
    });
});
