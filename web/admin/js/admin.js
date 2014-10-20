var fixNavbarHeight = function () {
    $('.max-height').height ($( document ).height() - $('.navbar-header').height());
}

// fix sidebar height
$ (window).resize (fixNavbarHeight);
fixNavbarHeight();

var fieldTypes = {
    text: function(field, value, moduleId, dataId)
    {
        return '<h4>' + field.name + '</h4>' +
            '<input class="input" type="text" name="' + field.id + '" placeholder="' + (field.placeholder || '') + '" value="' + (value || '') + '" />';
    },
    textarea: function(field, value, moduleId, dataId)
    {
        return '<h4>' + field.name + '</h4>' +
            '<textarea class="input textarea" name="' + field.id + '" placeholder="' + (field.placeholder || '') + '">' + (value || '') + '</textarea>';
    },
    select: function(field, value, moduleId, dataId)
    {
        var html = '<h4>' + field.name + '</h4><div class="picker" style="margin-left:0;">' +
            '<select class="select" name="' + field.id + '">' +
            '<option value="">' + (field.placeholder || '') + '</option>';
        if (field.values && field.values.length) {
            for (var i=0; i<field.values.length; i++) {
                var _value = field.values[i];
                value = value ? value.toString() : "";
                var isSelected = _value.value.toString() === value;
                html += '<option value="' + (_value.value || '') + '" ' + (isSelected ? 'selected' : '') + '>' + (_value.label || '') + '</option>'
            }
        }
        html += '</div></select>';
        return html;
    },
    youtube: function(field, value, moduleId, dataId)
    {
        value = value || {};
        value.url = value.url || "";
        var youtubeUrlDirect = value.url.match(/\?v=([\w-]+)/);
        var youtubeUrl = youtubeUrlDirect ? 'http://www.youtube.com/embed/' +  youtubeUrlDirect[1] : value.url;
        var imagePreview = value.name ? ('./api/file.php?id=' + moduleId + '&file=' + value.name + '&width=150&height=150') : '';
        return '<h4>' +
            '<span>' + field.name + '</span>' +
            '<span class="pull_right" style="height: 38px;"><span class="alert-yt-container "></span>&nbsp;</span>' +
            '</h4>' +
            '<div class="image photo pull_left rounded switch" gumby-trigger="#preview-' + dataId + '" style="margin-left: 1px !important; background-color: white; cursor: pointer;">' +
            '<img src="' + (!imagePreview ? './img/thumb_not_available.png' : imagePreview) + '" class="thumb-image-container" style="height:150px;width:150px;" alt="">' +
            '</div>' +
            '<div class="modal" id="preview-' + dataId + '">' +
            '<div class="content">' +
            '<a class="close switch btn rounded default" gumby-trigger="|#preview-' + dataId + '"><i class="icon-cancel" /></i></a>' +
            '<div class="row text-center">' +
            '<article class="youtube video">' +
            '<iframe width="560" height="315" src="' + youtubeUrl + '"></iframe >' +
            '</article>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<input type="hidden" name="' + field.id + '[name]" value="' + (value.name || '') + '" />' +
            '<input class="input youtube" type="text" name="' + field.id + '[url]" field-id="' + field.id + '" module-id="' + moduleId + '" data-id="' + dataId + '" placeholder="' + (field.placeholder || '') + '" value="' + (value.url || '') + '" />';
    },
    image: function(field, value, moduleId, dataId)
    {
        var imagePreview = value ? ('./api/file.php?id=' + moduleId + '&file=' + value.name) : '';
        var imagePreviewThumb = imagePreview + '&width=150&height=150';
        return '<h4>' + field.name + '</h4>' +
            '<div class="image-preview ' + (!imagePreview ? 'hide' : '') + '">' +
            '<div class="image photo pull_left rounded switch" gumby-trigger="#preview-' + dataId + '" style="margin-left: 1px !important; background-color: white; cursor: pointer;">' +
            '<img src="' + (!imagePreview ? './img/thumb_not_available.png' : imagePreviewThumb) + '" class="thumb-image-container" style="height:150px;width:150px;" alt="">' +
            '</div>' +
            '<span class="small default btn rounded pull_left" style="margin-left: 10px !important;"><a href="#" data-action="delete-image" field-id="' + field.id + '" data-url="./api/remove.php?id=' + moduleId + '&data-id=' + dataId + '&field-id=' + field.id + '">Bild entfernen</a></span>' +
            '</div>' +
            '<div class="modal" id="preview-' + dataId + '">' +
            '<div class="content">' +
            '<a class="close switch btn rounded default" gumby-trigger="|#preview-' + dataId + '"><i class="icon-cancel" /></i></a>' +
            '<div class="row text-center">' +
            '<img src="' + (!imagePreview ? './img/thumb_not_available.png' : imagePreview) + '" class="image-container" alt="">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="danger alert hide"></div>' +
            '<div class="input">' +
            '<input id="fileupload" type="file" name="file" data-url="./api/upload.php?id=' + moduleId + '&data-id=' + dataId + '&field-id=' + field.id + '" accept="image/*">' +
            '<input type="hidden" name="' + field.id + '" value="' + (value || '') + '" />'
            '</div>';
    }
};

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
    $.each(content.fields, function(index){
        var field = content.fields[index];
        thead += '<th>' + field.name + '</th>';
    });
    thead += '<th></th>';
    thead += '</tr>';

    var tbody = '';
    $.each(content.data, function(i){
        var data = content.data[i];
        tbody += '<tr>'
        $.each(content.fields, function(ii){
            var field = content.fields[ii];
            var value = data[field.id];
            if (field.type === 'image') {
                var imagePreview = value ? ('./api/file.php?id=' + content.id + '&file=' + value.name + '&width=150&height=100&quality=50') : './img/thumb_not_available.png';
                value = '<a href="#" data-action="edit" module-name="' + content.name + '" module-id="' + content.id + '" data-id="' + data.id + '"><img data-original="' + imagePreview + '" class="lazy list-thumb" /></a>';
            }
            if (field.type === 'youtube') {
                var imagePreview = value ? ('./api/file.php?id=' + content.id + '&file=' + value.name + '&width=150&height=100&quality=50') : '';
                value = '<a href="#" data-action="edit" module-name="' + content.name + '" module-id="' + content.id + '" data-id="' + data.id + '"><img data-original="' + imagePreview + '" class="lazy list-thumb" /></a>';
            }
            tbody += '<td>' + value + '</td>';
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
    var exists = content.data && content.data.created_at !== undefined;
    var template = '<div class="row">\
        <h2 style="padding-bottom: 5px;">\
            ' + (!content.single && exists ? '<span class="small danger btn rounded pull_right"><a href="#" data-action="delete" module-single="' + content.single + '" module-name="' + content.name + '" module-id="' + content.id + '" data-id="' + content.data.id + '" >Löschen</a></span>' : '') + '\
            <span>' + content.name + ' ' + (exists ? 'bearbeiten' : 'hinzufügen') + '</span>\
        </h2>\
        <div style="font-size:70%;color:#ccc;">Element-ID: ' + content.data.id + '</div>\
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
        '<input type="hidden" name="data-id" value="' + content.data.id + '" /></li>';

    $.each(content.fields, function(index){
        var field = content.fields[index];
        if ('function' === typeof fieldTypes[field.type]) {
            fields += '<li class="field">' + fieldTypes[field.type](field, content.data[field.id], content.id, content.data.id) + '</li>';
        } else {
            console.log('Fieldtype ' + field.type + ' is not defined');
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
        loadModule($(this).attr('module-id'), $(this).attr('module-name'), true);
    });

    // edit
    $('#admin-content a[data-action="edit"]').click(function(e){
        e.preventDefault();
        loadModule($(this).attr('module-id'), $(this).attr('module-name'), true, $(this).attr('data-id'));
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
                    loadModule(id, name, single);
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
        loadModule(id, name, single);
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
            console.log(response);
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
function loadModule (id, name, single, dataid)
{
    var loadingTimeout = setTimeout(function(){
        $('#admin-content').html('<div class="row"><h2>Wird geladen...</h2><p>Das ' + name + '-Modul wird jeden Moment angezeigt.</p></div>');
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
    });
}

// navi
$('.side-navigation a').click(function(e){
    e.preventDefault ();
    var id = $(this).attr('module-id');
    var html = $(this).attr('module-html');
    var single = $(this).attr('module-single') === "true";
    var name = $(this).text();
    $('.side-navigation .active').removeClass('active');
    $(this).parent().addClass ('active');
    if (id) {
        loadModule(id, name, single);
    }
    if (html) {
        $('#admin-content').html($('#' + html).html());
    }
});

// load default page
$('.side-navigation li.active a').click();


// file uploads
var initFileUpload = function()
{
    $('#fileupload').fileupload({
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
                    thumbnail.attr('src', data.result.url + '&width=150&height=150');
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