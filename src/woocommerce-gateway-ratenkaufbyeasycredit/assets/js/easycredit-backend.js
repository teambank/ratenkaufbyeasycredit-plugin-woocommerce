jQuery(function($){
    var prefix = 'wc_ratenkaufbyeasycredit_';
    var getBaseUrl = function(action) {
        var l = window.location;
        return l.protocol+'//'+l.host+'/'+l.pathname.split('/')[1]+'/admin-post.php?action='+prefix+action;
    }

    $('#woocommerce_ratenkaufbyeasycredit_api_verify_credentials').click(function(){
        var apiKey = $('#woocommerce_ratenkaufbyeasycredit_api_key').val();
        var apiToken = $('#woocommerce_ratenkaufbyeasycredit_api_token').val();

        $.getJSON(getBaseUrl('verify_credentials'),{api_key: apiKey, api_token: apiToken},function(r){
            alert(r.msg);
        });
    });

});
