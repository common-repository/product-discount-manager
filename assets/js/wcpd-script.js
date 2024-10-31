"use strict";
jQuery(document).ready(function ($) {
  // Schedule Date & Time Picker
  $('.schedulePicker .time').timepicker({
    'showDuration': true,
    'timeFormat': 'g:i',
    'show2400': true
  });

  $('.schedulePicker .date').datepicker({
    'format': 'yyyy-m-d',
    'autoclose': true
  });

  // initialize datepair
  $('.schedulePicker').datepair();


  /* ======= ootsrap Nab-Tabs ======*/
  const triggerTabList = $("#wcpd-admin-tab button");
  triggerTabList.each(function () {
    const tabTrigger = new bootstrap.Tab(this);

    $(this).on("click", function (event) {
      event.preventDefault();
      tabTrigger.show();
    });
  });

  /* ======= Create Group Name======*/
  $("#submit_group_name").on("click", function (e) {
    e.preventDefault();
    let groupName = $("#wcpd_group_name").val();
    // Empty previous value from input field 
    $("#wcpd_group_name").val('');

    if (groupName != "") {
      $.ajax({
        url: ajaxurl, // Use the WordPress AJAX endpoint URL
        method: "POST",
        dataType: "json",
        data: {
          'name': groupName,
          'action': "group_name_create",
          'security': wcpdObj.wcpd_nonce,
          'author'  : wcpdObj.current_userId,
        },
        success: function (response) {
          let group_data = response.data.groupName;
          let groupNameList = $("#groupNameList");
          let groupNameSelect = $("#groupNameSelect");
              
          group_data.forEach((item) => {
            let options =
              '<option value="' + item.id + '">' + item.group_name + "</option>";
            let list =
            '<li class="list-group-item group_name_item">' + item.group_name + '<span type="button" data-groupid="' + item.id + '" class="grbDelete_btn">&#x2715</span>' + '</li>';
            groupNameList.append(list);
            groupNameSelect.append(options);
          });
          groupNameDel();
        },
        error: function (xhr, textStatus, error) {
          console.error('AJAX request failed with error:', error);
        },
      });
    }
  });
  /* ======= Remove Created Group Name======*/
  function groupNameDel(){
    $(".grbDelete_btn").on('click', function () {
      let groupId = $(this).data('groupid');
      $(this).closest('li').remove();
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          'action': "del_groupName_action",
          'security': wcpdObj.wcpd_nonce,
          'author'  : wcpdObj.current_userId,
          groupId,
        },
        success: function (response) { 
          // Handle the server response here
          const deleteMessage = response.data.success;
          $(".deleteMessage").text(deleteMessage).show();
        
        },
        error: function (xhr, textStatus, error) {
          console.error("AJAX request failed with error:", error);
        }
      });
  
  
    });
  }
  groupNameDel();

  /* ======= Remove Group Item from Group ======*/
  function removeGroupItems() {
    $(".close").click(function () {
      let listItem = $(this).parent();
      let postId = listItem.attr("productId");
      let groupItem = $(listItem).parent();
      let groupId = $(groupItem).attr("groupId");
  
      // AJAX request
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          'action': "remove_groupItem_action",
          'security': wcpdObj.wcpd_nonce,
          'author'  : wcpdObj.current_userId,
          groupId: groupId,
          postId: postId,
        },
        success: function (response) {
           // On success, remove the list item from the DOM
          listItem.remove();
        },
        error: function (xhr, status, error) {
          // Handle any errors if necessary
          console.log(error);
        },
      });
    });
  }
  removeGroupItems();

  /* ======= Delete Created Group ======*/
  function removeCreatedGroup() {
      $(".groupClose").on('click', function () {
        // Get the parent accordionItem element
        let groupItem = $(this).closest(".accordionItem");
        let groupId = groupItem.attr("gId");
      
        // AJAX request
        $.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            'action': "delete_group_action",
            'security': wcpdObj.wcpd_nonce,
            'author'  : wcpdObj.current_userId,
            groupId: groupId,
          },
          success: function (response) {
            // On success, remove the list item from the DOM
            groupItem.remove();
          },
          error: function (xhr, status, error) {
            // Handle any errors if necessary
            console.log(error);
          },
        });
      });
  }
  removeCreatedGroup();

  $(".variation-text").on('click', function () {
      let pVariation = $(this).next();
      if (pVariation.length > 0) {
        pVariation.toggle();
  
      }
  });

  /* ======= Initialization Data Tables ======*/
  $("#wcpd_dataTables").DataTable();

  /* ======= Select All Checkbox When click on All Select. ======*/
  var selectedItems = [];
  $('[data-dt-idx]').click(function () {
    // Call selectProduct() function if it's defined
    if (typeof selectProduct === 'function') {
        selectProduct();
    }
  });

  function selectProduct() {
    $('.wcpd_product').on('change', function () {
      const dataType = $(this).data('type');
    
      if ($(this).is(':checked')) {
        // Checkbox is checked
        if ($(this).closest('.variation_items').length) {
          // Child checkbox is checked
          const parentCheckbox = $(this).closest('tr').find('.wcpd_product').first();
          parentCheckbox.prop('checked', false);
        } else {
          // Parent checkbox is checked, check all child checkboxes
          const childCheckboxes = $(this).closest('tr').find('.variation_items .wcpd_product');
          childCheckboxes.prop('checked', true);
          // Store selected item data for each child checkbox
          childCheckboxes.each(function () {
            const id = $(this).val();
            const dataType = $(this).data('type');
            selectedItems.push({ id, dataType });
          });
        }
        // Store selected item data for the current checkbox
        const id = $(this).val();
        selectedItems.push({ id, dataType });
    
      } else {
        // Checkbox is unchecked
        if ($(this).closest('.variation_items').length) {
          // Child checkbox is unchecked
          // Remove unchecked item from selectedItems
          const uncheckedValue = $(this).val();
          selectedItems = selectedItems.filter(item => !(item.id === uncheckedValue && item.dataType === dataType));
        } else {
          // Parent checkbox is unchecked, uncheck all child checkboxes
          const childCheckboxes = $(this).closest('tr').find('.variation_items .wcpd_product');
          childCheckboxes.prop('checked', false);
          // Remove unchecked items from selectedItems
          childCheckboxes.each(function () {
            const uncheckedValue = $(this).val();
            selectedItems = selectedItems.filter(item => !(item.id === uncheckedValue && item.dataType === dataType));
          });
          // Remove unchecked item from selectedItems
          const uncheckedValue = $(this).val();
          selectedItems = selectedItems.filter(item => !(item.id === uncheckedValue && item.dataType === dataType));
        }
      }
    
    });
    
  }
  selectProduct();
  // Retrieve the checkbox element
  const allCheckbox = $("#product_all_select");

  // Add a click event listener to the checkbox
  allCheckbox.on("click", function () {
    const checkboxes = $('input[type="checkbox"]');

    // Check if the "select all" checkbox is checked
    const isAllChecked = allCheckbox.prop("checked");

    // Loop through each checkbox
    checkboxes.each(function () {
      // Retrieve the current checkbox
      const checkbox = $(this);

      // Toggle the checked property only if it's not the "product_all_select" checkbox
      if (checkbox.attr("id") !== allCheckbox.attr("id")) {
        checkbox.prop("checked", isAllChecked);

        // Add or remove the checkbox value from the selectedItems array
        const id = checkbox.val();
        const dataType = checkbox.data("type");
        const index = selectedItems.findIndex(item => item.id === id && item.dataType === dataType);

        if (isAllChecked && index === -1) {
          // Add the value and dataType if the checkbox is checked and not already in selectedItems
          selectedItems.push({ id, dataType });
        } else if (!isAllChecked && index !== -1) {
          // Remove the value and dataType if the checkbox is unchecked and present in selectedItems
          selectedItems.splice(index, 1);
        }
      }
    });
    // Update the "product_all_select" checkbox based on the state of other checkboxes
    allCheckbox.prop(
      "checked",
      checkboxes.not(allCheckbox).length === checkboxes.not(":checked").length
    );
  });

  //Group Product Create
  $("#submit_product_group").on("click", function () {
    var groupSelectId = $("#groupNameSelect").val();
    if (!isNaN(groupSelectId) && selectedItems.length > 0) {
      $.ajax({
        url: ajaxurl, // Use the WordPress AJAX endpoint URL
        method: "POST",
        dataType: "json",
        data: {
          'action': "group_product_action", // The AJAX action name defined in the WordPress endpoint
          'security': wcpdObj.wcpd_nonce,
          'author'  : wcpdObj.current_userId,
          group_id: groupSelectId,
          products: selectedItems,
        },
        success: function (response) {

          let groupData = response.data;
          let discountGroup_accordion = $("#wcpd_discountGroup_accordion");
        
          let groupId = groupData.groupId;
          let groupName = groupData.groupName;
          let groupProducts = groupData.products;

          // Create the accordion item
          let accordionItemHTML = `
          <div class="accordion-item accordionItem" gId="${groupId}">
            <h2 class="accordion-header" id="flush-headingOne_${groupId}">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" aria-expanded="false" data-bs-target="#flush-collapseOne_${groupId}" aria-controls="flush-collapseOne">
                ${groupName}
                <span type="button" class="groupClose">&times;</span>
              </button>
            </h2>
            <div id="flush-collapseOne_${groupId}" class="accordion-collapse collapse" aria-labelledby="flush-headingOne_${groupId}" data-bs-parent="#wcpd_discountGroup_accordion">
              <div class="accordion-body">
                <ul class="list-group" groupId="${groupId}">
                      ${groupProducts.map(product => `
                          <li productId="${product.ID}" class="list-group-item">
                            ${product.title}
                            <span type="button" aria-hidden="true" class="close" aria-label="Close">&times;</span>
                            ${product.variations.length > 0 ? `
                              <span id="varId${product.ID}" class="text-info variation-text">Show Variation</span>
                              <ul class="group-variation">
                                ${product.variations.map(item => `<li class="list-group-item" productId="${item.ID}">${item.title + '(' + item.sku + ')'}</li>`).join('')}
                              </ul>
                            ` : ''}
                          </li>
                    `).join('')}    
                </ul>
              </div>
            </div>
          </div>
        `;
          discountGroup_accordion.append(accordionItemHTML);
          removeGroupItems();
          removeCreatedGroup();
        },
        error: function (error) {
          console.error("AJAX request failed with error:", error);
        },
      });
    }
  });

  // Trigger the updateSKUData function whenever the category selection changes
  let selectedCatIds = [];
  $(".wcpd_product_category input, .wcpd_updateProduct_category input").on('click', function () {

    $(".wcpd_variable_skus, .wcpd_simple_skus").empty();
    $(".wcpd_variable_sku, .wcpd_simple_sku").empty();
    let selectedCatId = $(this).val();
    // Check if the ID is already in the array
    if (this.checked && !selectedCatIds.includes(selectedCatId)) {
      // Add the ID to the array
      selectedCatIds.push(selectedCatId);
    } else if (!this.checked && selectedCatIds.includes(selectedCatId)) {
      // Remove the ID from the array
      selectedCatIds = selectedCatIds.filter(id => id !== selectedCatId);
    }

    // Make an AJAX request to trigger the session creation and retrieve SKU data
    if (selectedCatIds.length > 0) {
      $.ajax({
        url: ajaxurl, // Use the WordPress AJAX endpoint URL
        method: "POST",
        dataType: "json",
        data: {
          'action': "check_sku_byCategoryIds_action",
          'security': wcpdObj.wcpd_nonce,
          'author'  : wcpdObj.current_userId,
          'catIds': selectedCatIds,
        },
        success: function (response) {
          // Response Datas

          let skus = response.simple_skus;
          let varSkus = response.variable_skus;
  
          // Selector to Append Data
          let varSelector = $(".wcpd_variable_skus");
          let Selector    = $(".wcpd_simple_skus");

          let varUpSelector = $(".wcpd_variable_sku");
          let upSelector    = $(".wcpd_simple_sku");

          // Simple Skus
          if (skus !== null) {
            Object.entries(skus).forEach(([key, value]) => {
              let option = `<option value="${key}">${value}</option>`;
              Selector.append(option);
            });
          }
          // Variable Skus
          if (varSkus !== null) {
            Object.entries(varSkus).forEach(([skuKey, skuObj]) => {
              let optionGroup = `<optgroup label="${skuKey}">`;
              Object.entries(skuObj).forEach(([key, value]) => {
                let option = `<option value="${key}">${value}</option>`;
                optionGroup += option;
              });
              optionGroup += '</optgroup>';
              varSelector.append(optionGroup);
            });
          }


          // For Update Page
          // Simple Sku
          if (skus !== null || skus == null) {
            Object.entries(skus).forEach(([key, value]) => {
              let option = `<option value="${key}">${value}</option>`;
              upSelector.append(option);
            });
          }
          // Variable Sku
          if (varSkus !== null || varSkus !== null) {
            Object.entries(varSkus).forEach(([skuKey, skuObj]) => {
              let optionGroup = `<optgroup label="${skuKey}">`;
              Object.entries(skuObj).forEach(([key, value]) => {
                let option = `<option value="${key}">${value}</option>`;
                optionGroup += option;
              });
              optionGroup += '</optgroup>';
              varUpSelector.append(optionGroup);
            });
          }
        },
        error: function (xhr, textStatus, error) {
          console.error("AJAX request failed with error:", error);
        },
      });
    }

  });

  // Select2.js Initialize
  function wcpdSlect2() {
    $('.wcpd_simple_skus').select2({
      placeholder: 'Select an option',
      allowClear: true,
      multiple: true,
    }
    );
    $('.wcpd_variable_skus').select2({
      placeholder: 'Select an option',
      allowClear: true,
      multiple: true,
    }
    );
    $('.wcpd_simple_sku').select2({
      placeholder: 'Select an option',
      allowClear: true,
      multiple: true,
    }
    );
    $('.wcpd_variable_sku').select2({
      placeholder: 'Select an option',
      allowClear: true,
      multiple: true,
    }
    );
  }
  wcpdSlect2();

  /* ======= Discount Settings Tab  ======*/
  // Dependency Control
  const producType = $(".producType");
  const productCat = $(".productCategory");
  const productGroup = $(".productGroup");

  const simpleSku = $(".simpleSku");
  const variableSku = $(".variableSku");
  // Hide all elements initially
  $(".producType, .productCategory, .productGroup").hide();
  // Hide all elements initially
  $(".simpleSku, .variableSku").hide();
  // Check the initial state of the radio button
  const discount_By = $("input[name='discountBy']:checked").val();
  if (discount_By === "group") {
    $(document).ready(function () {
      simpleSku.hide();
     })
  }
  toggleElements(discount_By);
  
  // Function to toggle the visibility of elements based on the selected value
  function toggleElements(selectedValue) {
    if (selectedValue === "category") {
      productGroup.hide();
      productCat.show();
      producType.show();
      simpleSku.show();
    }
    if (selectedValue === "group") {
      producType.hide();
      productCat.hide();
      productGroup.show();
      simpleSku.hide();
    }
  }
  // Click event handler for group button
  $(".btnGroup, .btnCategory").on('click', function () {
    const selectedValue = $(this).val();
    toggleElements(selectedValue);
  });
  
  // Attach the click event handler to the radio buttons
  $(".btnSimple, .btnVariable").on('click', function () {
    const selectedTypes = $(this).val();
    toggleTypes(selectedTypes);
  });

  // Function to toggle the visibility of elements based on the selected value
  function toggleTypes(selectedTypes) {
    if (selectedTypes === "simple") {
      simpleSku.show();
      variableSku.hide();
    }else if(selectedTypes === "variable") {
      simpleSku.hide();
      variableSku.show();
    }
  }
  // This will work on when checked simple / variable product type initially
  const product_type = $("input[name='productType']:checked").val();
  toggleTypes(product_type);
 
  /* ========== Save Discount Settings =============*/
  $(".saveSettings").on('click', function (event) {
    event.preventDefault(); // Prevent form submission
    const discountTitle = $(".discountTitle").val();
    const discount_amount = $(".discountAmount").val();

    // Perform validation
    if (discountTitle.trim() === '' || discount_amount.trim() === '' ) {
      $(".discountTitle").css({ "border": "1px solid red" });
      $(".discountAmount").css({ "border": "1px solid red" });
      $(".error_subtile").css({ "color": "red" }).text("Discount Title and amount are Required").show();
      return;
    }
    // Category 
    const selectedCategories = [];
    $("input[name='productCate']:checked").each(function () {
      selectedCategories.push($(this).val());
    });

    const discountBy = $("input[name='discountBy']:checked").val();
    const productType  = $("input[name='productType']:checked").val();
    const productGroup = $(".wcpd_product_group").val();
    const simpleSkus   = $(".wcpd_simple_skus").val();
    const variableSkus = $(".wcpd_variable_skus").val();

    const discountType   = $(".discountType").val();
    const discountAmount = $("input[name='discountAmount']").val();
    // Discount Schedule 
    const dateStart = $("input[name='dateStart']").val();
    const dateEnd   = $("input[name='dateEnd']").val();
    const timeStart = $("input[name='timeStart']").val();
    const timeEnd   = $("input[name='timeEnd']").val();
    const discountSchedule = {
      "dateStart": dateStart,
      "dateEnd": dateEnd,
      "timeStart": timeStart,
      "timeEnd": timeEnd,
    };
    const applyOn = $("input[name='apply_on']:checked").val();
    const timeZone = $(".wcpd_timeZone").val();

    const formData = {
      discountTitle,
      discountBy,
      "categories": selectedCategories,
      productType,
      productGroup,
      simpleSkus,
      variableSkus,
      discountType,
      discountAmount,
      discountSchedule,
      applyOn,
      timeZone
    }
    let editNonce = $('#wcpdm_nonce').val();

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        'action': "discount_settings_action", // The AJAX action name defined in the WordPress endpoint
        'security': wcpdObj.wcpd_nonce,
        'editNonce': editNonce,
        'author'  : wcpdObj.current_userId,
        'formData': formData,
      },
      success: function (response) {

       // Check Category < 2 ase kina. 
        if (response.success) {
          const category_count = response?.data?.category_count;

          if ( selectedCategories.length > 0 && category_count && category_count.length > 0 && category_count.length < 2 ) {
            const msg1 = `Category Limit Exceed ${category_count.length} for this Version.`;
    
            if (selectedCategories.length > 1 ) {
              $(".wcpd_category_input:checked").last().prop("checked", false);
            }
            showCustomModal('#wcpd_customAlert', msg1);
           // return false;
          }
        } 

        // Handle the server response here
        const settingsDataContainer = $("#discountSetting");
        const discountData  = response?.data?.discountData;
        const updatePageUrl = response?.data?.updatePageUrl;
        const groupQuery = response?.data?.groupQuery;
        const categoryNames = response?.data?.category_name; 

        // Check if discountData is defined
        if (discountData) {
            const id = discountData.id;
            const discountTitle = discountData.wcpd_discount_title;
            const discountBy = discountData.wcpd_discount_by;
            const productType = discountData.wcpd_product_type;
            // const productCategory = JSON.parse(discountData.wcpd_product_category); 
            const variableSkus = JSON.parse(discountData.wcpd_variable_skus); 
            const simpleSkus = JSON.parse(discountData.wcpd_simple_skus); 
            const productGroupId = discountData.wcpd_productGroup_id;
            const discountType = discountData.wcpd_discount_type;
            const discountAmount = discountData.wcpd_discount_amount;
            const discountSchedule = JSON.parse(discountData.wcpd_discount_schedule_date);
            const timezoneDiscount = discountData.wcpd_timezone_discount;
            const discountApply_on = discountData.wcpd_discount_apply_on;
          
            let dType = '';
            if (discountType == 'percentage') {
              dType = "%";
            }
            // Create the HTML markup for the accordion item
            const accordionItem = `
                <div class="accordion-item mb-2">
                    <h2 class="accordion-header wcpd_accordionBtn" id="heading_${id}">
                        <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse${id}"
                            aria-expanded="false" aria-controls="collapse${id}">
                            ${discountTitle}
                        </button>
                        <!-- Update & Delete Button -->
                        <div class="accordion_itemAction">
                          <a href="${updatePageUrl}" target="_self" type="button" id="${id}" class="btn btn-primary itemUpdate">
                              <span class="dashicons dashicons-edit"></span>
                          </a>
                          <span class="itemDelete_wrapper">
                              <span type="button" class="btn btn-danger itemDelete" id="${id}">
                                  <span class="dashicons dashicons-trash"></span>
                              </span>
                          </span>
                        </div>
                    </h2>
                    <div id="collapse${id}" class="accordion-collapse collapse"
                        aria-labelledby="heading_${id}" data-bs-parent="#discountSetting">
                        <div class="accordion-body">
                            <table class="table settingsTable">
                                <tbody>
                                    <!-- Table Body -->
                                    <tr>
                                        <td>
                                            <h6>Discount Naming / Title:</h6>
                                        </td>
                                        <td>
                                            <h6>${discountTitle}</h6>
                                        </td>
                                    </tr>
                                    <tr>
                                    <td>
                                        <h6> Discount By: </h6>
                                    </td>
                                    <td>
                                        <h5>
                                            ${discountBy === "category" ? '<span class="">Category</span>' :
                                                discountBy === "group" ? '<span class="">Group</span>' :
                                                'No Discount By Selected'}
                                        </h5>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6> Product Type:</h6>
                                    </td>
                                    <td>
                                        <h6>
                                            ${productType === "simple" ? '<span class="">Simple</span>' :
                                                productType === "variable" ? '<span class="">Variable</span>' :
                                                'No Discount Type Selected'}
                                        </h6>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6>Category:</h6>
                                    </td>
                                    <td>
                                        <h6>
                                            ${categoryNames ? categoryNames : 'No categories were selected'}
                                        </h6>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6>Product Group:</h6>
                                    </td>
                                    <td>
                                        <h6>
                                            ${productGroupId ? groupQuery[0].group_name : 'No Group Was Selected'}
                                        </h6>
                                    </td>
                                </tr>
                                ${simpleSkus?.length > 0 ?
                                    `<tr>
                                        <td>
                                            <h6>Simple SKU:</h6>
                                        </td>
                                        <td>
                                            ${simpleSkus.join(', ')}
                                        </td>
                                    </tr>` : ''}
                                ${variableSkus?.length > 0 ?
                                    `<tr>
                                        <td>
                                            <h6>Variable SKU:</h6>
                                        </td>
                                        <td>
                                            ${variableSkus.join(', ')}
                                        </td>
                                    </tr>` : ''}
                                <tr>
                                    <td>
                                        <h6>Discount Type:</h6>
                                    </td>
                                    <td>
                                        <h5>
                                            ${discountType === "percentage" ? '<span class="">Percentage</span>' :
                                                discountType === "fixed" ? '<span class="">Fixed</span>' :
                                                'No Discount Type Selected'}
                                        </h5>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6>Discount Amount:</h6>
                                    </td>
                                    <td>
                                        <h6>
                                            ${discountAmount ? '<span>' + discountAmount + ''+ dType + '</span>' : 'No Discount'}
                                        </h6>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6>Discount Schedule:</h6>
                                    </td>
                                    <td>
                                        <h6>
                                            ${discountSchedule ? `<h6">${discountSchedule.dateStart} - ${discountSchedule.dateEnd} <span>${discountSchedule.timeStart} - ${discountSchedule.timeEnd}</span></h6>` : 'No Schedule'}
                                        </h6>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6>Apply On:</h6>
                                    </td>
                                    <td>
                                        <h6>
                                            ${discountApply_on === "regular" ? '<span>' + 'regular' + '</span>' :
                                                discountApply_on === "sale" ? '<span>' + 'sale' + '</span>' : 'No Data'}
                                        </h6>
                                    </td>
                                </tr>

                                </tbody> <!-- ./Table Body -->
                            </table>
                        </div>
                    </div>
                </div>
            `;
          settingsDataContainer.append(accordionItem);
          delete_discountSettings();
        }
        // Handle the rest of your logic
        const successMessage = response?.data?.success;

        if (response.success) {
            $(".successMsg").text(successMessage).show();
            // Append the accordion item to the #discountSetting container
            $('#wcpd-admin-tab button[data-bs-target="#navDiscountSettings"]').tab('show');
        }else {
            // Handle any error or display a message if needed
            console.error('AJAX request failed');
        }
      },
      error: function (error) {
        console.error('AJAX request failed: ' + textStatus, errorThrown);     
      }
      
    });
  });

  // Delete Discount Settings Items / Settings Data
  delete_discountSettings();
  function delete_discountSettings() {
    $(".itemDelete").on('click', function () {
      const delId = $(this).attr('id');
      $(this).closest('.accordion-item').hide();
  
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          'action': "delete_discountSettings_action",
          'security': wcpdObj.wcpd_nonce,
          'author'  : wcpdObj.current_userId,
          delId,
        },
        success: function (response) {
          // Handle the server response here
          const deleteMessage = response.data.success;
          $(".deleteMessage").text(deleteMessage).show();
          if (response.success) {
            // Redirect to the URL sent in the response data
            // window.location.href = response.data.redirect_url;
            $('#wcpd-admin-tab button[data-bs-target="#navDiscountSettings"]').tab('show');
        } else {
            // Handle any error or display a message if needed
            console.error('AJAX request failed');
        }
  
        },
        error: function (xhr, textStatus, error) {
          console.error("AJAX request failed with error:", error);
        }
      });
  
  
    });
  }

  // Save Update Settings Data
  $("#saveUpdateSettings").on('click', function (event) {

    event.preventDefault(); // Prevent form submission
    const discountTitle = $(".discountTitle").val();
    const updateId = $("input[name='updateId']").val();
    const discountBy = $("input[name='discountBy']:checked").val();

    // Category 
    const selectedCategories = [];
    $("input[name='productCate']:checked").each(function () {
      selectedCategories.push($(this).val());
    });
    const productType = $("input[name='productType']:checked").val();
    const productGroup = $(".wcpd_product_group").val();
    const simpleSkus = $(".wcpd_simple_sku").val();
    const variableSkus = $(".wcpd_variable_sku").val();
    const discountType = $(".discountType").val();
    const discountAmount = $("input[name='discountAmount']").val();


    // Discount Schedule 
    const dateStart = $("input[name='dateStart']").val();
    const dateEnd = $("input[name='dateEnd']").val();
    const timeStart = $("input[name='timeStart']").val();
    const timeEnd = $("input[name='timeEnd']").val();
    const discountSchedule = {
      "dateStart": dateStart,
      "dateEnd": dateEnd,
      "timeStart": timeStart,
      "timeEnd": timeEnd,
    };
    const applyOn = $("input[name='apply_on']:checked").val();
    const timeZone = $(".wcpd_timeZone").val();
  
    const formData = {
      updateId,
      discountTitle,
      discountBy,
      "categories": selectedCategories,
      productType,
      productGroup,
      simpleSkus,
      variableSkus,
      discountType,
      discountAmount,
      discountSchedule,
      applyOn,
      timeZone
    }

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        'action': "update_discount_settings_action",
        'security': wcpdObj.wcpd_nonce,
        'author'  : wcpdObj.current_userId,
        'formData': formData,
      },
      success: function (response) {
        // Check Category < 2 ase kina. 
        if (response.success) {
          const category_count = response?.data?.category_count;

          if (category_count && category_count.length > 0 && category_count.length < 2 ) {
            const msg1 = `Category Limit Exceed ${category_count.length} for this Version.`;
    
            if (selectedCategories.length > 1 ) {
              $(".wcpd_category_input:checked").last().prop("checked", false);
            }
            showCustomModal('#wcpd_customUpdateAlert', msg1);
          }
        } 

        const updateMessage = response.data.success;
        if (response.success) {
          $(".updateMessage").text(updateMessage).show();
            // Redirect to the specified URL
            window.location.href = response.data.redirect_url;
            // After the page has loaded, activate the desired tab
            $('#wcpd-admin-tab button[data-bs-target="#navDiscountSettings"]').tab('show');

        } else {
            // Handle any error or display a message if needed
            console.error('AJAX request failed');
        }
      },
      error: function (error) {
        console.error("AJAX request failed with error:", error);
      }
    });
  }); // End of Save Update Settings Data

  // Redirect to Targeted tab after discount settings update
  function updateUrl(){
      // Get the hash part of the current URL
    var hash = window.location.hash;    
    if (hash === "#navDiscountSettings") {
      $('#nav-discount-tab').click();
    }  
    if (hash === "#navPriceChange") {
      $('#nav-price-tab').click();
      } 
  }
  updateUrl();


  /* ======= Duplicate Data Validation  ======*/
  $(".wcpd_checkData, .wcpd_category_input, .wcpd_product").on("change", function (e) {

    const isCheckbox = $(this).is(":checkbox");
    const isSelect = $(this).is("select");

    if (isCheckbox || isSelect) {
      const selectedValue = isCheckbox ? $(this).is(":checked") : $(this).val();
      
        if (selectedValue !== undefined && selectedValue !== null) {

          const dataId = $(this).val(); // Get the data ID
          const dataType = $(this).attr('dataType'); // Get the data type
          const catErrMsg = $(this).next('.form-check-label').children('.err_msg');  

          $.ajax({
            url: ajaxurl, 
            method: "POST",
            dataType: "json",
            data: {
              'action': "check_duplicate_data", 
              'security': wcpdObj.wcpd_nonce,
              'author'  : wcpdObj.current_userId,
              'data_type': dataType, // Send the data type
              'data_id': dataId, // Send the data ID
            },
            success: function (response) {

              const data_id = response.data.data_id;
              const timezone = response.data.timezone;
              const catErrorTxt = "This Category already selected for a Discount";

              if (dataType === "category") {
                if (data_id && data_id == dataId) {
                  catErrMsg.text(catErrorTxt);
                  catErrMsg.toggle();
                }
              }
              if (dataType === "group") {
                if (data_id && data_id == dataId) {
                  const msg = `This Group has already been selected for a Discount!`;
                  showCustomModal('#wcpd_customAlert', msg);
                  showCustomModal('#wcpd_customAlert1', msg);
                  }
              }
              if (dataType === "timezone") {
                if (timezone && timezone == dataId) {
                    const msg = "This Timezone has already been selected for a Discount!";
                    showCustomModal('#wcpd_customAlert', msg);
                    showCustomModal('#wcpd_customAlert1', msg);
                  }
              }
              if (dataType === "product") {
                if (data_id && data_id == dataId) {
                  const msg = "This Product has already been selected for a Discount!";
                  showCustomModal('#wcpd_customAlert', msg);
                  // Remove Checkbox if already selected for discount.
                  const checkboxId = document.getElementById('proCheckBox' + data_id);
                  if (checkboxId) {
                    checkboxId.style.border = "1px solid red";
                    checkboxId.checked = false;
                  }
                }
              }
            },
            error: function (error) {
              console.error("AJAX request failed with error:", error);
            }
          });

        }
    }
  });

  // SKU Validation
  $(".wcpd_checkSku").select2().on("select2:select", function (e) {
    const isCheckbox = $(this).is(":checkbox");
    const isSelect = $(this).is("select");

    const selectedOption = $(e.params.data.element);
    const lastSelectedValue = selectedOption.val();

    if (isCheckbox || isSelect) {
      const selectedValue = isCheckbox ? $(this).is(":checked") : $(this).val();
      
        if (selectedValue !== undefined && selectedValue !== null) {

          const dataId = $(this).val(); // Get the data ID
          const dataType = $(this).attr('dataType'); // Get the data type

          // Add data-value attribute
          const lastSku = lastSelectedValue;
          selectedOption.attr('data-sku', lastSku);

          $.ajax({
            url: ajaxurl, 
            method: "POST",
            dataType: "json",
            data: {
              'action': "check_skuData_action", 
              'security': wcpdObj.wcpd_nonce,
              'author'  : wcpdObj.current_userId,
              'data_type': dataType, // Send the data type
              'data_id': lastSku, // Send the data ID
            },
            success: function (response) {

              const simpleSku = response?.data?.simpleSku;
              const variableSku = response?.data?.variableSku;

              // Simple SKU
              if (dataType === "skuSimple" && simpleSku != null) {
                simpleSku.forEach(skuVal => {
                  if (skuVal == lastSku) {
                    const msg = "This SKU has already been used for a Discount!";
                    showCustomModal('#wcpd_customAlert', msg);
                    $('[data-sku="' + skuVal + '"]').remove();
                    $(".wcpd_checkSku").trigger('change');
                  }
                
                });
              }
              // Variable SKU
              if (dataType === "skuVariable" && variableSku != null) {
                variableSku.forEach(skuVal => {
                  if (skuVal == lastSku) {
                    const msg = "This SKU has already been used for a Discount!";
                    showCustomModal('#wcpd_customAlert', msg);
                    $('[data-sku="' + skuVal + '"]').remove();
                    $(".wcpd_checkSku").trigger('change');
                  }
                });
 
              }
            },
            error: function (error) {
              console.error("AJAX request failed with error:", error);
            }
          });

        }
    }
  });

  // This handles each checkbox's change event for Category
  var catErrMsg = '';
  $("input[name='changePriceCat']").on("change", function () {
  // If the checkbox is checked
    if ($(this).prop("checked")) {
      // Show the error message associated with the checkbox
      catErrMsg = $(this).next('.form-check-label').children('.err_msg').show();
      showCustomModal();
    } else {
        // Otherwise, hide the error message
      catErrMsg = $(this).next('.form-check-label').children('.err_msg').hide();
    }
  });

  // Price Change Validation
  $(".changePriceSettings").on("change", function (e) {

    const changeBy = $("input[name='changeBy']:checked").val();
    let productType = null;
    if (changeBy === "category") {
      productType = null;
    } else {
      productType = $("input[name='byProductType']:checked").val();
    }
    if (changeBy === "productType") {
      $("input[name='changePriceCat']:checked").prop('checked', false);
      $(".err_msg").hide();
    }

    // Category
    const changePriceCats = [];
    $("input[name='changePriceCat']:checked").each(function () {
      changePriceCats.push($(this).val());
    });

    const pricingData = {
      changeBy,
      productType,
      changePriceCats,
    }
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        'action': "changePrice_check_action",
        'security': wcpdObj.wcpd_nonce,
        'author'  : wcpdObj.current_userId,
        'pricingData': pricingData,
      },
      success: function (response) {
        const dbPricingData = response.data.pricingData;
        if (dbPricingData && Array.isArray(dbPricingData)) {
            dbPricingData.forEach(priceData => {
              const changePriceBy = priceData?.changePriceBy;
              const changesCategory = priceData?.changesCategory;
              const changesProductType = priceData?.changesProductType;
              const categoryCount = changesCategory?.length;

              if (changePriceBy === "productType" && changesProductType === productType) {
                const msg = `This "${productType}" Product Type has already been used for Price Changes!!`;
                showCustomModal('#wcpd_customAlert', msg);
              }

              if (changePriceBy === "category" && changeBy === "category") {
                const msg1 = `Category Limit Exceed ${categoryCount} for this Version.`;
                // Check Already Category Selected or Not;
                if (categoryCount < 2) {
                  showCustomModal('#wcpd_customAlert', msg1);

                  if ( changePriceCats.length > 1) {
                    $(".changePriceCat:checked").last().prop("checked", false);
                  }
              } else {
                // Find matching elements
                var matchedElements = $.grep(changePriceCats, function(element) {
                  return $.inArray(element, changesCategory) !== -1;
                });
                const catErrorTxt = "This Category already Selected Changes Price";
                // Check if there are any matched elements
                if (matchedElements.length > 0) {
                  // If there are matches, you can show a message
                  const msg = "Matched Category Detected !! Proceeding with this selection will overwrite the previously established price.";
                  catErrMsg.text(catErrorTxt);
                  showCustomModal('#wcpd_customAlert', msg);
                }
              }
            }
            });
        }
      },
      error: function (error) {
        console.error("AJAX request failed with error:", error);
      }
    }); 

  });
  /* ======= Change Product Pricing ======*/
  const changePriceBy = $("input[name='changeBy']:checked").val();
  priceToggleType(changePriceBy);
  
  $("#changeByCat, #changeByProduct").on('click', function () {
    const priceType = $(this).val();
    priceToggleType(priceType);
  });
  function priceToggleType(priceType) {
    if (priceType === "category") {
      $(".priceChangeProductCategory").show();
      $(".productType").hide();
    }
    if (priceType === "productType") {
      $(".productType").show();
      $(".priceChangeProductCategory").hide();
    }
  }

  /* ========== Save pricing settings ============*/
  $(".savePrice_Settings").on('click', function () {
    const changeBy = $("input[name='changeBy']:checked").val();

    const changeType = $(".changeType").val();
    const priceType = $(".priceType").val();
    const changeAmount = $(".changeAmount").val();
    const changeApply_on = $("input[name='changeApply_on']:checked").val();

    let byProductType = '';
    if (changeBy === "category") {
      byProductType = null;
    } else {
      byProductType = $("input[name='byProductType']:checked").val();
    }
    // Category
    const changePriceCats = [];
    $("input[name='changePriceCat']:checked").each(function () {
      changePriceCats.push($(this).val());
    });

    const formData = {
      changeBy,
      byProductType,
      changeType,
      changePriceCats,
      priceType,
      changeAmount,
      changeApply_on,
    }
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        'action': "changePrice_settings_action",
        'security': wcpdObj.wcpd_nonce,
        'author'  : wcpdObj.current_userId,
        'formData': formData,
      },
      success: function (response) {

        const successMsg = response?.data?.msg;
        const error_msg = response?.data?.error_msg;
        if (response.success) {
          $(".successMsg").text(successMsg).show();
          // Redirect to the specified URL
          setTimeout(function() {
              window.location.href = response.data.redirect_url;
              location.reload(); 
          }, 300);
          $('#wcpd-admin-tab button[data-bs-target="#navPriceChange"]').tab('show');

        } else {
            showCustomModal('#wcpd_customAlert', error_msg);
            console.error('AJAX request failed');
        }
      },
      error: function (error) {
        console.error("AJAX request failed with error:", error);
      }
    }); 
  });

  /* ========== Remove Changes Price List =============*/
  function removeChangesPriceList() {
    $(".priceCloseBtn").on('click', function () {
      // Get the parent accordionItem element
      let changePriceItem = $(this).closest(".priceChange_accordion");
      let changesPriceId = changePriceItem.attr("priceChangeId");
    
      // AJAX request
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          'action': "delete_changePrice_action",
          'security': wcpdObj.wcpd_nonce,
          'author'  : wcpdObj.current_userId,
          'changesPriceId': changesPriceId,
        },
        success: function (response) {
          // On success, remove the list item from the DOM
          if (response.success) {
            changePriceItem.remove();
          }
        },
        error: function (xhr, status, error) {
          // Handle any errors if necessary
          console.log(error);
        },
      });

    });
  }
  removeChangesPriceList();


  // Function to show the Bootstrap modal
  function showCustomModal(alertId, msg) {
    $(".alertMsg").text(msg);
    $(`${alertId}`).modal('show');
  }

}); // End of jQuery




                                                                            





















