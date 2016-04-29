/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Important! The original DragDrop.js must be replaced with this one.
 * so we undefine the module via requirejs!
 */
requirejs.undef("TYPO3/CMS/Backend/LayoutModule/DragDrop");

/**
 * this JS code does the drag+drop logic for the Layout module (Web => Page)
 * based on jQuery UI
 */
define(['jquery', 'jquery-ui/sortable'], function ($) {
    'use strict';

    /**
     *
     * @type {{contentIdentifier: string, dragIdentifier: string, dropZoneAvailableIdentifier: string, dropPossibleClass: string, sortableItemsIdentifier: string, columnIdentifier: string, columnHolderIdentifier: string, addContentIdentifier: string, langClassPrefix: string}}
     * @exports TYPO3/CMS/Backend/LayoutModule/DragDrop
     */
    var DragDrop = {
        contentIdentifier: '.t3js-page-ce',
        dragIdentifier: '.t3js-page-ce-draghandle',
        dropZoneAvailableIdentifier: '.t3js-page-ce-dropzone-available',
        dropPossibleClass: 't3-page-ce-dropzone-possible',
        sortableItemsIdentifier: '.t3js-page-ce-sortable',
        columnIdentifier: '.t3js-page-column',
        columnHolderIdentifier: '.t3js-page-columns',
        addContentIdentifier: '.t3js-page-new-ce',
        langClassPrefix: '.t3js-sortable-lang-'
    };

    /**
     * initializes Drag+Drop for all content elements on the page
     */
    DragDrop.initialize = function() {
        // setting the style of all divs with data-language-uid to 'postion:relative' in order to prevend during dragging a wrong positioning
        $('div[data-language-uid]').css('position','relative');

        $('td[data-language-uid]').each(function() {
            var connectWithClassName = DragDrop.langClassPrefix + $(this).data('language-uid');
            $(connectWithClassName).sortable({
                items: DragDrop.sortableItemsIdentifier,
                connectWith: connectWithClassName,
                handle: DragDrop.dragIdentifier,
                distance: 1,
                cursor: 'move',
                helper: 'clone',
                cursorAt: { left: -10, top: -10 },
                placeholder: DragDrop.dropPossibleClass,
                tolerance: 'pointer',
                start: function(e, ui) {
                    DragDrop.onSortStart($(this), ui);
                    $(this).addClass('t3-is-dragged');
                },
                stop: function(e, ui) {
                    DragDrop.onSortStop($(this), ui);
                    $(this).removeClass('t3-is-dragged');
                },
                change: function(e, ui) {
                    DragDrop.onSortChange($(this), ui);
                },
                update: function(e, ui) {
                    if (this === ui.item.parent()[0]) {
                        DragDrop.onSortUpdate($(this), ui);
                    }
                }
            }).disableSelection();
        });
    };

    /**
     * Called when an item is about to be moved
     *
     * @param {Object} $container
     * @param {Object} ui
     */
    DragDrop.onSortStart = function($container, ui) {
        var $item = $(ui.item),
            $helper = $(ui.helper),
            $placeholder = $(ui.placeholder);

        //$placeholder.height($item.height() - $helper.find(DragDrop.addContentIdentifier).height()-20);
        $placeholder.height(40);
        DragDrop.changeDropzoneVisibility($container, $item, $helper);

        // show all dropzones, except the own
        $helper.find(DragDrop.dropZoneAvailableIdentifier).removeClass('active');
        $container.parents(DragDrop.columnHolderIdentifier).find(DragDrop.addContentIdentifier).hide();
    };

    /**
     * Called when the sorting stopped
     *
     * @param {Object} $container
     * @param {Object} ui
     */
    DragDrop.onSortStop = function($container, ui) {
        var $allColumns = $container.parents(DragDrop.columnHolderIdentifier);
        $allColumns.find(DragDrop.addContentIdentifier).show();
        $allColumns.find(DragDrop.dropZoneAvailableIdentifier + '.active').removeClass('active');
    };

    /**
     * Called when the index of the element in the sortable list has changed
     *
     * @param {Object} $container
     * @param {Object} ui
     */
    DragDrop.onSortChange = function($container, ui) {
        var $helper = $(ui.helper),
            $placeholder = $(ui.placeholder);

        DragDrop.changeDropzoneVisibility($container, $placeholder, $helper);
    };

    /**
     *
     * @param {Object} $container
     * @param {Object} $subject
     * @param {Object} $helper
     */
    DragDrop.changeDropzoneVisibility = function($container, $subject, $helper) {
        var $prev = $subject.prev(':visible'),
            droppableClassName = DragDrop.langClassPrefix + $container.data('language-uid');

        if ($prev.length === 0) {
            $prev = $subject.prevUntil(':visible').last().prev();

        }
        $container.parents(DragDrop.columnHolderIdentifier).find(droppableClassName).find(DragDrop.contentIdentifier + ':not(.ui-sortable-helper)').not($prev).find(DragDrop.dropZoneAvailableIdentifier).addClass('active');
        $prev.find('> '+DragDrop.dropZoneAvailableIdentifier + '.active').removeClass('active');
        $helper.find(DragDrop.dropZoneAvailableIdentifier + '.active').removeClass('active');
    };

    /**
     * Called when the new position of the element gets stored
     *
     * @param {Object} $container
     * @param {Object} ui
     */
    DragDrop.onSortUpdate = function($container, ui) {
        var $selectedItem = $(ui.item),
            contentElementUid = parseInt($selectedItem.data('uid')),
            parameters = {};

        // send an AJAX requst via the AjaxDataHandler
        if (contentElementUid > 0) {

            // add the information about a possible column position change
            parameters['data'] = {tt_content: {}};
            parameters['data']['tt_content'][contentElementUid] = {colPos: parseInt($container.data('colpos'))};
        }

        var targetContentElementUid = $selectedItem.prev().data('uid');
        // the item was moved to the top of the colPos, so the page ID is used here
        if (typeof targetContentElementUid === 'undefined') {
            // the actual page is needed
            targetContentElementUid = parseInt($container.find(DragDrop.contentIdentifier).first().data('page'));
        } else {
            // the negative value of the content element after where it should be moved
            targetContentElementUid = parseInt(targetContentElementUid) * -1;
        }

        parameters['cmd'] = {tt_content: {}};
        parameters['cmd']['tt_content'][contentElementUid] = {move: targetContentElementUid};
        // fire the request, and show a message if it has failed
        require(['TYPO3/CMS/Backend/AjaxDataHandler'], function(DataHandler) {
            DataHandler.process(parameters).done(function(result) {
                if (result.hasErrors) {
                    $container.sortable('cancel');
                }
            });
        });
    };

    $(DragDrop.initialize);

        return DragDrop;
});
