/**
 * Admin JS File.
 *
 * @package pstds
 */

jQuery(document).ready(function ($) {
  
  if( ! pstds_ajax_object.pstds_is_licensed){
    $('#pstds_settings_discount_status').attr('disabled',true);
    $("label[for='pstds_settings_discount_status']").append("<a href='javascript:void(0);' onclick='getProForm()' style='margin-left:10px;color:red'>Get Pro Version</a>");
    $('#pstds_settings_store_type').val("site");
    $('#pstds_settings_store_type option[value="product"]').attr("disabled", true);
    $('#pstds_settings_store_type option[value="product"]').html("Specific Product <span style='margin-left:10px;color:red'> Get Pro Version</span>");
    $('#pstds_settings_store_type option[value="category"]').html("Specific Category <span style='margin-left:10px;color:red'> Get Pro Version</span>");
    $('#pstds_settings_store_type option[value="category"]').attr("disabled", true);

  }else{
    $("label[for='pstds_settings_discount_status']").append("<a href='javascript:void(0);' onclick='getProForm()' style='margin-left:10px;color:blue'>Edit License Key</a>");
    $("label[for='pstds_settings_discount_status']").append("<a href='javascript:void(0);' onclick='deleteLicensed()' style='margin-left:10px;color:red'>Delete License</a>");
    $("#pstds_settings_pro_licensed_key").parent().append("<a href='javascript:void(0);' onclick='deleteLicensed()' style='margin-left:10px;color:red'>Delete License</a>");
    console.log('hi');
  }
  if ($("#pstds_settings_cart_empty_notice").val() == "") {
    $("#pstds_settings_cart_empty_notice").val(
      "Store is closed, please come later"
    );
  }
  if ($("#pstds_settings_store_closed_notice").val() == "") {
    $("#pstds_settings_store_closed_notice").val(
      "Store is closed, please come later"
    );
  }
  if ($("#pstds_settings_product_closed_notice").val() == "") {
    $("#pstds_settings_product_closed_notice").val(
      "Some products are closed, please come later"
    );
  }
  if ($("#pstds_settings_category_closed_notice").val() == "") {
    $("#pstds_settings_category_closed_notice").val(
      "Some categories are closed, please come later"
    );
  }
  discountTypeToggle();
  checkIfSettingsAreDisabled();
  $("#pstds_settings_discount_type").change(function (e) {
    var type = $(this).val();
    discountTypeToggle(type);
  });

  $("#pstds_settings_store_time_status").on("change", function () {
    checkIfSettingsAreDisabled();
  });

  $("#pstds_settings_store_discount_banner_status").on("change", function () {
    toggleBannerDiscount();
  });

  $("#pstds_settings_store_type").on("change", function () {
    var type = $(this).val();
    toggleStoreTimeType(type);
  });

  $("#pstds_settings_store_sale_discount_status").on("change", function () {
    toggleAlreadySaleNotice();
  });

  $("#pstds_settings_discount_status").on("change", function () {
    checkIfSettingsAreDisabled();
  });

  $("#mainform").submit(function (e) {
    if (jQuery("#pstds_settings_store_sale_discount_status").prop("checked")) {
      if ($("#pstds_settings_sale_product_notice").val() == "") {
        alert("Already sale override notice is required");
        e.preventDefault();
      }
    }
    if (
      jQuery("#pstds_settings_store_discount_banner_status").prop("checked")
    ) {
      if ($("#pstds_settings_store_discount_banner").val() == "") {
        alert("Discount Banner is required");
        e.preventDefault();
      }
    }
    if (jQuery("#pstds_settings_discount_status").prop("checked")) {
      if (
        $("#pstds_settings_discount_type").val() == "choose" ||
        $("#pstds_settings_discount_type").val() == ""
      ) {
        alert("Please select discount type!");
        e.preventDefault();
      }
      if (
        $("#pstds_settings_discount_rule").val() == "choose" ||
        $("#pstds_settings_discount_rule").val() == ""
      ) {
        alert("Please select discount rule!");
        e.preventDefault();
      }
      if ($("#pstds_settings_discount_type").val() == "product") {
        if ($("#pstds_settings_product_discount").val() <= 0) {
          alert(
            "Zero, Empty & Negative Discount On Product Are Not Allowed. Please enter valid value"
          );
          e.preventDefault();
        }
        
        if ($("#pstds_settings_product_discount_ids").val() == "") {
          alert("Please select atleast one product!");
          e.preventDefault();
        }
      }

      if ($("#pstds_settings_discount_type").val() == "category") {
        if ($("#pstds_settings_category_discount").val() <= 0) {
          alert(
            "Zero, Empty & Negative Discount On Category Are Not Allowed. Please enter valid value"
          );
          e.preventDefault();
        }
        
        if ($("#pstds_settings_category_discount_ids").val() == "") {
          alert("Please select atleast one category!");
          e.preventDefault();
        }
      }

      if ($("#pstds_settings_discount_type").val() == "site") {
        if ($("#pstds_settings_site_discount").val() <= 0) {
          alert(
            "Zero, Empty & Negative Discount On Site Are Not Allowed. Please enter valid value"
          );
          e.preventDefault();
        }
        
      }
    }
    if (jQuery("#pstds_settings_store_time_status").prop("checked")) {
      if (
        $("#pstds_settings_timezone").val() == "" ||
        $("#pstds_settings_store_open_time").val() == "" ||
        $("#pstds_settings_store_closed_time").val() == ""
      ) {
        alert("Please fill all the required fields!");
        e.preventDefault();
      }
      var open_time = $("#pstds_settings_store_open_time").val();
      var closed_time = $("#pstds_settings_store_closed_time").val();

      // if (open_time > closed_time) {
      //   alert("Store open time must be smaller than the closed time!");
      //   e.preventDefault();
      // }
      if ($("#pstds_settings_cart_empty_notice").val() == "") {
        alert("Empty cart notice is required!");
        e.preventDefault();
      }
      if (
        $("#pstds_settings_store_type").val() == "choose" ||
        $("#pstds_settings_store_type").val() == ""
      ) {
        alert("Please select store type for time");
        e.preventDefault();
      }
      if ($("#pstds_settings_store_type").val() == "product") {
        if ($("#pstds_settings_store_product_time_ids").val() == "") {
          alert("Please select atleast one product!");
          e.preventDefault();
        }
        if ($("#pstds_settings_product_closed_notice").val() == "") {
          alert("Product empty cart notice is required!");
          e.preventDefault();
        }
      } else if ($("#pstds_settings_store_type").val() == "site") {
        if ($("#pstds_settings_store_closed_notice").val() == "") {
          alert("Store closed notice is required!");
          e.preventDefault();
        }
      } else if ($("#pstds_settings_store_type").val() == "category") {
        if ($("#pstds_settings_store_category_time_ids").val() == "") {
          alert("Please select atleast one category!");
          e.preventDefault();
        }
        if ($("#pstds_settings_category_closed_notice").val() == "") {
          alert("Category empty cart notice is required!");
          e.preventDefault();
        }
      }
    }
  });
});

