class StripeCheckout {
    constructor ($form, $response) {
        this.form = jQuery($form)
        this.data = $response.data
        this.intent = $response.data?.intent

        window.form = this.form;
        console.log(this.data, this.form, this.intent)
    }

    init () {
        this.form.find('.fluent_booking_payment_methods').hide()

        let submitButton = "<button id='fluent_booking_stipe_pay' style='margin-top:23px;!important' type='submit'>Pay Now</button>";

        var stripe = Stripe(this.data?.data?.payment_args?.public_key);

        const elements = stripe.elements({
            clientSecret: this.intent.client_secret
        });

        const paymentElement = elements.create('payment', {
        });

        paymentElement.mount('.fcal_payment_items_wrapper');

        jQuery('.fcal_payment_items_wrapper').append('<p id="fluent_booking_loading_payment_processor">Loading Payment Processor...</p>');
        this.form.find('.fcal_submit').hide();
        let that= this;

        paymentElement.on('ready', function(event) {
            jQuery('#fluent_booking_loading_payment_processor').remove();
            jQuery('.fcal_payment_items_wrapper').append(submitButton);

            jQuery('#fluent_booking_stipe_pay').on('click', function(e) {
                e.preventDefault()
                elements.submit().then(result=> {
                    jQuery(this).text('Processing...');
                    jQuery(this).attr('disabled', true);
                    const pay = stripe.confirmPayment({
                        elements,
                        confirmParams: {
                            // redirect: 'if_required'
                            // return_url: that.data?.data?.payment_args?.success_url
                        },
                        redirect: 'if_required'
                    }).then((result) => {
                        jQuery.post(window.fluentCalendarPublicVars.ajaxurl, {
                            action: 'fluent_cal_confirm_stripe_payment',
                            intentId: result?.paymentIntent?.id
                        }).then((response) => {
                            window.location.href =  that.data?.data?.payment_args?.success_url;
                            jQuery(this).text('Pay Now');
                            jQuery(this).attr('disabled', false);
                        });
                    })

                }).catch(error => {
                    jQuery(this).text('Pay Now');
                    jQuery(this).attr('disabled', false);
                })

            })
        });
    }
  }
  
  window.addEventListener("fluent_booking_payment_next_action_stripe", function (e) {
    new StripeCheckout(e.detail.form, e.detail.response).init();
  });
