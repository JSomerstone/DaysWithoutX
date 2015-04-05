dwo = {
    message : {
        defaultContainer : "#notification-container",
        info : function(msg, containerId)
        {
            containerId = containerId || dwo.message.defaultContainer;
            $(containerId).html(
                dwo.message.render('alert-info', '', msg)
            );
        },
        warning : function(msg, containerId)
        {
            containerId = containerId || dwo.message.defaultContainer;
            $(containerId).html(
                dwo.message.render('alert-warning', 'Notice:', msg)
            );
        },
        error: function(msg, containerId)
        {
            containerId = containerId || dwo.message.defaultContainer;
            $(containerId).html(
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

    createApiCallback : function(options)
    {
        var options = {
            container : options.container || dwo.message.defaultContainer,
            onSuccess :  options.onSuccess || function(){},
            onFailure :  options.onFailure || function(){}
        }
        return function(data)
        {
            var response = jQuery.parseJSON(data);
            if (console)
            {
                console.log(response)
            }
            if ( response.success )
            {
                dwo.message.info(response.message, options.container);
                options.onSuccess(response);
            }
            else {
                dwo.message.error(response.message, options.container);
                options.onFailure(response);
            }
        }
    },
    handleApiResponse : function(data)
    {
        var response = jQuery.parseJSON(data);
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
    },

    formApiUrl : function(action, counter, owner)
    {
        var url = '/api/' + action + '/' + counter;
        if (owner)
            url += "/" + owner;

        return url;
    },

    convertTimestamp : function (dateString)
    {
        var postDate = new Date(dateString),
            now = new Date();
        now.addHours(now.getTimezoneOffset() - postDate.getTimezoneOffset());
        var dif = now - postDate, // ms
            justNowLimit = 2 * 60 * 1000,
            s   = Math.floor(dif/1000),
            m   = Math.floor(s/60),
            h   = Math.floor(m/60),
            d   = Math.floor(h/24),
            w   = Math.floor(d/7),
            M   = Math.floor(d/30),
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

        if (dif < justNowLimit)
        {
            return 'just now';
        }
        if (a == 1 && t == 'day')
        {
            return 'yesterday';
        }
        else if (a == 1 && t == 'hour')
        {
            return 'hour ago';
        }
        else
        {
            return ( a==1 ? "last " + t : a +" "+ t +"s ago") ;
        }
    },

    openDialog: function(name)
    {
        var dialog = $('#' + name + '-dialog');
        if (dialog)
        {
            dialog.modal();
        }
    }
}

$(function() {

    dwo.openDialog(window.location.hash.substring(1));

    $("a.nogo").click(function() {
        var href = $(this).attr("href");
        history.pushState({}, '', href);
        dwo.openDialog(href.substring(1));
    });

    /**
     * Delete-counter-functionality
     */
    $('a.login').click(function()
    {
        var link = $(this),
            counter = link.attr('counter'),
            owner = link.attr('owner'),
            confirmed = confirm('You´re about to remove a counter - this cannot be undone. Are you sure?');

        if (confirmed)
        {
            $.post(
                dwo.formApiUrl('counter/delete', counter, owner),
                {},
                dwo.handleApiResponse
            );
        }
    });

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
            $.post(
                dwo.formApiUrl('counter/delete', counter, owner),
                {},
                dwo.handleApiResponse
            );
        }
    });

    /**
     * Convert timestamp of reset-history into human readable format
     */
    $('span.timestamp').html(function()
    {
        return dwo.convertTimestamp(this.innerHTML);
    });

    /**
     * Reset-button functionality
     */
    $('#resetDialog button.reset').click(function()
    {
        var counter = $('#resetDialog #counter-name').val(),
            owner = $('#resetDialog #counter-owner').val(),
            postParameters = {
                comment: $('#resetDialog #reset-comment').val(),
                username: $('#reset-username').val(),
                password: $('#reset-password').val()
            };

        $.post(
            dwo.formApiUrl('counter/reset', counter, owner),
            postParameters,
            dwo.handleApiResponse
        );
    });

    $('#sign-up-button').click(function(){
        var post = {
            nick: $('#signup-dialog #nickField').val(),
            password: $('#signup-dialog #passwordField').val(),
            'password-confirm': $('#signup-dialog #passwordConfirmField').val()
        }
        $.post(
            'api/signup',
            post,
            dwo.createApiCallback({
                container: '#signup-dialog-msg-container',
                onSuccess: function(){ window.location = '/';}
            })
        );
    });

    $('#logout-button').click(function(){
        $.post(
            'api/logout',
            {},
            dwo.createApiCallback({
                container: '#logout-dialog-msg-container',
                onSuccess: function(){ window.location = '/';}
            })
        );
    });
});