function toggleAlreadySaleNotice() {
  if (!jQuery("#pstds_settings_store_sale_discount_status").prop("checked")) {
    toggleRows([{ id: "pstds_settings_sale_product_notice", status: "hide" }]);
  } else {
    toggleRows([{ id: "pstds_settings_sale_product_notice", status: "show" }]);
  }
}

function toggleBannerDiscount() {
  if (!jQuery("#pstds_settings_store_discount_banner_status").prop("checked")) {
    if (jQuery("#pstds_settings_discount_type").val() == "site") {
      toggleRows([
        { id: "pstds_settings_store_discount_banner", status: "hide" },
      ]);
    } else {
      toggleRows([
        { id: "pstds_settings_store_discount_banner", status: "hide" },
        { id: "pstds_settings_store_discount_banner_status", status: "hide" },
      ]);
    }
  } else {
    toggleRows([
      { id: "pstds_settings_store_discount_banner", status: "show" },
      { id: "pstds_settings_store_discount_banner_status", status: "show" },
    ]);
  }
}

function toggleStoreTimeType(
  type = jQuery("#pstds_settings_store_type").val()
) {
  var objectArray;
  if (type == "product") {
    objectArray = [
      { id: "pstds_settings_store_product_time_ids", status: "show" },
      { id: "pstds_settings_store_category_time_ids", status: "hide" },
      { id: "pstds_settings_product_closed_notice", status: "show" },
      { id: "pstds_settings_category_closed_notice", status: "hide" },
      { id: "pstds_settings_store_closed_notice", status: "hide" },
    ];
  } else if (type == "category") {
    objectArray = [
      { id: "pstds_settings_store_product_time_ids", status: "hide" },
      { id: "pstds_settings_store_category_time_ids", status: "show" },
      { id: "pstds_settings_product_closed_notice", status: "hide" },
      { id: "pstds_settings_category_closed_notice", status: "show" },
      { id: "pstds_settings_store_closed_notice", status: "hide" },
    ];
  } else {
    objectArray = [
      { id: "pstds_settings_store_product_time_ids", status: "hide" },
      { id: "pstds_settings_store_category_time_ids", status: "hide" },
      { id: "pstds_settings_product_closed_notice", status: "hide" },
      { id: "pstds_settings_category_closed_notice", status: "hide" },
      { id: "pstds_settings_store_closed_notice", status: "show" },
    ];
  }

  toggleRows(objectArray);
}

