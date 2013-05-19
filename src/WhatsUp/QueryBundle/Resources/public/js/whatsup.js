$(function(){
    var doSearch = function(event)
    {
        event.preventDefault();
        
        var topLevelDomains = [
            'com', 'net', 'org', 'eu', 'uk', 
            'ca', 'de', 'jp', 'fr', 'au', 'us', 'ru', 'it', 'fi'
        ];
        
        var someSites = [
            'Twitter'
        ];
        
        var theName = $('#name-field').val();
        
        $('#domain-list').empty();
        $('#some-list').empty();
        for (var s in someSites) {
            searchForSocialMedia(theName, someSites[s]);
        }
        for (var n in topLevelDomains) {
            searchForDomain(theName, topLevelDomains[n]);
        }
    };
    
    var searchForDomain = function(name, domain)
    {
        var listIconId = 'li-' + name + domain;
        var li = [
            '<li>',
                name, '.', domain, 
                '&nbsp;',
                '<span id="', listIconId ,'"',
                    ' class="icon">',
                    '&nbsp;',
                '</span>',
            '</li>'
        ];
        $('#domain-list').append(li.join(''));
        var icon = $('#' + listIconId);
        
        $.ajax({
            url: "whois/" + name + '.' + domain
        }).done(function(response) {
            if ( ! response.success) {
                icon.addClass('icon-warning-sign').attr('title', 'Error occured');
            } else if ( ! response.registred) {
                icon.addClass('icon-ok-sign').attr('title', 'Is available');
            } else {
                icon.addClass('icon-exclamation-sign').attr('title', 'Is already taken');
            }
        });
    };
    
    var searchForSocialMedia = function(name, media)
    {
        var listIconId = ['li', name, media].join('-');
        var li = [
            '<li>',
                media, ' / ', name,
                '&nbsp;',
                '<span id="', listIconId ,'"',
                    ' class="icon">',
                    '&nbsp;',
                '</span>',
            '</li>'
        ];
        $('#some-list').append(li.join(''));
        var icon = $('#' + listIconId);
        
        $.ajax({
            url: "socialmedia/" + media + '/' + name
        }).done(function(response) {
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
});