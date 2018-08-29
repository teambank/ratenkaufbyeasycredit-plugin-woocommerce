jQuery(function($){

        var selector = $('meta[name=easycredit-widget-selector]').attr('content');
        $(selector).rkPaymentPage({
            webshopId : $('meta[name=easycredit-api-key]').attr('content'),
            amount: $('meta[name=easycredit-widget-price]').attr('content'),
        });
});
