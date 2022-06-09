jQuery(document).ready(function () {
    jQuery.each(obj, function (index, value) {
        var cont = index + ": " + value;
        var elem = jQuery("<p></p>").text(cont);
        jQuery(elem).appendTo('#rewrite');
    });
});