/**
 * jquery-twodal plugin v0.1
 * Copyright (C) 2010-2012 BerlinOnline Stadtportal GmbH & Co. Kg.
 * Thorsten Schmitt-Rink - thorsten.schmitt-rink@berlinonline.de
 *
 * This plugin is a wrapper around twitter-bootstrap modal plugin.
 * It extends bootstrap's modal behaviour to support event registration
 * by using a data-attribute named "data-twodal-event" that can by applied to dialog (.btn)buttons.
 * You can than pass in callbacks for those events when initializing a twodal-dialog.
 * Example:
 * Html       - <div id="foodialog" class="modal">...<a data-twodal-event="registerclicked" class=".btn">register</a>..</div>
 * Javascript - var el = $('#foodialog').twodal({
 *     events: {
 *         registerclicked: function() {
               var promptVal = el.twodal('promptVal', '.foo-input')
 *             console.log("yay I can haz clicks!", promptVal);
 *         }
 *     },
 * });
 *
 * Besides the "events" option use in the above example,
 * jquery-twodal also supports all of the standard bootstrap modal options(show, keyboard, backdrop).
 */
(function( $ ) {
    // ###############################################
    // constants
    // ###############################################
    var EVT_ATTR_NAME = 'data-twodal-event';
    var EVT_TARGET_SELECTOR = '.btn';
    var OPTS_DEFAULT = {
        show: true,
        backdrop: true,
        keyboard: true,
        events: {}
    };

    // ###############################################
    // define our plugin's "public" interface.
    // ###############################################
    var api = {
        /**
         * Initialize a twodal plugin instance.
         *
         * @param object options An options hash, that may hold any of the following optional settings:
         * boolean:show default:true, Defines whether to directly show the modal dialog when initializing.
         * object:events, default:{}, A hash holding keys(event names) for events to be automatically mapped.
         */
        init: function(options) {
            // merge given options with our default options.
            options = $.merge(options || {}, OPTS_DEFAULT);
            this.find(EVT_TARGET_SELECTOR).each(function(idx, trigger)
            {
                trigger = $(trigger);
                var evt_name = trigger.attr(EVT_ATTR_NAME);
                if (evt_name)
                {
                    trigger.click(function(evt)
                    {
                        evt.preventDefault();
                        var evt_map = options.events;
                        if ('function' == typeof evt_map[evt_name])
                        {
                            evt_map[evt_name]();
                        }
                    });
                }
            });
            this.modal({
                show: options.show,
                backdrop: options.backdrop,
                keyboard: options.keyboard
            });
            return this;
        },

        /**
         * Returns the form value of the inputfield matching the given element-selector.
         * When passed two parameters, like jquery.val(), the method will then behave as a setter.
         *
         * @param string selector A query expression pointing to the desired input relative to the dialogs root.
         *
         * @return string The value of the target form input.
         */
        promptVal: function(selector)
        {
            if (2 === arguments.length)
            {
                this.find(selector).val(arguments[1]);
                return this;
            }
            return this.find(selector).val();
        },

        /**
         * Shows the dialog.
         *
         * @return jQuery A reference to this, to support a fluent api.
         */
        show: function()
        {
            this.modal('show');
            return this;
        },

        /**
         * Hides the dialog.
         *
         * @return jQuery A reference to this for the sake of fluent api.
         */
        hide: function()
        {
            this.modal('hide');
            return this;
        }
    };

    // ###############################################
    // expose our "public" methods to the plugin's namespace.
    // ###############################################
    $.fn.twodal = function(method) {
        if (api[method])
        {
            return api[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        else if ('object' === typeof method || ! method)
        {
            return api.init.apply(this, arguments);
        }
        else
        {
            $.error('Method ' +  method + ' does not exist on jQuery.twodal');
        }
    };
})(jQuery);

(function($)
{
    jQuery.fn.putCursorAtEnd = function()
    {
        return this.each(function()
        {
            if (this.createTextRange) {
                //IE
                var FieldRange = this.createTextRange();
                FieldRange.moveStart('character', this.value.length);
                FieldRange.collapse();
                FieldRange.select();
            }
            else
            {
                //Firefox and Opera
                this.focus();
                var length = this.value.length;
                this.setSelectionRange(length, length);
            }
        });
    };
})(jQuery);
