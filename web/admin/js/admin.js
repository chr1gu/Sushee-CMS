var fixNavbarHeight = function () {
    var content = $('#admin-content');
    content.css('min-height', '');
    var parent = content.parent();
    content.css('min-height', parent.height() + 'px');
    //$('#admin-content').height('').height ($( document ).height() - $('.navbar-header').height());
}

// fix sidebar height
$ (window).resize (fixNavbarHeight);
fixNavbarHeight();

var getListTemplate = function (content) {
    var template = '<div class="row">\
        <h2>\
            <span>' + content.name + '</span>\
            ' + (!content.single ? '<span class="small secondary btn rounded pull_right"><a href="#" data-action="new" module-name="' + content.name + '" module-id="' + content.id + '">Neuer Inhalt</a></span>' : '') + '\
        </h2>\
        {table}\
    </div>';
    var table = '<table class="striped rounded">\
        <thead>\
                {thead}\
        </thead>\
        <tbody>\
                {tbody}\
        </tbody>\
    </table>';
    var countLabel = content.data.length > 1 ? '<p>' + content.data.length + ' Elemente</p>' : '<p>1 Element</p>';
    table = countLabel + table;
    template = template.replace('{table}', (content.data && content.data.length ? table : '<p>Es wurde noch kein Inhalt erfasst.</p>'));

    var thead = '<tr>';
    var fields = content.fields;
    $.each(fields, function(index){
        var field = fields[index];
        thead += '<th>' + field.name + '</th>';
    });
    thead += '<th></th>';
    thead += '</tr>';

    var tbody = '';
    $.each(content.data, function(i){
        var data = content.data[i];
        tbody += '<tr>'
        $.each(data.fields, function(ii){
            var field = data.fields[ii];
            var value = field.value;
            var html = fieldsFactory.getListTemplate(field, value, content.id, content.name, data.id);
            tbody += '<td>' + html + '</td>';
        });
        tbody += '<td><span class="small default btn rounded pull_right"><a href="#" data-action="edit" module-name="' + content.name + '" module-id="' + content.id + '" data-id="' + data.id + '"><i class="icon-cog"></i></a></span></td>';
        tbody += '</tr>';
    });
    template = template.replace('{thead}', thead);
    template = template.replace('{tbody}', tbody);
    return template;
};

var getFormTemplate = function (content)
{
    var exists = content.data && content.data[0] && content.data[0].created_at !== undefined;
    var dataId = exists ? content.data[0].id : new Date().getTime();
    var template = '<div class="row">\
        <h2 style="padding-bottom: 5px;">\
            ' + (!content.single && exists ? '<span class="small danger btn rounded pull_right"><a href="#" data-action="delete" module-single="' + content.single + '" module-name="' + content.name + '" module-id="' + content.id + '" data-id="' + content.data[0].id + '" >Löschen</a></span>' : '') + '\
            <span>' + content.name + ' ' + (exists ? 'bearbeiten' : 'hinzufügen') + '</span>\
        </h2>\
        <div style="font-size:70%;color:#ccc;">Element-ID: ' + dataId + '</div>\
        <div style="height: 38px;"><span class="alert-container pull_left">&nbsp;</span></div>\
        <form action="api/module.form.php" method="post">\
            <ul>\
                {fields}\
            </ul>\
            <div style="padding-top: 20px;">\
                <div class="medium primary rounded btn pull_right">\
                    <input type="submit" value="Speichern"/>\
                </div>\
                ' + (!content.single ? '<span class="medium secondary btn rounded"><a href="#" data-action="list" module-single="' + content.single + '" module-name="' + content.name + '" module-id="' + content.id + '">Liste anzeigen</a></span>' : '') + '\
            </div>\
        </form>\
    </div>';

    var fields = '<li><span></span><input type="hidden" name="id" value="' + content.id + '" />' +
        '<input type="hidden" name="data-id" value="' + dataId + '" /></li>';

    // form template = single data record
    $.each(content.fields, function(index) {
        var field = content.fields[index];
        var html = fieldsFactory.getTemplate(field, exists ? content.data[0].fields[index].value : null, content.id, dataId);
        if (html) {
            fields += '<li class="field">' + html + '</li>';
        }
    });
    template = template.replace('{fields}', fields);
    return template;
}

