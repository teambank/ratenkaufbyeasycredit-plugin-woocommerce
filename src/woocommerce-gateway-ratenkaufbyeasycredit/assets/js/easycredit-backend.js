jQuery(function($){
    if (typeof wc_ratenkaufbyeasycredit_config === 'undefined') {
        return;
    }

    var prefix = 'wc_ratenkaufbyeasycredit_';
    var getBaseUrl = function(action) {
        return wc_ratenkaufbyeasycredit_config.url+'?action='+prefix+action;
    }

    $('#woocommerce_ratenkaufbyeasycredit_api_verify_credentials').click(function(){
        let button = $(this);
        button.prop('disabled', true);
        var apiKey = $('#woocommerce_ratenkaufbyeasycredit_api_key').val();
        var apiToken = $('#woocommerce_ratenkaufbyeasycredit_api_token').val();
        var apiSignature = $('#woocommerce_ratenkaufbyeasycredit_api_signature').val();

        $.getJSON(getBaseUrl('verify_credentials'),{api_key: apiKey, api_token: apiToken, api_signature: apiSignature},function(r){
            button.prop('disabled', false);
            alert(r.msg);
        });
    });

});
