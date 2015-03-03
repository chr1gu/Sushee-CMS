/**
 * Created by chrigu on 10/02/15.
 */

$( "#push-form" ).submit( function(e) {
    // cancel submission
    var confirm = window.confirm("Bist du sicher? Dieser Schritt kann nicht Rückgängig gemacht werden");
    if (!confirm)
        e.preventDefault();

    //if ($('[name="message"]').val().length === 0)
    //    e.preventDefault();
});

$( "#push-form" ).on( "submit-success", function() {
    // custom success handling
    // clear form
    $("input[type=text], textarea").val("");
});

$( "#push-form" ).on( "submit-error", function() {
    // custom error handling
});
