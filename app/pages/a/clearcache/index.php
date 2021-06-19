<p>キャッシュを消去します。</p>
<p><button class="px2-btn px2-btn--primary cont-btn-clearcache">消去する</button></p>
<pre class="cont-console"><code></code></pre>
<script>
$('.cont-btn-clearcache').on('click', function(){
    $.ajax({
        "url": '?a=api.clearcache',
        'success': function(data){
            console.log(data);
            if( data.result ){
                $('.cont-console code').html(data.stdout);
            }
        },
        'error': function(data){
            console.error(data);
        },
        'complete': function(){
            console.log('done!');
        }
    });
});
</script>