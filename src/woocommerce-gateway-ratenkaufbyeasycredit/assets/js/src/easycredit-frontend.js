jQuery(function($){

    var selector = $('meta[name=easycredit-widget-selector]').attr('content');
    $(selector).after($('<easycredit-widget />').attr({
        'webshop-id' : $('meta[name=easycredit-api-key]').attr('content'),
        'amount': $('meta[name=easycredit-widget-price]').attr('content'),
    }));

    $('.woocommerce-checkout').on( 'change', '#billing_company', function(){
        $(this).trigger("update_checkout");
    });

    var onHydrated = function (selector, cb) {
        if (!document.querySelector(selector)) {
            return
        }

        window.setTimeout(function() {
            if (!document.querySelector(selector).classList.contains('hydrated')) {
                return onHydrated(selector, cb);
            }
            cb();
        }, 50)
    }

    var watchForSelector = function (selector, cb) {
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType !== 1) {
                        return;
                    }
                    if (el = node.querySelector(selector)) {
                        cb();
                    }
                });
            });
        });
        observer.observe(document, { subtree: true, childList: true });
    }

    var handleShippingPaymentConfirm = function () {
        onHydrated('easycredit-checkout', function() {
            $('easycredit-checkout').submit(function(e){
                var form = $('form.checkout');
                form.append('<input type="hidden" name="easycredit[submit]" value="1" />')
                if (e.detail && e.detail.numberOfInstallments) {
                    form.append('<input type="hidden" name="easycredit[number-of-installments]" value="'+ e.detail.numberOfInstallments +'" />')
                }
                form.submit();

                return false;
            });

            $('form.checkout').on('checkout_place_order', function() {
                if (!$('easycredit-checkout').is(':visible')
                    || !$('easycredit-checkout').prop('isActive')
                    || $('easycredit-checkout').prop('paymentPlan') !== ''
                    || $('easycredit-checkout').prop('alert') !== ''
                ) {
                    return true;
                }

                if ($(this).find('input[name="easycredit[submit]"]').length > 0) {
                    return true;
                }

                $('easycredit-checkout')
                    .get(0)
                    .dispatchEvent(new Event('openModal'));
                return false;
            });
            $(document.body).on('checkout_error', function() {
                $('easycredit-checkout')
                    .get(0)
                    .dispatchEvent(new Event('closeModal'));
            });
        });
    }

    watchForSelector('easycredit-checkout', handleShippingPaymentConfirm);
    onHydrated('easycredit-checkout', handleShippingPaymentConfirm);
});
