jQuery(function($){

        $('#main div.product .summary .price').rkPaymentPage({
            webshopId : $('meta[name=easycredit-api-key]').attr('content'),
            amount: $('meta[name=easycredit-product-price]').attr('content'),
        });
        
});
