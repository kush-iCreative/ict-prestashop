/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
$(document).ready(function(){
    if (typeof ets_wa_params !== 'undefined' && ets_wa_params.number_phone) {
        ets_wa_render_sticker();
    }

    $(document).on('click','.ets_wa_whatsapp_block a',function(e){
        if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
           	e.preventDefault();
            window.location.href = $(this).attr('data-mobile-href');
        }
    });

    function ets_wa_render_sticker() {
        var params = ets_wa_params;
        var style = document.createElement('style');
        var css = '';

        css += '.ets_wa_whatsapp_block.right_center { right: ' + (params.adjust_right || 0) + 'px; bottom: 50%; transform: translateY(50%); }';
        css += '.ets_wa_whatsapp_block.right_bottom { right: ' + (params.adjust_right || 0) + 'px; bottom: ' + (params.adjust_bottom || 0) + 'px; }';
        css += '.ets_wa_whatsapp_block.left_center { left: ' + (params.adjust_left || 0) + 'px; bottom: 50%; transform: translateY(50%); }';
        css += '.ets_wa_whatsapp_block.left_bottom { left: ' + (params.adjust_left || 0) + 'px; bottom: ' + (params.adjust_bottom || 0) + 'px; }';

        if (params.button_color) {
            css += '.ets_wa_whatsapp_block .ets_wa_title { background-color: ' + params.button_color + '; }';
        }
        if (typeof params.button_radius !== 'undefined') {
            css += '.ets_wa_whatsapp_block .ets_wa_title { border-radius: ' + params.button_radius + 'px; }';
        }

        style.type = 'text/css';
        if (style.styleSheet) {
            style.styleSheet.cssText = css;
        } else {
            style.appendChild(document.createTextNode(css));
        }
        document.head.appendChild(style);

        var whatsapp_url = 'https://wa.me/send?phone=' + params.call_prefix + params.number_phone;
        var mobile_url = 'https://api.whatsapp.com/send?phone=' + params.call_prefix + params.number_phone;
        
        if (params.send_current_url) {
            whatsapp_url += '&text=' + encodeURIComponent(params.send_current_url);
            mobile_url += '&text=' + encodeURIComponent(params.send_current_url);
        }

        var sticker_html = '<div class="ets_wa_whatsapp_block ' + params.display_position + '">' +
            '<a target="_blank" data-mobile-href="' + mobile_url + '" href="' + whatsapp_url + '">' +
                '<img src="' + params.icon_url + '" />' +
            '</a>';
        
        if (params.display_title) {
            sticker_html += '<p class="ets_wa_title">' + params.display_title + '</p>';
        }
        sticker_html += '</div>';

        $('body').append(sticker_html);
    }
});