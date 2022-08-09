    /* eslint-env es6 */
    /* eslint-disable require-jsdoc */


    // let button = document.querySelector('[data-id="modal-shower"]');
    // let modal = document.querySelector('.exam-modal');
    // button.addEventListener('click', function() { modal.classList.remove('d-none'); });

    window.addEventListener('DOMContentLoaded', (event) => {
        //console.log('DOM fully loaded and parsed');

        // Select the node that will be observed for mutations
        //  const targetNode = document.querySelector('div.confirmation-buttons .btn-primary');
        const targetNode = document.getElementById("page-mod-quiz-summary");

        // Options for the observer (which mutations to observe)
        const config = { attributes: true, childList: true, subtree: true };

        // Callback function to execute when mutations are observed
        const callback = function(mutationsList, observer) {
            // Use traditional 'for loops' for IE 11
            for (const mutation of mutationsList) {
                if (mutation.type === 'childList') {
                    var button = document.querySelector('.confirmation-buttons input.btn-primary');
                    if (button != null) {
                        //console.log('A child node has been added or removed.');
                        //var button = document.querySelector('.confirmation-buttons input.btn-primary');
                        var modal = document.querySelector('.exam-modal');
                        var bodySu = document.querySelector('div#page-wrapper');
                        button.addEventListener('click', function() {
                            modal.classList.remove('d-none');
                            bodySu.classList.add('d-none');
                            setTimeout(() => { console.log('Checked'); }, 5000);
                            //observer.disconnect();
                        });
                    }
                    //observer.disconnect();
                }
                /*else if (mutation.type === 'attributes') {
                                   console.log('The ' + mutation.attributeName + ' attribute was modified.');
                               }*/
            }
        };

        // Create an observer instance linked to the callback function
        const observer = new MutationObserver(callback);

        // Start observing the target node for configured mutations
        observer.observe(targetNode, config);

        // Later, you can stop observing
        //observer.disconnect();
    });