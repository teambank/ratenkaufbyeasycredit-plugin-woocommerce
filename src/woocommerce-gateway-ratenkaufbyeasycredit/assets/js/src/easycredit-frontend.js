jQuery(function($){

    var selector = $('meta[name=easycredit-widget-selector]').attr('content');

    var widget = $('<easycredit-widget />').attr({
        'webshop-id' : $('meta[name=easycredit-api-key]').attr('content'),
        'amount': $('meta[name=easycredit-widget-price]').attr('content'),
    });
    $(selector).first().after(widget);

    $('.single_variation_wrap').on( 'show_variation', function ( event, variation ) {
        if (variation.display_price) {
            widget.get(0).setAttribute('amount', variation.display_price);
        }
    } );

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
            cb(selector);
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
                        cb(selector);
                    }
                });
            });
        });
        observer.observe(document, { subtree: true, childList: true });
    }

    var handleShippingPaymentConfirm = function (selector) {
        onHydrated(selector, function(selector) {
            $(selector).submit(function(e){
                var form = $('form.checkout');
                form.append('<input type="hidden" name="easycredit[submit]" value="1" />')
                form.append('<input type="hidden" name="terms" value="On" />')
                form.append('<input type="hidden" name="legal" value="On" />')
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

                $(selector)
                    .get(0)
                    .dispatchEvent(new Event('openModal'));
                return false;
            });
            $(document.body).on('checkout_error', function() {
                $(selector)
                    .get(0)
                    .dispatchEvent(new Event('closeModal'));
            });
        });
    }

    watchForSelector('easycredit-checkout', handleShippingPaymentConfirm);
    onHydrated('easycredit-checkout', handleShippingPaymentConfirm);


    function replicateForm(buyForm, additionalData) {
      if (!buyForm) {
        return false;
      }

      var form = document.createElement("form");
      form.setAttribute("action", buyForm.getAttribute('action'));
      form.setAttribute("method", buyForm.getAttribute('method'));
      form.style.display = "none";

      var formData = new FormData(buyForm);
      for (const prop in additionalData) {
        formData.set(prop, additionalData[prop]);
      }

      for (var key of formData.keys()) {
        let field = document.createElement("input");
        field.setAttribute("name", key);
        field.setAttribute("value", formData.get(key));
        form.append(field);
      }

      document.querySelector("body").append(form);

      return form;
    }

    var handleExpressButton = function(selector) {
        onHydrated(selector, function(selector) {
            $(selector).submit(function(e){
                var form = $(this).closest('.summary').find('form.cart');
                if (form.length === 0) {
                    form = $('body').find('form.cart');
                }

                var addToCartButton = document.querySelector('button[name="add-to-cart"], button.single_add_to_cart_button');
                if (addToCartButton) {

                    var form = replicateForm(form.get(0), {
                        'add-to-cart': addToCartButton.getAttribute('value'),
                        'easycredit-express': 1
                    });
                    form.submit();

                    return;
                }

                if ($(this).closest('.wc-proceed-to-checkout').length > 0) {
                    window.location.href = $(this).data('url');
                    return;
                }
                alert('Der easyCredit-Ratenkauf konnte nicht gestartet werden.');
            });
            $('form.variations_form').on('show_variation', function(event, data, purchasable) {
                (!purchasable) ? $(selector).hide() : $(selector).show();
            });
        });
    }

    watchForSelector('easycredit-express-button', handleExpressButton);
    onHydrated('easycredit-express-button', handleExpressButton);
});
