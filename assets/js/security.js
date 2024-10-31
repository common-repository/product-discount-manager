"use strict";

jQuery(document).ready(function ($) {

    // Function to show the Bootstrap modal
    function showCustomModalTwo(alertId, msg) {
        $(".alertMsg").text(msg);
        $(`${alertId}`).modal('show');
    }
    
    /* ======= Restriction for Category Number  ======*/
    let catItem = [];
    function handleCategoryCheckbox(checkbox) {
        const catItemVal = checkbox.val();
    
        if (checkbox.is(':checked')) {
            if (!catItem.includes(catItemVal)) {
                // Uncheck previously checked checkboxes
                const classes = checkbox.attr('class').split(' ');
                const checkboxSelector = classes[classes.length - 1];
    
                $(`.${checkboxSelector}:checked`).not(checkbox).prop('checked', false);
                catItem = [catItemVal]; // Reset catItem to the currently checked checkbox
            }
        } else {
            const index = catItem.indexOf(catItemVal);
            if (index !== -1) {
                catItem.splice(index, 1);
            }
        }
    
        if (catItem.length >= 1) {
            const msg = `You are not allowed to choose more than ${catItem.length} Category in this version!`;
            showCustomModalTwo('#wcpd_customAlert', msg);
            showCustomModalTwo('#wcpd_customUpdateAlert', msg);
        }
    }
    
    // When a .wcpd_category_input checkbox is clicked
    $(".wcpd_category_input").on('click', function () {
        // Unchecked ".changePriceCat" if already Checked
        $(".changePriceCat").each(function() {
            if ($(this).is(':checked')) {
                $(this).prop('checked', false);
                handleCategoryCheckbox($(this));
            }
        });
        handleCategoryCheckbox($(this));
    });
    
    // When a .changePriceCat checkbox is clicked
    $(".changePriceCat").on('click', function () {
        // Unchecked ".wcpd_category_input" if already Checked
        $(".wcpd_category_input").each(function() {
            if ($(this).is(':checked')) {
                $(this).prop('checked', false);
                handleCategoryCheckbox($(this));
            }
        });
        handleCategoryCheckbox($(this));
    });
    

    /* ======= Discount Setting Item Check  ======*/
    
    // If  Already 2 or more than Discount Setting Exist 
    $(".saveSettings").on('click', function () {
        const discount_Title = $(".discountTitle").val();
        if (discount_Title.trim() !== '') {
            const count = $("#discountSetting .accordion-item").length;
            const discount_setting_header = $(".discount_setting_header");
   
            if (count === 2) {
                const pro = `<span style="background: #16a085; color: #fff; padding: 0 3px; border-radius: 2px" class="text-white mx-1">Go Pro</span>`;
                const msg1 = `Maximum Discount Settings Limit ${count} Exceed.`;
                const msg2 = `Limit Exceed.`;
    
                if (!$(discount_setting_header).next().hasClass('discount_header_msg')) {
                    discount_setting_header.after('<h6 class="discount_header_msg text-danger p-1">' + msg1 + '</h6>');
                }
    
                if (!$(this).next().hasClass('discount_limit_msg')) {
                    $(this).after('<span class="discount_limit_msg text-danger p-1">' + msg2 + '</span>');
                }
            }
        }
    
    });

    // Show Created Discount Notification when limit over
    const count_discountItem = $("#discountSetting .accordion-item").length;
    if (count_discountItem === 2) {
        const pro = `<span style="background: #16a085; color: #fff; padding: 0 3px; border-radius: 2px" class="text-white mx-1">Go Pro</span>`;
        const created_discount_title = $(".created_discount_title");
        const msg3 = `Limit ( ${count_discountItem} ) Exceed for this version.`;

        if (!created_discount_title.next().hasClass('createdDiscount_title')) {
            created_discount_title.after('<h6 class="createdDiscount_title text-danger p-1">' + msg3 + '</h6>');
        }
    }

    // .wcpd_paid
    const $wcpd_paid = $(".wcpd_paid");
    if ($wcpd_paid.length > 0) {
        // $wcpd_paid.css({ });
        $wcpd_paid.prop('disabled', true);
    } else {
        $wcpd_paid.prop('disabled', false);
    }
    
    $(".wcpd_paid").hover(
        function () {
            $(this).append('<div class="overlay">Coming Soon</div>');
        },
        function () {
            $(this).find(".overlay").remove();
        }
    );
    
    const $wcpd_sku_paid = $(".wcpd_sku_paid");
    if ($wcpd_sku_paid.length > 0) {
        // $wcpd_paid.css({ });
        $wcpd_sku_paid.prop('disabled', true);
    } else {
        $wcpd_sku_paid.prop('disabled', false);
    }
    
    $(".wcpd_sku_paid").hover(
        function () {
            $(this).append('<div class="sku_overlay">Coming Soon</div>');
        },
        function () {
            $(this).find(".sku_overlay").remove();

        }
      );

});

