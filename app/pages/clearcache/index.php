<p>キャッシュを消去します。</p>
<p><button class="px2-btn px2-btn--primary cont-btn-clearcache">消去する</button></p>
<pre class="cont-console"><code></code></pre>
<script>
$('.cont-btn-clearcache').on('click', function(){
    var $btn = $(this);
    var $console = $('.cont-console code');
    $console.html('');

    $btn.attr({'disabled': true});
    px2style.loading();

    $.ajax({
        "url": '?a=api.clearcache',
        "method": "post",
        "data": {
            'ADMIN_USER_CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        'success': function(data){
            console.log(data);
            if( data.result ){
                $console.html(data.stdout);
            }
        },
        'error': function(data){
            console.error(data);
        },
        'complete': function(){
            console.log('done!');
            $btn.attr({'disabled': false});
            px2style.closeLoading();
        }
    });
});
</script>