function checkIfSettingsAreDisabled() {
  var timeSettingsIds = [
    "pstds_settings_timezone",
    "pstds_settings_store_open_time",
    "pstds_settings_store_closed_time",
    "pstds_settings_store_closed_notice",
    "pstds_settings_category_closed_notice",
    "pstds_settings_product_closed_notice",
    "pstds_settings_store_product_time_ids",
    "pstds_settings_store_category_time_ids",
    "pstds_settings_store_type",
    "pstds_settings_cart_empty_notice",
  ];
  var discountSettingsIds = [
    "pstds_settings_discount_type",
    "pstds_settings_store_sale_discount_status",
    "pstds_settings_product_discount",
    "pstds_settings_product_discount_ids",
    "pstds_settings_category_discount_ids",
    "pstds_settings_site_discount",
    "pstds_settings_category_discount",
    "pstds_settings_discount_rule",
    "pstds_settings_store_discount_banner_status",
    "pstds_settings_store_discount_banner",
    "pstds_settings_sale_product_notice",
  ];

  if (!jQuery("#pstds_settings_store_time_status").prop("checked")) {
    jQuery.each(timeSettingsIds, function (indexInArray, id_val) {
      toggleRows([{ id: id_val, status: "hide" }]);
    });
  } else {
    jQuery.each(timeSettingsIds, function (indexInArray, id_val) {
      toggleRows([{ id: id_val, status: "show" }]);
    });
    discountTypeToggle();
    toggleStoreTimeType();
  }

  if (!jQuery("#pstds_settings_discount_status").prop("checked")) {
    jQuery.each(discountSettingsIds, function (indexInArray, id_val) {
      toggleRows([{ id: id_val, status: "hide" }]);
    });
  } else {
    jQuery.each(discountSettingsIds, function (indexInArray, id_val) {
      toggleRows([{ id: id_val, status: "show" }]);
      toggleBannerDiscount();
      discountTypeToggle();
    });
  }
}

function toggleRows(idsArray) {
  jQuery.each(idsArray, function (indexInArray, object) {
    let status = object.status;
    jQuery("#" + object.id)
      .closest("tr")
      [status]();
  });
}

function getProForm(){
  var url = document.location.href+"&pro_form=yes";
  window.location = url;
}

// function deleteLicensed(){
//   alert("Licensed deleted!");
// }

function deleteLicensed(){
  if (confirm('Are you sure do you wan\'t to delete licence')) {
    jQuery.ajax({
      url: pstds_ajax_object.ajax_url,
      type : "get",
      data: {
          action : 'pstds_delete_licence_ajax',
      },   
      success: function(response) {
        // console.log(response);
        alert("Licensed deleted!");
        location.reload();
      }
  });

  }
}

function discountTypeToggle(
  type = jQuery("#pstds_settings_discount_type").val()
) {
  var objectArray;
  if (type == "site") {
    objectArray = [
      { id: "pstds_settings_site_discount", status: "show" },
      { id: "pstds_settings_store_discount_banner", status: "show" },
      { id: "pstds_settings_store_discount_banner_status", status: "show" },
      { id: "pstds_settings_product_discount", status: "hide" },
      { id: "pstds_settings_product_discount_ids", status: "hide" },
      { id: "pstds_settings_category_discount_ids", status: "hide" },
      { id: "pstds_settings_category_discount", status: "hide" },
    ];
  } else if (type == "product") {
    objectArray = [
      { id: "pstds_settings_site_discount", status: "hide" },
      { id: "pstds_settings_store_discount_banner", status: "hide" },
      { id: "pstds_settings_store_discount_banner_status", status: "hide" },
      { id: "pstds_settings_category_discount_ids", status: "hide" },
      { id: "pstds_settings_category_discount", status: "hide" },
      { id: "pstds_settings_product_discount", status: "show" },
      { id: "pstds_settings_product_discount_ids", status: "show" },
    ];
  } else if (type == "category") {
    objectArray = [
      { id: "pstds_settings_site_discount", status: "hide" },
      { id: "pstds_settings_store_discount_banner", status: "hide" },
      { id: "pstds_settings_store_discount_banner_status", status: "hide" },
      { id: "pstds_settings_product_discount", status: "hide" },
      { id: "pstds_settings_product_discount_ids", status: "hide" },
      { id: "pstds_settings_category_discount_ids", status: "show" },
      { id: "pstds_settings_category_discount", status: "show" },
    ];
  } else {
    objectArray = [
      { id: "pstds_settings_site_discount", status: "hide" },
      { id: "pstds_settings_store_discount_banner", status: "hide" },
      { id: "pstds_settings_store_discount_banner_status", status: "hide" },
      { id: "pstds_settings_product_discount", status: "hide" },
      { id: "pstds_settings_product_discount_ids", status: "hide" },
      { id: "pstds_settings_category_discount_ids", status: "hide" },
      { id: "pstds_settings_category_discount", status: "hide" },
    ];
  }

  toggleRows(objectArray);
}