function initAjaxContent()
{
    // save data
    $('#admin-content form').submit(function(e){
        e.preventDefault();
        $('input').blur();
        var form = $(this);
        var params = form.serialize();
        var alertContainer = form.parent().find('.alert-container');
        alertContainer.hide().html('<div class="secondary alert">Inhalt wird gespeichert...</div>');
        alertContainer.fadeIn();

        $.post(form.attr('action'), params)
            .always(function(data) {
                data = data || {};
                if (data.success === true) {
                    alertContainer.html('<div class="success alert">Inhalt gespeichert!</div>');
                    setTimeout(function(){
                        alertContainer.fadeOut();
                    }, 1000);
                } else {
                    var error = typeof data.error === 'string' ? data.error : 'Fehler beim Speichern!';
                    alertContainer.html('<div class="danger alert">' + error + '</div>');
                }
            });
    });

    // add new
    $('#admin-content a[data-action="new"]').click(function(e){
        e.preventDefault();
        loadModule($(this).attr('module-id'), true);
    });

    // edit
    $('#admin-content a[data-action="edit"]').click(function(e){
        e.preventDefault();
        loadModule($(this).attr('module-id'), true, $(this).attr('data-id'));
    });

    // delete
    $('#admin-content a[data-action="delete"]').click(function(e){
        e.preventDefault();
        if (confirm('Wirklich löschen?')) {
            var id = $(this).attr('module-id');
            var single = $(this).attr('module-single') === "true";
            var name = $(this).attr('module-name');
            var dataId = $(this).attr('data-id');
            $.getJSON('./api/remove.php', {
                "id": id,
                "data-id": dataId
            }, function(response) {
                response = response || {};
                if (response.success === true) {
                    loadModule(id, single);
                } else {
                    $('.alert-container').hide()
                        .html('<div class="danger alert">' + (response.error || 'Fehler beim Löschen') + '</div>')
                        .fadeIn();
                }
            });
        }
    });
    
    // delete image
    $('#admin-content a[data-action="delete-image"]').click(function(e){
        e.preventDefault();
        var url = $(this).attr('data-url');
        var id = $(this).attr('field-id');
        $.getJSON(url, {}, function(response) {
            if (response && response.success === true) {
                $('#admin-content .image-preview').hide();
                $('#admin-content input[name=' + id + ']').val(''); 
            }
        });
    });

    // list module data
    $('#admin-content a[data-action="list"]').click(function(e){
        e.preventDefault();
        var id = $(this).attr('module-id');
        var single = $(this).attr('module-single') === "true";
        var name = $(this).attr('module-name');
        loadModule(id, single);
    });

    // lazy load images
    $('img.lazy').each(function(i){
        var img = $(this);
        img.attr('src', img.attr('data-original'));
    });

    // upload youtube thumbnail
    $('.youtube').blur(function(){
        var value = $(this).val();
        var container = $(this).parent();
        var alertContainer = $(this).parent().find('.alert-yt-container');
        alertContainer.hide().html('<div class="secondary alert">Wird aktualisiert...</div>');
        alertContainer.fadeIn();
        $.getJSON('./api/upload.youtube.php', {
            "url": value,
            "id": $(this).attr('module-id'),
            "data-id": $(this).attr('data-id'),
            "field-id": $(this).attr('field-id')
        }, function(response){
            if (response && typeof response.success === 'boolean' && response.success) {
                container.find('input[type="hidden"]').val(response.name);
                container.find('.image img').attr('src', response.url + '&width=150&height=150');
                alertContainer.hide().html('<div class="success alert">Aktualisiert!</div>');
                alertContainer.fadeIn();
                setTimeout(function(){
                    alertContainer.find('.alert').fadeOut();
                }, 1000);
            } else {
                alertContainer.hide().html('<div class="danger alert">Vorschau nicht verfügbar!</div>');
                alertContainer.fadeIn();
            }
        });
    });

    initFileUpload();
    Gumby.initialize('switches');
}

