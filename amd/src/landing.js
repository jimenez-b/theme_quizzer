/* eslint-disable linebreak-style */
/* eslint-disable require-jsdoc */
define(['jquery'], function($) {

    return {
        init: function() {
            // Put whatever you like here. $ is available
            // to you as normal.

            //hide the quiz default description
            $('body#page-mod-quiz-view div#intro p').addClass("d-none");

            $('.quizstartbuttondiv button[type="submit"]').prop('disabled', true);
            $('input#instructions_verification, input#copyrightnotice').click(function() {
                if ($("input#instructions_verification").prop("checked") == true &&
                    $("input#copyrightnotice").prop("checked") == true) {
                    $('.quizstartbuttondiv button[type="submit"]').prop('disabled', false);
                } else {
                    $('.quizstartbuttondiv button[type="submit"]').prop('disabled', true);
                }
            });
        },
        qrchanges: function() {
            // Function to show/hide the upper div on the landing page when the qrcode is present
            var upper = document.getElementById("upper-content");
            if (document.querySelector(".k1-qrcode")) {
                //if (document.querySelector(".k1-qrcode").style.display == "none") {
                //    upper.classList.remove("d-none");
                //} else {
                upper.classList.add("d-none");
                //}
            } else {
                upper.classList.remove("d-none");
            }
        },
        rearranger: function() {
            //function to rearrange menu items on the quiz editing submenu
            if (document.querySelector('a[aria-label="Actions menu"]')) {
                const actions = document.querySelector('a[aria-label="Actions menu"]');
                const actionid = actions.id;
                const menuid = actionid.charAt(actionid.length - 1);
                const submenuid = "action-menu-" + menuid + "-menu";
                var submenu = document.querySelector("#" + submenuid);
                var subelements = document.querySelectorAll("#" + submenuid + " .dropdown-item");
                var [i, a] = [0, 0];
                while (i < subelements.length) {
                    if (subelements[i].innerHTML.indexOf("Locally assigned roles") !== -1) {
                        a = i;
                    }
                    i++;
                }
                var column1 = document.createElement("div");
                column1.classList.add("column-1");
                var divider = document.createElement("div");
                divider.classList.add("divider");
                var column2 = document.createElement("div");
                column2.classList.add("column-2");

                for (let i = 0; i < subelements.length; i++) {
                    if (i < a) {
                        column1.appendChild(subelements[i]);
                    } else {
                        column2.appendChild(subelements[i]);
                    }
                }
                submenu.appendChild(column1);
                submenu.appendChild(divider);
                submenu.appendChild(column2);
            }
        }
    };
});