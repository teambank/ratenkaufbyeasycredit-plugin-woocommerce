jQuery(function($){
    if (typeof wc_ratenkaufbyeasycredit_config === 'undefined') {
        return;
    }

    var prefix = 'wc_ratenkaufbyeasycredit_';
    var getBaseUrl = function(action) {
        return wc_ratenkaufbyeasycredit_config.url+'?action='+prefix+action;
    }

    $('#woocommerce_ratenkaufbyeasycredit_api_verify_credentials').click(function(){
        var apiKey = $('#woocommerce_ratenkaufbyeasycredit_api_key').val();
        var apiToken = $('#woocommerce_ratenkaufbyeasycredit_api_token').val();

        $.getJSON(getBaseUrl('verify_credentials'),{api_key: apiKey, api_token: apiToken},function(r){
            alert(r.msg);
        });
    });

});
