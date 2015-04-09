dwo = {
    message : {
        defaultContainer : "#notification-container",
        info : function(msg, containerId)
        {
            if ( ! msg)
            {
                return;
            }
            containerId = containerId || dwo.message.defaultContainer;
            $(containerId).html(
                dwo.message.render('alert-info', '', msg)
            );
        },
        warning : function(msg, containerId)
        {
            if ( ! msg)
            {
                return;
            }
            containerId = containerId || dwo.message.defaultContainer;
            $(containerId).html(
                dwo.message.render('alert-warning', 'Notice:', msg)
            );
        },
        error: function(msg, containerId)
        {
            if ( ! msg)
            {
                return;
            }
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
            container   : options.container || dwo.message.defaultContainer,
            onSuccess   : options.onSuccess || function(){},
            onFailure   : options.onFailure || function(){},
            delay       : options.delay || 1000
        }
        return function(response)
        {
            if (console)
            {
                console.log(response)
            }
            switch (response.level)
            {
                case 'info':
                    dwo.message.info(response.message, options.container);
                    break;
                case 'error':
                    dwo.message.error(response.message, options.container);
                    break;
                case 'warning':
                    dwo.message.warning(response.message, options.container);
                    break;
            }

            if (response.success)
            {
                setTimeout( function(){options.onSuccess(response)}, options.delay);
            } else {
                setTimeout( function(){options.onFailure(response)}, options.delay);
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

    formUrl : function(options)
    {
        var options = [
            options.action || null,
            options.counter || null,
            options.user || null
        ].filter(function(v){ return v?true:false;});

        return '/' + options.join('/');
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
            dwo.createApiCallback({
                onSuccess: function(){ history.go(0); }
            })
        );
    });

    $('#sign-up-button').click(function(){
        var post = {
            nick: $('#signup-dialog #nickField').val(),
            password: $('#signup-dialog #passwordField').val(),
            'password-confirm': $('#signup-dialog #passwordConfirmField').val()
        }
        $.post(
            '/api/signup',
            post,
            dwo.createApiCallback({
                container: '#signup-dialog-msg-container',
                onSuccess: function(){ window.location = '/';}
            })
        );
    });

    $('#login-button').click(function(){
        var post = {
            nick: $('#login-nick').val(),
            password: $('#login-password').val()
        }
        $.post(
            '/api/login',
            post,
            dwo.createApiCallback({
                container: '#login-dialog-msg-container',
                onSuccess: function(){ history.go(0); }
            })
        );
    });

    $('#logout-button').click(function(){
        $.post(
            '/api/logout',
            {},
            dwo.createApiCallback({
                container: '#logout-dialog-msg-container',
                onSuccess: function(){ window.location = '/';}
            })
        );
    });

    /**
     * Create counter functionality
     */
    $('#counter-form button').click(function(event){

        event.preventDefault();
        var post = {
            visibility : event.originalEvent.toElement.value,
            headline: $('#headline-field').val()
        };
        console.log(post);
        $.post(
            '/api/counter',
            post,
            dwo.createApiCallback({
                onSuccess: function(response)
                {
                    window.location = dwo.formUrl({ counter: response.data.name, user: response.data.owner });
                }
            })
        );
        /**
         *
        $("input[type=submit]", $(this).parents("form")).removeAttr("clicked");
            $(this).attr("clicked", "true");
         */
    });


    $('#reset-button').click(function(){
        var post = {
            comment: $('#reset-comment').val()
        }
        $.post(
            dwo.formApiUrl('counter', $('#counter-name').val(), $('#counter-owner').val()),
            post,
            dwo.createApiCallback({
                container: '#reset-dialog-msg-container',
                onSuccess: function(){ history.go(0); }
            })
        );
    });

    /**
     * Delete-counter-functionality
     */
    $('#confirm-delete').click(function(){
        $('#delete-button').prop('disabled', ! this.checked);
    });

    $('#delete-button').click(function(){
        var post = {
                confirm: $('#confirm-delete').val()
            },
            counter =  $('#counter-indicator').val(),
            owner =  $('#owner-indicator').val();

        $.post(
            dwo.formApiUrl('counter/delete', counter, owner),
            post,
            dwo.createApiCallback({
                container: '#delete-counter-dialog-msg-container',
                delay: 500,
                onSuccess: function(){
                    if (owner)
                    {
                        window.location = dwo.formUrl({action: 'user', user: owner});
                    } else {
                        window.location = '/';
                    }
                }
            })
        );
    });
});
