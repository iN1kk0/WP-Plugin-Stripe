<div class="payment-form-block">
    <form id="payment-form">

        <h2>How many credit reports will you require?</h2>

        <div class="input-group plus-minus-input">

            <div class="input-group-button">
                <a href="#" type="button" class="button hollow circle" data-quantity="minus" data-field="quantity">
                    <i class="fa fa-minus" aria-hidden="true"></i>
                </a>
            </div>

            <input type="number" id="quantity" name="quantity" min="1" max="10" value="1"/>

            <div class="input-group-button">
                <a href="#" type="button" class="button hollow circle" data-quantity="plus" data-field="quantity">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </a>
            </div>

            <b>$<span id="price_number">25</span>.00</b>

        </div>

        <div class="report_blocks">
            <div class="report_block" data-id="1">
                <h3>Report #<span class="report_number">1</span></h3>
                <label for="first_name">First name
                    <input required type="text" class="first_name" name="report[1][first_name]"
                           placeholder="First name"/>
                </label>
                <label for="last_name">Last name
                    <input required type="text" class="last_name" name="report[1][last_name]" placeholder="Last name"/>
                </label>
                <div style="clear:both;"></div>
            </div>
        </div>

        <h3>Payment</h3>

        <label for="email">Billing Email address
            <input required type="text" id="email" placeholder="Billing Email address"/>
        </label>

        <label for="name">Cardholder name
            <input required type="text" id="name" placeholder="Cardholder name"/>
        </label>

        <div id="card-element"></div>

        <button id="submit">
            <div class="spinner hidden" id="spinner"></div>
            <span id="button-text">Order report</span>
        </button>

        <p class="privacy-form-text">By paying for the reports you are agreeing to our <a href="#">T&Cs</a> and <a
                    href="#">Privacy Policy</a></p>

        <p id="card-error" role="alert"></p>
        <p class="result-message hidden">
            Payment succeeded, see the result in your
            <a href="" target="_blank">Stripe dashboard.</a> Refresh the page to pay again.
        </p>
    </form>

    <h2 id="thankyou">Thank you for your order!</h2>
</div>