jQuery(document).ready(function () {
  var success_message = jQuery(".success-message");
  // search for post or title
  jQuery("#search").keyup(function () {
    let search = jQuery(this).val();
    if (search == "") {
      jQuery('input[name="id"]').val("");
    }
    jQuery.ajax({
      url: ajaxurl,
      method: "POST",
      data: {
        action: "admin_hook",
        search: search,
        nonce: ajax_admin.nonce,
      },
      success: function (res) {
        jQuery("#response").html(res).show();
      },
    });
  });
  jQuery("#submit-btn").click(function (e) {
    e.preventDefault();
    let id = jQuery('input[name="id"]').val();
    let title = jQuery('input[name="title"]').val();
    let description = jQuery('textarea[name="description"]').val();

    //flag update or post method
    let flag = jQuery('input[name="method"]').val();
    if (title == "" || description == "") {
      success_message.html(
        "<span class='alert alert-danger'>All fields are required</span>"
      );
      return;
    }

    // extra protection
    if (id == "") {
      success_message.html(
        "<span class='alert alert-danger'>Something went wrong. Please refresh & try again</span>"
      );
      return;
    }
    jQuery.ajax({
      url: ajaxurl,
      method: "POST",
      dataType: "JSON",
      data: {
        action: "save_form",
        id: id,
        title: title,
        description: description,
        flag: flag,
        nonce: ajax_admin.nonce,
      },
      success: function (res) {
        if (res.success == false) {
          success_message.html(
            "<span class='alert alert-danger'>" + res.data + "</span>"
          );
          return;
        } else if (res.success == true) {
          success_message.html(
            "<span class='alert alert-success'>" + res.data + "</span>"
          );
          jQuery(".seo-form")[0].reset();
          // reset previous id
          jQuery('input[name="id"]').val("");
          // reset method default method is post
          jQuery('input[name="method"]').val("post");

          jQuery(".search-wrapper").removeClass("d-none");
          jQuery(".meta-wrapper").addClass("d-none");
        } else {
          success_message.html(
            "<span class='alert alert-danger'>Something went wrong.</span>"
          );
        }
      },
    });
  });

  // hide ajax response list on outside click
  document.addEventListener("click", (evt) => {
    const response_div = jQuery("#response");
    let targetElement = evt.target; // clicked element

    do {
      if (targetElement == response_div) {
        // Do nothing
        // document.getElementById("flyout-debug").textContent = "Clicked inside!";
        return;
      }
      // Go up the DOM
      targetElement = targetElement.parentNode;
    } while (targetElement);

    // This is a click outside.
    jQuery("#response").hide();
  });

  // .btn-next click show rest of form and hide search form
  jQuery("#btn-next").click(function (e) {
    let id = jQuery('input[name="id"]').val();
    let search = jQuery('input[name="search"]').val();
    if (search == "") {
      success_message.html(
        "<span class='alert alert-danger'>Search input field is required</span>"
      );
      return;
    }

    //reset error message
    success_message.html("");

    jQuery.ajax({
      url: ajaxurl,
      method: "POST",
      dataType: "JSON",
      data: {
        action: "process_form",
        id: id,
        search: search,
        nonce: ajax_admin.nonce,
      },
      success: function (res) {
        // jQuery(".seo-form")[0].reset();
        if (res.success == true) {
          // if not blank means title or description alrady exsits in database
          // and this is update
          if (res.data.desc != "" || res.data.title != "") {
            jQuery('input[name="method"]').val("update");
          } else {
            jQuery('input[name="method"]').val("post");
          }
          jQuery('input[name="title"]').val(res.data.title);
          jQuery('textarea[name="description"]').val(res.data.desc);
          jQuery(".search-wrapper").addClass("d-none");
          jQuery(".meta-wrapper").removeClass("d-none");
        } else {
          success_message.html(
            "<span class='alert alert-danger'>" + res.data + "</span>"
          );
          return;
        }
      },
    });
  });

  // .btn-back click show search form and hide rest of form
  jQuery("#btn-back").click(function (e) {
    jQuery(".search-wrapper").removeClass("d-none");
    jQuery(".meta-wrapper").addClass("d-none");
  });

  // Get the modal
  var modal = document.getElementById("my-modal");

  // Get the button that opens the modal
  var modal_preview = document.getElementById("modal-preview");

  // Get the <span> element that closes the modal
  var close_modal = document.getElementsByClassName("close-modal")[0];

  // When the user clicks the button, open the modal
  modal_preview.onclick = function () {
    // Get user entered title from input field
    let user_entered_title = jQuery("#title").val();

    //select span
    let site_title_span = jQuery(".site-title");

    // put into site title span for checking width
    site_title_span.html(user_entered_title);

    // empty array for pushing string until width more than 600
    var perfect_array = [];

    var combined_site_title = "";

    // checking width
    if (site_title_span.width() > 600) {
      // site title span split into array so we can check after every value
      let site_title_array = site_title_span.html().split(" ");
      site_title_array.forEach((element) => {
        //concat string
        combined_site_title += element + " ";
        //put concat string into span so we can check width every time of loop
        site_title_span.html(combined_site_title);

        // check site title span width
        if (site_title_span.width() < 598) {
          // less than 598 then push to our new array
          perfect_array.push(element);
        } else {
          // ignore if greater than
          return;
        }
      });
      // at the end of the string place "..."
      perfect_array.push("...");

      // make string from array
      var perfect_string = perfect_array.join(" ");

      // put perfect string
      site_title_span.html(perfect_string);
    }

    // get value of meta description
    var user_entered_meta_description = jQuery("#description").val();
    // modal span of meta description
    var modal_meta_description = jQuery(".site-meta-description");
    if (user_entered_meta_description.length > 160) {
      var perfect_meta_desc = user_entered_meta_description.slice(0, 160);
      perfect_meta_desc = perfect_meta_desc.substring(
        0,
        perfect_meta_desc.lastIndexOf(" ")
      );
      modal_meta_description.html(perfect_meta_desc + " ...");
    } else {
      modal_meta_description.html(user_entered_meta_description);
    }
    // show modal with preview content
    modal.style.visibility = "visible";
    modal.style.opacity = 1;
  };

  // When the user clicks on <span> (x), close the modal
  close_modal.onclick = function () {
    modal.style.visibility = "hidden";
    modal.style.opacity = 0;
  };

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.visibility = "hidden";
      modal.style.opacity = 0;
    }
  };
});
