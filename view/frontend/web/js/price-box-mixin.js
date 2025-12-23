define([
    'jquery',
    'mage/template',
    'text!Hmh_SpCountDown/template/special-price-message.html'
], function ($, mageTemplate, messageTemplate) {
    'use strict';

    return function () {
        $.widget('mage.priceBox', $.mage.priceBox, {
            _create: function () {
                this._super();
                this._initCountdown();
            },

            reloadPrice: function () {
                this._super();
                this._initCountdown(true);
            },

            _initCountdown: function (force) {
                const config = this.options.priceConfig;
                const specialToDate = this._resolveSpecialToDate(config);

                if (!force && this._countdownInitialized) {
                    return;
                }

                if (!specialToDate) {
                    this._clearCountdown();
                    return;
                }

                const endDate = this._getEndOfDayDate(specialToDate);

                if (!endDate) {
                    this._clearCountdown();
                    return;
                }

                this._countdownInitialized = true;
                this._renderCountdown(endDate);
            },

            _resolveSpecialToDate: function (config) {
                if (!config) {
                    return null;
                }

                const childId = this._getSelectedChildProductId();

                if (childId && config.optionSpecialEndDate && config.optionSpecialEndDate[childId]) {
                    return config.optionSpecialEndDate[childId].special_to_date || null;
                }

                return config.special_to_date || null;
            },

            _getSelectedChildProductId: function () {
                const renderer = this._getSwatchRenderer();
                if (!renderer) {
                    return null;
                }

                const selectedOptions = this._getSelectedOptionIds();

                if (!selectedOptions) {
                    return null;
                }

                const result = this._findMatchingProductId(renderer.options.jsonConfig.index, selectedOptions);
                return result || null;
            },

            _findMatchingProductId: function (index, selected) {
                return Object.keys(index).find(key => {
                    const obj = index[key];
                    if (Object.keys(obj).length !== Object.keys(selected).length) {
                        return false;
                    }
                    return Object.entries(selected).every(
                        ([k, v]) => obj[k] === v
                    );
                }) || null;
            },

            _getSwatchRenderer: function () {
                const el = document.querySelector('[data-role=swatch-options]');
                return el ? jQuery(el).data('mage-SwatchRenderer') : null;
            },

            _getSelectedOptionIds: function () {
                const selected_options = {};
                jQuery('div.swatch-attribute').each(function (k, v) {
                    const attribute_id = jQuery(v).attr('data-attribute-id');
                    const option_selected = jQuery(v).attr('data-option-selected');
                    if (!attribute_id || !option_selected) { return;}
                    selected_options[attribute_id] = option_selected;
                });

                return selected_options || null;
            },

            _renderCountdown: function (endDate) {
                this._clearCountdown();

                const message = $('<div/>').appendTo(this.element);
                this._messageElement = message;

                const updateCountdown = () => {
                    const remaining = endDate.getTime() - Date.now();

                    if (remaining <= 0) {
                        message.html(this._renderMessage($.mage.__('Special price has ended.')));
                        clearInterval(this._countdownInterval);
                        return;
                    }

                    message.html(
                        this._renderMessage(
                            $.mage.__('Special price ends in %1').replace(
                                '%1',
                                this._formatDuration(remaining)
                            )
                        )
                    );
                };

                updateCountdown();
                this._countdownInterval = setInterval(updateCountdown, 1000);
            },

            _renderMessage: function (messageText) {
                if (!this._messageTemplate) {
                    this._messageTemplate = mageTemplate(messageTemplate);
                }

                return this._messageTemplate({
                    message: messageText
                });
            },

            _formatDuration: function (milliseconds) {
                const totalSeconds = Math.floor(milliseconds / 1000);
                const days = Math.floor(totalSeconds / 86400);
                const hours = Math.floor((totalSeconds % 86400) / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                const parts = [];

                if (days) {
                    parts.push(days + $.mage.__('d'));
                }
                if (hours || days) {
                    parts.push(hours + $.mage.__('h'));
                }

                parts.push(minutes + $.mage.__('m'));
                parts.push(seconds + $.mage.__('s'));

                return parts.join(' ');
            },

            _getEndOfDayDate: function (dateString) {
                const parsed = new Date(dateString);

                if (Number.isNaN(parsed.getTime())) {
                    return null;
                }

                parsed.setHours(23, 59, 59, 999);
                return parsed;
            },

            _destroy: function () {
                this._clearCountdown();
                this._super();
            },

            _clearCountdown: function () {
                if (this._countdownInterval) {
                    clearInterval(this._countdownInterval);
                    this._countdownInterval = null;
                }

                if (this._messageElement) {
                    this._messageElement.remove();
                    this._messageElement = null;
                }
                this._countdownInitialized = false;
            }
        });

        return $.mage.priceBox;
    };
});