var moduleRequest;
function loadModule (id, single, dataid)
{
    var loadingTimeout = setTimeout(function(){
        $('#admin-content').html('<div class="row"><h2>Wird geladen...</h2><p>Das Modul wird jeden Moment angezeigt.</p></div>');
    }, 300);
    var script = single ? "./api/module.form.php" : "./api/module.list.php";
    if (moduleRequest) {
        moduleRequest.abort();
    }
    moduleRequest = $.get(script, {
        "id": id,
        "data-id": dataid
    }).always(function(data) {
        clearTimeout(loadingTimeout);
        // ignore aborted requests
        if (data.status === 0) return;
        if (data.success === true) {
            var template = !single ? getListTemplate(data) : getFormTemplate(data);
            $('#admin-content').html(template);
            initAjaxContent();
        } else {
            $('#admin-content').html('<div class="row"><h2>Oops...</h2><p>Irgendwas ist schief gelaufen! Das Modul konnte nicht geladen werden.</p></div>');
        }
        $(window).scrollTop(0);
        fixNavbarHeight();
    });
}

function loadController (controller, options)
{
    var loadingTimeout = setTimeout(function(){
        $('#admin-content').html('<div class="row"><h2>Wird geladen...</h2><p>Das Modul wird jeden Moment angezeigt.</p></div>');
    }, 300);
    if (moduleRequest) {
        moduleRequest.abort();
    }
    var params = {};
    if (options) {
        params = options.replace(/(^\?)/,'').split("&").map(function(n){return n = n.split("="),this[n[0]] = n[1],this}.bind({}))[0];
    }
    params.controller = controller;
    moduleRequest = $.get("./api/module.custom.php", params).always(function(data) {
        clearTimeout(loadingTimeout);
        // ignore aborted requests
        if (data.status === 0) return;
        if (data.success === true) {
            //var template = !single ? getListTemplate(data) : getFormTemplate(data);
            $('#admin-content').html(data.html);
            initAjaxContent();
        } else {
            $('#admin-content').html('<div class="row"><h2>Oops...</h2><p>Irgendwas ist schief gelaufen! Das Modul konnte nicht geladen werden.</p></div>');
        }
        $(window).scrollTop(0);
        fixNavbarHeight();
    });
}

// navi
$('.side-navigation a').click(function(e){
    e.preventDefault ();
    var id = $(this).attr('module-id');
    var html = $(this).attr('module-html');
    var controller = $(this).attr('module-controller');
    var options = '';
    if (controller && controller.indexOf('?') !== -1) {
        options = controller.substr(controller.indexOf('?') + 1);
        controller = controller.substr(0, controller.indexOf('?'));
    }
    var single = $(this).attr('module-single') === "true";
    $('.side-navigation .active').removeClass('active');
    $(this).parent().addClass ('active');
    if (controller) {
        loadController(controller, options);
    }
    else if (id) {
        loadModule(id, single);
    }
    else if (html) {
        $('#admin-content').html($('#' + html).html());
    }
});

// load default page
$('.side-navigation li.active a').click();


// file uploads
var initFileUpload = function()
{
    $('.fileupload').fileupload({
        dataType: 'json',
        submit: function ()
        {
            var alert = $(this).parent().parent().find('.alert');
            alert.removeClass();
            alert.addClass('alert light');
            alert.html('Wird hochgeladen...');
            alert.show ();
        },
        always: function (e, data)
        {
            data.result = data.result || {};
            var alert = $(this).parent().parent().find('.alert');
            var preview = $(this).parent().parent().find('.image-preview');
            var image = $(this).parent().parent().find('.image-container');
            var thumbnail = $(this).parent().parent().find('.thumb-image-container');
            var input = $(this).parent().parent().find('input[type="hidden"]');
            alert.removeClass();
            if (data.result.success) {
                alert.addClass('alert success');
                alert.html('Bild wurde gespeichert');
                preview.removeClass('hide').show();
                if (data.result.url) {
                    image.attr('src', data.result.url);
                    thumbnail.attr('src', data.result.url + '&width=150&height=150&no-cache='+Date.now());
                    input.val(data.result.name);
                }
            } else {
                alert.addClass('alert danger');
                alert.html(data.result.error || 'Datei konnte nicht gespeichert werden');
            }
            alert.show();
            setTimeout(function(){
                alert.fadeOut();
            }, 2000);
        }
    });
}