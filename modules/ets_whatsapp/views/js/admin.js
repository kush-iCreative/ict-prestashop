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
   $(document).on('click','.ets-wa-group-number-phone .js-dropdown-item',function(){
      $('#ETS_WA_CALL_PREFIX').val($(this).data('value'));
      $('.input-group-addon.call_prefix').html('+'+$(this).data('call_prefix')); 
      $('.ets-wa-group-number-phone .dropdown-toggle').html($(this).html());
   }); 
   $(document).keyup(function(e){
        if($('.ets-wa-group-number-phone .dropdown.open').length)
        {
            $('.ets-wa-group-number-phone .js-dropdown-item').show();
            if(e.key!='')
            {
                var key_current = e.key;
                $('.ets-wa-group-number-phone .js-dropdown-item').each(function(){
                    var $this_item = $(this);
                    if($this_item.find('span').html().toLowerCase().indexOf(key_current)==0)
                    {
                        $('.js-choice-options').animate({scrollTop: $('.js-choice-options').scrollTop() + $this_item.position().top});
                        return false;
                    }
                });
            }
            return false;
        }
   });
   $(document).on('change','#ETS_WA_ICON',function(){
      var file = this.files && this.files[0];
      if (!file) {
         return;
      }
      var reader = new FileReader();
      reader.onload = function(e){
         var $preview = $('.ets_wa_uploaded_preview');
         var $label = $('.ets_wa_uploaded_label');
         var $img = $preview.find('.ets_wa_uploaded_img');
         if (!$img.length) {
            $img = $('<img/>', {'class':'ets_wa_uploaded_img'});
            $preview.prepend($img);
         }
         $img.attr('src', e.target.result);
         $preview.removeClass('hidden');
         $label.removeClass('hidden');
      };
      reader.readAsDataURL(file);
   });
   $(document).on('click','.ets_wa_icon_delete',function(e){
      e.preventDefault();
      var $btn = $(this);
      var url = $btn.data('delete-url');
      if (!url) {
         return;
      }
      if (!confirm('Delete this image?')) {
         return;
      }
      $.ajax({
         type: 'POST',
         url: url,
         data: {
            ajax: 1,
            action: 'deleteIcon'
         },
         dataType: 'json'
      }).done(function(resp){
         if (resp && resp.success) {
            var $preview = $('.ets_wa_uploaded_preview');
            var $label = $('.ets_wa_uploaded_label');
            $preview.addClass('hidden');
            $preview.find('.ets_wa_uploaded_img').attr('src','');
            $('#ETS_WA_ICON').val('');
            $label.addClass('hidden');
         } else if (resp && resp.message) {
            alert(resp.message);
         } else {
            alert(typeof ETS_WA_DELETE_FAILED !== 'undefined' ? ETS_WA_DELETE_FAILED : 'Delete failed.');
         }
      }).fail(function(){
         alert(typeof ETS_WA_DELETE_FAILED !== 'undefined' ? ETS_WA_DELETE_FAILED : 'Delete failed.');
      });
   });
});
