/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

define(['jquery'], function ($) {
    var Cookies = {

        set: function (key, value) {
            document.cookie = key + "=" + encodeURIComponent(JSON.stringify(value));
        },

        get: function (key) {
            var result,
                cookies = document.cookie ? document.cookie.split('; ') : [],
                pattern = /(%[0-9A-Z]{2})+/g,
                i = 0;

            for (; i < cookies.length; i++) {
                var parts = cookies[i].split('='),
                    cookie = parts.slice(1).join('=');

                if (cookie.charAt(0) === '"') {
                    cookie = cookie.slice(1, -1);
                }

                try {
                    var name = parts[0].replace(pattern, decodeURIComponent);
                    cookie = cookie.replace(pattern, decodeURIComponent);

                    if (key === name) {
                        try {
                            cookie = JSON.parse(cookie);
                        } catch (e) {
                        }

                        result = cookie;
                        break;
                    }

                } catch (e) {
                }
            }
            return result;
        },
        remove: function (key) {
            if (Cookies.get(key)) {
                document.cookie = key + '=' + '; expires=Thu, 01-Jan-70 00:00:01 GMT';
            }
        },
    };

    var FluxCollapse = {

        init: function () {
            $('[data-toggler-uid]').on('click', this.fluxCollapse);
        },

        fluxCollapse: function (event) {
            var cookie = Cookies.get('fluxCollapseStates');
            if (!Array.isArray(cookie)) {
                cookie = [];
            }

            var i, toggle = $(this),
                toggleContent = $('[data-toggle-uid='+toggle.data('togglerUid')+']'),
                fluxGrid = toggleContent.find('> .t3-grid-container > .flux-grid'),
                uid = toggleContent.data('toggleUid');
            if (fluxGrid.hasClass('flux-grid-hidden')) {
                fluxGrid.removeClass('flux-grid-hidden');
                toggle.removeClass('toggler-expand').addClass('toggler-collapse');
                for (i in cookie) {
                    if (cookie.hasOwnProperty(i)) {
                        if (cookie[i] == uid) {
                            delete cookie[i];
                        }
                    }
                }
            } else {
                fluxGrid.addClass('flux-grid-hidden');
                toggle.removeClass('toggler-collapse').addClass('toggler-expand');
                if (cookie.indexOf(uid) < 0) {
                    cookie.push(uid);
                }
            }
            // remove deleted uids
            var cookieCompress = [];
            for (i in cookie) {
                if (cookie[i] !== null && cookie[i] !== undefined) {
                    cookieCompress.push(cookie[i]);
                }
            }
            Cookies.set('fluxCollapseStates', cookieCompress);
        }
    };

    // init if document is ready
    $(document).ready(function () {
        FluxCollapse.init();
    });

    return FluxCollapse;
});
