$(function(){
    var doSearch = function(event)
    {
        event.preventDefault();
        //$(this).attr('disabled','disabled');
        
        var topLevelDomains = [
            'com', 'net', 'org', 'fi', 'eu', 'info', 'io'
        ];
        
        var theName = $('#name-field').val();
        
        $('#domain-list').empty();
        for (n in topLevelDomains) {
            searchForDomain(theName, topLevelDomains[n]);
        }
        //$(this).removeAttr('disabled');
    };
    
    var searchForDomain = function(name, domain)
    {
        var listIconId = 'li-' + name + domain;
        var li = [
            '<li>',
                '<span id="', listIconId ,'"',
                    ' class="icon">',
                    '&nbsp;',
                '</span>',
                '&nbsp;',
                name, '.', domain, 
            '</li>'
        ];
        $('#domain-list').append(li.join(''));
        var icon = $('#' + listIconId);
        icon.addClass('icon-question-sign');
        
        $.ajax({
            url: "whois/" + name + '.' + domain
        }).done(function(response) {
            icon.removeClass('icon-question-sign');
            if ( ! response.success) {
                icon.addClass('icon-warning-sign').attr('title', 'Error occured');
            } else if ( ! response.registred) {
                icon.addClass('icon-ok-sign').attr('title', 'Is available');
            } else {
                icon.addClass('icon-exclamation-sign').attr('title', 'Is already taken');
            }
        });
    };
    
    $('#search-btn').click(doSearch);
    console.log('rdy');
});