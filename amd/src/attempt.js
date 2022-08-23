/* eslint-disable linebreak-style */
/* eslint-disable require-jsdoc */
/* eslint-disable max-len */
/* eslint-disable no-console */

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AMD module to create a modal to make images in a quiz fullscreen.
 *
 * @module     theme/quizzer
 * @package    theme_quizzer
 * @copyright  2022 onwards Brandon Jimenez <brandon.jimenez@concordia.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define(['core/modal_factory'], function(ModalFactory) {
    var selector = '#page-mod-quiz-attempt #page-wrapper #page #region-main .que:not(.informationitem) .content .formulation .qtext img';
    document.querySelectorAll(selector).forEach((item) => {
        item.addEventListener('click', (event) => {
            var clickedLink = event.currentTarget;
            ModalFactory.create({
                    title: clickedLink.alt,
                    body: '<div class="modal-enlarged-image">' + clickedLink.outerHTML + '</div>',
                    classes: [{
                        classes: "modal-enlarged-container"
                    }],
                })
                .done(function(modal) {
                    modal.setLarge();
                    modal.show();
                });
        });
    });

});