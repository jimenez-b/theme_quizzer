/* eslint-disable linebreak-style */
/* eslint-disable require-jsdoc */
define(['jquery'], function($) {

    return {
        init: function() {
            $("#quiz-timer").contents().get(0).nodeValue = "";
        }
    };
});