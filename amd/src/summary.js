/* eslint-disable linebreak-style */
/* eslint-disable require-jsdoc */
define([], function() {

    return {
        init: function() {
            let buttons = document.getElementsByClassName('sortbtn');

            function changeState(a, button) {
                let buttons2 = document.getElementsByClassName('sortbtn');
                for (let i = 0; i < buttons2.length; i++) {
                    buttons2[i].setAttribute('data-filter', 'disabled');
                }
                button.setAttribute('data-filter', 'enabled');
                let rows = document.getElementsByClassName('answerrow');
                var classCompare = '';
                switch (button.name) {
                    case "invalidanswer":
                    case "filterall":
                        classCompare = button.name;
                        break;
                    case "answered":
                        classCompare = 'answersaved';
                        break;
                    case "unsure":
                        classCompare = 'flagged';
                        break;
                    case "unanswered":
                        classCompare = 'notyetanswered';
                        break;
                    default:
                        classCompare = "it's never lupus";
                }
                for (let i = 0; i < rows.length; i++) {
                    if (classCompare == 'filterall') {
                        rows[i].classList.remove('d-none');
                    } else {
                        if (rows[i].classList.contains(classCompare)) {
                            rows[i].classList.add('show');
                            rows[i].classList.remove('d-none');
                        } else {
                            rows[i].classList.add('d-none');
                            rows[i].classList.remove('show');
                        }
                    }
                }
            }
            /*
            Array.from(buttons).forEach(button =>
                button.addEventListener("click", showNumber));

            function showNumber(event) { // Listener can access its triggering event
                const button = event.target; // event's `target` property is useful
                console.log('test to see what happens ' + button.classList);
                let buttons2 = document.getElementsByClassName('sortbtn');
                for (i = 0; i < buttons2.length; i++) {
                    buttons2[i].setAttribute('data-filter', 'disabled');
                }
                button.setAttribute('data-filter', 'enabled');
                //if (button.value != 5) { screen.innerHTML = button.value; }
                //else { screen.innerHTML = 5; }
            }*/
            buttons[0].addEventListener("click", function() {
                changeState(0, buttons[0]);
            });
            buttons[1].addEventListener("click", function() {
                changeState(1, buttons[1]);
            });
            buttons[2].addEventListener("click", function() {
                changeState(2, buttons[2]);
            });
            buttons[3].addEventListener("click", function() {
                changeState(3, buttons[3]);
            });
            buttons[4].addEventListener("click", function() {
                changeState(4, buttons[4]);
            });
        },
        modalSummary: function() {
            let button = document.querySelector('[data-id="modal-shower"]');
            // Let button = document.querySelector('.confirmation-buttons .btn.btn-primary');
            let modal = document.querySelector('.exam-modal');
            button.addEventListener('click', function() {
                modal.classList.remove('d-none');
            });
        }
    };
});