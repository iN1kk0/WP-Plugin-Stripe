let stripe = Stripe(pk);

let $ = jQuery.noConflict();
const amount_fix = 2500;
let amount = 2500;
let purchase = {
    items: amount
};

document.addEventListener("DOMContentLoaded", function (event) {
    const current_price = parseInt($('#price_number').text());

    $(document).on("wheel", "input[type=number]", function (e) {
        $(this).blur();
    });

    $("[type='number']").keypress(function (evt) {
        evt.preventDefault();
    });

    $('[data-quantity="plus"]').click(function (e) {
        e.preventDefault();
        fieldName = $(this).attr('data-field');
        let currentVal = parseInt($('input[name=' + fieldName + ']').val());
        if (!isNaN(currentVal) && currentVal < 10) {
            $('input[name=' + fieldName + ']').val(currentVal + 1);

            $('#submit #button-text').text('Order reports');

            amount = amount_fix * parseInt($('#quantity').val());

            purchase = {
                items: amount
            };

            let last_parent = $('.report_block').last();

            let current_value = parseInt($('#quantity').val());

            $('#price_number').text(current_price * current_value);

            let inc = parseInt(last_parent.attr('data-id')) + 1;

            let clone = last_parent.clone().attr('data-id', inc);

            clone.find('.report_number').text(inc);

            clone.find('input').each(function () {
                this.value = "";
                let name_number = this.name.match(/\d+/);
                name_number++;
                this.name = this.name.replace(/\[[0-9]\]+/, '[' + name_number + ']')
            });

            last_parent.after(clone);

        }

    });

    $('[data-quantity="minus"]').click(function (e) {
        e.preventDefault();
        fieldName = $(this).attr('data-field');
        let currentVal = parseInt($('input[name=' + fieldName + ']').val());
        if (!isNaN(currentVal) && currentVal > 1) {
            $('input[name=' + fieldName + ']').val(currentVal - 1);


            amount = amount_fix * parseInt($('#quantity').val());

            purchase = {
                items: amount
            };

            let current_value = parseInt($('#quantity').val());

            $('#price_number').text(current_price * current_value);

            let last_parent = $('.report_block').last();
            last_parent.remove();

        } else {
            $('input[name=' + fieldName + ']').val(1);
        }

        if (currentVal == 2) {
            $('#submit #button-text').text('Order report');
        }
    });
    document.getElementById("submit").disabled = true;

    let elements = stripe.elements();

    let style = {
        base: {
            color: "#32325d",
            fontFamily: 'Arial, sans-serif',
            fontSmoothing: "antialiased",
            fontSize: "16px",
            "::placeholder": {
                color: "#32325d"
            }
        },
        invalid: {
            fontFamily: 'Arial, sans-serif',
            color: "#fa755a",
            iconColor: "#fa755a"
        }
    };

    let card = elements.create("card", {
        style: style,
        hidePostalCode: true
    });
    card.mount("#card-element");

    card.on("change", function (event) {
        document.querySelector("button").disabled = event.empty;
        document.querySelector("#card-error").textContent = event.error ? event.error.message : "";
    });

    let form = document.getElementById("payment-form");
    form.addEventListener("submit", function (event) {
        event.preventDefault();

        loading(true);

        fetch("/wp-content/themes/thefox_child_theme/stripe/create.php", { // path to create.php file
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(purchase)
        })
            .then(function (result) {
                return result.json();
            })
            .then(function (data) {
                payWithCard(stripe, card, data.clientSecret);
            });
    });

    let payWithCard = function (stripe, card, clientSecret) {
        stripe
            .confirmCardPayment(clientSecret, {
                receipt_email: document.getElementById('email').value,
                payment_method: {
                    card: card,
                    billing_details: {
                        email: document.getElementById('email').value,
                        name: document.getElementById('name').value
                    },
                }
            })
            .then(function (result) {
                if (result.error) {
                    // Show error to your customer
                    showError(result.error.message);
                } else {
                    // The payment succeeded!
                    orderComplete(result.paymentIntent.id);

                    //console.log(result);
                    //console.log(amount);

                    let order_id = result.paymentIntent.id
                    let quantity = document.getElementById('quantity').value;
                    let email = document.getElementById('email').value;

                    let names = [];
                    $('.report_block').each(function () {
                        names.push($(this).find('.first_name').val() + ' ' + $(this).find('.last_name').val());
                    })
                    let names_string = names.join(', ');
                    //console.log(names_string);
                    let data = "action=add_reportes_orders&order_id=" + order_id + "&quantity=" + quantity + "&email=" + email + "&names=" + names_string;
                    $.ajax(
                        {
                            type: "POST",
                            url: ajaxurl,
                            data: data,
                            success: function (msg) {
                                //console.log(msg);
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                console.log(errorThrown);
                            }
                        });

                    $('#payment-form').hide();
                    $('#thankyou').show();

                }
            });
    };

// Shows a success message when the payment is complete
    let orderComplete = function (paymentIntentId) {
        loading(false);
        document
            .querySelector(".result-message a")
            .setAttribute(
                "href",
                "https://dashboard.stripe.com/test/payments/" + paymentIntentId
            );
        document.querySelector(".result-message").classList.remove("hidden");
        document.querySelector("button").disabled = true;
    };

// Show the customer the error from Stripe if their card fails to charge
    let showError = function (errorMsgText) {
        loading(false);
        let errorMsg = document.querySelector("#card-error");
        errorMsg.textContent = errorMsgText;
        setTimeout(function () {
            errorMsg.textContent = "";
        }, 4000);
    };

// Show a spinner on payment submission
    let loading = function (isLoading) {
        if (isLoading) {
            // Disable the button and show a spinner
            document.querySelector("button").disabled = true;
            document.querySelector("#spinner").classList.remove("hidden");
            document.querySelector("#button-text").classList.add("hidden");
        } else {
            document.querySelector("button").disabled = false;
            document.querySelector("#spinner").classList.add("hidden");
            document.querySelector("#button-text").classList.remove("hidden");
        }
    };

});