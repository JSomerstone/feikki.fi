$(function(){
    
    var nameInputMask = /^[a-z0-9\x08\x00\x13]+$/i;
    
    var onNameFieldKeyDown = function(event)
    {
        var char = String.fromCharCode(event.which);
        //Enter
        if (event.which === 13) {
            return true;
        } else if ( ! nameInputMask.test(char)) {
            event.preventDefault();
        }
    };
    
    var doSearch = function(event)
    {
        event.preventDefault();
        var theName = $('#name-field').val().toLowerCase();
        if ( ! nameInputMask.test(theName)) {
            $('#name-group').addClass('error');
            $('#name-field-help').append('Allowed characters a-z, 0-9');
            return false;
        } else {
            $('#name-group').removeClass('error');
            $('#name-field-help').empty();
        }
        
        var topLevelDomains = [
            'com', 'net', 'org', 'eu',
            'ca', 'de', 'jp', 'fr', 
            'us', 'ru', 'it', 'fi'
        ];
        
        var someSites = [
            'Twitter', 'Facebook', 'GitHub'
        ];
        
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
                name, '.<b>', domain, '</b>', 
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
                icon.addClass('icon-warning-sign').attr('title', response.message);
            } else if ( ! response.registered) {
                icon.addClass('icon-ok-sign').attr('title', response.message);
            } else {
                icon.addClass('icon-exclamation-sign').attr('title', response.message);
            }
        });
    };
    
    var searchForSocialMedia = function(name, media)
    {
        var linkId = ['a', name, media].join('-');
        var listIconId = ['li', name, media].join('-');
        var li = [
            '<li><a id="', linkId, '" href="#" target="BLANK">',
                media, ' / ', name,
                '&nbsp;',
                '<span id="', listIconId ,'"',
                    ' class="icon">',
                    '&nbsp;',
                '</span>',
            '</a></li>'
        ];
        $('#some-list').append(li.join(''));
        var icon = $('#' + listIconId);
        var link = $('#' + linkId);
        
        $.ajax({
            url: "socialmedia/" + media + '/' + name
        })
        .fail(function(response) {
            icon.addClass('icon-warning-sign').attr('title', response.message);
        })
        .done(function(response) {
            if ( ! response.success) {
                icon.addClass('icon-warning-sign').attr('title', response.message);
            } else if ( ! response.registered) {
                icon.addClass('icon-ok-sign').attr('title', response.message);
            } else {
                icon.addClass('icon-exclamation-sign').attr('title', response.message);
            }
            
            if (response.link) {
                link.attr('href', response.link);
            }
        });
    };
    
    $('#search-btn').click(doSearch);
    $('#name-field').keypress(onNameFieldKeyDown).focus();
    
});

if (!String.prototype.format) {
  String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) { 
      return typeof args[number] !== 'undefined'
        ? args[number]
        : match
      ;
    });
  };
}