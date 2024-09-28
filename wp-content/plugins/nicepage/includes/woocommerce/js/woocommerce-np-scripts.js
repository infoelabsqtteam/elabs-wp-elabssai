jQuery(document).ready(function($) {
    jQuery(document).on('click', '.u-product .u-btn.u-product-control', function(e) {
        e.preventDefault();
        jQuery(this).parents('.u-product').find('.single_add_to_cart_button').click();
    });
    function changePrice() {
        if (jQuery('.woocommerce-variation-price').length) {
            var priceControl = jQuery('.u-product-price:visible');
            if (priceControl.length > 1) {
                priceControl.each(function(index) {
                    if (index === 0) { return; }
                    priceControl[index].remove();
                });
            }
            priceControl.find('.u-price').html(jQuery('.woocommerce-variation-price .price ins').not(':visible').html());
            priceControl.find('.u-old-price').html(jQuery('.woocommerce-variation-price .price del').not(':visible').html());
        }
    };
    jQuery(document).on('change', '.u-product-variant select', changePrice);
    function changeQuantity() {
        if (jQuery('.quantity').length) {
            jQuery('form .quantity input.qty').val(jQuery('.u-quantity-input .u-input').val());
        }
    };
    jQuery(document).on('change', '.u-quantity-input', changeQuantity);

    jQuery('[data-products-datasource="cms"] .u-select-sorting').change(function() {
        let selectedOption = $(this).children("option:selected").val();
        let url = new URL(window.location.href);
        let params = new URLSearchParams(url.search);
        params.delete('sorting');
        params.append('sorting', selectedOption);
        url.search = params.toString();
        let newUrl = url.toString();
        if (newUrl) {
            window.location.href = newUrl;
        }
    });
    jQuery('[data-products-datasource="cms"] .u-select-categories').change(function() {
        let selectedOption = jQuery(this).children("option:selected").val();
        let url = new URL(window.location.href);
        let params = new URLSearchParams(url.search);
        params.delete('categoryId');
        params.append('categoryId', selectedOption);
        url.search = params.toString();
        let newUrl = url.toString();
        if (newUrl) {
            window.location.href = newUrl;
        }
    });
});