window.bindDirectorySuggestion = function( targetInputElement ){
    const $targetInputElement = $(targetInputElement);
    const $suggestBox = $(`<div class="directory-suggestion"></div>`);
    $targetInputElement.after($suggestBox);

    $targetInputElement.on('keyup change', function(){
    
        $.ajax({
            "url": "?a=api.directory_suggestion",
            "type": "post",
            "data": {
                "realpath_base_dir": $targetInputElement.val(),
                'CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            })
            .done((res) => {
                $suggestBox.html('');
                if( !res || !res.result ){
                    return;
                }

                var html = '';
                res.suggestion.forEach((row)=>{
                    html += `<li><button type="button" data-type="${row.type}" data-value="${row.realpath}">${row.realpath}</button></li>`;
                });
                $suggestBox.append(`<ul>${html}</ul>`);
                $suggestBox.find( 'button' ).on('click', function(e){
                    var $button = $(this);
                    $targetInputElement.val($button.attr('data-value'));
                });
            });
    });
}
