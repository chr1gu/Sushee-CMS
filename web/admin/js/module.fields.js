var fieldsFactory = function()
{
    return {
        getTemplate: function(field, value, moduleId, dataId)
        {
            switch (field.type)
            {
                case "text":
                case "title":
                case "button_sms":
                case "button_call":
                case "button_mail":
                {
                    var placeholder = field.placeholder || '';
                    return '<h4>' + field.name + '</h4>' +
                        '<input class="input" type="text" name="' + field.id + '" placeholder="' + placeholder + '" value="' + (value || '') + '" />';
                }
                case "textarea":
                case "mutable_select":
                {
                    return '<h4>' + field.name + '</h4>' +
                        '<textarea class="input textarea" name="' + field.id + '" placeholder="' + (field.placeholder || '') + '">' + (value || '') + '</textarea>';
                }
                case "select":
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
                }
                case "youtube":
                {
                    var youtubeUrlDirect = value != null ? value.match(/\?v=([\w-]+)/) : null;
                    var preview = field.preview || '';
                    var youtubeUrl = youtubeUrlDirect != null ? 'http://www.youtube.com/embed/' +  youtubeUrlDirect[1] : value;
                    var imagePreview = preview ? ('./api/file.php?id=' + moduleId + '&file=' + preview + '&width=150&height=150') : '';
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
                        '<input type="hidden" name="' + field.id + '_preview" value="' + preview + '" />' +
                        '<input class="input youtube" type="text" name="' + field.id + '" field-id="' + field.id + '" module-id="' + moduleId + '" data-id="' + dataId + '" placeholder="' + (field.placeholder || '') + '" value="' + (value || '') + '" />';
                }
                case "image":
                {
                    var imagePreview = value ? ('./api/file.php?id=' + moduleId + '&file=' + value) : '';
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
                        '<input class="fileupload" type="file" name="file" data-url="./api/upload.php?id=' + moduleId + '&data-id=' + dataId + '&field-id=' + field.id + '" accept="image/*">' +
                        '<input type="hidden" name="' + field.id + '" value="' + (value || '') + '" />'
                    '</div>';
                }
                case "input_text":
                case "input_textarea":
                case "input_datetime":
                case "button_submit":
                {
                    // return an empty string if there is no interaction possible
                    return '<h4>' + field.name + '</h4>' +
                    '<span>' + '&nbsp;' + '</span>';
                }
                default: {
                    console.log('Fieldtype ' + field.type + ' is not defined');
                    return null;
                }
            }
        },
        getListTemplate: function(field, value, moduleId, moduleName, dataId)
        {
            var imagePreview = '';
            if (field.type === 'image')
                imagePreview = value ? ('./api/file.php?id=' + moduleId + '&file=' + value + '&width=150&height=100&quality=50') : './img/thumb_not_available.png';
            else if (field.type === 'youtube')
                imagePreview = field.preview ? ('./api/file.php?id=' + moduleId + '&file=' + field.preview + '&width=150&height=100&quality=50') : './img/thumb_not_available.png';
            if (imagePreview !== '')
                return '<a href="#" data-action="edit" module-name="' + moduleName + '" module-id="' + moduleId + '" data-id="' + dataId + '"><img data-original="' + imagePreview + '" class="lazy list-thumb" /></a>';
            return value;
        }
    };
}();
